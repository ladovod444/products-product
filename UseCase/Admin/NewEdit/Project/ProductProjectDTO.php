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

namespace BaksDev\Products\Product\UseCase\Admin\NewEdit\Project;

use BaksDev\Core\Type\Device\Device;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Products\Product\Entity\Project\ProductProjectInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Project\Description\ProductProjectDescriptionDTO;
use BaksDev\Products\Product\UseCase\Admin\NewEdit\Project\Season\ProductProjectSeasonDTO;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class ProductProjectDTO implements ProductProjectInterface
{
    /* Профиль */
    #[Assert\Valid]
    private ?UserProfileUid $profile = null;

    /* Продукт */
    private ProductUid $product;

    /* Описание */
    #[Assert\Valid]
    private ArrayCollection $description;

    /* Сезонность */
    #[Assert\Valid]
    private ArrayCollection $season;


    public function __construct()
    {
        $this->description = new ArrayCollection();
        $this->season = new ArrayCollection();
    }

    public function getProfile(): ?UserProfileUid
    {
        return $this->profile;
    }

    public function setProfile(?UserProfileUid $profile): self
    {
        $this->profile = $profile;
        return $this;
    }

    public function getProduct(): ProductUid
    {
        return $this->product;
    }

    public function setProduct(ProductUid $product): self
    {
        $this->product = $product;
        return $this;
    }


    /* Описание */

    public function getDescription(): ArrayCollection
    {

        /* Вычисляем расхождение и добавляем неопределенные локали */
        foreach(Locale::diffLocale($this->description) as $locale)
        {
            /** @var Device $device */
            foreach(Device::cases() as $device)
            {
                $ProductProjectDescriptionDTO = new ProductProjectDescriptionDTO();
                $ProductProjectDescriptionDTO->setLocal($locale);
                $ProductProjectDescriptionDTO->setDevice($device);

                $this->addDescription($ProductProjectDescriptionDTO);
            }
        }

        return $this->description;
    }

    public function setDescription(ArrayCollection $description): void
    {
        $this->description = $description;
    }

    public function addDescription(ProductProjectDescriptionDTO $description): void
    {
        if(empty($description->getLocal()->getLocalValue()))
        {
            return;
        }

        if(!$this->description->contains($description))
        {
            $this->description->add($description);
        }
    }


    /*Сезонность*/

    /**
     * Коллекция сезонов
     *
     * @return ArrayCollection<int, ProductProjectSeasonDTO>
     */
    public function getSeason(): ArrayCollection
    {
        return $this->season;
    }

    public function setSeason(ArrayCollection $season): void
    {
        $this->season = $season;
    }

    public function addSeason(ProductProjectSeasonDTO $season): void
    {
        if(!$this->season->contains($season))
        {
            $this->season->add($season);
        }
    }

    public function removeSeason(ProductProjectSeasonDTO $season): void
    {
        $this->season->removeElement($season);
    }

}