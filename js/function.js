
function metprofUpdateCartWidgets(count, priceText) {
    var countValue = String(count);
    var priceValue = priceText || '0 руб.';

    document.querySelectorAll('.cart__number').forEach(function (el) {
        el.textContent = countValue;
    });

    document.querySelectorAll('.cart__sum--numbers').forEach(function (el) {
        el.textContent = priceValue;
    });

    document.querySelectorAll('.header__cart .cart__sum').forEach(function (el) {
        el.style.display = '';
    });
}

function metprofParseCartFromResponse(data) {
    if (!data || typeof data !== 'string') {
        return null;
    }

    var match = data.match(/<!--METPROF_CART:([\s\S]*?)-->/);
    if (!match) {
        return null;
    }

    try {
        return JSON.parse(match[1]);
    } catch (e) {
        return null;
    }
}

function metprofApplyCartFromResponse(data, xhr) {
    var cart = metprofParseCartFromResponse(data);

    if (cart && cart.count !== undefined) {
        metprofUpdateCartWidgets(cart.count, cart.price || '');
        return;
    }

    metprofApplyCartHeaders(xhr);
}

function metprofApplyCartHeaders(xhr) {
    if (!xhr || typeof xhr.getResponseHeader !== 'function') {
        return;
    }

    var count = xhr.getResponseHeader('X-Metprof-Cart-Count');
    if (count === null || count === '') {
        return;
    }

    var price = xhr.getResponseHeader('X-Metprof-Cart-Price');
    metprofUpdateCartWidgets(count, price ? decodeURIComponent(price) : '');
}

function metprofLoadCartHeader(cartEl, url) {
    if (!cartEl || !url) {
        return;
    }

    $.ajax({
        url: url,
        type: 'GET',
        cache: false,
        data: {
            _: Date.now()
        },
        success: function (html) {
            if (html && $.trim(html)) {
                cartEl.innerHTML = html;
            }
        }
    });
}

function replaseBasketTop() {
    metprofLoadCartHeader(
        document.querySelector('header .header__bottom .bx-basket'),
        '/ajax/basket-header.php'
    );
    metprofLoadCartHeader(
        document.querySelector('header .hmobile .bx-basket'),
        '/ajax/basket-header-mobile.php'
    );
}





var addToBasketPending = {};

function metprofIsBasketAddSuccess(response) {
    return response === 'Товар успешно добавлен в корзину'
        || response.indexOf('Товар уже в корзине') === 0;
}

function metprofMarkAddedToCart(el) {
    if (!el) {
        return;
    }

    var $el = $(el);

    if ($el.hasClass('polimer-search-dropdown__action--cart')) {
        $el.addClass('is-in-cart').attr('title', 'Перейти в корзину');
        $el.off('click').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            window.location.href = '/personal/cart/';
        });
        return;
    }

    var $cartBtn = $el.closest('.add2cart');
    if ($cartBtn.length) {
        $cartBtn.addClass('is-in-cart');
        $cartBtn.find('.txt2').text('В корзине');
        $cartBtn.find('.txt1').text('В корзине');
        $cartBtn.attr('onclick', 'window.location.href="/personal/cart/"');
        return;
    }

    $el.text('В корзине');
    $el.attr('onclick', 'window.location.href="/personal/cart/"');
}

