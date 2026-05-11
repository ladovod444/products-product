<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Products\Product\Repository\ProductDetail;

use BaksDev\Core\Type\Field\InputField;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use DateTimeImmutable;

final readonly class ProductDetailByInvariableResult implements ProductDetailInterface
{
    public function __construct(
        private string $product_id,
        private string $product_event,
        private string $product_name,

        private ?string $product_preview,
        private ?string $product_description,
        private ?string $product_url,

        private string $product_article,
        private ?string $product_barcode,
        private ?string $product_barcodes,

        private bool $active,
        private ?string $active_from,
        private ?string $active_to,

        private ?string $product_offer_id,
        private ?string $product_offer_const,
        private ?string $product_offer_value,
        private ?string $product_offer_postfix,
        private ?string $product_offer_reference,
        private ?string $product_offer_name,
        private ?string $product_offer_name_postfix,

        private ?string $product_variation_id,
        private ?string $product_variation_const,
        private ?string $product_variation_value,
        private ?string $product_variation_postfix,
        private ?string $product_variation_reference,
        private ?string $product_variation_name,
        private ?string $product_variation_name_postfix,

        private ?string $product_modification_id,
        private ?string $product_modification_const,
        private ?string $product_modification_value,
        private ?string $product_modification_postfix,
        private ?string $product_modification_reference,
        private ?string $product_modification_name,
        private ?string $product_modification_name_postfix,

        private ?string $product_image,
        private ?string $product_image_ext,
        private ?bool $product_image_cdn,


        private ?int $product_price,
        private ?int $product_old_price,
        private ?string $product_currency,

        private ?int $product_quantity,
        private ?int $product_reserve,

        private ?string $category_name,
        private ?string $category_url,
        private ?string $category_section_field,

    ) {}


    public function getProductName(): string
    {
        return $this->product_name;
    }

    public function getProductId(): ProductUid
    {
        return new ProductUid($this->product_id);
    }

    public function getProductEvent(): ProductEventUid
    {
        return new ProductEventUid($this->product_event);
    }

    public function getProductPreview(): ?string
    {
        return $this->product_preview;
    }

    public function getProductDescription(): ?string
    {
        return $this->product_description;
    }

    public function getProductArticle(): string
    {
        return $this->product_article;
    }

    public function getProductUrl(): ?string
    {
        return $this->product_url;
    }

    /** Active */

    public function isActive(): bool
    {
        return true === $this->active;
    }

    public function getActiveFrom(): ?DateTimeImmutable
    {
        return $this->active_from ? new DateTimeImmutable($this->active_from) : null;
    }

    public function getActiveTo(): ?DateTimeImmutable
    {
        return $this->active_to ? new DateTimeImmutable($this->active_to) : null;
    }

    /** Offer */

    public function getProductOfferUid(): ?ProductOfferUid
    {
        return $this->product_offer_id ? new ProductOfferUid($this->product_offer_id) : null;
    }

    public function getProductOfferConst(): ?ProductOfferConst
    {
        return $this->product_offer_const ? new ProductOfferConst($this->product_offer_const) : null;
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->product_offer_postfix;
    }

    public function getProductOfferReference(): InputField
    {
        return new InputField($this->product_offer_reference);
    }

    public function getProductOfferName(): ?string
    {
        return $this->product_offer_name;
    }

    public function getProductOfferNamePostfix(): ?string
    {
        return $this->product_offer_name_postfix;
    }


    /** Variation */

    public function getProductVariationUid(): ?ProductVariationUid
    {
        return $this->product_variation_id ? new ProductVariationUid($this->product_variation_id) : null;
    }

    public function getProductVariationConst(): ?ProductVariationConst
    {
        return $this->product_variation_const ? new ProductVariationConst($this->product_variation_const) : null;
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getProductVariationPostfix(): ?string
    {
        return $this->product_variation_postfix;
    }

    public function getProductVariationReference(): ?InputField
    {
        return new InputField($this->product_variation_reference);
    }

    public function getProductVariationName(): ?string
    {
        return $this->product_variation_name;
    }

    public function getProductVariationNamePostfix(): ?string
    {
        return $this->product_variation_name_postfix;
    }

    /** Modification */

    public function getProductModificationUid(): ?ProductModificationUid
    {
        return $this->product_modification_id ? new ProductModificationUid($this->product_modification_id) : null;
    }

    public function getProductModificationConst(): ?ProductModificationConst
    {
        return $this->product_modification_const ? new ProductModificationConst($this->product_modification_const) : null;
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getProductModificationPostfix(): ?string
    {
        return $this->product_modification_postfix;
    }

    public function getProductModificationReference(): InputField
    {
        return new InputField($this->product_modification_reference);
    }

    public function getProductModificationName(): ?string
    {
        return $this->product_modification_name;
    }

    public function getProductModificationNamePostfix(): ?string
    {
        return $this->product_modification_name_postfix;
    }


    public function getProductImage(): ?string
    {
        return $this->product_image;
    }

    public function getProductImageExt(): ?string
    {
        return $this->product_image_ext;
    }

    public function isProductImageCdn(): bool
    {
        return $this->product_image_cdn === true;
    }

    /**
     * Category
     */

    public function getCategoryName(): ?string
    {
        return $this->category_name;
    }

    public function getCategoryUrl(): ?string
    {
        return $this->category_url;
    }


    public function getCategorySectionField(): array|null
    {
        if(is_null($this->category_section_field))
        {
            return null;
        }

        if(false === json_validate($this->category_section_field))
        {
            return null;
        }

        $sectionFields = json_decode($this->category_section_field, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($sectionFields))
        {
            return null;
        }

        return $sectionFields;
    }


    public function getProductPrice(): Money|false
    {
        if(empty($this->product_price))
        {
            return false;
        }

        $price = new Money($this->product_price, true);

        // применяем скидку пользователя из профиля
        if(false === empty($this->profile_discount))
        {
            $price->applyString($this->profile_discount);
        }

        return $price;
    }

    public function getProductOldPrice(): Money|false
    {
        if(empty($this->product_old_price))
        {
            return false;
        }

        $price = new Money($this->product_old_price, true);

        // применяем скидку пользователя из профиля
        if(false === empty($this->profile_discount))
        {
            $price->applyString($this->profile_discount);
        }

        return $price;
    }

    public function getProductCurrency(): Currency
    {
        return new Currency($this->product_currency);
    }

    public function getProductQuantity(): int
    {
        return $this->product_quantity ?: 0;
    }

    public function getProductReserve(): int
    {
        return $this->product_reserve ?: 0;
    }


    public function getProductBarcode(): ?string
    {
        if(empty($this->product_barcode))
        {
            $barcodes = $this->getBarcodes();

            return empty($barcodes) ? null : current($barcodes);
        }

        return $this->product_barcode;
    }

    /**
     * @return array<int, string>|null
     */
    public function getBarcodes(): array|null
    {
        if(is_null($this->product_barcodes))
        {
            return null;
        }

        if(false === json_validate($this->product_barcodes))
        {
            return null;
        }

        $barcodes = json_decode($this->product_barcodes, true, 512, JSON_THROW_ON_ERROR);

        if(true === empty(current($barcodes)))
        {
            return null;
        }

        return $barcodes;
    }
}