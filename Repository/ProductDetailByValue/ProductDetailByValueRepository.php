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

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Cover\CategoryProductCover;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Trans\CategoryProductOffersTrans;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\Trans\CategoryProductModificationTrans;
use BaksDev\Products\Category\Entity\Offers\Variation\Trans\CategoryProductVariationTrans;
use BaksDev\Products\Category\Entity\Section\CategoryProductSection;
use BaksDev\Products\Category\Entity\Section\Field\CategoryProductSectionField;
use BaksDev\Products\Category\Entity\Section\Field\Trans\CategoryProductSectionFieldTrans;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Event\Profile\ProductProfile;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\Price\ProductOfferPrice;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image\ProductModificationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Price\ProductModificationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Price\ProductVariationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\ProductInvariable;
use BaksDev\Products\Product\Entity\Project\Description\ProductProjectDescription;
use BaksDev\Products\Product\Entity\Project\ProductProject;
use BaksDev\Products\Product\Entity\Project\Season\ProductProjectSeason;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Seo\ProductSeo;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Promotion\BaksDevProductsPromotionBundle;
use BaksDev\Products\Promotion\Entity\Event\Invariable\ProductPromotionInvariable;
use BaksDev\Products\Promotion\Entity\Event\Period\ProductPromotionPeriod;
use BaksDev\Products\Promotion\Entity\Event\Price\ProductPromotionPrice;
use BaksDev\Products\Promotion\Entity\ProductPromotion;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use BaksDev\Products\Stocks\Entity\Total\ProductStockTotal;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Delivery\UserProfileDelivery;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Discount\UserProfileDiscount;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Region\UserProfileRegion;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** @see ProductDetailByValueResult */
final class ProductDetailByValueRepository implements ProductDetailByValueInterface
{
    private ProductUid|false $productUid = false;

    private string|null|false $offer = false;

    private string|null|false $variation = false;

    private string|null|false $modification = false;