function addToBasket2(idel, quantity,el,type) {
        if (addToBasketPending[idel]) {
            return false;
        }

        quantity = parseFloat(quantity);
        if (!quantity || !isFinite(quantity) || quantity <= 0) {
            quantity = 1;
        }

		var minCount = parseFloat($('#order-table').attr('count-min'));
        var fullCount = parseFloat($('#order-table').attr('count-full'));

        addToBasketPending[idel] = true;

        $.ajax({
            url: '/ajax/maxQuantity.php?id='+idel,
            type: 'get',
            success: function (quaMin) {

            if(quaMin){
                var qu = quantity;
                quantity *= parseFloat(quaMin);
            }

            if(fullCount < minCount && type == 6) {
                delete addToBasketPending[idel];
                alertify.error("Минимальный заказ "+ minCount +"м2");

                return false;
            }

            var props;
            if(fullCount >= minCount && type == 6){

                    var props = [
                        {
                            NAME:"длина листа",
                            CODE:"WIDTH_LIST",
                            VALUE:$(el).attr('data-list')
                        },
                        {
                            NAME:"кол-во м²",
                            CODE:"SQUARE",
                            VALUE:$(el).attr('data-count')
                        }
                    ];
                $(el).remove();
                el = $('#button-cart-offers');
            }

            quantity = parseFloat(quantity);
            if (!quantity || !isFinite(quantity) || quantity <= 0) {
                quantity = 1;
            }

            $href = "/ajax/add.php";
            var _result = true;
                $.ajax({
                    url: $href,
                    type: 'post',
                    data:{
                        id:idel,
                        quantity:quantity,
                        type:type,
                        props:props
                    },
                    complete: function () {
                        delete addToBasketPending[idel];
                    },
                    success: function (data, textStatus, xhr) {
                        var response = $.trim(String(data).replace(/<!--METPROF_CART:[\s\S]*?-->/, ''));
                        metprofApplyCartFromResponse(data, xhr);
                        replaseBasketTop();
                        if (metprofIsBasketAddSuccess(response)) {
                            if (window.alertify) {
                                if (response === 'Товар успешно добавлен в корзину') {
                                    alertify.success(response);
                                } else {
                                    alertify.message(response);
                                }
                            }
                            metprofMarkAddedToCart(el);
                            if (response === 'Товар успешно добавлен в корзину' && typeof yaCounter48970379 !== 'undefined') {
                                yaCounter48970379.reachGoal('korzina250720181506');
                            }
                        } else {
                            if (window.alertify) {
                                alertify.error(response);
                            }
                            _result = false;
                        }

                        if(quaMin && _result){
                            alertify.success("Добавлено "+ qu +" упаковка(и) " +quaMin+ "шт.");
                        }
                        return _result;

                    }
                });
            },
            error: function () {
                delete addToBasketPending[idel];
            }
        });
}

function setCupon(){
    var numCupon = $('#coupon').val();
    if(numCupon){
        $.ajax({
            type: "GET",
            url: "/ajax/set_cupon.php?cupon="+numCupon,
            success: function(msg){
                if(msg)
                {

                    UpdateBigBasket();
                    alertify.success("Купон активирован!");
                }
                else
                {
                    alertify.error("Купон не найден");
                }
            }
        });
    }else{
        alertify.error('Введите номер купона!');
        return false;
    }

}

function inputQuntly(max,count,id){
    if(count < 1){
        $('.quantity#'+id+' input').val(1);
        alertify.error("Запрашиваемое кол-во. На складе нет");
        return false;
    }
    if(count > max){
        $('.quantity#'+id+' input').val(max);
        alertify.error("Запрашиваемое кол-во превышает остаток. На складе: " + max);
        return false;
    }else{
        var data="id="+id+"&quant="+count;
        ChangeCount(data);
    }
}


function basketPlus(max,count,id){
    var increm = parseInt(count)+1;

    if(increm > max){
        $('.quantity#'+id+' input').val(max-1);
        alertify.error("Запрашиваемое кол-во превышает остаток. На складе: " + max);
        return false;
    }else{
        var data="id="+id+"&quant="+increm;
        ChangeCount(data);
    }
}

function basketMinus(max,count,id){
    var increm = parseInt(count)-1;
    if(increm < 1){
        $('.quantity#'+id+' input').val(1);
        alertify.error("Запрашиваемое кол-во. На складе нет");
        return false;
    }else{
        var data="id="+id+"&quant="+increm;
        ChangeCount(data);
    }
}

function ChangeCount(data)
{
    $.ajax({
        type: "GET",
        url: "/ajax/change_count.php",
        data:data,
        success: function(msg){
            if(msg!="error")
            {
                 UpdateBigBasket();
            }
            else
            {
                alertify.error("");
            }
        }
    });
}
function UpdateBigBasket(){
    $.ajax({
        type: "GET",
        url: "/ajax/big_basket.php",
        data:"",
        success: function(msg){
            if(msg!="error")
            {
                var $target = $(".page_content");
                if ($target.length) {
                    $target.html(msg);
                }
                if (typeof replaseBasketTop === 'function') {
                    replaseBasketTop();
                }
            }
            else
            {
                alertify.error("Произошла ошибка. Попробуйте повторить запрос позже");
            }
        }
    });
}

