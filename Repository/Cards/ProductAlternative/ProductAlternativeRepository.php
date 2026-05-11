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

use BaksDev\Core\Doctrine\DBALQueryBuilder;
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
use BaksDev\Products\Product\Entity\Project\ProductProject;
use BaksDev\Products\Product\Entity\Project\Season\ProductProjectSeason;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
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
use Generator;
use InvalidArgumentException;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** @see ProductAlternativeResult */
final class ProductAlternativeRepository implements ProductAlternativeInterface
{
    private string|false $offer = false;

    private string|null|false $variation = false;

    private string|null|false $modification = false;

    private array|null $property = null;

    private ProductInvariableUid|false $exclude = false;

    private bool $active = false;

    private int|false $limit = 100;


    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        #[Autowire(env: 'PROJECT_REGION')] private readonly ?string $region = null,
    ) {}

    public function setMaxResult(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function forOfferValue(string $offer): self
    {
        $this->offer = $offer;
        return $this;
    }

    public function forVariationValue(string|null $variation): self
    {
        $this->variation = $variation;
        return $this;
    }

    public function forModificationValue(string|null $modification): self
    {
        $this->modification = $modification;
        return $this;
    }

    public function byProperty(array|null $property): self
    {
        if(empty($property))
        {
            return $this;
        }

        $this->property = $property;

        return $this;
    }

    public function onlyActive(): self
    {
        $this->active = true;
        return $this;
    }

    /**
     * Метод возвращает альтернативные варианты продукции по значению value торговых предложений
     *
     * @return array<int, ProductAlternativeResult>|false
     */
    public function toArray(): array|false
    {
        $result = $this->findAll();

        return (false !== $result) ? iterator_to_array($result) : false;
    }

    /**
     * Метод возвращает альтернативные варианты продукции по значению value торговых предложений
     *
     * @return Generator<int, ProductAlternativeResult>|false
     */
    public function findAll(): Generator|false
    {

        if(false === $this->offer)
        {
            throw new InvalidArgumentException('Не передан обязательный параметр запроса $offer');
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        /** OFFER */
        $dbal
            ->addSelect('product_offer.value as product_offer_value')
            ->addSelect('product_offer.postfix as product_offer_postfix')
            ->addSelect('product_offer.id as product_offer_uid')
            ->from(ProductOffer::class, 'product_offer');

        $dbal
            ->addSelect('product.id')
            ->addSelect('product.event')
            ->join(
                'product_offer',
                Product::class,
                'product',
                'product.event = product_offer.event',
            );

        /** VARIATION */
        if($this->variation)
        {
            $dbal->join(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id AND product_variation.value = :variation',
            );

            $dbal->setParameter('variation', $this->variation);
        }
        else
        {
            $dbal->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id',
            );
        }

        $dbal
            ->addSelect('product_variation.value as product_variation_value')
            ->addSelect('product_variation.postfix as product_variation_postfix')
            ->addSelect('product_variation.id as product_variation_uid');

        // МНОЖЕСТВЕННЫЕ ВАРИАНТЫ

        //        $variationMethod = empty($this->variation) ? 'leftJoin' : 'join';
        //
        //        $dbal
        //            ->addSelect('product_variation.value as product_variation_value')
        //            ->addSelect('product_variation.postfix as product_variation_postfix')
        //            ->addSelect('product_variation.id as product_variation_uid')
        //            ->{$variationMethod}(
        //                'product_offer',
        //                ProductVariation::class,
        //                'product_variation',
        //                'product_variation.offer = product_offer.id '.(empty($this->variation)) ? '' : 'AND product_variation.value = :variation')
        //            );
        //
        //        if(!empty($variation))
        //        {
        //            $dbal->setParameter('variation', $variation);
        //        }


        /** MODIFICATION */
        if($this->modification)
        {
            $dbal->join(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id AND product_modification.value = :modification',
            );

            $dbal->setParameter('modification', $this->modification);
        }
        else
        {
            $dbal->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id',
            );
        }

        $dbal
            ->addSelect('product_modification.value as product_modification_value')
            ->addSelect('product_modification.postfix as product_modification_postfix')
            ->addSelect('product_modification.id as product_modification_uid');

        // МОДИФИКАЦИЯ

        //        $modificationMethod = empty($modification) ? 'leftJoin' : 'join';
        //
        //        $dbal
        //            ->addSelect('product_modification.value as product_modification_value')
        //            ->addSelect('product_modification.postfix as product_modification_postfix')
        //            ->addSelect('product_modification.id as product_modification_uid')
        //            ->{$modificationMethod}(
        //                'product_variation',
        //                ProductModification::class,
        //                'product_modification',
        //                'product_modification.variation = product_variation.id '.(empty($modification) ? '' : 'AND product_modification.value = :modification')
        //            )
        //            ->addGroupBy('product_modification.article');
        //
        //        if(!empty($modification))
        //        {
        //            $dbal->setParameter('modification', $modification);
        //        }


        $dbal
            ->addSelect('product_active.active as product_active')
            ->addSelect('product_active.active_from as product_active_from')
            ->addSelect('product_active.active_to as product_active_to');


        /** Получаем только при условии активности карточки */
        if($this->active)
        {
            $dbal->join(
                'product',
                ProductActive::class,
                'product_active',
                '
                    product_active.event = product.event AND 
                    product_active.active IS TRUE AND
                    (product_active.active_to IS NULL OR product_active.active_to > NOW())
                ');
        }
        else
        {
            $dbal->leftJoin(
                'product',
                ProductActive::class,
                'product_active',
                'product_active.event = product.event',
            );
        }

        $dbal
            ->addSelect(':'.$dbal::PROJECT_PROFILE_KEY.' AS project_profile')
            ->addSelect("JSON_AGG (DISTINCT product_profile.value) FILTER (WHERE product_profile.value IS NOT NULL) AS profiles")
            ->leftJoin(
                'product',
                ProductProfile::class,
                'product_profile',
                'product_profile.event = product.event',
            );

        /** Название товара */
        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product.event AND product_trans.local = :local',
            );

        $dbal
            ->addSelect('product_info.url AS product_url')
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id ',
            );

        /** Артикул продукта */
        $dbal->addSelect('
            COALESCE(
                product_modification.article, 
                product_variation.article, 
                product_offer.article, 
                product_info.article
            ) AS article
		');


        /**
         * ТИПЫ ТОРГОВЫХ ПРЕДЛОЖЕНИЙ
         */

        // Получаем тип торгового предложения
        $dbal
            ->addSelect('category_offer.reference AS product_offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer',
            );

        // Получаем название торгового предложения
        $dbal
            ->addSelect('category_offer_trans.name as product_offer_name')
            ->leftJoin(
                'category_offer',
                CategoryProductOffersTrans::class,
                'category_offer_trans',
                'category_offer_trans.offer = category_offer.id AND category_offer_trans.local = :local',
            );


        // Получаем тип множественного варианта
        $dbal
            ->addSelect('category_offer_variation.reference as product_variation_reference')
            ->leftJoin(
                'product_variation',
                CategoryProductVariation::class,
                'category_offer_variation',
                'category_offer_variation.id = product_variation.category_variation',
            );

        // Получаем название множественного варианта
        $dbal
            ->addSelect('category_offer_variation_trans.name as product_variation_name')
            ->leftJoin(
                'category_offer_variation',
                CategoryProductVariationTrans::class,
                'category_offer_variation_trans',
                'category_offer_variation_trans.variation = category_offer_variation.id AND category_offer_variation_trans.local = :local',
            );

        // Получаем тип модификации множественного варианта
        $dbal
            ->addSelect('category_offer_modification.reference as product_modification_reference')
            ->leftJoin(
                'product_modification',
                CategoryProductModification::class,
                'category_offer_modification',
                'category_offer_modification.id = product_modification.category_modification',
            );

        // Получаем название типа модификации
        $dbal
            ->addSelect('category_offer_modification_trans.name as product_modification_name')
            ->leftJoin(
                'category_offer_modification',
                CategoryProductModificationTrans::class,
                'category_offer_modification_trans',
                'category_offer_modification_trans.modification = category_offer_modification.id AND category_offer_modification_trans.local = :local',
            );

        /**
         * СТОИМОСТЬ И ВАЛЮТА ПРОДУКТА
         */

        $dbal->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 
			   THEN product_modification_price.price
			   
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 
			   THEN product_variation_price.price
			   
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 
			   THEN product_offer_price.price
			   
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 
			   THEN product_price.price
			   
			   ELSE NULL
			END AS product_price
		',
        );

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


        // Валюта продукта
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
		',
        );

        // Базовая Цена товара
        $dbal
            ->leftJoin(
                'product',
                ProductPrice::class,
                'product_price',
                'product_price.event = product.event',
            );

        $dbal
            ->leftJoin(
                'product_offer',
                ProductOfferPrice::class,
                'product_offer_price',
                'product_offer_price.offer = product_offer.id',
            );

        // Цена множественного варианта
        $dbal
            ->leftJoin(
                'product_variation',
                ProductVariationPrice::class,
                'product_variation_price',
                'product_variation_price.variation = product_variation.id',
            );


        // Цена модификации множественного варианта
        $dbal
            ->leftJoin(
                'product_modification',
                ProductModificationPrice::class,
                'product_modification_price',
                'product_modification_price.modification = product_modification.id',
            );


        /**
         * НАЛИЧИЕ ПРОДУКТА
         */

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
			   
			END AS quantity
		',
        );


        /**
         * Наличие продукции на складе
         * Если подключен модуль складского учета и передан идентификатор профиля
         */

        if(false === empty($this->region) && class_exists(BaksDevProductsStocksBundle::class))
        {
            /* Получаем все профили данного региона */

            $dbal
                ->leftJoin(
                    'product_offer',
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


        // Наличие и резерв торгового предложения

        $dbal
            ->leftJoin(
                'product_offer',
                ProductOfferQuantity::class,
                'product_offer_quantity',
                'product_offer_quantity.offer = product_offer.id',
            );

        // Наличие и резерв множественного варианта
        $dbal
            ->leftJoin(
                'category_offer_variation',
                ProductVariationQuantity::class,
                'product_variation_quantity',
                'product_variation_quantity.variation = product_variation.id',
            );

        // Наличие и резерв модификации множественного варианта
        $dbal
            ->leftJoin(
                'category_offer_modification',
                ProductModificationQuantity::class,
                'product_modification_quantity',
                'product_modification_quantity.modification = product_modification.id',
            );


        /**
         * КАТЕГОРИЯ
         */

        $dbal->leftJoin(
            'product',
            ProductCategory::class,
            'product_event_category',
            'product_event_category.event = product.event AND product_event_category.root = true',
        );


        $dbal->leftJoin(
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
            ->addSelect('category_info.threshold AS category_threshold')
            ->join(
                'category',
                CategoryProductInfo::class,
                'category_info',
                'category_info.event = category.event AND category_info.active IS TRUE',
            );

        $dbal->leftJoin(
            'category',
            CategoryProductSection::class,
            'category_section',
            'category_section.event = category.event',
        );


        /**
         * СВОЙСТВА, УЧАВСТВУЮЩИЕ В ФИЛЬТРЕ АЛЬТЕРНАТИВ
         */

        if($this->property)
        {
            /** @var stdClass $props */
            foreach($this->property as $props)
            {
                if(empty($props->field_uid))
                {
                    continue;
                }

                $alias = md5($props->field_uid);

                $dbal->join(
                    'product_offer',
                    ProductProperty::class,
                    'product_property_'.$alias,
                    'product_property_'.$alias.'.event = product_offer.event AND product_property_'.$alias.'.value = :props_'.$alias,
                );

                $dbal->setParameter('props_'.$alias, $props->field_value);
            }
        }

        /**
         * СВОЙСТВА, УЧАСТВУЮЩИЕ В ПРЕВЬЮ
         */

        $dbal->leftJoin(
            'category_section',
            CategoryProductSectionField::class,
            'category_section_field',
            'category_section_field.section = category_section.id AND category_section_field.card = TRUE',
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
            'category_product_property',
            'category_product_property.event = product.event AND category_product_property.field = category_section_field.const',
        );

        $dbal->addSelect(
            "JSON_AGG
		( DISTINCT
			
				JSONB_BUILD_OBJECT
				(
				
					'0', category_section_field.sort,
					'field_type', category_section_field.type,
					'field_trans', category_section_field_trans.name,
					'field_value', category_product_property.value
				)
			
		)
			AS category_section_field",
        );

        /** Фото продукции*/

        /**
         * Фото модификаций
         */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_offer_modification_image',
            'product_offer_modification_image.modification = product_modification.id',
        );

        /**
         * Фото вариантов
         */
        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            'product_variation_image.variation = product_variation.id',
        );

        /**
         * Фото торговых предложений
         */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            'product_offer_images.offer = product_offer.id',
        );

        /**
         * Фото продукта
         */
        $dbal->leftJoin(
            'product',
            ProductPhoto::class,
            'product_photo',
            'product_photo.event = product.event',
        );

        $dbal->addSelect(
            "JSON_AGG 
            (DISTINCT
				CASE 
                    WHEN product_offer_images.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'img_root', product_offer_images.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name),
                            'img_ext', product_offer_images.ext,
                            'img_cdn', product_offer_images.cdn
                        ) 
                    WHEN product_variation_image.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'img_root', product_variation_image.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name),
                            'img_ext', product_variation_image.ext,
                            'img_cdn', product_variation_image.cdn
                        )	
                    WHEN product_offer_modification_image.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'img_root', product_offer_modification_image.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_offer_modification_image.name),
                            'img_ext', product_offer_modification_image.ext,
                            'img_cdn', product_offer_modification_image.cdn
                        )
                    WHEN product_photo.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'img_root', product_photo.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name),
                            'img_ext', product_photo.ext,
                            'img_cdn', product_photo.cdn
                        )
                    END) AS product_images",
        );


        /**
         * Product Invariable
         */
        $dbal
            ->addSelect('product_invariable.id AS product_invariable_id')
            ->join(
                'product_modification',
                ProductInvariable::class,
                'product_invariable',
                ($this->exclude ? 'product_invariable.id != :exclude AND ' : '').'
                    
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

        if($this->exclude instanceof ProductInvariableUid)
        {
            $dbal->setParameter(
                key: 'exclude',
                value: $this->exclude,
                type: ProductInvariableUid::TYPE,
            );
        }


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


        $dbal->where('product_offer.value = :offer');
        $dbal->setParameter('offer', $this->offer);

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

        $dbal->setMaxResults($this->limit);

        $dbal->enableCache('products-product', '1 day');

        $result = $dbal->fetchAllHydrate(ProductAlternativeResult::class);

        return (true === $result->valid()) ? $result : false;
    }

    public function excludeProductInvariable(ProductInvariableUid|null|false $exclude): self
    {
        if(empty($exclude))
        {
            $this->exclude = false;
            return $this;
        }

        $this->exclude = $exclude;
        return $this;
    }
}
