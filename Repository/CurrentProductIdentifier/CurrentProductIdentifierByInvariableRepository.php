<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\CurrentProductIdentifier;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Barcode\ProductOfferBarcode;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Barcode\ProductVariationBarcode;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Barcode\ProductModificationBarcode;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\ProductInvariable;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use InvalidArgumentException;


final class CurrentProductIdentifierByInvariableRepository implements CurrentProductIdentifierByInvariableInterface
{
    private ProductInvariableUid|false $invariable = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}


    public function forProductInvariable(ProductInvariableUid $invariable): self
    {
        $this->invariable = $invariable;
        return $this;
    }

    public function find(): CurrentProductIdentifierResult|false
    {
        if(false === ($this->invariable instanceof ProductInvariableUid))
        {
            throw new InvalidArgumentException('Invalid Argument ProductInvariableUid');
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->addSelect('invariable.id AS product_invariable')
            ->from(ProductInvariable::class, 'invariable')
            ->where('invariable.id = :invariable')
            ->setParameter(
                key: 'invariable',
                value: $this->invariable,
                type: ProductInvariableUid::TYPE,
            );


        $dbal
            ->addSelect('product.id')
            ->addSelect('product.event')
            ->join(
                'invariable',
                Product::class,
                'product',
                'product.id = invariable.product',
            );

        $dbal
            ->join(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.event = product.event',
            );


        /**
         * ProductOffer
         */


        $dbal
            ->addSelect('current_offer.id AS offer')
            ->addSelect('current_offer.const AS offer_const')
            ->addSelect('current_offer.value AS offer_value')
            ->leftJoin(
                'product',
                ProductOffer::class,
                'current_offer',
                'current_offer.event = product.event
                AND current_offer.const = invariable.offer',
            );

        $dbal
            ->leftJoin(
                'current_offer',
                ProductOfferBarcode::class,
                'product_offer_barcode',
                'product_offer_barcode.offer = current_offer.id',
            );


        /**
         * ProductVariation
         */

        $dbal
            ->addSelect('current_variation.id AS variation')
            ->addSelect('current_variation.const AS variation_const')
            ->addSelect('current_variation.value AS variation_value')
            ->leftJoin(
                'current_offer',
                ProductVariation::class,
                'current_variation',
                'current_variation.offer = current_offer.id
                AND current_variation.const = invariable.variation',
            );

        $dbal
            ->leftJoin(
                'current_variation',
                ProductVariationBarcode::class,
                'product_variation_barcode',
                'product_variation_barcode.variation = current_variation.id',
            );


        /**
         * ProductModification
         */

        $dbal
            ->addSelect('current_modification.id AS modification')
            ->addSelect('current_modification.const AS modification_const')
            ->addSelect('current_modification.value AS modification_value')
            ->leftJoin(
                'current_variation',
                ProductModification::class,
                'current_modification',
                'current_modification.variation = current_variation.id
                AND current_modification.const = invariable.modification',
            );

        $dbal
            ->leftJoin(
                'current_modification',
                ProductModificationBarcode::class,
                'product_modification_barcode',
                'product_modification_barcode.modification = current_modification.id',
            );


        /** Штрихкоды продукта */

        $dbal->addSelect(
            "
            JSON_AGG
                    (DISTINCT
         			CASE
         			    WHEN product_modification_barcode.value IS NOT NULL
                        THEN product_modification_barcode.value
                        
                        WHEN product_variation_barcode.value IS NOT NULL
                        THEN product_variation_barcode.value
                        
                        WHEN product_offer_barcode.value IS NOT NULL
                        THEN product_offer_barcode.value
                        
                        WHEN product_info.barcode IS NOT NULL
                        THEN product_info.barcode
                        
                        ELSE NULL
                    END
                    )
                    AS barcodes",
        );


        $dbal->addSelect(
            "
            COALESCE(
                current_modification.barcode_old,
                current_variation.barcode_old,
                current_offer.barcode_old,
                product_info.barcode
            ) 
            AS barcode
            ",
        );

        /* Артикул продукта */

        $dbal->addSelect('
            COALESCE(
                current_modification.article, 
                current_variation.article, 
                current_offer.article, 
                product_info.article
            ) AS article
		');

        $dbal->allGroupByExclude();


        return $dbal->fetchHydrate(CurrentProductIdentifierResult::class);
    }


}