function deleteBasket(){
    $.ajax({
        type: "GET",
        url: "/ajax/delete_all_basket.php",
        data:"",
        success: function(msg){
            if(msg!="error")
            {
                UpdateBigBasket();
                if (typeof metprofUpdateCartWidgets === 'function') {
                    metprofUpdateCartWidgets(0, '0 руб.');
                }
            }
            else
            {
                alertify.error("Произошла ошибка. Попробуйте повторить запрос позже");
            }
        }
    });
    return false;
}

function number_format( number, decimals, dec_point, thousands_sep ) {	// Format a number with grouped thousands

    var i, j, kw, kd, km;

    // input sanitation & defaults
    if( isNaN(decimals = Math.abs(decimals)) ){
        decimals = 2;
    }
    if( dec_point == undefined ){
        dec_point = ",";
    }
    if( thousands_sep == undefined ){
        thousands_sep = ".";
    }

    i = parseInt(number = (+number || 0).toFixed(decimals)) + "";

    if( (j = i.length) > 3 ){
        j = j % 3;
    } else{
        j = 0;
    }

    km = (j ? i.substr(0, j) + thousands_sep : "");
    kw = i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep);
    kd = (decimals ? dec_point + Math.abs(number - i).toFixed(decimals).replace(/-/, 0).slice(2) : "");


    return km + kw + kd;
}


//Удаление длины листа в детальной карте товара
function delRow(El) {
    $(El).parent().parent().remove();
}



function updatePrice(El, val) {
    if (!val)
    {
        $(El).attr('value', 0);
        val = 0;
    }
    if (val < 0) {
        $(El).attr('value', 0);
        val = 0;
        return false;
    }

            var sq_full = 0;
            var width_list = {};

            var _elements = $(".order-cnt");

            _elements.each(function (i, _el) {
                val = $(_el).find('input[name=_quantity]').val();
                var dv = $(_el).find('.drop-value');
                var length_part = (dv.length > 0) ? parseFloat($(_el).find('.drop-value').html()) : parseFloat($(_el).find('input[name=length]').val().replace(',', '.') * 1000);
                var width_part = $("input[name=width]").val();
                var sq = width_part * length_part * val / 1000000;
                width_list = sq
                sq_full = sq_full + sq;
                $('#order-table').attr('count-full',sq_full);
            });

            $('#count_product').val(sq_full);

            _elements.each(function (i, _el) {

                val = $(_el).find('input[name=_quantity]').val();
                var dv = $(_el).find('.drop-value');
                var length_part = (dv.length > 0) ? parseFloat($(_el).find('.drop-value').html()) : parseFloat($(_el).find('input[name=length]').val().replace(',', '.') * 1000);
                var width_part = $("input[name=width]").val();
                var sq = width_part * length_part * val / 1000000;

                if (width_part) {
                    var price = parseFloat(sq * parseFloat($(_el).attr('data-price')));
                } else {
                    var price = parseFloat(((length_part * val) / 1000) * parseFloat($(_el).attr('data-price')));
                }

                $(_el).attr('data-count',sq);
                $(_el).find('.sq').empty().append(sq + ' м²');
                $(_el).find('.price_in-table').empty().append(number_format(parseFloat(price), 2, '.', ' ') + '₽');

            });
}


