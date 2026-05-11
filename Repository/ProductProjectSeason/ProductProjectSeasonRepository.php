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

namespace BaksDev\Products\Product\Repository\ProductProjectSeason;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Project\ProductProject;
use BaksDev\Products\Product\Entity\Project\Season\ProductProjectSeason;
use BaksDev\Products\Product\Type\Id\ProductUid;
use Generator;
use InvalidArgumentException;

final class ProductProjectSeasonRepository implements ProductProjectSeasonInterface
{

    private ProductUid|false $product;

    public function byProduct(ProductUid $product): self
    {
        $this->product = $product;
        return $this;
    }

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function findAll(): Generator|false
    {

        if(false === $this->product)
        {
            throw new InvalidArgumentException(
                sprintf('Не задан параметр product (%s)', self::class.':'.__LINE__)
            );
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        /* Задать product */
        $dbal
            ->from(ProductProject::class, 'product_project')
            ->where('product_project.product = :product')
            ->setParameter(
                key: 'product',
                value: $this->product,
                type: ProductUid::TYPE,
            );


        $dbal
            ->addSelect('product_project_season.month AS month')
            ->addSelect('product_project_season.percent AS percent')
            ->join(
                'product_project',
                ProductProjectSeason::class,
                'product_project_season',
                'product_project.id = product_project_season.project'
            );


        /* Задать профиль - PROJECT_PROFILE */
        if(true === $dbal->bindProjectProfile())
        {
            $dbal->andWhere('product_project.profile = :'.$dbal::PROJECT_PROFILE_KEY.' OR product_project.profile IS NULL');
        }


        $result = $dbal->fetchAllHydrate(ProductProjectSeasonResult::class);

        return ($result->valid() === true) ? $result : false;

    }

}