    private string|null|false $postfix = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        #[Autowire(env: 'PROJECT_REGION')] private readonly ?string $region = null,
    ) {}

    /** Фильтрация по продукту */
    public function byProduct(Product|ProductUid|string $productUid): self
    {
        if(is_string($productUid))
        {
            $productUid = new ProductUid($productUid);
        }

        if($productUid instanceof Product)
        {
            $productUid = $productUid->getId();
        }

        $this->productUid = $productUid;

        return $this;
    }

    /** Фильтрация по Offer */
    public function byOfferValue(string|null $offer): self
    {
        if(is_null($offer))
        {
            $this->offer = false;
            return $this;
        }

        $offer = mb_strtolower($offer);
        $this->offer = trim($offer);

        return $this;
    }

    /** Фильтрация по Variation */
    public function byVariationValue(string|null $variation): self
    {
        if(is_null($variation))
        {
            $this->variation = false;
            return $this;
        }

        $variation = mb_strtolower($variation);
        $this->variation = trim($variation);

        return $this;
    }

    /** Фильтрация по Modification */
    public function byModificationValue(string|null $modification): self
    {
        if(is_null($modification))
        {
            $this->modification = false;
            return $this;
        }

        $modification = mb_strtolower($modification);
        $this->modification = trim($modification);

        return $this;
    }

    /** Фильтрация по Postfix */
    public function byPostfix(string|null $postfix): self
    {
        if(is_null($postfix))
        {
            $this->postfix = false;
            return $this;
        }

        $postfix = mb_strtolower($postfix);
        $this->postfix = trim($postfix);


        return $this;
    }

    /** Метод возвращает детальную информацию о продукте и его заполненному значению ТП, вариантов и модификаций. */
    public function find(): ProductDetailByValueResult|false
    {
        if(false === ($this->productUid instanceof ProductUid))
        {
            throw new InvalidArgumentException('Не передан обязательный параметр запроса $productUid');
        }

        if($this->postfix)
        {
            $this->postfix = str_replace('-', '/', $this->postfix);
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->addSelect('product.event')
            ->from(Product::class, 'product')
            ->where('product.id = :product')
            ->setParameter(
                key: 'product',
                value: $this->productUid,
                type: ProductUid::TYPE,
            );

        $dbal
            ->addSelect('product_active.active')
            ->addSelect('product_active.active_from')
            ->addSelect('product_active.active_to')
            ->join(
                'product',
                ProductActive::class,
                'product_active',
                'product_active.event = product.event',
            );


        $dbal
            ->addSelect(':'.$dbal::PROJECT_PROFILE_KEY.' AS project_profile')
            ->addSelect("JSON_AGG (DISTINCT product_profile.value) FILTER (WHERE product_profile.value IS NOT NULL) AS profiles")
            ->leftJoin(
                'product',
                ProductProfile::class,
                'product_profile',
                'product_profile.event = product.event',
            );


        $dbal
            ->addSelect('product_seo.title AS seo_title')
            ->addSelect('product_seo.keywords AS seo_keywords')
            ->addSelect('product_seo.description AS seo_description')
            ->leftJoin(
                'product',
                ProductSeo::class,
                'product_seo',
                'product_seo.event = product.event AND product_seo.local = :local',
            );

        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product.event AND product_trans.local = :local',
            );

        $dbal
            ->leftJoin(
                'product',
                ProductProject::class,
                'product_project',
                '
                    product_project.product = product.id 
                    '.(true === $dbal->bindProjectProfile()
                    ? 'AND product_project.profile = :'.$dbal::PROJECT_PROFILE_KEY
                    : 'AND product_project.profile IS NULL'),
            );

        $dbal
            ->addSelect('product_project_description.preview AS product_preview')
            ->addSelect('product_project_description.description AS product_description')
            ->leftJoin(
                'product_project',
                ProductProjectDescription::class,
                'product_project_description',
                '
                        product_project_description.project = product_project.id 
                        AND product_project_description.local = :local
                        AND product_project_description.device = :device
                    ',
            )->setParameter(
                key: 'device',
                value: 'pc',
            );

        /* Получить товарную наценку (скидку) по сезонности с учетом текущего месяца */
        $dbal
            ->addSelect('product_project_season.percent as season_percent')
            ->leftJoin(
                'product_project',
                ProductProjectSeason::class,
                'product_project_season',
                'product_project_season.project = product_project.id
                AND product_project_season.month = EXTRACT(MONTH FROM CURRENT_DATE)::INT',
            );

        /** ProductInfo */
        $dbal
            ->addSelect('product_info.url')
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id',
            );


        /** Торговое предложение */
        $dbal
            ->addSelect('product_offer.id as product_offer_uid')
            ->addSelect('product_offer.const as product_offer_const')
            ->addSelect('product_offer.value as product_offer_value')
            ->addSelect('product_offer.postfix as product_offer_postfix')
            ->{($this->offer ? 'join' : 'leftJoin')} (
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event '
                .($this->offer ? ' AND LOWER(product_offer.value) = :product_offer_value' : '')
                .($this->postfix ? ' AND ( LOWER(product_offer.postfix) = :postfix OR product_offer.postfix IS NULL )' : ''),
            );

        if($this->offer)
        {
            $dbal->setParameter(
                key: 'product_offer_value',
                value: $this->offer,
            );
        }

        if($this->postfix)
        {
            $dbal->setParameter(
                key: 'postfix',
                value: $this->postfix,
            );
        }

        /** Получаем тип торгового предложения */
        $dbal
            ->addSelect('category_offer.reference AS product_offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer',
            );

        /** Получаем название торгового предложения */
        $dbal
            ->addSelect('category_offer_trans.name as product_offer_name')
            ->addSelect('category_offer_trans.postfix as product_offer_name_postfix')
            ->leftJoin(
                'category_offer',
                CategoryProductOffersTrans::class,
                'category_offer_trans',
                'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local',
            );

        /** Множественные варианты торгового предложения */
        $dbal
            ->addSelect('product_variation.id as product_variation_uid')
            ->addSelect('product_variation.const as product_variation_const')
            ->addSelect('product_variation.value as product_variation_value')
            ->addSelect('product_variation.postfix as product_variation_postfix')
            ->{($this->variation ? 'join' : 'leftJoin')}(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id'
                .($this->variation ? ' AND LOWER(product_variation.value) = :product_variation_value' : '')
                .($this->postfix ? ' AND ( LOWER(product_variation.postfix) = :postfix OR product_variation.postfix IS NULL )' : ''),
            );

        if($this->variation)
        {
            $dbal->setParameter(
                key: 'product_variation_value',
                value: $this->variation,
            );
        }


        /** Получаем тип множественного варианта */
        $dbal
            ->addSelect('category_variation.reference as product_variation_reference')
            ->leftJoin(
                'product_variation',
                CategoryProductVariation::class,
                'category_variation',
                'category_variation.id = product_variation.category_variation',
            );

        /** Получаем название множественного варианта */
        $dbal
            ->addSelect('category_variation_trans.name as product_variation_name')
            ->addSelect('category_variation_trans.postfix as product_variation_name_postfix')
            ->leftJoin(
                'category_variation',
                CategoryProductVariationTrans::class,
                'category_variation_trans',
                'category_variation_trans.variation = category_variation.id AND category_variation_trans.local = :local',
            );


        /** Модификация множественного варианта торгового предложения */
        $dbal
            ->addSelect('product_modification.id as product_modification_uid')
            ->addSelect('product_modification.const as product_modification_const')
            ->addSelect('product_modification.value as product_modification_value')
            ->addSelect('product_modification.postfix as product_modification_postfix')
            ->{($this->modification ? 'join' : 'leftJoin')}(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id'
                .($this->modification ? ' AND LOWER(product_modification.value) = :product_modification_value' : '')
                .($this->postfix ? ' AND ( LOWER(product_modification.postfix) = :postfix OR product_modification.postfix IS NULL )' : ''),
            );

        if($this->modification)
        {
            $dbal->setParameter(
                key: 'product_modification_value',
                value: $this->modification,
            );
        }

        /** Получаем тип модификации множественного варианта */
        $dbal
            ->addSelect('category_modification.reference as product_modification_reference')
            ->leftJoin(
                'product_modification',
                CategoryProductModification::class,
                'category_modification',
                'category_modification.id = product_modification.category_modification',
            );

        /** Получаем название типа модификации */
        $dbal
            ->addSelect('category_modification_trans.name as product_modification_name')
            ->addSelect('category_modification_trans.postfix as product_modification_name_postfix')
            ->leftJoin(
                'category_modification',
                CategoryProductModificationTrans::class,
                'category_modification_trans',
                'category_modification_trans.modification = category_modification.id AND category_modification_trans.local = :local',
            );


        /** Базовая Цена товара */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event',
        );

        /** Цена торгового предо жения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id',
        );

        /** Цена множественного варианта */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id',
        );

        /** Цена модификации множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id',
        );

        /** Артикул продукта */
        $dbal->addSelect('
            COALESCE(
                product_modification.article, 
                product_variation.article, 
                product_offer.article, 
                product_info.article
            ) AS product_article
		');

        /** Фото продукта */
        $dbal
            ->leftJoin(
                'product',
                ProductPhoto::class,
                'product_photo',
                'product_photo.event = product.event',
            )
            ->addGroupBy('product_photo.ext');


        /** Фото торговых предложений */
        $dbal
            ->leftJoin(
                'product_offer',
                ProductOfferImage::class,
                'product_offer_images',
                'product_offer_images.offer = product_offer.id',
            )
            ->addGroupBy('product_offer_images.ext');

        /** Фото вариантов */
        $dbal
            ->leftJoin(
                'product_offer',
                ProductVariationImage::class,
                'product_variation_image',
                'product_variation_image.variation = product_variation.id',
            )
            ->addGroupBy('product_variation_image.ext');

        /** Фото модификаций */
        $dbal
            ->leftJoin(
                'product_modification',
                ProductModificationImage::class,
                'product_modification_image',
                'product_modification_image.modification = product_modification.id',
            )
            ->addGroupBy('product_modification_image.ext');

        /** Агрегация фотографий */
        $dbal->addSelect("
            CASE 
            WHEN product_modification_image.ext IS NOT NULL THEN
                JSON_AGG 
                    (DISTINCT
                        JSONB_BUILD_OBJECT
                            (
                                'product_img_root', product_modification_image.root,
                                'product_img', CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name),
                                'product_img_ext', product_modification_image.ext,
                                'product_img_cdn', product_modification_image.cdn
                            )
                    )
            
            WHEN product_variation_image.ext IS NOT NULL THEN
                JSON_AGG
                    (DISTINCT
                    JSONB_BUILD_OBJECT
                        (
                            'product_img_root', product_variation_image.root,
                            'product_img', CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name),
                            'product_img_ext', product_variation_image.ext,
                            'product_img_cdn', product_variation_image.cdn
                        ) 
                    )
                    
            WHEN product_offer_images.ext IS NOT NULL THEN
            JSON_AGG
                (DISTINCT
                    JSONB_BUILD_OBJECT
                        (
                            'product_img_root', product_offer_images.root,
                            'product_img', CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name),
                            'product_img_ext', product_offer_images.ext,
                            'product_img_cdn', product_offer_images.cdn
                        )
                        
                )
                
            WHEN product_photo.ext IS NOT NULL THEN
            JSON_AGG
                (DISTINCT
                    JSONB_BUILD_OBJECT
                        (
                            'product_img_root', product_photo.root,
                            'product_img', CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name),
                            'product_img_ext', product_photo.ext,
                            'product_img_cdn', product_photo.cdn
                        )
                    
                )
            
            ELSE NULL
            END
			AS product_images",
        );

        /** Стоимость продукта */
        $dbal->addSelect('
			COALESCE(
                NULLIF(product_modification_price.price, 0), 
                NULLIF(product_variation_price.price, 0), 
                NULLIF(product_offer_price.price, 0), 
                NULLIF(product_price.price, 0),
                0
            ) AS product_price
		');

        /** Предыдущая стоимость продукта */
        $dbal->addSelect("
			COALESCE(
                NULLIF(product_modification_price.old, 0),
                NULLIF(product_variation_price.old, 0),
                NULLIF(product_offer_price.old, 0),
                NULLIF(product_price.old, 0),
                0
            ) AS product_old_price
		");

        /** Валюта продукта */
        $dbal->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 
			   THEN product_modification_price.currency
			   
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 
			   THEN product_variation_price.currency
			   
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 
			   THEN product_offer_price.currency
			   
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 
			   THEN product_price.currency
			   
			   ELSE NULL
			END AS product_currency
		');


        /** Получаем информацию о магазине */


        /**
         * Наличие продукции на складе
         * Если подключен модуль складского учета и передан идентификатор профиля
         */

        if($this->region && class_exists(BaksDevProductsStocksBundle::class))
        {
            /* Получаем все профили данного региона */

            $dbal
                ->leftJoin(
                    'product',
                    UserProfileRegion::class,
                    'product_profile_region',
                    'product_profile_region.value = :region',
                )->setParameter(
                    key: 'region',
                    value: $this->region,
                    type: RegionUid::TYPE,
                );

            $dbal
                ->join(
                    'product_profile_region',
                    UserProfile::class,
                    'product_region_total',
                    'product_region_total.event = product_profile_region.event',
                );


            $dbal
                ->addSelect("JSON_AGG ( 
                        DISTINCT JSONB_BUILD_OBJECT (
                            'value', product_region_delivery.value, 
                            'day', product_region_delivery.day 
                        )) FILTER (WHERE product_region_delivery.day IS NOT NULL)
            
                        AS product_region_delivery",
                )
                ->leftJoin(
                    'product_profile_region',
                    UserProfileDelivery::class,
                    'product_region_delivery',
                    'product_region_delivery.event = product_profile_region.event',
                );


            $dbal
                ->addSelect("JSON_AGG ( 
                        DISTINCT JSONB_BUILD_OBJECT (
                            'total', stock.total, 
                            'reserve', stock.reserve 
                        )) FILTER (WHERE stock.total > stock.reserve)
            
                        AS product_quantity_stocks",
                )
                ->leftJoin(
                    'product_region_total',
                    ProductStockTotal::class,
                    'stock',
                    '
                    stock.profile = product_region_total.id AND
                    stock.product = product.id 
                    
                    AND
                        
                        CASE 
                            WHEN product_offer.const IS NOT NULL 
                            THEN stock.offer = product_offer.const
                            ELSE stock.offer IS NULL
                        END
                            
                    AND 
                    
                        CASE
                            WHEN product_variation.const IS NOT NULL 
                            THEN stock.variation = product_variation.const
                            ELSE stock.variation IS NULL
                        END
                        
                    AND
                    
                        CASE
                            WHEN product_modification.const IS NOT NULL 
                            THEN stock.modification = product_modification.const
                            ELSE stock.modification IS NULL
                        END
                ');

        }

        /**
         * Наличие и резерв продукции в карточке
         */

        /** Наличие и резерв торгового предложения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferQuantity::class,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id',
        );

        /** Наличие и резерв множественного варианта */
        $dbal->leftJoin(
            'category_variation',
            ProductVariationQuantity::class,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_variation.id',
        );

        /** Наличие и резерв модификации множественного варианта */
        $dbal->leftJoin(
            'category_modification',
            ProductModificationQuantity::class,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_modification.id',
        );

        $dbal
            ->addSelect("JSON_AGG (
                        DISTINCT JSONB_BUILD_OBJECT (
                            
                            
                            'total', COALESCE(
                                            product_modification_quantity.quantity, 
                                            product_variation_quantity.quantity, 
                                            product_offer_quantity.quantity, 
                                            product_price.quantity,
                                            0
                                        ), 
                            
                            
                            'reserve', COALESCE(
                                            product_modification_quantity.reserve, 
                                            product_variation_quantity.reserve, 
                                            product_offer_quantity.reserve, 
                                            product_price.reserve,
                                            0
                                        )
                        ) )
            
                        AS product_quantity",
            );


        /** Категория */
        $dbal->join(
            'product',
            ProductCategory::class,
            'product_event_category',
            'product_event_category.event = product.event AND product_event_category.root = true',
        );

        $dbal
            ->addSelect('category.id AS category_id')
            ->join(
                'product_event_category',
                CategoryProduct::class,
                'category',
                'category.id = product_event_category.category',
            );

        $dbal
            ->addSelect('category_trans.name AS category_name')
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category.event AND category_trans.local = :local',
            );

        $dbal
            ->addSelect('category_info.url AS category_url')
            ->addSelect('category_info.minimal AS category_minimal')
            ->addSelect('category_info.input AS category_input')
            ->addSelect('category_info.threshold AS category_threshold')
            ->addSelect('category_info.step AS category_step')
            ->leftJoin(
                'category',
                CategoryProductInfo::class,
                'category_info',
                'category_info.event = category.event',
            );

        $dbal->leftJoin(
            'category',
            CategoryProductSection::class,
            'category_section',
            'category_section.event = category.event',
        );

        /** Свойства, участвующие в карточке */
        $dbal->leftJoin(
            'category_section',
            CategoryProductSectionField::class,
            'category_section_field',
            'category_section_field.section = category_section.id',
        );

        $dbal->leftJoin(
            'category_section_field',
            CategoryProductSectionFieldTrans::class,
            'category_section_field_trans',
            'category_section_field_trans.field = category_section_field.id 
            AND category_section_field_trans.local = :local',
        );

        /** Обложка категории */
        $dbal
            ->addSelect("
                CASE
                   WHEN category_cover.name IS NOT NULL THEN
                        CONCAT ( '/upload/".$dbal->table(CategoryProductCover::class)."' , '/', category_cover.name)
                   ELSE NULL
                END AS category_cover_path
		    ")
            ->addSelect('category_cover.ext AS category_cover_ext')
            ->addSelect('category_cover.cdn AS category_cover_cdn')
            ->leftJoin(
                'category',
                CategoryProductCover::class,
                'category_cover',
                'category_cover.event = category.event',
            );


        $dbal->leftJoin(
            'category_section_field',
            ProductProperty::class,
            'product_property',
            'product_property.event = product.event AND product_property.field = category_section_field.const',
        );

        $dbal->addSelect(
            "JSON_AGG
            ( DISTINCT
                
                    JSONB_BUILD_OBJECT
                    (
                    
                        '0', category_section_field.sort, /* сортирвока */
                    
                        'field_uid', category_section_field.id,
                        'field_const', category_section_field.const,
                        'field_name', category_section_field.name,
                        'field_alternative', category_section_field.alternative,
                        'field_public', category_section_field.public,
                        'field_card', category_section_field.card,
                        'field_type', category_section_field.type,
                        'field_trans', category_section_field_trans.name,
                        'field_value', product_property.value
                    )
                
            )
			AS category_section_field",
        );

        /** ProductInvariable */
        $dbal
            ->addSelect('product_invariable.id AS product_invariable_id')
            ->leftJoin(
                'product_modification',
                ProductInvariable::class,
                'product_invariable',
                '
                    product_invariable.product = product.id AND 
                    
                    (
                        (product_offer.const IS NOT NULL AND product_invariable.offer = product_offer.const) OR 
                        (product_offer.const IS NULL AND product_invariable.offer IS NULL)
                    )
                    
                    AND
                     
                    (
                        (product_variation.const IS NOT NULL AND product_invariable.variation = product_variation.const) OR 
                        (product_variation.const IS NULL AND product_invariable.variation IS NULL)
                    )
                     
                   AND
                   
                   (
                        (product_modification.const IS NOT NULL AND product_invariable.modification = product_modification.const) OR 
                        (product_modification.const IS NULL AND product_invariable.modification IS NULL)
                   )
         
            ');

        /**
         * ProductsPromotion
         */
        if(true === class_exists(BaksDevProductsPromotionBundle::class) && true === $dbal->isProjectProfile())
        {
            $dbal
                ->leftJoin(
                    'product_invariable',
                    ProductPromotionInvariable::class,
                    'product_promotion_invariable',
                    '
                        product_promotion_invariable.product = product_invariable.id
                        AND product_promotion_invariable.profile = :'.$dbal::PROJECT_PROFILE_KEY,
                );

            $dbal
                ->leftJoin(
                    'product_promotion_invariable',
                    ProductPromotion::class,
                    'product_promotion',
                    'product_promotion.id = product_promotion_invariable.main',
                );

            $dbal
                ->addSelect('product_promotion_price.value AS promotion_price')
                ->leftJoin(
                    'product_promotion',
                    ProductPromotionPrice::class,
                    'product_promotion_price',
                    'product_promotion_price.event = product_promotion.event',
                );

            $dbal
                ->addSelect('
                CASE
                    WHEN 
                        CURRENT_DATE >= product_promotion_period.date_start
                        AND
                         (
                            product_promotion_period.date_end IS NULL OR CURRENT_DATE <= product_promotion_period.date_end
                         )
                    THEN true
                    ELSE false
                END AS promotion_active
            ')
                ->leftJoin(
                    'product_promotion',
                    ProductPromotionPeriod::class,
                    'product_promotion_period',
                    '
                        product_promotion_period.event = product_promotion.event',
                );
        }

        /** Персональная скидка из профиля авторизованного пользователя */
        if(true === $dbal->bindCurrentProfile())
        {

            $dbal
                ->join(
                    'product',
                    UserProfile::class,
                    'current_profile',
                    '
                        current_profile.id = :'.$dbal::CURRENT_PROFILE_KEY,
                );

            $dbal
                ->addSelect('current_profile_discount.value AS profile_discount')
                ->leftJoin(
                    'current_profile',
                    UserProfileDiscount::class,
                    'current_profile_discount',
                    '
                        current_profile_discount.event = current_profile.event
                        ',
                );
        }

        /** Общая скидка (наценка) из профиля магазина */
        if(true === $dbal->bindProjectProfile())
        {
            $dbal
                ->join(
                    'product',
                    UserProfile::class,
                    'project_profile',
                    '
                        project_profile.id = :'.$dbal::PROJECT_PROFILE_KEY,
                );

            $dbal
                ->addSelect('project_profile_discount.value AS project_discount')
                ->leftJoin(
                    'project_profile',
                    UserProfileDiscount::class,
                    'project_profile_discount',
                    '
                        project_profile_discount.event = project_profile.event',
                );
        }

        $dbal->allGroupByExclude(['product_modification_postfix']);

        $result = $dbal
            ->enableCache('products-product', '1 day')
            ->fetchHydrate(ProductDetailByValueResult::class);

        return ($result instanceof ProductDetailByValueResult) ? $result : false;
    }
}