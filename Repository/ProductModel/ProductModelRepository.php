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
use BaksDev\Users\Profile\UserProfile\Entity\Event\Discount\UserProfileDiscount;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Region\UserProfileRegion;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use Doctrine\DBAL\ParameterType;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** @see ProductModelResult */
final class ProductModelRepository implements ProductModelInterface
{
    private string|false $offer = false;

    private string|false $variation = false;

    private ProductUid|false $productUid = false;

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
    public function byOffer(string|null $offer): self
    {
        if(is_null($offer))
        {
            $this->offer = false;
            return $this;
        }

        $this->offer = $offer;
        return $this;
    }

    /** Фильтрация по Variation */
    public function byVariation(string|null $variation): self
    {
        if(is_null($variation))
        {
            $this->variation = false;
            return $this;
        }

        $this->variation = $variation;
        return $this;
    }

    /** Информация о модели со списком offer, variation, modification */
    public function find(): ProductModelResult|false
    {
        $builder = $this->builder();

        $builder->enableCache('products-product', 86400);
        $result = $builder->fetchHydrate(ProductModelResult::class);

        return ($result instanceof ProductModelResult) ? $result : false;
    }

    public function builder(): DBALQueryBuilder
    {
        if(false === $this->productUid)
        {
            throw new InvalidArgumentException('Не передан обязательный параметр $productUid');
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->addSelect('product.event')
            ->from(Product::class, 'product');

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

        /** Цена товара */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event',
        );

        /** ProductInfo */
        $dbal
            ->addSelect('product_info.url')
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id ',
            );

        /** OFFERS */
        if(false === $this->offer)
        {
            $dbal->leftJoin(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event',
            );
        }
        else
        {
            $dbal->leftJoin(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event AND product_offer.value = :product_offer_value',
            );

            $dbal->setParameter('product_offer_value', $this->offer);
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
            ->leftJoin(
                'category_offer',
                CategoryProductOffersTrans::class,
                'category_offer_trans',
                'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local',
            );

        /** Цена OFFERS */
        $dbal
            ->leftOneJoin(
                'product_offer',
                ProductOfferPrice::class,
                'product_offer_price',
                'product_offer_price.offer = product_offer.id',
                'offer',
            );

        /** Наличие и резерв торгового предложения */
        $dbal
            ->leftJoin(
                'product_offer',
                ProductOfferQuantity::class,
                'product_offer_quantity',
                'product_offer_quantity.offer = product_offer.id',
            );


        /** VARIATION */
        if(false === $this->variation)
        {
            $dbal
                ->leftJoin(
                    'product_offer',
                    ProductVariation::class,
                    'product_variation',
                    'product_variation.offer = product_offer.id',
                );
        }
        else
        {
            $dbal
                ->leftJoin(
                    'product_offer',
                    ProductVariation::class,
                    'product_variation',
                    'product_variation.offer = product_offer.id AND product_variation.value = :product_variation_value',
                );

            $dbal->setParameter('product_variation_value', $this->variation);
        }

        $dbal
            ->leftJoin(
                'product_variation',
                CategoryProductVariation::class,
                'category_variation',
                'category_variation.id = product_variation.category_variation',
            );


        $dbal
            ->leftJoin(
                'category_variation',
                CategoryProductVariationTrans::class,
                'category_variation_trans',
                'category_variation_trans.variation = category_variation.id AND category_variation_trans.local = :local',
            );

        $dbal
            ->leftOneJoin(
                'product_variation',
                ProductVariationPrice::class,
                'product_variation_price',
                'product_variation_price.variation = product_variation.id',
                'variation',
            );

        /** Наличие и резерв множественного варианта */
        $dbal
            ->leftJoin(
                'category_variation',
                ProductVariationQuantity::class,
                'product_variation_quantity',
                'product_variation_quantity.variation = product_variation.id',
            );


        /** Модификация множественного варианта торгового предложения */
        $dbal
            ->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id',
            );

        /** Получаем название типа */
        $dbal
            ->leftJoin(
                'category_modification',
                CategoryProductModificationTrans::class,
                'category_modification_trans',
                'category_modification_trans.modification = category_modification.id AND category_modification_trans.local = :local',
            );

        $dbal
            ->leftJoin(
                'product_modification',
                CategoryProductModification::class,
                'category_modification',
                'category_modification.id = product_modification.category_modification',
            );


