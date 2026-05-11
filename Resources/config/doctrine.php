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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Products\Product\BaksDevProductsProductBundle;
use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
use BaksDev\Products\Product\Type\Barcode\ProductBarcodeType;
use BaksDev\Products\Product\Type\Event\ProductEventType;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\File\ProductFileType;
use BaksDev\Products\Product\Type\File\ProductFileUid;
use BaksDev\Products\Product\Type\Id\ProductType;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableType;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Material\MaterialType;
use BaksDev\Products\Product\Type\Material\MaterialUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConstType;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferType;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Image\ProductOfferImageType;
use BaksDev\Products\Product\Type\Offers\Image\ProductOfferImageUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConstType;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationType;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Image\ProductOfferVariationImageType;
use BaksDev\Products\Product\Type\Offers\Variation\Image\ProductOfferVariationImageUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConstType;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationType;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Image\ProductOfferVariationModificationImageType;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Image\ProductOfferVariationModificationImageUid;
use BaksDev\Products\Product\Type\Photo\ProductPhotoType;
use BaksDev\Products\Product\Type\Photo\ProductPhotoUid;
use BaksDev\Products\Product\Type\Project\Description\ProductProjectDescriptionType;
use BaksDev\Products\Product\Type\Project\Description\ProductProjectDescriptionUid;
use BaksDev\Products\Product\Type\Project\ProductProjectType;
use BaksDev\Products\Product\Type\Project\ProductProjectUid;
use BaksDev\Products\Product\Type\Project\Season\ProductProjectSeasonType;
use BaksDev\Products\Product\Type\Project\Season\ProductProjectSeasonUid;
use BaksDev\Products\Product\Type\Settings\ProductSettingsIdentifier;
use BaksDev\Products\Product\Type\Settings\ProductSettingsType;
use BaksDev\Products\Product\Type\Video\ProductVideoType;
use BaksDev\Products\Product\Type\Video\ProductVideoUid;
use Symfony\Config\DoctrineConfig;

return static function(ContainerConfigurator $container, DoctrineConfig $doctrine) {

    /* ProductUid */
    $doctrine->dbal()->type(ProductUid::TYPE)->class(ProductType::class);
    $doctrine->dbal()->type(ProductEventUid::TYPE)->class(ProductEventType::class);
    $doctrine->dbal()->type(ProductOfferConst::TYPE)->class(ProductOfferConstType::class);
    $doctrine->dbal()->type(ProductFileUid::TYPE)->class(ProductFileType::class);
    $doctrine->dbal()->type(ProductOfferUid::TYPE)->class(ProductOfferType::class);
    $doctrine->dbal()->type(ProductSettingsIdentifier::TYPE)->class(ProductSettingsType::class);
    $doctrine->dbal()->type(ProductPhotoUid::TYPE)->class(ProductPhotoType::class);
    $doctrine->dbal()->type(ProductVideoUid::TYPE)->class(ProductVideoType::class);
    $doctrine->dbal()->type(ProductOfferImageUid::TYPE)->class(ProductOfferImageType::class);

    $doctrine->dbal()->type(ProductOfferVariationImageUid::TYPE)->class(ProductOfferVariationImageType::class);
    $doctrine->dbal()->type(ProductVariationConst::TYPE)->class(ProductVariationConstType::class);
    $doctrine->dbal()->type(ProductVariationUid::TYPE)->class(ProductVariationType::class);

    $doctrine->dbal()->type(ProductOfferVariationModificationImageUid::TYPE)->class(ProductOfferVariationModificationImageType::class);
    $doctrine->dbal()->type(ProductModificationConst::TYPE)->class(ProductModificationConstType::class);
    $doctrine->dbal()->type(ProductModificationUid::TYPE)->class(ProductModificationType::class);
    $doctrine->dbal()->type(ProductBarcode::TYPE)->class(ProductBarcodeType::class);
    $doctrine->dbal()->type(ProductInvariableUid::TYPE)->class(ProductInvariableType::class);

    $doctrine->dbal()->type(MaterialUid::TYPE)->class(MaterialType::class);

    $doctrine->dbal()->type(ProductProjectUid::TYPE)->class(ProductProjectType::class);
    $doctrine->dbal()->type(ProductProjectDescriptionUid::TYPE)->class(ProductProjectDescriptionType::class);

    $doctrine->dbal()->type(ProductProjectSeasonUid::TYPE)->class(ProductProjectSeasonType::class);


    $services = $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    /** Value Resolver */

    $services->set(ProductUid::class)->class(ProductUid::class);
    $services->set(ProductEventUid::class)->class(ProductEventUid::class);

    $services->set(ProductOfferUid::class)->class(ProductOfferUid::class);
    $services->set(ProductOfferConst::class)->class(ProductOfferConst::class);

    $services->set(ProductVariationUid::class)->class(ProductVariationUid::class);
    $services->set(ProductVariationConst::class)->class(ProductVariationConst::class);

    $services->set(ProductModificationUid::class)->class(ProductModificationUid::class);
    $services->set(ProductModificationConst::class)->class(ProductModificationConst::class);

    $services->set(ProductInvariableUid::class)->class(ProductInvariableUid::class);


    $services->set(MaterialUid::class)->class(MaterialUid::class);

    $emDefault = $doctrine->orm()->entityManager('default')->autoMapping(true);

    $emDefault
        ->mapping('products-product')
        ->type('attribute')
        ->dir(BaksDevProductsProductBundle::PATH.'Entity')
        ->isBundle(false)
        ->prefix(BaksDevProductsProductBundle::NAMESPACE.'\\Entity')
        ->alias('products-product');
};
