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

namespace BaksDev\Products\Product\Repository\Ids\ProductIdsByBarcodesRepository;

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
use Doctrine\DBAL\ArrayParameterType;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

final class ProductIdsByBarcodesRepository implements ProductIdsByBarcodesInterface
{
    private array|false $barcodes = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder
    ) {}

    /** Массив штрихкодов */
    public function byBarcodes(array $barcodes): self
    {
        $this->barcodes = $barcodes;
        return $this;
    }

    /**
     * Метод возвращает активные идентификаторы продукции по штрихкоду
     */
    public function find(): ProductIdsByBarcodesResult|false
    {

        if(false === $this->barcodes)
        {
            throw new InvalidArgumentException('Не передан обязательный параметр запроса $this->barcodes');
        }

        /** Поиск артикула INFO */

        $dbalInfo = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbalInfo->select('product.id AS product');
        $dbalInfo->addSelect('product.event');
        $dbalInfo->addSelect('NULL::uuid  AS offer');
        $dbalInfo->addSelect('NULL::uuid  AS offer_const');
        $dbalInfo->addSelect('NULL::uuid  AS variation');
        $dbalInfo->addSelect('NULL::uuid  AS variation_const');
        $dbalInfo->addSelect('NULL::uuid  AS modification');
        $dbalInfo->addSelect('NULL::uuid  AS modification_const');

        $dbalInfo->from(ProductInfo::class, 'info');

        $dbalInfo->join(
            'info',
            Product::class, 'product',
            'product.id = info.product',
        );

        $dbalInfo
            ->where('info.barcode IN (:barcodes)');

        $dbalInfo
            ->addSelect('invariable.id AS invariable')
            ->join(
                'product',
                ProductInvariable::class, 'invariable',
                '
                    invariable.product = product.id AND
                    invariable.offer IS NULL AND
                    invariable.variation IS NULL AND
                    invariable.modification IS NULL
                ',
            );

        /** Поиск артикула OFFER */

        $dbalOffer = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbalOffer->select('product.id AS product');
        $dbalOffer->addSelect('product.event');
        $dbalOffer->addSelect('offer.id AS offer');
        $dbalOffer->addSelect('offer.const AS offer_const');
        $dbalOffer->addSelect('NULL::uuid  AS variation');
        $dbalOffer->addSelect('NULL::uuid  AS variation_const');
        $dbalOffer->addSelect('NULL::uuid  AS modification');
        $dbalOffer->addSelect('NULL::uuid  AS modification_const');


        $dbalOffer
            ->from(ProductOfferBarcode::class, 'product_offer_barcode')
            ->where('product_offer_barcode.value IN (:barcodes)');

        $dbalOffer
            ->join(
                'product_offer_barcode',
                ProductOffer::class,
                'offer',
                'offer.id = product_offer_barcode.offer',
            );


        $dbalOffer->join(
            'offer',
            Product::class, 'product',
            'product.event = offer.event',
        );

        $dbalOffer
            ->addSelect('invariable.id AS invariable')
            ->join(
                'product',
                ProductInvariable::class, 'invariable',
                '
                    invariable.product = product.id AND
                    invariable.offer = offer.const AND
                    invariable.variation IS NULL AND
                    invariable.modification IS NULL
                ',
            );


        /** Поиск артикула VARIATION */

        $dbalVariation = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbalVariation->select('product.id AS product');
        $dbalVariation->addSelect('product.event');
        $dbalVariation->addSelect('offer.id AS offer');
        $dbalVariation->addSelect('offer.const AS offer_const');
        $dbalVariation->addSelect('variation.id AS variation');
        $dbalVariation->addSelect('variation.const AS variation_const');
        $dbalVariation->addSelect('NULL::uuid AS modification');
        $dbalVariation->addSelect('NULL::uuid AS modification_const');


        $dbalVariation
            ->from(ProductVariationBarcode::class, 'product_variation_barcode')
            ->where('product_variation_barcode.value IN (:barcodes)');

        $dbalVariation
            ->join(
                'product_variation_barcode',
                ProductVariation::class,
                'variation',
                'variation.id = product_variation_barcode.variation',
            );

        $dbalVariation
            ->join(
                'variation',
                ProductOffer::class, 'offer',
                'offer.id = variation.offer',
            );

        $dbalVariation
            ->join(
                'offer',
                Product::class, 'product',
                'product.event = offer.event',
            );

        $dbalVariation
            ->addSelect('invariable.id AS invariable')
            ->join(
                'product',
                ProductInvariable::class, 'invariable',
                '
                    invariable.product = product.id AND
                    invariable.offer = offer.const AND
                    invariable.variation = variation.const AND
                    invariable.modification IS NULL
                ',
            );


        /** Поиск артикула MODIFICATION */

        $dbalModification = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbalModification->select('product.id AS product');
        $dbalModification->addSelect('product.event');
        $dbalModification->addSelect('offer.id AS offer');
        $dbalModification->addSelect('offer.const AS offer_const');
        $dbalModification->addSelect('variation.id AS variation');
        $dbalModification->addSelect('variation.const AS variation_const');
        $dbalModification->addSelect('modification.id  AS modification');
        $dbalModification->addSelect('modification.const  AS modification_const');


        $dbalModification
            ->from(ProductModificationBarcode::class, 'product_modification_barcode')
            ->where('product_modification_barcode.value IN (:barcodes)');


        $dbalModification
            ->join(
                'product_modification_barcode',
                ProductModification::class,
                'modification',
                'modification.id = product_modification_barcode.modification',
            );

        $dbalModification
            ->join(
                'modification',
                ProductVariation::class, 'variation',
                'variation.id = modification.variation',
            );

        $dbalModification
            ->join(
                'variation',
                ProductOffer::class, 'offer',
                'offer.id = variation.offer',
            );

        $dbalModification
            ->join(
                'offer',
                Product::class, 'product',
                'product.event = offer.event',
            );

        $dbalModification
            ->addSelect('invariable.id AS invariable')
            ->join(
                'product',
                ProductInvariable::class, 'invariable',
                '
                    invariable.product = product.id AND
                    invariable.offer = offer.const AND
                    invariable.variation = variation.const AND
                    invariable.modification = modification.const
                ',
            );


        /** UNION */

        $union = [
            str_replace('SELECT', '', $dbalInfo->getSQL()),
            $dbalOffer->getSQL(),
            $dbalVariation->getSQL(),
            $dbalModification->getSQL(),
        ];

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);
        $dbal->select(implode(' UNION ', $union));
        $dbal->setParameter('barcodes', $this->barcodes, ArrayParameterType::STRING);


        $dbal->orderBy('modification', 'DESC');
        $dbal->addOrderBy('variation', 'DESC');
        $dbal->addOrderBy('offer', 'DESC');
        $dbal->addOrderBy('event', 'DESC');


        return $dbal
            ->enableCache('products-product', 86400)
            ->fetchHydrate(ProductIdsByBarcodesResult::class);
    }
}