(function waitForJQuery(start) {
    if (window.jQuery) {
        start(window.jQuery);
        return;
    }
    setTimeout(function () { waitForJQuery(start); }, 30);
})(function ($) {
    window.jQuery = window.$ = $;

    function loadReadmore(cb) {
        if (typeof $.fn.readmore === 'function') {
            cb();
            return;
        }
        var existing = document.querySelector('script[data-metprof-readmore="1"]');
        if (existing) {
            var tries = 0;
            var t = setInterval(function () {
                tries += 1;
                if (typeof $.fn.readmore === 'function' || tries > 80) {
                    clearInterval(t);
                    cb();
                }
            }, 50);
            return;
        }
        var s = document.createElement('script');
        s.src = '/js/readmore.js?v=2';
        s.async = true;
        s.setAttribute('data-metprof-readmore', '1');
        s.onload = function () { cb(); };
        s.onerror = function () { cb(); };
        document.head.appendChild(s);
    }

    loadReadmore(function () {

$(function(){

    function initSectionReadmore() {
        if (typeof $.fn.readmore !== 'function') {
            return false;
        }

        var moreLink = '<a href="#" class="catalog-sections-more">Показать ещё</a>';
        var lessLink = '<a href="#" class="catalog-sections-more is-open">Скрыть</a>';
        var boundAny = false;

        $('.related_articles').each(function () {
            var $wrap = $(this);
            var $text = $wrap.find('.col-txt > .catalog-sections-text-hidden').first();
            if (!$text.length || $text.data('readmore-bound')) {
                return;
            }

            var maxHeight = 320;
            var $articles = $wrap.find('.col-articles').first();
            var $allBtn = $articles.find('a.allarticles').first();
            if ($articles.length && $(window).width() > 1019) {
                var textTop = $text.offset().top;
                if ($allBtn.length) {
                    // Кнопка «Показать ещё» (margin-top ~20) на уровне «Все статьи»
                    maxHeight = Math.max(180, Math.round($allBtn.offset().top - textTop - 20));
                } else {
                    maxHeight = Math.max(180, Math.round($articles.outerHeight(true) - 60));
                }
            }

            $text.data('readmore-bound', 1);
            boundAny = true;
            $text.readmore({
                speed: 75,
                maxHeight: maxHeight,
                moreLink: moreLink,
                lessLink: lessLink
            });
        });

        $('.catalog-sections-text').each(function () {
            var $text = $(this);
            if ($text.closest('.related_articles').length || $text.data('readmore-bound')) {
                return;
            }
            $text.data('readmore-bound', 1);
            boundAny = true;
            $text.readmore({
                speed: 75,
                maxHeight: 220,
                moreLink: moreLink,
                lessLink: lessLink
            });
        });

        if (boundAny) {
            return true;
        }
        return !$('.related_articles .catalog-sections-text-hidden, .catalog-sections-text').length;
    }

    if (!initSectionReadmore()) {
        var readmoreTries = 0;
        var readmoreTimer = setInterval(function () {
            readmoreTries += 1;
            if (initSectionReadmore() || readmoreTries > 80) {
                clearInterval(readmoreTimer);
            }
        }, 50);
    }

    $('.category__show').click(function(){
       var than = $(this);
        than.parent().find('.toggle_product_no').slideToggle();
        return false;
    });


    //РћРїСЂРµРґРµР»РµРЅРёРµ СЏС‡РµР№РєРё
    $('#available-length').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var item = button.data('item');
        $(this).find('table').attr('data-td', item);
        //alert(item);
    });
    //Выбор длины в карточке товара.
    $("#available-length td").bind('click', function () {
        if (!$(this).hasClass('disable')) {
            var data_item = $("#av-length-table").attr('data-td');
            $("td[data-item='" + data_item + "'] div.drop-value").empty().append($(this).html() + ' мм');
            $("td[data-item='" + data_item + "']").parent().attr('data-list',$(this).html());

            $("td[data-item='" + data_item + "']").parent().attr('data-id',$(this).attr('data-id'));
            $("td[data-item='" + data_item + "']").parent().attr('data-idblock',$(this).attr('data-idblock'));
            $("td[data-item='" + data_item + "']").parent().attr('data-price',$(this).attr('data-price'));
            $("#available-length").modal('hide');

            updatePrice($("td[data-item='" + data_item + "'] div.drop-value").parent().parent().parent().find('input[name=_quantity]'), $("td[data-item='" + data_item + "'] div.drop-value").parent().parent().parent().find('input[name=_quantity]').val());
        }
    });




    //Добавление длины листа в детальной карте товара
    $(".p-view__order-table-add").bind('click', function () {
        var seconds = new Date().getTime();
        if ($(this).attr('data-type') != 'p23') {
            $("#order-table").append('<tr class="order-cnt">' +
                '<td data-toggle="modal" data-target="#available-length" data-item="item-' + seconds + '">' +
                '<div class="dropdown dropdown_double-icon dropdown-modal">' +
                '<div class="drop-value">0 мм</div>' +
                '</div>' +
                '</td>' +
                '<td>' +
                '<div class="dropdown dropdown_double-icon">' +
                '<input name="_quantity" type="number" max="50000" style="width: 60px;" placeholder="0 шт" onkeyup="updatePrice(this, $(this).val())" onchange="updatePrice(this, $(this).val())" onkeypress="return event.charCode >= 48 && event.charCode <= 57">' +
                '</div>' +
                '</td>' +
                '<td class="sq">' +
                '0 м²' +
                '</td>' +
                '<td>' +
                '<span class="price_in-table">0 ₽</span>' +
                '<button class="no-style p-view__order-table-del" onclick="delRow(this)">&times;' +
                '</button>' +
                '</td>' +
                '</tr>');
        } else {
            $("#order-table").append('<tr class="order-cnt">' +
                '<td data-toggle="modal" data-target="#available-length" data-item="item-' + seconds + '">' +
                '<div class="dropdown dropdown_double-icon dropdown-modal">' +
                '<div class="drop-value">0 мм</div>' +
                '</div>' +
                '</td>' +
                '<td>' +
                '<div class="dropdown dropdown_double-icon">' +
                '<input name="_quantity" type="number" max="50000" style="width: 60px;" placeholder="0 шт" onkeyup="updatePrice(this, $(this).val())" onchange="updatePrice(this, $(this).val())" onkeypress="return event.charCode >= 48 && event.charCode <= 57">' +
                '</div>' +
                '</td>' +
                '<td>' +
                '<span class="price_in-table">0 ₽</span>' +
                '<button class="no-style p-view__order-table-del" onclick="delRow(this)">&times;' +
                '</button>' +
                '</td>' +
                '</tr>');
        }
    });



    $("#button-cart-offers").bind('click',function(){
        var el = $(this).closest('.data-square').find('.order-cnt');

        el.each(function (i, el) {
            var id = $(el).attr('data-id');
            var count = $(el).attr('data-count');
            var list = $(el).attr('data-list');
            if(count <= 0 || count == undefined){
                alertify.error("Не указано количество, товар " + list + " мм. не добавлен" );
            }else{
                addToBasket2(id,count,el,6);
            }
        });

    });



    $('#modal-form-size').ajaxForm({
        url:"/ajax/modal_size_form.php",
        type:"post",
        dataType:"json",
        success:function(responseText, statusText, xhr, $form){
            if(responseText.response){
                alertify.success("Сообщение отправлено!");
                $form.html("<div class='alert alert-success' role='alert'><strong>Сообщение отправлено!</strong> Номер обращения: " + responseText.data + ". </div>");
            }else{
                alertify.error(responseText.data);
            }
        }
    });

    $.get('/ajax/popover.php', function(data) {
        if(data.response){
            var popoverTemp = '<div class="popover" role="tooltip">'
                + '<div class="arrow"></div>'
                + '<h3 class="popover-title"></h3>'
                + '<div class="popover-content"></div>'
                + '<div class="popover-button">'
                + '<button type="button" class="btn btn-default" onclick="destroyPopover()">Ok</button>'
                + '</div>'
                + '</div>';

            $('#order-table').popover({
                "delay" : {"show" : 1000},
                "title" : "Длинна листа и количество.",
                "content" : "Выберете необходимую длину и количество листов.",
                "placement" : "left",
                "template" : popoverTemp
            }).trigger('click');

            $('#popover-button-cart').popover({
                "delay" : {"show" : 1000},
                "title" : "Добавление в корзину.",
                "content" : "После выбора всех листов положите все в корзину.",
                "placement" : "bottom",
                "template" : popoverTemp
            }).trigger('click');

            $('#popover-button-cart-table-add').popover({
                "delay" : {"show" : 1000},
                "container" : "body",
                "title" : "Лист другого размера.",
                "content" : "Если требуется доп.товар с другой длиной нажмите здесь.",
                "placement" : "right",
                "template" : popoverTemp
            }).trigger('click');
        }else{
            console.log("Вы уже ученый!");
        }
    }, "json");


	  $('.ym-goal-subscribe-price').submit(function(e) {
        var $form = $(this);
        $.ajax({
          type: $form.attr('method'),
          url: $form.attr('action'),
          data: $form.serialize()
        }).done(function(data) {
          alertify.success("Подписка оформлена!");
		  $form[0].reset();
        }).fail(function() {
          alertify.error("Произошла ошибка. Попробуйте повторить запрос позже");
        });
        //отмена действия по умолчанию для кнопки submit
        e.preventDefault();
      });
});

if($(window).width() < 768){
    $('.slider__product img').each(function(li,el){
        var self = $(el);
        self.attr('src',self.attr('data-small'));
    });
}

    }); // loadReadmore
}); // waitForJQuery

function destroyPopover(){
    $('#order-table, #popover-button-cart, #popover-button-cart-table-add').popover('destroy');
    $.get('/ajax/popover.php', { button : true });
}

