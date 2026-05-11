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

namespace BaksDev\Products\Product\Repository\AllProductsByCategory;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use DateTimeImmutable;

/** @see AllProductsByCategoryResult */
final class AllProductsByCategoryResult
{
    public function __construct(

        private string $id, //  "018954cb-0a6e-744a-97f0-128e7f05d76d",
        private ?string $product_invariable, //  "018db273-839c-72dd-bb36-de5c523881be"

        private string $url, //  "triangle_effexsport_th202"
        private int $sort, //  900
        private string $product_name, //  "Triangle EffeXSport XL TH202"
        private ?string $preview,
        //  "<p><strong>Triangle EffeXSport TH202</strong> – это новая разработка Triangle ....
        private string $product_article, //  "TH202-16-195-45-84W"

        private string $product_barcode, //  "2744652835819"
        private ?string $barcodes,

        private $product_image, //  "/upload/product_photo/46ee2f0f6adab11443789192821c4ec3"
        private $product_image_ext, //  "webp"
        private $product_image_cdn, //  true

        private string $modify, //  "2025-04-14 20:59:42"

        private ?string $offer_value, //  "16"
        private ?string $offer_postfix, //  null
        private ?string $offer_reference, //  "tire_radius_field"
        private ?string $offer_name, //  "tire_radius_field"
        private ?string $offer_name_postfix, //  "tire_radius_field"


        private ?string $variation_value, //  "195"
        private ?string $variation_postfix, //  null
        private ?string $variation_reference, //  "tire_width_field"
        private ?string $variation_name, //  "tire_width_field"
        private ?string $variation_name_postfix, //  "tire_width_field"


        private ?string $modification_value, //  "45"
        private ?string $modification_postfix, //  "84W"
        private ?string $modification_reference, //  "tire_profile_field"
        private ?string $modification_name,
        private ?string $modification_name_postfix,

        private int $product_price, //  530000
        private int $product_old_price, //  0
        private string $product_currency, //  "rur"

        private int $product_quantity, //  0


        private ?string $category, //  "01876af0-ddfc-70c3-ab25-5f85f55a9907",
        private ?string $category_url, //  "triangle"
        private ?string $category_name, //  "Triangle"
        private ?string $category_desc, //  "Triangle Group"
        private ?int $category_threshold, //  "Triangle Group"


        //  "[{"0": 100, "field_uid": "01985d78-dbfb-79ba-89a0-ede2de932c6b", "field_card": true, "field_name": false,
        // "field_type": "tire_cartype_field", "field_const": "018ec822-1ecf-72b6-85c1-c42787b8849b", "field_trans":
        // "Тип автомобиля", "field_value": "passenger"}, {"0": 200, "field_uid": "01985d78-dbfd-7ebb-a88d-004c087b3259",
        // "field_card": true, "field_name": false, "field_type": "tire_season_field", "field_const":
        // "018ec822-1ed0-7404-9411-8102958baac6", "field_trans": "Сезонность", "field_value": "summer"}, ]"
        private $category_section_field,

        private ?int $product_parameter_length, //  450
        private ?int $product_parameter_width, //  450
        private ?int $product_parameter_height, //  225
        private ?int $product_parameter_weight, //  1000

        private string|null $project_discount = null,

        private ?string $stocks_quantity = null, //  0

        private string|int|null $promotion_price = null,
        private ?bool $promotion_active = null,

        private string|null $season_percent = null,
    ) {}

    public function getProductId(): ProductUid
    {
        return new ProductUid($this->id);
    }

    public function getProductInvariable(): ProductInvariableUid|false
    {
        return $this->product_invariable ? new ProductInvariableUid($this->product_invariable) : false;
    }

    public function getCategoryId(): CategoryProductUid|false
    {
        return $this->category ? new CategoryProductUid($this->category) : false;
    }

    public function getProductUrl(): ?string
    {
        return $this->url;
    }

    /**
     * Sort
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    public function getProductName(): string
    {
        return $this->product_name;
    }

    public function getModelName(): string|false
    {
        $category_name = explode(' ', $this->category_name);

        $model = $this->product_name;

        foreach($category_name as $category)
        {
            $model = str_replace($category, '', $model);
            $model = trim($model);
        }

        return empty($model) || $model === $this->product_name ? false : $model;
    }

    public function getPreview(): ?string
    {
        return $this->preview;
    }

    public function getProductArticle(): string
    {
        return $this->product_article;
    }

    public function getProductBarcode(): string|false
    {
        $barcodes = $this->getBarcodes();

        return empty($barcodes) ? false : current($barcodes);
    }

    /**
     * @return array<int, string>|null
     */
    public function getBarcodes(): array|null
    {
        if(is_null($this->barcodes))
        {
            return null;
        }

        if(false === json_validate($this->barcodes))
        {
            return null;
        }

        $barcodes = json_decode($this->barcodes, true, 512, JSON_THROW_ON_ERROR);

        if(true === empty(current($barcodes)))
        {
            return null;
        }

        return $barcodes;
    }

