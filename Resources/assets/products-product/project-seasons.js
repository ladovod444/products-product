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


/** Добавление настройки сезонности */
$addButtonSeason = document.getElementById("seasonAddCollection");

if($addButtonSeason)
{

    /* Блок для новой коллекции */
    let $blockCollectionCall = document.getElementById("collection-season");

    if($blockCollectionCall)
    {
        $addButtonSeason.addEventListener("click", function()
        {

            let $addButtonSeason = this;
            /* получаем прототип коллекции  */
            let newForm = $addButtonSeason.dataset.prototype;
            let index = $addButtonSeason.dataset.index * 1;

            /* Замена '__seasons__' в HTML-коде прототипа
             вместо этого будет число, основанное на том, сколько коллекций */
            newForm = newForm.replace(/__seasons__/g, index);

            /* Вставляем новую коллекцию */
            let div = document.createElement("div");
            div.id = "product_form_project_season_" + index;
            div.classList.add("mb-3");
            div.classList.add("item-season");

            div.innerHTML = newForm;
            $blockCollectionCall.append(div);


            /* Удалить */
            (div.querySelector(".del-item-season"))?.addEventListener("click", deleteSeason);

            const delButton = div.querySelector(".del-item-season");
            delButton.dataset.delete = "product_form_project_season_" + (index).toString();


            /* Увеличиваем data-index на 1 после вставки новой коллекции */
            $addButtonSeason.dataset.index = (index + 1).toString();

            /* Плавная прокрутка к элементу */
            div.scrollIntoView({block : "center", inline : "center", behavior : "smooth"});

        });
    }
}

/** Удаление настройки сезонности */
document.querySelectorAll(".del-item-season").forEach(function(item)
{
    item.addEventListener("click", deleteSeason);
});

function deleteSeason()
{ 
    let seasons = document.querySelectorAll(".item-season").length;

    document.getElementById(this.dataset.delete).remove();
}
