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

namespace BaksDev\Products\Product\Repository\Cards\ProductAlternative;

use BaksDev\Products\Product\Repository\Cards\ProductCardResultInterfaceProduct;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/** @see ProductAlternativeRepository */
#[Exclude]
final readonly class ProductAlternativeResult implements ProductCardResultInterfaceProduct
{
    public function __construct(
        private string $id,
        private string $event,
        private string|null $product_offer_uid,
        private string|null $product_offer_name,
        private string|null $product_offer_value,
        private string|null $product_offer_postfix,
        private string|null $product_offer_reference,
        private string|null $product_variation_uid,
        private string|null $product_variation_name,
        private string|null $product_variation_value,
        private string|null $product_variation_postfix,
        private string|null $product_variation_reference,
        private string|null $product_modification_uid,
        private string|null $product_modification_name,
        private string|null $product_modification_value,
        private string|null $product_modification_postfix,
        private string|null $product_modification_reference,

        private bool|null $product_active,
        private string|null $product_active_from,
        private string|null $product_active_to,

        private string $product_name,
        private string $product_url,
        private string|null $article,
        private string $product_images,

        private int|null $product_price,
        private int|null $product_old_price,
        private string|null $product_currency,

        private int|null $quantity,
        private string $category_name,
        private string $category_url,
        private string $category_section_field,
        private int|null $category_threshold,
        private string|null $product_invariable_id,

        private ?bool $promotion_active = null,
        private string|int|null $promotion_price = null,

        private string|null $profile_discount = null,
        private string|null $project_discount = null,

        private string|null $project_profile = null,
        private string|null $profiles = null,

        private string|null $product_quantity_stocks = null,

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
        return new ProductUid($this->id);
    }

    public function getProductEvent(): ProductEventUid
    {
        return new ProductEventUid($this->event);
    }

    public function getProductOfferUid(): ProductOfferUid|null
    {
        if(null === $this->product_offer_uid)
        {
            return null;
        }

        return new ProductOfferUid($this->product_offer_uid);
    }

    public function getProductOfferName(): ?string
    {
        return $this->product_offer_name;
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

    public function getProductVariationName(): ?string
    {
        return $this->product_variation_name;
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

    public function getProductModificationName(): ?string
    {
        return $this->product_modification_name;
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

    public function getProductActiveFrom(): string|null
    {
        return $this->product_active_from;
    }

    public function getProductName(): string
    {
        return $this->product_name;
    }

    public function getProductUrl(): string
    {
        return $this->product_url;
    }

    public function getProductArticle(): string|null
    {
        return $this->article;
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

    public function getProductCurrency(): Currency
    {
        return new Currency($this->product_currency);
    }

    public function getProductQuantity(?string $profile = null): int|null
    {
        /** Если карточка не активна - нет в наличии */
        if($this->product_active !== true)
        {
            return null;
        }

        /** Если дата публикации не наступила - нет в наличии */
        if(
            false === empty($this->product_active_from)
            && new DateTimeImmutable($this->product_active_from) > new DateTimeImmutable()
        )
        {
            return null;
        }

        if(
            false === empty($this->product_active_to)
            && new DateTimeImmutable($this->product_active_to) < new DateTimeImmutable()
        )
        {
            return null;
        }

        return $this->quantity;
    }

    public function getCategoryName(): string
    {
        return $this->category_name;
    }

    public function getCategoryUrl(): string
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

    public function getCategoryThreshold(): ?int
    {
        return $this->category_threshold;
    }

    public function getProductInvariableId(): ProductInvariableUid|null
    {
        if(null === $this->product_invariable_id)
        {
            return null;
        }

        return new ProductInvariableUid($this->product_invariable_id);
    }

    /** Методы - заглушки */

    public function getProductOfferConst(): bool
    {
        return false;
    }

    public function getProductVariationConst(): bool
    {
        return false;
    }

    public function getProductModificationConst(): bool
    {
        return false;
    }

    public function getProductReserve(): bool
    {
        return false;
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

    /** Helpers */

    public function isDeliveryRegion(): bool
    {
        if(empty($this->project_profile))
        {
            return true;
        }

        if(empty($this->profiles))
        {
            return true;
        }

        if(false === json_validate($this->profiles))
        {
            return true;
        }

        $profiles = json_decode($this->profiles, true, 512, JSON_THROW_ON_ERROR);

        if(empty($profiles))
        {
            return true;
        }

        if(in_array($this->project_profile, $profiles, true))
        {
            return true;
        }

        return false;
    }

    /** Возвращает разницу между старой и новой ценами в процентах */
    public function getDiscountPercent(): int|null
    {
        if(false === $this->getProductPrice())
        {
            return null;
        }

        if(false === $this->getProductOldPrice())
        {
            return null;
        }

        $price = $this->getProductPrice()->getValue();
        $oldPrice = $this->getProductOldPrice()->getValue();

        $discountPercent = null;
        if($oldPrice > $price)
        {
            $discountPercent = (int) (($oldPrice - $price) / $oldPrice * 100);
        }

        return $discountPercent;
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
}