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
 *
 */

declare(strict_types=1);

namespace BaksDev\Products\Product\Repository\AllProductsByCategory;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Core\Type\Device\Device;
use BaksDev\Core\Type\Device\Devices\Desktop;
use BaksDev\DeliveryTransport\BaksDevDeliveryTransportBundle;
use BaksDev\DeliveryTransport\Entity\ProductParameter\DeliveryPackageProductParameter;
use BaksDev\Products\Category\Entity\CategoryProduct;
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
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Description\ProductDescription;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Modify\ProductModify;
use BaksDev\Products\Product\Entity\Offers\Barcode\ProductOfferBarcode;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\Price\ProductOfferPrice;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Barcode\ProductVariationBarcode;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Barcode\ProductModificationBarcode;
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
use BaksDev\Products\Product\Entity\Project\ProductProject;
use BaksDev\Products\Product\Entity\Project\Season\ProductProjectSeason;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Forms\ProductCategoryFilter\User\ProductCategoryFilterDTO;
use BaksDev\Products\Promotion\BaksDevProductsPromotionBundle;
use BaksDev\Products\Promotion\Entity\Event\Invariable\ProductPromotionInvariable;
use BaksDev\Products\Promotion\Entity\Event\Period\ProductPromotionPeriod;
use BaksDev\Products\Promotion\Entity\Event\Price\ProductPromotionPrice;
use BaksDev\Products\Promotion\Entity\ProductPromotion;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use BaksDev\Products\Stocks\Entity\Total\ProductStockTotal;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Discount\UserProfileDiscount;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;

final class AllProductsByCategoryRepository implements AllProductsByCategoryInterface
{
    private ProductCategoryFilterDTO|null $filter = null;

    private array|null $property = null;

    private UserProfileUid|false $profile = false;

