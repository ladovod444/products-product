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


/** Символьный код */
/* Получаем поле, Согласно выбранной локали */
let $name = document.querySelector("input[data-lang='product_form_trans_" + $locale + "']");

if($name)
{

    setTimeout(function initCatUrl()
    {

        if(typeof catUrl.debounce == "function")
        {

            $name.addEventListener("input", catUrl.debounce(500));

            document.getElementById("product_form_product").addEventListener("keydown", function(event)
            {
                if(event.key === "Enter")
                {
                    event.preventDefault();
                }
            });


            return;
        }

        setTimeout(initCatUrl, 100);

    }, 100);

    function catUrl()
    {
        /* Заполняем транслитом URL */
        document.getElementById("product_form_info_url").value = translitRuEn(this.value).toLowerCase();
    }
}


document.addEventListener("keydown", function(event)
{
    if(event.key === "Enter")
    {
        event.preventDefault();
    }
});


const allOffers = document.getElementById("allOffers");

if(allOffers)
{
    allOffers.addEventListener("change", (event) =>
    {
        if(allOffers.checked)
        {
            document.querySelectorAll(".offers").forEach(function(item)
            {

                if(item.classList.contains("d-none"))
                {
                    item.classList.remove("d-none");
                    item.classList.add("d-block");
                }

            });
        } else
        {
            document.querySelectorAll(".offers").forEach(function(item)
            {

                if(item.classList.contains("d-block"))
                {
                    item.classList.remove("d-block");
                    item.classList.add("d-none");
                }

            });
        }
    });
}

const searcOffer = document.getElementById("searcherOffer");

if(searcOffer)
{
    let $ul = document.getElementById("searcher-offer");

    searcOffer.addEventListener("keyup", searcherOffer);
    searcOffer.addEventListener("focus", searcherOffer);

    document.addEventListener("click", function(event)
    {
        var isClickInsideBlock1 = $ul.contains(event.target);
        var isClickInsideBlock2 = searcOffer.contains(event.target);

        if(!isClickInsideBlock1 && !isClickInsideBlock2)
        {
            $ul.classList.remove("show");
        }
    });


    $ul.querySelectorAll("li").forEach(function(item)
    {

        searcOffer.classList.remove("d-none");

        item.addEventListener("click", function()
        {
            let inpt = document.getElementById(item.dataset.href);
            inpt.scrollIntoView({block: "center", inline: "center", behavior: "smooth"});
            $ul.classList.remove("show");
            setTimeout(function()
            {
                inpt.focus();
            }, 200);
        });
    });


    // searcOffer.addEventListener("focusout", (event) => {
    //     $ul.classList.remove('show');
    // });

}

function searcherOffer()
{

    let $filter = this.value.toUpperCase();
    let $counter = 0;

    let $ul = document.getElementById("searcher-offer");

    $ul.querySelectorAll("li").forEach(function(item)
    {
        let txtValue = item.textContent || item.innerText;

        if(txtValue.toUpperCase().indexOf($filter) > -1)
        {
            item.style.display = "";
            $counter++;
        } else
        {
            item.style.display = "none";
        }

        if($filter.length < 2 || $counter === 0)
        {
            $ul.classList.remove("show");
        } else
        {
            $ul.classList.add("show");
        }
    });
}


/* Поиск по штрихкодам SearchBarcode */

const searchBarcode = document.getElementById("searcherBarcode");

if(searchBarcode)
{
    let $ul = document.getElementById("searcher-barcode");

    searchBarcode.addEventListener("keyup", searcherBarcode);
    searchBarcode.addEventListener("focus", searcherBarcode);

    document.addEventListener("click", function(event)
    {
        var isClickInsideBlock1 = $ul.contains(event.target);
        var isClickInsideBlock2 = searchBarcode.contains(event.target);

        if(!isClickInsideBlock1 && !isClickInsideBlock2)
        {
            $ul.classList.remove("show");
        }
    });


    const lengthBarcodes = $ul.querySelectorAll("li").length;

    /**
     *  Проверить, что кол-во элементов "штрихкодов" > 1
     *  (На странице создания нового товара не имеет смысла отображать этот элемент)
     */
    if(lengthBarcodes > 1)
    {

        $ul.querySelectorAll("li").forEach(function(item)
        {

            searchBarcode.classList.remove("d-none");

            item.addEventListener("click", function()
            {

                let inpt = document.getElementById(item.dataset.href);

                /** Отобразить элемент */

                let targetId = '#flush-' + item.dataset.accordion;

                /* Получение и клик по элементу - кнопке аккордиона */
                let accordion_button = document.querySelector(`[data-bs-target="${targetId}"]`);

                // Cделать click, только если element с содержимым скрыт
                let elemTargetId = 'flush-' + item.dataset.accordion;
                let elem = document.getElementById(elemTargetId)

                if(elem.classList.contains('show') === false)
                {
                    accordion_button.click();
                }


                inpt.scrollIntoView({block: "center", inline: "center", behavior: "smooth"});

                $ul.classList.remove("show");
                setTimeout(function()
                {
                    inpt.focus();
                }, 200);
            });
        });
    }

}


/* Обработчик поиска по штрихкоду */
function searcherBarcode()
{

    let $filter = this.value.toUpperCase();
    let $counter = 0;

    let $ul = document.getElementById("searcher-barcode");

    $ul.querySelectorAll("li").forEach(function(item)
    {

        let txtValue = item.textContent || item.innerText;

        if(txtValue.toUpperCase().indexOf($filter) > -1)
        {
            item.style.display = "";
            $counter++;
        } else
        {
            item.style.display = "none";
        }

        if($filter.length < 2 || $counter === 0)
        {
            $ul.classList.remove("show");
        } else
        {
            $ul.classList.add("show");
        }
    });
}



