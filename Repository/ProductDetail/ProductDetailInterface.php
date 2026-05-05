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

namespace BaksDev\Products\Product\Repository\ProductDetail;

use BaksDev\Core\Type\Field\InputField;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use DateTimeImmutable;

interface ProductDetailInterface
{
    public function getProductId(): ProductUid;

    public function getProductEvent(): ProductEventUid;

    public function getProductName(): ?string;

    public function getProductPreview(): ?string;

    public function getProductDescription(): ?string;

    public function getProductUrl(): ?string;

    /** SKU */

    public function getProductArticle(): ?string;

    public function getBarcodes(): array|null;

    public function getProductBarcode(): ?string;


    /** Active */

    public function isActive(): bool;

    public function getActiveFrom(): ?DateTimeImmutable;

    public function getActiveTo(): ?DateTimeImmutable;


    /** Offer */

    public function getProductOfferUid(): ProductOfferUid|null;

    public function getProductOfferConst(): ?ProductOfferConst;

    public function getProductOfferValue(): ?string;

    public function getProductOfferPostfix(): ?string;

    public function getProductOfferReference(): InputField;

    public function getProductOfferName(): ?string;

    public function getProductOfferNamePostfix(): ?string;

    /** Variation */

    public function getProductVariationUid(): ProductVariationUid|null;

    public function getProductVariationConst(): ?ProductVariationConst;

    public function getProductVariationValue(): ?string;

    public function getProductVariationPostfix(): ?string;

    public function getProductVariationReference(): ?InputField;

    public function getProductVariationName(): ?string;

    public function getProductVariationNamePostfix(): ?string;

    /** Modification */

    public function getProductModificationUid(): ProductModificationUid|null;

    public function getProductModificationConst(): ?ProductModificationConst;

    public function getProductModificationValue(): ?string;

    public function getProductModificationPostfix(): ?string;

    public function getProductModificationReference(): InputField;

    public function getProductModificationName(): ?string;

    public function getProductModificationNamePostfix(): ?string;


    /** Image */

    public function getProductImage(): ?string;

    public function getProductImageExt(): ?string;

    public function isProductImageCdn(): bool;


    /** Category */

    public function getCategoryName(): ?string;

    public function getCategoryUrl(): ?string;

    public function getCategorySectionField(): array|null;


    /** Price | Quantity */

    public function getProductPrice(): Money|false;

    public function getProductOldPrice(): Money|false;

    public function getProductCurrency(): Currency;

    public function getProductQuantity(): int;

    public function getProductReserve(): int;


}