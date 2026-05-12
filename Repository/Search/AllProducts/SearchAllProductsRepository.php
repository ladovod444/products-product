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
 */

namespace BaksDev\Products\Product\Repository\Search\AllProducts;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Category\Entity\Section\CategoryProductSection;
use BaksDev\Products\Category\Entity\Section\Field\CategoryProductSectionField;
use BaksDev\Products\Category\Entity\Section\Field\Trans\CategoryProductSectionFieldTrans;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
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
use BaksDev\Products\Product\Entity\Project\ProductProject;
use BaksDev\Products\Product\Entity\Project\Season\ProductProjectSeason;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Seo\ProductSeo;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Type\SearchTags\ProductSearchTag;
use BaksDev\Products\Promotion\BaksDevProductsPromotionBundle;
use BaksDev\Products\Promotion\Entity\Event\Invariable\ProductPromotionInvariable;
use BaksDev\Products\Promotion\Entity\Event\Period\ProductPromotionPeriod;
use BaksDev\Products\Promotion\Entity\Event\Price\ProductPromotionPrice;
use BaksDev\Products\Promotion\Entity\ProductPromotion;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use BaksDev\Products\Stocks\Entity\Total\ProductStockTotal;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Search\Index\SearchIndexInterface;
use BaksDev\Search\Repository\SearchRepository\SearchRepositoryInterface;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Discount\UserProfileDiscount;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Region\UserProfileRegion;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Generator;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;

/**
 * Используется для нахождения сущностей в БД по индексу
 */
final class SearchAllProductsRepository implements SearchRepositoryInterface
{
    const int MAX_RESULTS = 10;

    private ?SearchDTO $search = null;

    private int|false $maxResult = false;

    public function __construct(
        #[Target('productsProductLogger')] private LoggerInterface $logger,
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly ?SearchIndexInterface $SearchIndexHandler = null,
        #[Autowire(env: 'PROJECT_REGION')] private readonly ?string $region = null,
    ) {}

    /** Максимальное количество записей в результате */
    public function maxResult(int $max): self
    {
        $this->maxResult = $max;
        return $this;
    }

    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function toArray(): array|false
    {
        $result = $this->findAll();

        return ($result && true === $result->valid()) ? iterator_to_array($result) : false;
    }

    public function findAll(): Generator|false
    {

        if(is_null($this->search))
        {
            throw new InvalidArgumentException('Не передан обязательный параметр запроса $search');
        }

        if(empty($this->search->getQuery()))
        {
            return false;
        }


        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->addSelect('product.event')
            ->from(Product::class, 'product');

        $dbal->leftJoin(
            'product',
            ProductEvent::class,
            'product_event',
            'product_event.id = product.event',
        );

        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product_event',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product_event.id AND product_trans.local = :local',
            );