    private CategoryProductUid|false $category = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator
    ) {}

    /** Фильтра по offer, variation, modification в зависимости от настроек */
    public function filter(ProductCategoryFilterDTO $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * @deprecated
     * Фильтр по свойствам категории
     */
    public function property(?array $property): self
    {
        if(empty($property))
        {
            return $this;
        }

        $this->property = $property;

        return $this;
    }

    public function forCategory(CategoryProduct|CategoryProductUid|null|false $category): self
    {
        if(empty($category))
        {
            $this->category = false;
            return $this;
        }

        if($category instanceof CategoryProduct)
        {
            $category = $category->getId();
        }

        $this->category = $category;

        return $this;
    }

    public function forProfile(UserProfile|UserProfileUid|null|false $profile): self
    {
        if(empty($profile))
        {
            $this->profile = false;
            return $this;
        }

        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }

    /** Метод возвращает все товары в категории */
    public function fetchAllProductByCategory(): Generator|false
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->addSelect('product_category.category');

        if(false === ($this->category instanceof CategoryProductUid))
        {
            $dbal->from(Product::class, 'product');

            $dbal->join(
                'product',
                ProductCategory::class,
                'product_category',
                '
                    product_category.event = product.event 
                    AND product_category.root IS TRUE
                ',
            );
        }
        else
        {
            $dbal
                ->from(ProductCategory::class, 'product_category')
                ->where('product_category.category = :category')
                ->andWhere('product_category.root IS TRUE')
                ->setParameter(
                    key: 'category',
                    value: $this->category,
                    type: CategoryProductUid::TYPE,
                );

            $dbal->join(
                'product_category',
                Product::class,
                'product',
                'product.event = product_category.event',
            );
        }

        $dbal->addSelect('product.id');

        $dbal
            ->addSelect('product_info.url')
            ->addSelect('product_info.sort')
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id',
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
            ->addSelect('product_desc.preview')
            ->leftJoin(
                'product',
                ProductDescription::class,
                'product_desc',
                '
                    product_desc.event = product.event 
                    AND product_desc.local = :local 
                    AND product_desc.device = :device
                ',
            )
            ->setParameter(
                key: 'device',
                value: new Device(Desktop::class),
                type: Device::TYPE,
            );

        $dbal
            ->addSelect('product_modify.mod_date AS modify')
            ->leftJoin(
                'product',
                ProductModify::class,
                'product_modify',
                'product_modify.event = product.event',
            );


        /**
         * Торговое предложение
         */

        $dbal
            ->addSelect('product_offer.value AS offer_value')
            ->addSelect('product_offer.postfix AS offer_postfix')
            ->leftJoin(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event',
            );

        /** Offer Barcode */

        $dbal
            ->leftJoin(
                'product_offer',
                ProductOfferBarcode::class,
                'product_offer_barcode',
                'product_offer_barcode.offer = product_offer.id',
            );

        /* Цена торгового предожения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id',
        );

        $dbal->leftJoin(
            'product_offer',
            ProductOfferQuantity::class,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id',
        );

        /* Получаем тип торгового предложения */
        $dbal
            ->addSelect('category_offer.reference as offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer',
            );


        /* Получаем название торгового предложения */
        $dbal
            ->addSelect('category_offer_trans.name as offer_name')
            ->addSelect('category_offer_trans.postfix as offer_name_postfix')
            ->leftJoin(
                'category_offer',
                CategoryProductOffersTrans::class,
                'category_offer_trans',
                'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local',
            );


        /**
         * Множественный вариант
         */

        $dbal
            ->addSelect('product_variation.value AS variation_value')
            ->addSelect('product_variation.postfix AS variation_postfix')
            ->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id',
            );

        /** Variation Barcode */

        $dbal
            ->leftJoin(
                'product_variation',
                ProductVariationBarcode::class,
                'product_variation_barcode',
                'product_variation_barcode.variation = product_variation.id',
            );

        $dbal->leftJoin(
            'product_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id',
        );

        $dbal->leftJoin(
            'category_variation',
            ProductVariationQuantity::class,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_variation.id',
        );

        $dbal
            ->addSelect('category_variation.reference as variation_reference')
            ->leftJoin(
                'product_variation',
                CategoryProductVariation::class,
                'category_variation',
                'category_variation.id = product_variation.category_variation',
            );

        /* Получаем название множественного варианта */
        $dbal
            ->addSelect('category_variation_trans.name as variation_name')
            ->addSelect('category_variation_trans.postfix as variation_name_postfix')
            ->leftJoin(
                'category_variation',
                CategoryProductVariationTrans::class,
                'category_variation_trans',
                'category_variation_trans.variation = category_variation.id AND category_variation_trans.local = :local',
            );

        /**
         * Модификация множественного варианта торгового предложения
         */

        $dbal
            ->addSelect('product_modification.value AS modification_value')
            ->addSelect('product_modification.postfix AS modification_postfix')
            ->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id',
            );

        /** Modification Barcode */

        $dbal
            ->leftJoin(
                'product_modification',
                ProductModificationBarcode::class,
                'product_modification_barcode',
                'product_modification_barcode.modification = product_modification.id',
            );

        /** Цена множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id',
        );

        $dbal->leftJoin(
            'category_modification',
            ProductModificationQuantity::class,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_modification.id',
        );

        /** Получаем тип модификации множественного варианта */
        $dbal
            ->addSelect('category_modification.reference as modification_reference')
            ->leftJoin(
                'product_modification',
                CategoryProductModification::class,
                'category_modification',
                'category_modification.id = product_modification.category_modification',
            );

        /* Получаем название типа модификации */
        $dbal
            ->addSelect('category_modification_trans.name as modification_name')
            ->addSelect('category_modification_trans.postfix as modification_name_postfix')
            ->leftJoin(
                'category_modification',
                CategoryProductModificationTrans::class,
                'category_modification_trans',
                'category_modification_trans.modification = category_modification.id AND category_modification_trans.local = :local',
            );


        /** Цена товара */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event',
        );


        /** Стоимость */
        $dbal->addSelect('
			COALESCE(
                NULLIF(product_modification_price.price, 0), 
                NULLIF(product_variation_price.price, 0), 
                NULLIF(product_offer_price.price, 0), 
                NULLIF(product_price.price, 0),
                0
            ) AS product_price
		');


        /* Предыдущая стоимость продукта */

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
            "
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 
			   THEN product_modification_price.currency
			   
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0  
			   THEN product_variation_price.currency
			   
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 
			   THEN product_offer_price.currency
			   
			   WHEN product_price.price IS NOT NULL 
			   THEN product_price.currency
			   
			   ELSE NULL
			END AS product_currency
		");


        /** Остатки товара на складе */

        $dbal->addSelect(
            '
			CASE

			    WHEN product_modification_quantity.quantity > 0 AND product_modification_quantity.quantity > product_modification_quantity.reserve 
			   THEN (product_modification_quantity.quantity - product_modification_quantity.reserve)

			   WHEN product_variation_quantity.quantity > 0 AND product_variation_quantity.quantity > product_variation_quantity.reserve  
			   THEN (product_variation_quantity.quantity - product_variation_quantity.reserve)
			
			   WHEN product_offer_quantity.quantity > 0 AND product_offer_quantity.quantity > product_offer_quantity.reserve 
			   THEN (product_offer_quantity.quantity - product_offer_quantity.reserve)

			   WHEN product_price.quantity > 0 AND product_price.quantity > product_price.reserve 
			   THEN (product_price.quantity - product_price.reserve)
	
			   ELSE 0
			END AS product_quantity
		');

        /**
         * Если имеется складской учет и передан идентификатор профиля магазина
         */
        if(class_exists(BaksDevProductsStocksBundle::class) && true === ($this->profile instanceof UserProfileUid))
        {
            $dbal
                ->addSelect("JSON_AGG ( 
                        DISTINCT JSONB_BUILD_OBJECT (
                            'total', stock.total, 
                            'reserve', stock.reserve 
                        )) AS stocks_quantity",
                )
                ->leftJoin(
                    'product_modification',
                    ProductStockTotal::class,
                    'stock',
                    '
                    stock.profile = :stock_profile AND
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
                ')
                ->setParameter(
                    key: 'stock_profile',
                    value: $this->profile,
                    type: UserProfileUid::TYPE,
                );


            $dbal->andWhere('(stock.total - stock.reserve) > 0');
        }

        $dbal
            ->addSelect('product_invariable.id AS product_invariable')
            ->leftJoin(
                'product_modification',
                ProductInvariable::class,
                'product_invariable',
                '
                    product_invariable.product = product.id
                    
                    AND
                        CASE 
                            WHEN product_offer.const IS NOT NULL 
                            THEN product_invariable.offer = product_offer.const
                            ELSE product_invariable.offer IS NULL
                        END
                    AND 
                        CASE
                            WHEN product_variation.const IS NOT NULL 
                            THEN product_invariable.variation = product_variation.const
                            ELSE product_invariable.variation IS NULL
                        END
                    AND
                        CASE
                            WHEN product_modification.const IS NOT NULL 
                            THEN product_invariable.modification = product_modification.const
                            ELSE product_invariable.modification IS NULL
                        END
                ');


        /** Общая скидка (наценка) из профиля магазина */
        if(true === $dbal->bindProjectProfile())
        {

            $dbal
                ->join(
                    'product',
                    UserProfile::class,
                    'project_profile',
                    'project_profile.id = :'.$dbal::PROJECT_PROFILE_KEY,
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

        /* Получить товарную наценку (скидку) по сезонности с учетом текущего месяца */
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
            ->addSelect('product_project_season.percent as season_percent')
            ->leftJoin(
                'product_project',
                ProductProjectSeason::class,
                'product_project_season',
                'product_project_season.project = product_project.id
                AND product_project_season.month = EXTRACT(MONTH FROM CURRENT_DATE)::INT',
            );


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


        /** Фото продукта */

        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_modification_image',
            '
			product_modification_image.modification = product_modification.id AND
			product_modification_image.root = true
			',
        );

        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            '
			product_variation_image.variation = product_variation.id AND
			product_variation_image.root = true
			',
        );

        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            '
			product_variation_image.name IS NULL AND
			product_offer_images.offer = product_offer.id AND
			product_offer_images.root = true
			',
        );

        $dbal->leftJoin(
            'product_offer',
            ProductPhoto::class,
            'product_photo',
            '
			product_offer_images.name IS NULL AND
			product_photo.event = product.event AND
			product_photo.root = true
			',
        );

        $dbal->addSelect(
            "
			CASE
			 WHEN product_modification_image.name IS NOT NULL 
			 THEN CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name)
					
			   WHEN product_variation_image.name IS NOT NULL 
			   THEN CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name)
					
			   WHEN product_offer_images.name IS NOT NULL 
			   THEN CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name)
					
			   WHEN product_photo.name IS NOT NULL 
			   THEN CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name)
					
			   ELSE NULL
			END AS product_image
		",
        );

        /** Расширение файла */
        $dbal->addSelect(
            "
			CASE
                WHEN product_modification_image.ext IS NOT NULL AND product_modification_image.name IS NOT NULL 
                THEN product_modification_image.ext
					
			   WHEN product_variation_image.ext IS NOT NULL AND product_variation_image.name IS NOT NULL 
			   THEN product_variation_image.ext
					
			   WHEN product_offer_images.ext IS NOT NULL AND product_offer_images.name IS NOT NULL 
			   THEN product_offer_images.ext
					
			   WHEN product_photo.ext IS NOT NULL AND product_photo.name IS NOT NULL 
			   THEN product_photo.ext
					
			   ELSE NULL
			END AS product_image_ext
		",
        );

        /** Флаг загрузки файла CDN */
        $dbal->addSelect(
            "
			CASE
			    WHEN product_modification_image.cdn IS NOT NULL AND product_modification_image.name IS NOT NULL 
			    THEN product_modification_image.cdn
					
			   WHEN product_variation_image.cdn IS NOT NULL AND product_variation_image.name IS NOT NULL 
			   THEN product_variation_image.cdn
					
			   WHEN product_offer_images.cdn IS NOT NULL AND product_offer_images.name IS NOT NULL 
			   THEN product_offer_images.cdn
					
			   WHEN product_photo.cdn IS NOT NULL AND product_photo.name IS NOT NULL 
			   THEN product_photo.cdn
					
			   ELSE NULL
			END AS product_image_cdn
		");


        /** Свойства, учавствующие в карточке */

        $dbal->leftJoin(
            'product_category',
            CategoryProduct::class,
            'category',
            'category.id = product_category.category',
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

        $dbal
            ->addSelect('category_trans.name AS category_name')
            ->addSelect('category_trans.description AS category_desc')
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category.event AND category_trans.local = :local',
            );

        $dbal->leftJoin(
            'category',
            CategoryProductSection::class,
            'category_section',
            'category_section.event = category.event',
        );


        $dbal->leftJoin(
            'category_section',
            CategoryProductSectionField::class,
            'category_section_field',
            'category_section_field.section = category_section.id 
            AND category_section_field.card = TRUE',
        );

        $dbal->leftJoin(
            'category_section_field',
            CategoryProductSectionFieldTrans::class,
            'category_section_field_trans',
            'category_section_field_trans.field = category_section_field.id 
            AND category_section_field_trans.local = :local',
        );


        $dbal->leftJoin(
            'category_section_field',
            ProductProperty::class,
            'product_property',
            'product_property.event = product.event 
            AND product_property.field = category_section_field.const',
        );


        /* Артикул продукта */

        $dbal->addSelect('
            COALESCE(
                product_modification.article, 
                product_variation.article, 
                product_offer.article, 
                product_info.article
            ) AS product_article
		');


        /** Штрихкоды продукта */

        $dbal->addSelect(
            "
            JSON_AGG
                    (DISTINCT
         			CASE
         			    WHEN product_modification_barcode.value IS NOT NULL
                        THEN product_modification_barcode.value
                        
                        WHEN product_variation_barcode.value IS NOT NULL
                        THEN product_variation_barcode.value
                        
                        WHEN product_offer_barcode.value IS NOT NULL
                        THEN product_offer_barcode.value
                        
                        WHEN product_info.barcode IS NOT NULL
                        THEN product_info.barcode
                        
                        ELSE NULL
                    END
                    )
                    AS barcodes",
        );


        $dbal->addSelect(
            '
			CASE
			   WHEN product_modification.barcode_old IS NOT NULL 
			   THEN product_modification.barcode_old
			   
			   WHEN product_variation.barcode_old IS NOT NULL 
			   THEN product_variation.barcode_old
			   
			   WHEN product_offer.barcode_old IS NOT NULL 
			   THEN product_offer.barcode_old
			   
			   WHEN product_info.barcode IS NOT NULL 
			   THEN product_info.barcode
			   
			   ELSE NULL
			END AS product_barcode
		',
        );


        $dbal->addSelect(
            "JSON_AGG
		( DISTINCT

				JSONB_BUILD_OBJECT
				(
					'0', category_section_field.sort,
					'field_uid', category_section_field.id,
					'field_const', category_section_field.const,
					'field_name', category_section_field.name,
					'field_card', category_section_field.card,
					'field_type', category_section_field.type,
					'field_trans', category_section_field_trans.name,
					'field_value', product_property.value
				)

		)
			AS category_section_field",
        );


        /**  Вес товара  */

        if(class_exists(BaksDevDeliveryTransportBundle::class))
        {

            $dbal
                ->addSelect('product_package.length AS product_parameter_length')
                ->addSelect('product_package.width AS product_parameter_width')
                ->addSelect('product_package.height AS product_parameter_height')
                ->addSelect('product_package.weight AS product_parameter_weight')
                ->leftJoin(
                    'product_modification',
                    DeliveryPackageProductParameter::class,
                    'product_package',
                    '
                    
                    product_package.product = product.id  

                    AND
                        
                        CASE 
                            WHEN product_offer.const IS NOT NULL 
                            THEN product_package.offer = product_offer.const
                            ELSE product_package.offer IS NULL
                        END
                            
                    AND 
                    
                        CASE
                            WHEN product_variation.const IS NOT NULL 
                            THEN product_package.variation = product_variation.const
                            ELSE product_package.variation IS NULL
                        END
                        
                    AND
                    
                        CASE
                            WHEN product_modification.const IS NOT NULL 
                            THEN product_package.modification = product_modification.const
                            ELSE product_package.modification IS NULL
                        END

            ');

        }


        $dbal->addOrderBy('product_info.sort', 'DESC');

        $dbal->allGroupByExclude();

        //$dbal->setMaxResults(1000);

        //        return $dbal
        //            ->enableCache('products-product', 86400)
        //            ->fetchAllAssociative();

        return $dbal
            ->enableCache('products-product', '1 day')
            ->fetchAllHydrate(AllProductsByCategoryResult::class);

    }
}
