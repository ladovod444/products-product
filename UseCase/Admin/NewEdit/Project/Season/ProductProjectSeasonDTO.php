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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Project\Season;

use BaksDev\Products\Product\Entity\Project\Season\ProductProjectSeasonInterface;
use Symfony\Component\Validator\Constraints as Assert;

final class ProductProjectSeasonDTO implements ProductProjectSeasonInterface
{

    /** Торговая наценка */
    #[Assert\Valid]
    #[Assert\NotBlank]
    private string $percent = '0';

    /** Значение месяца */
    #[Assert\Valid]
    #[Assert\NotBlank]
    private int $month = 1;

    public function getPercent(): string
    {
        return $this->percent;
    }

    public function setPercent(string $percent): self
    {
        $this->percent = $percent;
        return $this;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function setMonth(?int $month): self
    {
        $this->month = $month;
        return $this;
    }

}