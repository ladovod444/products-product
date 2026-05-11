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

namespace BaksDev\Products\Product\Repository\ProductDetailByValue;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Repository\ProductPriceResultInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Money\Type\Money;
use DateInterval;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/** @see ProductDetailByValueRepository */
#[Exclude]
final readonly class ProductDetailByValueResult implements ProductPriceResultInterface
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

        private string|null $product_offer_uid,
        private string|null $product_offer_const,
        private string|null $product_offer_value,
        private string|null $product_offer_postfix,
        private string|null $product_offer_reference,
        private string|null $product_offer_name,
        private string|null $product_offer_name_postfix,

        private string|null $product_variation_uid,
        private string|null $product_variation_const,
        private string|null $product_variation_value,
        private string|null $product_variation_postfix,
        private string|null $product_variation_reference,
        private string|null $product_variation_name,
        private string|null $product_variation_name_postfix,

        private string|null $product_modification_uid,
        private string|null $product_modification_const,
        private string|null $product_modification_value,
        private string|null $product_modification_postfix,
        private string|null $product_modification_reference,
        private string|null $product_modification_name,
        private string|null $product_modification_name_postfix,

        private string|null $product_article,
        private string|null $product_images,

        private int|null $product_price,
        private int|null $product_old_price,
        private string|null $product_currency,
        private string|null $product_quantity,


        private string|null $category_id,
        private string|null $category_name,
        private string|null $category_url,
        private int|null $category_minimal,
        private int|null $category_input,
        private int|null $category_threshold,
        private int|null $category_step,
        private string|null $category_cover_ext,
        private bool|null $category_cover_cdn,
        private string|null $category_cover_path,
        private string|null $category_section_field,

        private string|null $product_invariable_id,

        private ?bool $promotion_active = null,
        private string|int|null $promotion_price = null,

        private string|null $profile_discount = null,
        private string|null $project_discount = null,
        private string|null $product_quantity_stocks = null,
        private string|null $product_region_delivery = null,

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

    public function getProductOfferUid(): ProductOfferUid|null
    {
        if(is_null($this->product_offer_uid))
        {
            return null;
        }

        return new ProductOfferUid($this->product_offer_uid);
    }

    public function getProductOfferConst(): ProductOfferConst|null
    {
        if(is_null($this->product_offer_const))
        {
            return null;
        }

        return new ProductOfferConst($this->product_offer_const);
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

    public function getProductOfferName(): ?string
    {
        return $this->product_offer_name;
    }

    public function getProductOfferNamePostfix(): ?string
    {
        return $this->product_offer_name_postfix;
    }

    public function getProductVariationUid(): ProductVariationUid|null
    {
        if(is_null($this->product_variation_uid))
        {
            return null;
        }

        return new ProductVariationUid($this->product_variation_uid);
    }

    public function getProductVariationConst(): ProductVariationConst|null
    {
        if(is_null($this->product_variation_const))
        {
            return null;
        }

        return new ProductVariationConst($this->product_variation_const);
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

    public function getProductVariationName(): ?string
    {
        return $this->product_variation_name;
    }

    public function getProductVariationNamePostfix(): ?string
    {
        return $this->product_variation_name_postfix;
    }

    public function getProductModificationUid(): ProductModificationUid|null
    {
        if(is_null($this->product_modification_uid))
        {
            return null;
        }

        return new ProductModificationUid($this->product_modification_uid);
    }

    public function getProductModificationConst(): ProductModificationConst|null
    {
        if(is_null($this->product_modification_const))
        {
            return null;
        }

        return new ProductModificationConst($this->product_modification_const);
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

    public function getProductModificationName(): ?string
    {
        return $this->product_modification_name;
    }

    public function getProductModificationNamePostfix(): ?string
    {
        return $this->product_modification_name_postfix;
    }

    public function getProductArticle(): ?string
    {
        return $this->product_article;
    }

    public function getProductImages(): ?array
    {
        if(empty($this->product_images))
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

    public function getProductCurrency(): ?string
    {
        return $this->product_currency;
    }

    public function getProductQuantityStocks(): ?int
    {
        if(empty($this->product_quantity_stocks))
        {
            return 0;
        }

        if(false === json_validate($this->product_quantity_stocks))
        {
            return 0;
        }

        $decode = json_decode($this->product_quantity_stocks, false, 512, JSON_THROW_ON_ERROR);

        $quantity = 0;

        foreach($decode as $item)
        {
            $quantity += (empty($item->total) ? 0 : $item->total);
            $quantity -= (empty($item->reserve) ? 0 : $item->reserve);
        }

        return max($quantity, 0);
    }

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

    public function getProductQuantity(): ?int
    {
        if(empty($this->product_quantity))
        {
            return 0;
        }

        if(false === json_validate($this->product_quantity))
        {
            return 0;
        }

        $decode = json_decode($this->product_quantity, false, 512, JSON_THROW_ON_ERROR);

        $quantity = 0;

        foreach($decode as $item)
        {
            $quantity += (empty($item->total) ? 0 : $item->total);
            $quantity -= (empty($item->reserve) ? 0 : $item->reserve);
        }

        return max($quantity, 0);
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

    public function getCategoryMinimal(): ?int
    {
        return $this->category_minimal;
    }

    public function getCategoryInput(): ?int
    {
        return $this->category_input;
    }

    public function getCategoryThreshold(): ?int
    {
        return $this->category_threshold;
    }

    public function getCategoryStep(): ?int
    {
        return $this->category_step;
    }

    public function getCategoryCoverExt(): ?string
    {
        return $this->category_cover_ext;
    }

    public function getCategoryCoverCdn(): ?bool
    {
        return $this->category_cover_cdn;
    }

    public function getCategoryCoverPath(): ?string
    {
        return $this->category_cover_path;
    }

    public function getCategorySectionField(): ?array
    {
        $sectionFields = json_decode($this->category_section_field, false, 512, JSON_THROW_ON_ERROR);

        if(null === current($sectionFields))
        {
            return null;
        }

        return array_filter($sectionFields, static fn($n) => $n->field_public === true);
    }

    public function getProductInvariableId(): ?ProductInvariableUid
    {
        if(null === $this->product_invariable_id)
        {
            return null;
        }

        return new ProductInvariableUid($this->product_invariable_id);
    }

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
            return $f->product_img_root === true ? -1 : 1;
        });

        return $images;
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

    /** Helpers */

    public function getProductPrice(): Money|false
    {
        if(empty($this->product_price))
        {
            return false;
        }

        /** Оригинальная цена */
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

    public function getProductRegionDelivery(): DateTimeImmutable
    {
        /** По умолчанию доставка завтра */
        $tomorrow = new DateTimeImmutable('+ 1 day');

        if(is_null($this->product_region_delivery))
        {
            return $tomorrow;
        }

        if(false === json_validate($this->product_region_delivery))
        {
            return $tomorrow;
        }

        $delivery = json_decode($this->product_region_delivery, false, 512, JSON_THROW_ON_ERROR);

        if(null === current($delivery))
        {
            return $tomorrow;
        }

        // Сортировка массива элементов с изображениями по root = true

        usort($delivery, function($a, $b) {
            return $a->day <=> $b->day;
        });

        $delivery = current($delivery);

        $delivery = new DateTimeImmutable($delivery->value)
            ->add(DateInterval::createFromDateString(sprintf('%s days', $delivery->day)));

        if($tomorrow > $delivery)
        {
            return $tomorrow;
        }

        return $delivery;
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