    /**
     * ProductImage
     */
    public function getProductImage()
    {
        return $this->product_image;
    }

    public function getProductImageExt()
    {
        return $this->product_image_ext;
    }

    public function getProductImageCdn()
    {
        return $this->product_image_cdn;
    }

    public function getModify(): DateTimeImmutable
    {
        return new DateTimeImmutable($this->modify);
    }

    /** Offer */

    public function getOfferValue(): ?string
    {
        return $this->offer_value;
    }

    public function getOfferPostfix(): ?string
    {
        return $this->offer_postfix;
    }

    public function getOfferReference(): ?string
    {
        return $this->offer_reference;
    }

    public function getOfferName(): ?string
    {
        return $this->offer_name;
    }

    public function getOfferNamePostfix(): ?string
    {
        return $this->offer_name_postfix;
    }


    /** Variation */

    public function getVariationValue(): ?string
    {
        return $this->variation_value;
    }

    public function getVariationPostfix(): ?string
    {
        return $this->variation_postfix;
    }

    public function getVariationReference(): ?string
    {
        return $this->variation_reference;
    }

    public function getVariationName(): ?string
    {
        return $this->variation_name;
    }

    public function getVariationNamePostfix(): ?string
    {
        return $this->variation_name_postfix;
    }


    /** Modification */

    public function getModificationValue(): ?string
    {
        return $this->modification_value;
    }

    public function getModificationPostfix(): ?string
    {
        return $this->modification_postfix;
    }

    public function getModificationReference(): ?string
    {
        return $this->modification_reference;
    }

    public function getModificationName(): ?string
    {
        return $this->modification_name;
    }

    public function getModificationNamePostfix(): ?string
    {
        return $this->modification_name_postfix;
    }


    public function getProductPrice(): Money
    {
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

        /* Торговая наценка с учетом сезонности */
        if(false === empty($this->season_percent))
        {
            $price->applyString($this->season_percent);
        }

        return $price;
    }

    public function getProductOldPrice(): Money|false
    {
        return $this->product_old_price ? new Money($this->product_old_price, true) : false;
    }

    public function getProductCurrency(): Currency
    {
        return new Currency($this->product_currency);
    }

    public function getProductQuantity(): int
    {
        return max($this->product_quantity, 0);
    }

    public function getStocksQuantity(): int
    {
        if(empty($this->stocks_quantity))
        {
            return 0;
        }

        if(false === json_validate($this->stocks_quantity))
        {
            return 0;
        }

        $quantity = json_decode($this->stocks_quantity, false, 512, JSON_THROW_ON_ERROR);

        $total = 0;

        foreach($quantity as $stock)
        {
            $total += $stock->total;
            $total -= $stock->reserve;
        }

        return max($total, 0);
    }


    public function getCategoryUrl(): ?string
    {
        return $this->category_url;
    }

    public function getCategoryName(): ?string
    {
        return $this->category_name;
    }

    public function getCategoryDesc(): ?string
    {
        return $this->category_desc;
    }

    public function getCategoryThreshold(): int
    {
        if(empty($this->category_threshold))
        {
            return 0;
        }

        return $this->category_threshold;
    }

    /**
     * CategorySectionField
     */
    public function getCategorySectionField(): array|false
    {
        if(empty($this->category_section_field))
        {
            return false;
        }

        if(false === json_validate($this->category_section_field))
        {
            return false;
        }

        return json_decode($this->category_section_field, false, 512, JSON_THROW_ON_ERROR);
    }


    public function getProductLength(): int|float|null
    {
        return $this->product_parameter_length ? ($this->product_parameter_length * 0.1) : null;
    }

    public function getProductWidth(): int|float|null
    {
        return $this->product_parameter_width ? ($this->product_parameter_width * 0.1) : null;
    }

    public function getProductHeight(): int|float|null
    {
        return $this->product_parameter_height ? ($this->product_parameter_height * 0.1) : null;
    }

    public function getProductWeight(): int|float|null
    {
        return $this->product_parameter_weight ? ($this->product_parameter_weight * 0.01) : null;
    }


}