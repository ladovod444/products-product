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

namespace BaksDev\Products\Product\Repository\Cards\ModelOrProduct;

use BaksDev\Products\Product\Repository\Cards\ModelsOrProductsCardResultInterfaceProduct;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use DateTimeImmutable;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/** @see ModelOrProductRepository */
#[Exclude]
final readonly class ModelOrProductResult implements ModelsOrProductsCardResultInterfaceProduct
{

    public function __construct(
        private string $product_id,
        private string $product_event,
        private string $product_name,
        private string $product_url,
        private int $product_sort,

        private ?bool $product_active,
        private ?string $product_active_from,
        private ?string $product_active_to,

        private bool|null $category_offer_card,
        private string|null $product_offer_reference,
        private string|null $product_offer_value,
        private string|null $product_offer_postfix,
        private string $offer_agg,

        private bool|null $category_variation_card,
        private string|null $product_variation_reference,
        private string|null $product_variation_value,
        private string|null $product_variation_postfix,
        private string $variation_agg,

        private bool|null $category_modification_card,
        private string|null $product_modification_reference,
        private string|null $product_modification_value,
        private string|null $product_modification_postfix,
        private string $modification_agg,

        private string $invariable,
        private string $product_root_images,

        private ?string $category_url,
        private ?string $category_name,

        private int|null $product_price,
        private int|null $product_old_price,
        private string|null $product_currency,
        private int|null $product_quantity,

        private string|int|null $promotion_price = null,

        private string|null $category_section_field = null,

        private string|null $profile_discount = null,
        private string|null $project_discount = null,
        private string|null $profiles = null,

        private string|null $product_quantity_stocks = null,

        private string|null $season_percent = null,
    ) {}


    /* Есть ли в данном регионе */
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

    public function getProductId(): ProductUid
    {
        return new ProductUid($this->product_id);
    }

    public function getProductEvent(): ProductEventUid
    {
        return new ProductEventUid($this->product_event);
    }

    public function getProductName(): ?string
    {
        return $this->product_name;
    }

    public function getProductUrl(): ?string
    {
        return $this->product_url;
    }

    public function getProductSort(): ?int
    {
        return $this->product_sort;
    }

    public function getCategoryOfferCard(): ?bool
    {
        return $this->category_offer_card;
    }

    public function getProductOfferReference(): ?string
    {
        return $this->product_offer_reference;
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getOfferAgg(): string
    {
        return $this->offer_agg;
    }

    public function getCategoryVariationCard(): ?bool
    {
        return $this->category_variation_card;
    }

    public function getProductVariationReference(): ?string
    {
        return $this->product_variation_reference;
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getVariationAgg(): string
    {
        return $this->variation_agg;
    }

    public function getCategoryModificationCard(): ?bool
    {
        return $this->category_modification_card;
    }

    public function getProductModificationReference(): ?string
    {
        return $this->product_modification_reference;
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getModificationAgg(): string
    {
        return $this->modification_agg;
    }

    public function getInvariable(): array|null
    {
        if(is_null($this->invariable))
        {
            return null;
        }

        if(false === json_validate($this->invariable))
        {
            return null;
        }

        $invariables = json_decode($this->invariable, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($invariables))
        {
            return null;
        }

        return $invariables;
    }

    public function getProductRootImages(): array|null
    {
        if(is_null($this->product_root_images))
        {
            return null;
        }

        if(false === json_validate($this->product_root_images))
        {
            return null;
        }

        $images = json_decode($this->product_root_images, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($images))
        {
            return null;
        }

        return $images;
    }

    public function getCategoryUrl(): ?string
    {
        return $this->category_url;
    }

    public function getCategoryName(): ?string
    {
        return $this->category_name;
    }

    public function getProductPrice(): Money|false
    {
        if(empty($this->product_price))
        {
            return false;
        }

        $price = new Money($this->product_price, true);

        /** Кастомная цена */
        $promotionPrice = $this->minPromotionPrice();

        if($promotionPrice instanceof Money)
        {
            $price = $promotionPrice;
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

    /**
     * Метод возвращает минимальную стоимость с учетом применения кастомной скидки (надбавки)
     */
    private function minPromotionPrice(): ?Money
    {
        if(true === empty($this->promotion_price))
        {
            return null;
        }

        if(false === json_validate($this->promotion_price))
        {
            return null;
        }

        $promotionPrice = json_decode($this->promotion_price, true, 512, JSON_THROW_ON_ERROR);

        if(is_null($promotionPrice))
        {
            return null;
        }

        $promotionPrice = array_filter($promotionPrice, fn($value) => null !== $value);

        if(true === empty($promotionPrice))
        {
            return null;
        }

        /** Создаем массив с ценами и применяем кастомную скидку (надбавку) */
        $promotionPriceMoney = array_map(function(array $element) {
            $money = new Money($element['price'], true);
            $money->applyString($element['promo']);

            return $money;
        }, $promotionPrice);

        // сортировка по возрастанию цены - от меньшей к большей
        usort($promotionPriceMoney, static function(Money $a, Money $b) {
            return $a->getValue() <=> $b->getValue();
        });

        return current($promotionPriceMoney);
    }

    public function getProductOldPrice(): Money|false
    {
        if(empty($this->product_old_price))
        {
            return false;
        }

        $price = new Money($this->product_old_price, true);

        /** Кастомная цена */
        $promotionPrice = $this->minPromotionPrice();

        if($promotionPrice instanceof Money)
        {
            $price = $promotionPrice;
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

    public function getProductCurrency(): Currency
    {
        return new Currency($this->product_currency);
    }

    public function getProductQuantity(?string $profile = null): int|null
    {
        /** Если карточка не активна - нет в наличии */
        if(false === $this->product_active)
        {
            return null;
        }

        /** Если дата публикации не наступила - нет в наличии */
        if(
            false === empty($this->product_active_from)
            && new DateTimeImmutable($this->product_active_from) > new DateTimeImmutable()
        )
        {
            return null;
        }

        if(
            false === empty($this->product_active_to)
            && new DateTimeImmutable($this->product_active_to) < new DateTimeImmutable()
        )
        {
            return null;
        }

        if(empty($this->profiles))
        {
            return $this->product_quantity;
        }

        if(false === json_validate($this->profiles))
        {
            return $this->product_quantity;
        }

        $profiles = json_decode($this->profiles, true, 512, JSON_THROW_ON_ERROR);

        if(empty($profiles))
        {
            return $this->product_quantity;
        }

        if(false === empty($profile) && false === in_array($profile, $profiles, true))
        {
            return null;
        }

        return $this->product_quantity;
    }

    public function getProductActiveFrom(): ?string
    {
        return $this->product_active_from;
    }

    public function getCategorySectionField(): array|null
    {
        if(is_null($this->category_section_field))
        {
            return null;
        }

        if(false === json_validate($this->category_section_field))
        {
            return null;
        }

        $category_section_field = json_decode($this->category_section_field, true, 512, JSON_THROW_ON_ERROR);

        if(null === current($category_section_field))
        {
            return null;
        }

        return $category_section_field;
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->category_offer_card ? $this->product_offer_postfix : null;
    }

    public function getProductVariationPostfix(): ?string
    {
        return $this->category_variation_card ? $this->product_variation_postfix : null;
    }

    /** Helpers */

    public function getProductModificationPostfix(): ?string
    {
        return $this->category_modification_card ? $this->product_modification_postfix : null;
    }
}