        $dbal
            ->leftOneJoin(
                'product_modification',
                ProductModificationPrice::class,
                'product_modification_price',
                'product_modification_price.modification = product_modification.id',
                'modification',
            );


        /** Наличие и резерв модификации множественного варианта */
        $dbal
            ->leftJoin(
                'category_modification',
                ProductModificationQuantity::class,
                'product_modification_quantity',
                'product_modification_quantity.modification = product_modification.id',
            );

        $dbal->leftJoin(
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

        /** Персональная скидка из профиля авторизованного пользователя */
        $profileDiscountSelect = "'profile_discount', NULL,";

        if(true === $dbal->bindCurrentProfile())
        {

            $profileDiscountSelect = "'profile_discount', current_profile_discount.value,";

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
        $projectDiscountSelect = "'project_discount', NULL,";

        if(true === $dbal->bindProjectProfile())
        {
            $projectDiscountSelect = "'project_discount', project_profile_discount.value,";

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


        /* Сезонная торговая наценка */
        $projectSeasonSelect = "'season_percent', NULL,";

        if(true === $dbal->bindProjectProfile())
        {
            $projectSeasonSelect = "'season_percent', product_project_season.percent,";

            $dbal
                ->addSelect('product_project_season.percent as season_percent')
                ->leftJoin(
                    'product_project',
                    ProductProjectSeason::class,
                    'product_project_season',
                    'product_project_season.project = product_project.id
                    AND product_project_season.month = :month',
                )
                ->setParameter(
                    key: 'month',
                    value: (int) date('n'),
                    type: ParameterType::INTEGER,
                );
        }


        /**
         * ProductsPromotion
         */

        $promotionPriceSelect = "'promotion_price', NULL,";

        $promotionActiveSelect = "'promotion_active', NULL,";

        if(true === class_exists(BaksDevProductsPromotionBundle::class) && true === $dbal->isProjectProfile())
        {
            $promotionPriceSelect = "'promotion_price', product_promotion_price.value,";
            $promotionActiveSelect = "'promotion_active',  
                                CASE
                                    WHEN 
                                        CURRENT_DATE >= product_promotion_period.date_start
                                        AND
                                         (
                                            product_promotion_period.date_end IS NULL OR CURRENT_DATE <= product_promotion_period.date_end
                                         )
                                    THEN true
                                    ELSE false
                                END,";

            $dbal
                ->leftJoin(
                    'product_invariable',
                    ProductPromotionInvariable::class,
                    'product_promotion_invariable',
                    '
                        product_promotion_invariable.product = product_invariable.id
                        AND
                        product_promotion_invariable.profile = :'.$dbal::PROJECT_PROFILE_KEY,
                );

            $dbal
                ->leftJoin(
                    'product_promotion_invariable',
                    ProductPromotion::class,
                    'product_promotion',
                    'product_promotion.id = product_promotion_invariable.main',
                );

            $dbal
                ->leftJoin(
                    'product_promotion',
                    ProductPromotionPrice::class,
                    'product_promotion_price',
                    'product_promotion_price.event = product_promotion.event',
                );

            $dbal
                ->leftJoin(
                    'product_promotion',
                    ProductPromotionPeriod::class,
                    'product_promotion_period',
                    '
                        product_promotion_period.event = product_promotion.event',
                );
        }


        /**
         * Наличие продукции на складе (необходимо для отображения кнопки "в корзину")
         * Если подключен модуль складского учета и передан идентификатор профиля
         */

        $productQuantityStocks = "'product_quantity_stocks', NULL,";

        if(false === empty($this->region) && class_exists(BaksDevProductsStocksBundle::class))
        {

            /* Создать отдельный QueryBuilder для подзапроса с профилями */
            $profilesQB = $dbal->createQueryBuilder(self::class)
                ->select('profile_total.id')
                ->from(UserProfileRegion::class, 'profile_region')
                ->join(
                    'profile_region',
                    UserProfile::class,
                    'profile_total',
                    'profile_total.event = profile_region.event'
                )
                ->where('profile_region.value = :region');

            /* Создать отдельный QueryBuilder для подзапроса с остатками */
            $stocksQB = $dbal->createQueryBuilder(self::class)
                ->select(
                    "COALESCE(
                        JSONB_AGG(
                            DISTINCT JSONB_BUILD_OBJECT(
                                'total', stock.total,
                                'reserve', stock.reserve
                            )
                        ),
                        NULL
                    )"
                )
                ->from(ProductStockTotal::class, 'stock')
                ->where('stock.profile IN ('.$profilesQB->getSQL().')')
                ->andWhere('stock.product = product.id')
                ->andWhere('stock.total > stock.reserve')
                ->andWhere('(product_offer.const IS NULL OR stock.offer = product_offer.const)')
                ->andWhere('(product_variation.const IS NULL OR stock.variation = product_variation.const)')
                ->andWhere('(product_modification.const IS NULL OR stock.modification = product_modification.const)');

            /* Задать параметр региона */
            $dbal->setParameter('region', $this->region, RegionUid::TYPE);

            /* Получить SQL подзапроса с остатками  */
            $stocksSubquery = '('.$stocksQB->getSQL().')';

            $productQuantityStocks = "'product_quantity_stocks', ".$stocksSubquery.",";
        }


        /** Продукты внутри категории */
        $dbal->addSelect(
            "JSON_AGG
        			( DISTINCT

        					JSONB_BUILD_OBJECT
        					(

        						/* свойства для сортировки JSON */
        						'0', CONCAT(product_offer.value, product_variation.value, product_modification.value, product_modification_price.price),
        						
        						'offer_uid', product_offer.id,
        						'offer_value', product_offer.value, /* значение торгового предложения */
        						'offer_postfix', product_offer.postfix, /* постфикс торгового предложения */
        						'offer_reference', category_offer.reference, /* тип (field) торгового предложения */
        						'offer_name', category_offer_trans.name, /* Название свойства */

        						'variation_uid', product_variation.id,
        						'variation_value', product_variation.value, /* значение множественного варианта */
        						'variation_postfix', product_variation.postfix, /* постфикс множественного варианта */
        						'variation_reference', category_variation.reference, /* тип (field) множественного варианта */
        						'variation_name', category_variation_trans.name, /* Название свойства */

        						'modification_uid', product_modification.id,
        						'modification_value', product_modification.value, /* значение модификации */
        						'modification_postfix', product_modification.postfix, /* постфикс модификации */
        						'modification_reference', category_modification.reference, /* тип (field) модификации */
        						'modification_name', category_modification_trans.name, /* артикул модификации */

        						'article', CASE
        						   WHEN product_modification.article IS NOT NULL THEN product_modification.article
        						   WHEN product_variation.article IS NOT NULL THEN product_variation.article
        						   WHEN product_offer.article IS NOT NULL THEN product_offer.article
        						   WHEN product_info.article IS NOT NULL THEN product_info.article
        						   ELSE NULL
        						END,

                                /* Product Invariable */
        						'product_invariable_id', COALESCE(
        						    product_invariable.id
        						),
        						
        						/* Profile Discount */
        						{$profileDiscountSelect}
        						
        						/* Project Discount */
        						{$projectDiscountSelect}
        						
        						/* Project Season */
        						{$projectSeasonSelect}
        						
                                /* Project Product Quantity Stocks */
                                {$productQuantityStocks}

        						/* Кастомная цена */
        						{$promotionPriceSelect}
        						{$promotionActiveSelect}
                        
        						'price', CASE
        						   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.price
        						   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 THEN product_variation_price.price
        						   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.price
        						   WHEN product_price.price IS NOT NULL AND product_price.price > 0 THEN product_price.price
        						   ELSE NULL
        						END,

                                'old_price', COALESCE(
                                    NULLIF(product_modification_price.old, 0),
                                    NULLIF(product_variation_price.old, 0),
                                    NULLIF(product_offer_price.old, 0),
                                    NULLIF(product_price.old, 0),
                                    0
                                ),

        						'currency', CASE
        						   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.currency
        						   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 THEN product_variation_price.currency
        						   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.currency
        						   WHEN product_price.price IS NOT NULL AND product_price.price > 0 THEN product_price.currency
        						   ELSE NULL
        						END,

        						'quantity', CASE
        						   WHEN product_modification_quantity.quantity IS NOT NULL THEN (product_modification_quantity.quantity - product_modification_quantity.reserve)
        						   WHEN product_variation_quantity.quantity IS NOT NULL THEN (product_variation_quantity.quantity - product_variation_quantity.reserve)
        						   WHEN product_offer_quantity.quantity IS NOT NULL THEN (product_offer_quantity.quantity - product_offer_quantity.reserve)
        						   WHEN product_price.quantity IS NOT NULL THEN (product_price.quantity - product_price.reserve)
        						   ELSE NULL
        						END
        					)
        			)
        			
        			    /* Только с ценой */
                        FILTER (WHERE 
                                    CASE
                                       WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 THEN product_modification_price.price
                                       WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 THEN product_variation_price.price
                                       WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 THEN product_offer_price.price
                                       WHEN product_price.price IS NOT NULL AND product_price.price > 0 THEN product_price.price
                                    ELSE 0
                                END > 0)
                
        			AS product_offers",
        );

        /** Фото PRODUCT */
        $dbal
            ->leftJoin(
                'product_offer',
                ProductPhoto::class,
                'product_photo',
                'product_photo.event = product.event',
            )
            ->addGroupBy('product_photo.ext');


        /** Фото OFFERS */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            'product_offer_images.offer = product_offer.id',
        )
            ->addGroupBy('product_offer_images.ext');


        /** Фото VARIATION */
        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            'product_variation_image.variation = product_variation.id',
        )
            ->addGroupBy('product_variation_image.ext');