        /* Базовая Цена товара */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event',
        );

        $dbal
            ->addSelect('product_info.url AS url')
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id ',
            );

        $dbal
            ->addSelect('seo.title AS search_desc')
            ->leftJoin(
                'product',
                ProductSeo::class,
                'seo',
                'seo.event = product.event',
            );


        /** Торговое предложение */

        $dbal
            ->addSelect('product_offer.id as product_offer_uid')
            ->addSelect('product_offer.const as product_offer_const')
            ->addSelect('product_offer.value as product_offer_value')
            ->addSelect('product_offer.postfix as product_offer_postfix')
            ->leftJoin(
                'product_event',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product_event.id',
            );

        /* Цена торгового предо жения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id',
        );

        /* Тип торгового предложения */
        $dbal
            ->addSelect('category_offer.reference as product_offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer',
            );


        /** Множественные варианты торгового предложения */

        $dbal
            ->addSelect('product_variation.id as product_variation_uid')
            ->addSelect('product_variation.const as product_variation_const')
            ->addSelect('product_variation.value as product_variation_value')
            ->addSelect('product_variation.postfix as product_variation_postfix')
            ->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id',
            );

        /* Цена множественного варианта */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id',
        );


        /* Тип множественного варианта торгового предложения */
        $dbal
            ->addSelect('category_variation.reference as product_variation_reference')
            ->leftJoin(
                'product_variation',
                CategoryProductVariation::class,
                'category_variation',
                'category_variation.id = product_variation.category_variation',
            );


        /** Модификация множественного варианта */
        $dbal
            ->addSelect('product_modification.id as product_modification_uid')
            ->addSelect('product_modification.const as product_modification_const')
            ->addSelect('product_modification.value as product_modification_value')
            ->addSelect('product_modification.postfix as product_modification_postfix')
            ->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id ',
            );

        /* Цена модификации множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id',
        );

        /** Получаем тип модификации множественного варианта */
        $dbal
            ->addSelect('category_modification.reference as product_modification_reference')
            ->leftJoin(
                'product_modification',
                CategoryProductModification::class,
                'category_modification',
                'category_modification.id = product_modification.category_modification',
            );


        /** Артикул продукта */

        $dbal->addSelect("
                COALESCE(
                    product_modification.article,
                    product_variation.article,
                    product_offer.article,
                    product_info.article
                ) AS product_article
            ");


        /**
         * Изображение продукта
         */
        $dbal->leftJoin(
            'product_event',
            ProductPhoto::class,
            'product_photo',
            'product_photo.event = product_event.id AND product_photo.root = true',
        )
            ->addGroupBy('product_photo.ext');

        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            'product_offer_images.offer = product_offer.id AND product_offer_images.root = true',
        )
            ->addGroupBy('product_offer_images.ext');

        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            'product_variation_image.variation = product_variation.id AND product_variation_image.root = true',
        )
            ->addGroupBy('product_variation_image.ext');

        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_modification_image',
            'product_modification_image.modification = product_modification.id AND product_modification_image.root = true',
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
                                'img_root', product_modification_image.root,
                                'img', CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name),
                                'img_ext', product_modification_image.ext,
                                'img_cdn', product_modification_image.cdn
                            )
                    )
            
            WHEN product_variation_image.ext IS NOT NULL THEN
                JSON_AGG
                    (DISTINCT
                    JSONB_BUILD_OBJECT
                        (
                            'img_root', product_variation_image.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name),
                            'img_ext', product_variation_image.ext,
                            'img_cdn', product_variation_image.cdn
                        ) 
                    )
                    
            WHEN product_offer_images.ext IS NOT NULL THEN
            JSON_AGG
                (DISTINCT
                    JSONB_BUILD_OBJECT
                        (
                            'img_root', product_offer_images.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name),
                            'img_ext', product_offer_images.ext,
                            'img_cdn', product_offer_images.cdn
                        )
                        
                    /*ORDER BY product_photo.root DESC, product_photo.id*/
                )
                
            WHEN product_photo.ext IS NOT NULL THEN
            JSON_AGG
                (DISTINCT
                    JSONB_BUILD_OBJECT
                        (
                            'img_root', product_photo.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name),
                            'img_ext', product_photo.ext,
                            'img_cdn', product_photo.cdn
                        )
                    
                    /*ORDER BY product_photo.root DESC, product_photo.id*/
                )
            
            ELSE NULL
            END
			AS product_root_image",
        );

        /* Стоимость продукта */

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

        /* Валюта продукта */

        $CURRENCY = 'CASE
                   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 
                   THEN product_modification_price.currency
                   
                   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 
                   THEN product_variation_price.currency
                   
                   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 
                   THEN product_offer_price.currency
                   
                   WHEN product_price.price IS NOT NULL AND product_price.price > 0 
                   THEN product_price.currency
                   
                   ELSE NULL
                END
		    ';

        $dbal->addSelect($CURRENCY.' AS product_currency ');
        $dbal->andWhere($CURRENCY.' IS NOT NULL');


        /** Категория */
        $dbal->leftJoin(
            'product_event',
            ProductCategory::class,
            'product_event_category',
            '
                    product_event_category.event = product_event.id AND 
                    product_event_category.root = true',
        );

        $dbal->leftJoin(
            'product_event_category',
            CategoryProduct::class,
            'category',
            'category.id = product_event_category.category',
        );

        $dbal
            ->addSelect('category_info.url AS category_url')
            //                ->addSelect('category_info.minimal AS category_minimal')
            //                ->addSelect('category_info.input AS category_input')
            //                ->addSelect('category_info.threshold AS category_threshold')
            ->leftJoin(
                'category',
                CategoryProductInfo::class,
                'category_info',
                'category_info.event = category.event',
            );

        $dbal
            ->addSelect('category_trans.name AS category_name')
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

        /** Свойства, участвующие в карточке */
        $dbal->leftJoin(
            'category_section',
            CategoryProductSectionField::class,
            'category_section_field',
            'category_section_field.section = category_section.id AND (category_section_field.card = TRUE OR category_section_field.photo = TRUE OR category_section_field.name = TRUE )',
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

        $dbal->addSelect("JSON_AGG
                ( DISTINCT
                    
                        JSONB_BUILD_OBJECT
                        (
                            'field_sort', category_section_field.sort,
                            'field_name', category_section_field.name,
                            'field_card', category_section_field.card,
                            'field_photo', category_section_field.photo,
                            'field_type', category_section_field.type,
                            'field_trans', category_section_field_trans.name,
                            'field_value', product_property.value
                        )
                    
                )
			    AS category_section_field",
        );


        /**
         * Наличие продукта
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
            'product_variation',
            ProductVariationQuantity::class,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_variation.id',
        );

        /** Наличие и резерв модификации множественного варианта */
        $dbal
            ->leftJoin(
                'product_modification',
                ProductModificationQuantity::class,
                'product_modification_quantity',
                'product_modification_quantity.modification = product_modification.id',
            );


        $dbal->addSelect("
                COALESCE(
                    NULLIF(product_modification_quantity.quantity, 0),
                    NULLIF(product_variation_quantity.quantity, 0),
                    NULLIF(product_offer_quantity.quantity, 0),
                    NULLIF(product_price.quantity, 0),
                    0
                ) AS product_quantity   
		    ");

        $dbal->addSelect("
                COALESCE(
                    NULLIF(product_modification_quantity.reserve, 0),
                    NULLIF(product_variation_quantity.reserve, 0),
                    NULLIF(product_offer_quantity.reserve, 0),
                    NULLIF(product_price.reserve, 0),
                    0
                ) AS product_reserve
            ");

        $dbal
            ->addSelect('product_active.active_from AS product_active_from')
            ->join(
                'product',
                ProductActive::class,
                'product_active',
                '
                    product_active.event = product.event AND 
                    product_active.active IS TRUE    
                ',
            );

        /** Product Invariable */
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


        /**
         * Наличие продукции на складе (необходимо для отображения кнопки "в корзину")
         * Если подключен модуль складского учета и передан идентификатор профиля
         */

        if(false === empty($this->region) && class_exists(BaksDevProductsStocksBundle::class))
        {
            /* Получить все профили данного региона */

            $dbal
                ->leftJoin(
                    'product',
                    UserProfileRegion::class,
                    'product_profile_region',
                    'product_profile_region.value = :region',
                )
                ->setParameter(
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
                 AND product_project_season.month = :month',
            )
            ->setParameter(
                key: 'month',
                value: (int) date('n'),
                type: ParameterType::INTEGER,
            );


        /** Поиск */
        $search = str_replace('-', ' ', $this->search->getQuery());

        /** Очистить поисковую строку от всех НЕ буквенных/числовых символов */
        $search = preg_replace('/[^ a-zа-яё\d]/ui', ' ', $search);
        $search = preg_replace('/\br(\d+)\b/i', '$1', $search);  // Заменяем R или r в начале строки, за которым следует цифра

        $this->logger->info(sprintf('Строка поиска: %s', $search));

        /** Задать префикс и суффикс для реализации варианта "содержит" */
        $search = '*'.trim($search).'*';

        /** Получим ids из индекса */
        $resultProducts = $this->SearchIndexHandler instanceof SearchIndexInterface
            ? $this->SearchIndexHandler->handleSearchQuery($search, ProductSearchTag::TAG)
            : false;


        if($this->SearchIndexHandler instanceof SearchIndexInterface && $resultProducts !== false)
        {
            /** Фильтруем по полученным из индекса ids: */

            $ids = array_column($resultProducts, 'id');

            /** Товары */
            $dbal->andWhere('(
                product.id IN (:uuids) 
                OR product_offer.id IN (:uuids)
                OR product_variation.id IN (:uuids) 
                OR product_modification.id IN (:uuids)
            )')
                ->setParameter(
                    key: 'uuids',
                    value: $ids,
                    type: ArrayParameterType::STRING);

            if(is_array($resultProducts))
            {
                $this->logger->warning('Найден результат поиска product');
            }

        }

        /**
         * В случае если не найден результат в индексе - пробуем найти по базе
         */

        if($resultProducts === false)
        {
            $this->logger->warning('Поиск по индексу не найден, пробуем найти по базе данных');

            $searchBuilder = $dbal->createSearchQueryBuilder($this->search);

            $searchBuilder
                ->addSearchEqualUid('product.id')
                ->addSearchEqualUid('product.event')
                ->addSearchEqualUid('product_variation.id')
                ->addSearchEqualUid('product_modification.id')
                ->addSearchLike('product_trans.name')
                ->addSearchLike('product_info.article')
                ->addSearchLike('product_offer.article')
                ->addSearchLike('product_modification.article')
                ->addSearchLike('product_modification.article')
                ->addSearchLike('product_variation.article');
        }

        $dbal->allGroupByExclude();

        /** Используем индекс сортировки для поднятия в топ списка */
        $dbal
            ->addGroupBy('product_info.sort')
            ->addOrderBy('product_info.sort', 'DESC');

        /** Сортируем список по количеству резерва продукции, суммируем если группировка по иному свойству */
        $dbal->addOrderBy('SUM(product_modification_quantity.reserve)', 'DESC');
        $dbal->addOrderBy('SUM(product_variation_quantity.reserve)', 'DESC');
        $dbal->addOrderBy('SUM(product_offer_quantity.reserve)', 'DESC');
        $dbal->addOrderBy('SUM(product_price.reserve)', 'DESC');

        $dbal->addOrderBy('SUM(product_modification_quantity.quantity)', 'DESC');
        $dbal->addOrderBy('SUM(product_variation_quantity.quantity)', 'DESC');
        $dbal->addOrderBy('SUM(product_offer_quantity.quantity)', 'DESC');
        $dbal->addOrderBy('SUM(product_price.quantity)', 'DESC');


        $this->maxResult ? $dbal->setMaxResults($this->maxResult) : $dbal->setMaxResults(self::MAX_RESULTS);

        $result = $dbal->fetchAllHydrate(SearchAllResult::class);

        return ($result->valid() === true) ? $result : false;

    }

}
