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
 *
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\BestSellerProducts;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Repository\ProductPriceResultInterface;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/** @see BestSellerProductsRepository */
#[Exclude]
final readonly class BestSellerProductsResult implements ProductPriceResultInterface
{
    public function __construct(
        private int $sort,

        private string|null $url,
        private string|null $product_name,

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

        private string|null $category_id,
        private string|null $category_url,

        private int $product_price,
        private int $product_old_price,
        private string $product_currency,
        private string $product_images,
        private string|null $product_invariable_id,

        private ?bool $promotion_active = null,
        private string|int|null $promotion_price = null,

        private string|null $profile_discount = null,
        private string|null $project_discount = null,

        private string|null $season_percent = null,

    ) {}

    public function getSort(): int
    {
        return $this->sort;
    }

    public function getProductUrl(): string
    {
        return $this->url;
    }

    public function getProductName(): ?string
    {
        return $this->product_name;
    }

    public function getProductOfferUid(): ProductOfferUid|null
    {
        if(is_null($this->product_offer_uid))
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
        if(is_null($this->product_variation_uid))
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
        if(is_null($this->product_modification_uid))
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

    public function getCategoryId(): CategoryProductUid|null
    {
        if(is_null($this->category_id))
        {
            return null;
        }

        return new CategoryProductUid($this->category_id);
    }

    public function getCategoryUrl(): string
    {
        return $this->category_url;
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

    public function getProductCurrency(): string
    {
        return $this->product_currency;
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

    public function getProductInvariableId(): ?ProductInvariableUid
    {
        if(is_null($this->product_invariable_id))
        {
            return null;
        }

        return new ProductInvariableUid($this->product_invariable_id);
    }

    /** Helpers */

    /** Изображения, отсортированные по флагу root */
    public function getProductImagesSortByRoot(): array|null
    {
        if(is_null($this->product_images))
        {
            return null;
        }

        if(false === json_validate($this->product_images))
        {
            return null;
        }

        $images = json_decode($this->product_images, null, 512, JSON_THROW_ON_ERROR);

        if(null === current($images))
        {
            return null;
        }

        // Сортировка массива элементов с изображениями по root = true
        usort($images, function($f) {
            return $f->img_root === true ? -1 : 1;
        });

        return $images;
    }

}