        /** Фото MODIFICATION */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_modification_image',
            ' product_modification_image.modification = product_modification.id',
        )
            ->addGroupBy('product_modification_image.ext');

        /** Агрегация фото продуктов из offer, variation, modification */
        $dbal->addSelect("
                JSON_AGG 
                    (DISTINCT
                        CASE
                            WHEN product_modification_image.ext IS NOT NULL THEN
                                JSONB_BUILD_OBJECT
                                    (
                                        'product_img_root', product_modification_image.root,
                                        'product_img', CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name),
                                        'product_img_ext', product_modification_image.ext,
                                        'product_img_cdn', product_modification_image.cdn
                                    )
                            WHEN product_variation_image.ext IS NOT NULL THEN
                                    JSONB_BUILD_OBJECT
                                    (
                                        'product_img_root', product_variation_image.root,
                                        'product_img', CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name),
                                        'product_img_ext', product_variation_image.ext,
                                        'product_img_cdn', product_variation_image.cdn
                                    ) 
                            WHEN product_offer_images.ext IS NOT NULL THEN
                                JSONB_BUILD_OBJECT
                                    (
                                        'product_img_root', product_offer_images.root,
                                        'product_img', CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name),
                                        'product_img_ext', product_offer_images.ext,
                                        'product_img_cdn', product_offer_images.cdn
                                    )
                            WHEN product_photo.ext IS NOT NULL THEN
                                JSONB_BUILD_OBJECT
                                    (
                                        'product_img_root', product_photo.root,
                                        'product_img', CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name),
                                        'product_img_ext', product_photo.ext,
                                        'product_img_cdn', product_photo.cdn
                                    )
                            ELSE NULL
                        END)
			AS product_images",
        );

        /** Категория */
        $dbal->join(
            'product',
            ProductCategory::class,
            'product_event_category',
            'product_event_category.event = product.event AND product_event_category.root = true',
        );

        $dbal
            ->addSelect('category.id as category_id')
            ->join(
                'product_event_category',
                CategoryProduct::class,
                'category',
                'category.id = product_event_category.category',
            )
            ->groupBy('category.id');

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
            ->addSelect('category_info.threshold AS category_threshold')
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

        /** Обложка */
        $dbal
            ->addSelect('category_cover.ext AS category_cover_ext')
            ->addSelect('category_cover.cdn AS category_cover_cdn')
            ->addSelect(
                "
			CASE
                 WHEN category_cover.name IS NOT NULL
                 THEN CONCAT ( '/upload/".$dbal->table(CategoryProductCover::class)."' , '/', category_cover.name)
                 ELSE NULL
			END AS category_cover_dir",
            );

        $dbal->leftJoin(
            'category',
            CategoryProductCover::class,
            'category_cover',
            'category_cover.event = category.event',
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
            'category_section_field_trans.field = category_section_field.id AND category_section_field_trans.local = :local',
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
        					'0', category_section_field.sort,

        					'field_name', category_section_field.name,
        					'field_public', category_section_field.public,
        					'field_card', category_section_field.card,
        					'field_type', category_section_field.type,
        					'field_trans', category_section_field_trans.name,
        					'field_value', product_property.value
        				)
        		)
            AS category_section_field",
        );

        $dbal->allGroupByExclude();

        $dbal->where('product.id = :product');
        $dbal->setParameter('product', $this->productUid, ProductUid::TYPE);

        return $dbal;
    }

    public function analyze(): void
    {
        $this->builder()->analyze();
    }
}
