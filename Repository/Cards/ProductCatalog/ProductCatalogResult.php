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

namespace BaksDev\Products\Product\Repository\Cards\ProductCatalog;

use BaksDev\Products\Product\Repository\Cards\ProductCardResultInterfaceProduct;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/** @see ProductCatalogRepository */
#[Exclude]
final readonly class ProductCatalogResult implements ProductCardResultInterfaceProduct
{
    public function __construct(
        private string $product_id,
        private string $product_event,
        private string $product_name,
        private string|null $product_url,
        private string|null $product_offer_uid,
        private string|null $product_offer_value,
        private string|null $product_offer_postfix,
        private string|null $product_offer_reference,
        private string|null $product_variation_uid,
        private string|null $product_variation_value,
        private string|null $product_variation_postfix,
        private string|null $product_variation_reference,
        private string|null $product_modification_uid,
        private string|null $product_modification_value,
        private string|null $product_modification_postfix,
        private string|null $product_modification_reference,
        private string|null $product_article,

        private ?bool $product_active,
        private ?string $product_active_from,
        private ?string $product_active_to,

        private string|null $product_images,
        private int|null $product_price,
        private int|null $product_old_price,
        private string|null $product_currency,
        private string $category_url,
        private string $category_name,
        private string $category_section_field,
        private string|null $product_invariable_id,

        private ?bool $promotion_active = null,
        private string|int|null $promotion_price = null,

        private string|null $profile_discount = null,
        private string|null $project_discount = null,

        private string|null $product_quantity_stocks = null,
        private string|null $product_region_delivery = null,

        private string|null $season_percent = null,
    ) {}

    /* Есть ли в данном регионе */
    public function isProductExistRegion()
    {
        if(empty($this->product_quantity_stocks))
        {
            return false;
        }

        if(false === json_validate($this->product_quantity_stocks))
        {
            return false;
        }

        return true;
    }

    public function getProductId(): ProductUid
    {
        return new ProductUid($this->product_id);
    }

    public function getProductEvent(): ProductEventUid
    {
        return new ProductEventUid($this->product_event);
    }

    public function getProductName(): string
    {
        return $this->product_name;
    }

    public function getProductUrl(): ?string
    {
        return $this->product_url;
    }

    public function getProductOfferUid(): ProductOfferUid|null
    {
        if(null === $this->product_offer_uid)
        {
            return null;
        }

        return new ProductOfferUid($this->product_offer_uid);
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->product_offer_postfix;
    }

    public function getProductOfferReference(): ?string
    {
        return $this->product_offer_reference;
    }

    public function getProductVariationUid(): ProductVariationUid|null
    {
        if(null === $this->product_variation_uid)
        {
            return null;
        }

        return new ProductVariationUid($this->product_variation_uid);
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getProductVariationPostfix(): ?string
    {
        return $this->product_variation_postfix;
    }

    public function getProductVariationReference(): ?string
    {
        return $this->product_variation_reference;
    }

    public function getProductModificationUid(): ProductModificationUid|null
    {
        if(null === $this->product_modification_uid)
        {
            return null;
        }

        return new ProductModificationUid($this->product_modification_uid);
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getProductModificationPostfix(): ?string
    {
        return $this->product_modification_postfix;
    }

    public function getProductModificationReference(): ?string
    {
        return $this->product_modification_reference;
    }

    public function getProductArticle(): string|null
    {
        return $this->product_article;
    }

    public function getProductImages(): array|null
    {
        if(is_null($this->product_images))
        {
            return null;
        }

        if(false === json_validate($this->product_images))
        {
            return null;
        }

        $images = json_decode($this->product_images, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($images))
        {
            return null;
        }

        return $images;
    }

    public function getProductPrice(): Money|false
    {
        if(empty($this->product_price))
        {
            return false;
        }

        $price = new Money($this->product_price, true);

        /** Кастомная цена */
        if(false === empty($this->promotion_price) && true === $this->promotion_active)
        {
            $price->applyString($this->promotion_price);
        }

        /** Скидка магазина */
        if(false === empty($this->project_discount))
        {
            $price->applyString($this->project_discount);
        }

        /** Скидка пользователя */
        if(false === empty($this->profile_discount))
        {
            $price->applyString($this->profile_discount);
        }

        /* Торговая наценка с учетом сезонности */
        if(false === empty($this->season_percent))
        {
            $price->applyString($this->season_percent);
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

        /** Кастомная цена */
        if(false === empty($this->promotion_price) && true === $this->promotion_active)
        {
            $price->applyString($this->promotion_price);
        }

        /** Скидка магазина */
        if(false === empty($this->project_discount))
        {
            $price->applyString($this->project_discount);
        }

        /** Скидка пользователя */
        if(false === empty($this->profile_discount))
        {
            $price->applyString($this->profile_discount);
        }


        /* Торговая наценка с учетом сезонности */
        if(false === empty($this->season_percent))
        {
            $price->applyString($this->season_percent);
        }

        return $price;
    }

    public function getProductCurrency(): Currency
    {
        return new Currency($this->product_currency);
    }

    public function getCategoryUrl(): string
    {
        return $this->category_url;
    }

    public function getCategoryName(): string
    {
        return $this->category_name;
    }

    public function getCategorySectionField(): array|null
    {
        $sectionFields = json_decode($this->category_section_field, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($sectionFields))
        {
            return null;
        }

        return $sectionFields;
    }

    public function getProductInvariableId(): ProductInvariableUid|null
    {
        if(null === $this->product_invariable_id)
        {
            return null;
        }

        return new ProductInvariableUid($this->product_invariable_id);
    }

    public function getProductActiveFrom(): string|null
    {
        return $this->product_active_from;
    }

    /** Методы - заглушки */

    public function getProductOfferConst(): bool
    {
        return false;
    }

    public function getProductOfferName(): bool
    {
        return false;
    }

    public function getProductVariationConst(): bool
    {
        return false;
    }

    public function getProductVariationName(): bool
    {
        return false;
    }

    public function getProductModificationConst(): bool
    {
        return false;
    }

    public function getProductModificationName(): bool
    {
        return false;
    }

    public function getProductReserve(): bool
    {
        return false;
    }

    public function getProductQuantity(): bool
    {
        return true;
    }

    public function getProductInvariableOfferConst(): bool
    {
        return false;
    }

    public function getProductCategory(): bool
    {
        return false;
    }

    public function getCategoryEvent(): bool
    {
        return false;
    }
}
