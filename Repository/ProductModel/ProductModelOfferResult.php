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

namespace BaksDev\Products\Product\Repository\ProductModel;

use BaksDev\Products\Product\Repository\ProductPriceResultInterface;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * @see ProductModelResult
 */
#[Exclude]
final readonly class ProductModelOfferResult implements ProductPriceResultInterface
{
    public function __construct(
        private string|null $offer_uid,
        private string|null $offer_name,
        private string|null $offer_value,
        private string|null $offer_postfix,
        private string|null $offer_reference,

        private string|null $variation_uid,
        private string|null $variation_name,
        private string|null $variation_value,
        private string|null $variation_postfix,
        private string|null $variation_reference,

        private string|null $modification_uid,
        private string|null $modification_name,
        private string|null $modification_value,
        private string|null $modification_postfix,
        private string|null $modification_reference,

        private string|null $product_invariable_id,

        private string $article,
        private int $price,
        private int $old_price,
        private string $currency,
        private ?int $quantity,

        private string|int|null $promotion_price = null,
        private ?bool $promotion_active = null,

        private string|null $profile_discount = null,
        private string|null $project_discount = null,

        private string|null $project_profile = null,
        private string|null $profiles = null,

        private array|null $product_quantity_stocks = null,

        private string|null $season_percent = null,
    ) {}


    /* Есть ли в данном регионе */
    public function isProductExistRegion()
    {
        if(empty($this->product_quantity_stocks))
        {
            return false;
        }

        return true;
    }


    public function getProductOfferUid(): ProductOfferUid|null
    {
        if(is_null($this->offer_uid))
        {
            return null;
        }

        return new ProductOfferUid($this->offer_uid);
    }

    public function getProductOfferName(): ?string
    {
        return $this->offer_name;
    }

    public function getProductOfferValue(): ?string
    {
        return $this->offer_value;
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->offer_postfix;
    }

    public function getProductOfferReference(): ?string
    {
        return $this->offer_reference;
    }

    public function getProductVariationUid(): ProductVariationUid|null
    {
        if(is_null($this->variation_uid))
        {
            return null;
        }

        return new ProductVariationUid($this->variation_uid);
    }

    public function getProductVariationName(): ?string
    {
        return $this->variation_name;
    }

    public function getProductVariationValue(): ?string
    {
        return $this->variation_value;
    }

    public function getProductVariationPostfix(): ?string
    {
        return $this->variation_postfix;
    }

    public function getProductVariationReference(): ?string
    {
        return $this->variation_reference;
    }

    public function getProductModificationUid(): ProductModificationUid|null
    {
        if(is_null($this->modification_uid))
        {
            return null;
        }

        return new ProductModificationUid($this->modification_uid);
    }

    public function getProductModificationName(): ?string
    {
        return $this->modification_name;
    }

    public function getProductModificationValue(): ?string
    {
        return $this->modification_value;
    }

    public function getProductModificationPostfix(): ?string
    {
        return $this->modification_postfix;
    }

    public function getProductModificationReference(): ?string
    {
        return $this->modification_reference;
    }

    public function getProductInvariableId(): ?ProductInvariableUid
    {
        if(is_null($this->product_invariable_id))
        {
            return null;
        }

        return new ProductInvariableUid($this->product_invariable_id);
    }

    public function getProductArticle(): string
    {
        return $this->article;
    }

    public function getProductCurrency(): string
    {
        return $this->currency;
    }

    public function getProductQuantity(): int
    {
        return $this->quantity ?: 0;
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
        if(empty($this->price))
        {
            return false;
        }

        $price = new Money($this->price, true);

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

    /** Helpers */

    public function getProductOldPrice(): Money|false
    {
        if(empty($this->old_price))
        {
            return false;
        }

        $price = new Money($this->old_price, true);

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
}
