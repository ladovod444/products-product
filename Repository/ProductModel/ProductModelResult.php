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

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * @see ProductModelRepository
 * @see ProductModelOfferResult
 */
#[Exclude]
final readonly class ProductModelResult
{
    public function __construct(
        private string $id,
        private string $event,

        private bool $active,
        private string|null $active_from,
        private string|null $active_to,

        private string|null $seo_title,
        private string|null $seo_keywords,
        private string|null $seo_description,
        private string|null $product_name,
        private string|null $product_preview,
        private string|null $product_description,
        private string|null $url,
        private string|null $product_offer_reference,
        private string|null $product_offers,
        private string|null $product_images,
        private string|null $category_id,
        private string|null $category_name,
        private string|null $category_url,
        private string|null $category_cover_ext,
        private bool|null $category_cover_cdn,
        private string|null $category_cover_dir,
        private string|null $category_section_field,
        private int|null $category_threshold,

        private string|null $profile_discount = null,
        private string|null $project_discount = null,

        private string|null $project_profile = null,
        private string|null $profiles = null,

        private string|null $season_percent = null,
    ) {}

    public function getProductId(): ProductUid
    {
        return new ProductUid($this->id);
    }

    public function getProductEvent(): ProductEventUid
    {
        return new ProductEventUid($this->event);
    }

    public function isActiveProduct(): bool
    {
        return $this->active;
    }

    public function getProductActiveFrom(): ?string
    {
        return $this->active_from;
    }

    public function getProductActiveTo(): ?string
    {
        return $this->active_to;
    }

    public function getProductSeoTitle(): ?string
    {
        return $this->seo_title;
    }

    public function getProductSeoKeywords(): ?string
    {
        return $this->seo_keywords;
    }

    public function getProductSeoDescription(): ?string
    {
        return $this->seo_description;
    }

    public function getProductName(): ?string
    {
        return $this->product_name;
    }

    public function getProductPreview(): ?string
    {
        return $this->product_preview;
    }

    public function getProductDescription(): ?string
    {
        return $this->product_description;
    }

    public function getProductUrl(): ?string
    {
        return $this->url;
    }

    public function getProductOfferReference(): ?string
    {
        return $this->product_offer_reference;
    }

    public function getProductImages(): array|null
    {
        if(false === json_validate((string) $this->product_images))
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

    public function getCategoryId(): ?CategoryProductUid
    {
        if(is_null($this->category_id))
        {
            return null;
        }

        return new CategoryProductUid($this->category_id);
    }

    public function getCategoryName(): ?string
    {
        return $this->category_name;
    }

    public function getCategoryUrl(): ?string
    {
        return $this->category_url;
    }

    public function getCategoryCoverExt(): ?string
    {
        return $this->category_cover_ext;
    }

    public function isCategoryCoverCdn(): bool|null
    {
        return $this->category_cover_cdn;
    }

    public function getCategoryCoverDir(): ?string
    {
        return $this->category_cover_dir;
    }

    public function getCategorySectionField(): array|null
    {
        if(false === json_validate((string) $this->category_section_field))
        {
            return null;
        }

        $sectionFields = json_decode($this->category_section_field, false, 512, JSON_THROW_ON_ERROR);

        if(null === current($sectionFields))
        {
            return null;
        }

        return array_filter($sectionFields, static fn($n) => $n->field_public === true);
    }

    public function getCategoryThreshold(): ?int
    {
        return $this->category_threshold;
    }

    /** Helpers */

    /** Минимальная цена из торговых предложений */
    public function getMinPrice(): ?Money
    {
        $offers = $this->getProductOffersResult();

        if(is_null($offers))
        {
            return null;
        }

        if(empty(current($offers)))
        {
            return null;
        }

        // продукты только в наличии
        $offers = array_filter($offers, static function(ProductModelOfferResult $offer) {

            return $offer->getProductQuantity() !== null and $offer->getProductQuantity() > 0;
        });

        if(empty($offers))
        {
            return null;
        }

        // сортировка по возрастанию цены
        usort($offers, static function(ProductModelOfferResult $a, ProductModelOfferResult $b) {
            return $a->getProductPrice() <=> $b->getProductPrice();
        });

        $minPrice = current($offers)->getProductPrice();

        /** Скидка магазина */
        if(false === empty($this->project_discount))
        {
            $minPrice->applyString($this->project_discount);
        }

        /** Скидка пользователя */
        if(false === empty($this->profile_discount))
        {
            $minPrice->applyString($this->profile_discount);
        }

        /* Торговая наценка с учетом сезонности */
        if(false === empty($this->season_percent))
        {
            $minPrice->applyString($this->season_percent);
        }

        return $minPrice;
    }

    /**
     * @return array<int, ProductModelOfferResult>|null
     */
    public function getProductOffersResult(): array|null
    {
        if(false === json_validate((string) $this->product_offers))
        {
            return null;
        }

        $offers = json_decode($this->product_offers, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($offers))
        {
            return null;
        }

        $offersResult = [];
        foreach($offers as $offer)
        {
            $offer['project_profile'] = $this->project_profile;
            $offer['profiles'] = $this->profiles;

            // первый ключ в массиве - ключ для сортировки при сортировке в JSON_BUILD - удаляем его
            unset($offer[0]);

            $offersResult[] = new ProductModelOfferResult(...$offer);
        }

        return $offersResult;
    }

    /** Валюта из торговых предложений */
    public function getOfferCurrency(): ?string
    {
        if(false === json_validate((string) $this->product_offers))
        {
            return null;
        }

        $offers = json_decode($this->product_offers, null, 512, JSON_THROW_ON_ERROR);

        if(null === current($offers))
        {
            return null;
        }

        // Сортировка по возрастанию цены
        usort($offers, static function($a, $b) {
            return $a->price <=> $b->price;
        });

        return current($offers)->currency;
    }

    /**
     * Торговые предложения только в наличии
     *
     * @return array<int, ProductModelOfferResult>|array<empty>|null
     */
    public function getInStockOffersResult(): ?array
    {
        $offers = $this->getProductOffersResult();

        if(is_null($offers))
        {
            return null;
        }

        $inStock = array_filter($offers, static function(ProductModelOfferResult $offer) {
            return $offer->getProductQuantity() !== 0;
        });

        return $inStock;
    }

    /**
     * Торговые предложения не в наличии
     *
     * @return array<int, ProductModelOfferResult>|array<empty>|null
     */
    public function getOutOfStockOffersResult(): ?array
    {
        $offers = $this->getProductOffersResult();

        if(is_null($offers))
        {
            return null;
        }

        $outOfStock = array_filter($offers, static function(ProductModelOfferResult $offer) {
            return $offer->getProductQuantity() === 0;
        });

        return $outOfStock;
    }

    /** Изображения, отсортированные по флагу root */
    public function getProductImagesSortByRoot(): array|null
    {
        if(false === json_validate((string) $this->product_images))
        {
            return null;
        }

        $images = json_decode($this->product_images, null, 512, JSON_THROW_ON_ERROR);

        if(empty(current($images)))
        {
            return null;
        }

        // Сортировка массива элементов с изображениями по root = true
        usort($images, static function($f) {
            return $f->product_img_root === true ? -1 : 1;
        });

        return $images;
    }

    /** Для модели нет Invariable */
    public function getProductInvariableId(): null
    {
        return null;
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