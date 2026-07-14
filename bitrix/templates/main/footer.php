<?if(!$is_main && $pages[1] !== 'basket' && !($pages[1] == 'catalog' && $pages[3])){?>
 <!--end::page_content--> <?}?> <footer>
<div class="footer__top cl">
	<div class="footer__col col--1">
 <a href="/" class="footer__logo"> <img width="206" alt="Металлинвест Профиль" src="/bitrix/templates/main/img/h_logo.jpg" height="44"> </a>
		<p class="footer__logotext">
			Кровельные и фасадные материалы для промышленного и гражданского строительства.
		</p>
 <a href="/upload/politics.pdf" target="_blank" style="font-size: 11px; text-decoration: none; color: #4d4d4d;">Политика конфиденциальности</a><br>
		<a href="/upload/rules-recommendation.pdf" target="_blank" style="font-size: 11px; text-decoration: none; color: #4d4d4d;">Рекомендательные технологии</a><br>
		<br>
		 <iframe src="https://yandex.ru/sprav/widget/rating-badge/2411245882?type=rating&theme=dark"; width="150" height="50" frameborder="0"></iframe>
	</div>
	<!--end::col__1-->
	<div class="footer__col col--2">
		<div class="footer__title">
			Контакты
		</div>
		<ul class="footer__list cl">
			<li><?= tplvar('phone_bottom_one', true);?></li>
			<li><?= tplvar('phone_bottom_two', true);?></li>
                       <!-- <li><?= tplvar('vk', true);?></li>
                        <li><?= tplvar('vu', true);?></li>-->
			<li><a href="/contacts/">Адреса магазинов</a> </li>
		</ul>
		<p>Мы в социальных сетях:</p>
		<div style="display: flex; flex-wrap: nowrap; grid-gap: 10px;">
			<noindex><a rel="nofollow" href="https://vk.com/metplusvrn"><img width="35px" src="/upload/medialibrary/1fe/yhyohram3at1msycz2wcmo82486rluin.png"></a></noindex>
			<noindex><a rel="nofollow" href="https://t.me/Metallinvest36"><img width="35px" src="/upload/medialibrary/a20/vrlu31q84lgsgcanuogccu75upf99wqk.png"></a></noindex>
			<noindex><a rel="nofollow" href="https://max.ru/u/f9LHodD0cOLdvXeNqMJVOopO2EmGb_FqMPSFAM1rjk9ZyRwsul_iFle8h70"><img width="35px" src="/upload/medialibrary/2a4/m94mqlfcghefbg6a3kwf5pw0ohuqsnz9.png"></a></noindex>
		</div>

	</div>

	<!--end::col__2-->
	<div class="footer__col col--3">
		<div class="footer__title">
			Компания
		</div>
		 <?$APPLICATION->IncludeComponent(
	"bitrix:menu",
	"footer-about",
	Array(
		"ALLOW_MULTI_SELECT" => "N",
		"CHILD_MENU_TYPE" => "top",
		"COMPONENT_TEMPLATE" => "footer-about",
		"DELAY" => "N",
		"MAX_LEVEL" => "1",
		"MENU_CACHE_GET_VARS" => array(),
		"MENU_CACHE_TIME" => "3600",
		"MENU_CACHE_TYPE" => "A",
		"MENU_CACHE_USE_GROUPS" => "Y",
		"ROOT_MENU_TYPE" => "top",
		"USE_EXT" => "N"
	)
);?>
	</div>
	<!--end::col__3-->
	<div class="footer__col col--4">
		<div class="footer__title">
			Каталог
		</div>
		<div class="cl">
			<ul class="footer__list footer__list--50">
				<li><a href="/catalog/krovelnye_materialy/">Кровельные материалы</a></li>
				<li><a href="/catalog/fasadnye_materialy/">Фасадные материалы</a></li>
			</ul>
		</div>
		 <?
							/*
							$APPLICATION->IncludeComponent(
								"bitrix:menu", 
								"footer-catalog", 
								array(
									"ALLOW_MULTI_SELECT" => "N",
									"CHILD_MENU_TYPE" => "footer",
									"DELAY" => "N",
									"MAX_LEVEL" => "1",
									"MENU_CACHE_GET_VARS" => array(
									),
									"MENU_CACHE_TIME" => "3600",
									"MENU_CACHE_TYPE" => "A",
									"MENU_CACHE_USE_GROUPS" => "Y",
									"ROOT_MENU_TYPE" => "footer_catalog",
									"USE_EXT" => "N",
									"COMPONENT_TEMPLATE" => "footer"
								),
								false
							); //footer__list */?>
	</div>
</div>
<div class="footer__bottom cl">
	<noindex><p style="font-size: .857em;">Наш сайт использует <a href="/upload/cookies.pdf" target="_blank">cookies</a> для обеспечения работоспособности и сбора статистики. С их помощью мы анализируем пользовательскую активность, улучшаем работу сайта и делаем рекламу более релевантной. Оставаясь на сайте, вы даете согласие на обработку ваших персональных данных. Вы можете отключить сохранение cookies в настройках браузера в любой момент. На сайте также применяются <a href="/upload/rules-recommendation.pdf" target="_blank">рекомендательные технологии</a>. Подробнее об обработке персональных данных — в соответствующей <a href="/upload/politics.pdf" target="_blank">Политике</a>.</p></noindex>

	<div class="footer__copyright">
		© 2006 — <?=date("Y");?>. Металлинвест Профиль. Воронеж
	</div>
	<div class="primelogo">
		<a href="https://prime-ltd.su/?from=https://metprof-vrn.ru/"> <img alt="Продвижение сайтов" src="http://prime-ltd.su/logo/black.svg" title="Продвижение сайтов"></a>
	</div>
	 <!--
						<ul class="footer__pay pay" title="Все способы оплаты">
							<li><a href="javascript:void(0)" class="visa">Visa</a></li>
							<li><a href="javascript:void(0)" class="master">MasterCard</a></li>
							<li><a href="javascript:void(0)" class="qiwi">Qiwi</a></li>
							<li><a href="javascript:void(0)" class="webmoney">Webmoney</a></li>
							<li><a href="javascript:void(0)" class="ya">Яндекс Деньги</a></li>
						</ul>
-->
	<div class="footer__studio">
	</div>
</div>
 </footer>
			<!--end::wr-->
     	<!--end::container-->
<!-- roistat от Prime должен стоять выше Живосайта -->

<!-- Roistat Counter Start -->
<script>
(function(w, d, s, h, id) {
    w.roistatProjectId = id; w.roistatHost = h; w.roistatPage = d.location.href; w.roistatReferrer = d.referrer;
    var p = d.location.protocol == "https:" ? "https://" : "http://";
    var u = /^.*roistat_visit=[^;]+(.*)?$/.test(d.cookie) ? "/dist/module.js" : "/api/site/1.0/"+id+"/init?referrer="+encodeURIComponent(d.location.href);
    var js = d.createElement(s); js.charset="UTF-8"; js.async = 1; js.src = p+h+u; var js2 = d.getElementsByTagName(s)[0]; js2.parentNode.insertBefore(js, js2);
})(window, document, 'script', 'cloud.roistat.com', 'cc563f216e9979c2d9c12cfcd7e3a05c');
</script>
<!-- Roistat Counter End -->



<!-- чат от Битрикса -->
<script>
        (function(w,d,u){
                var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
                var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
        })(window,document,'https://cdn-ru.bitrix24.ru/b7243579/crm/site_button/loader_5_s0ja29.js');
		
		window.onRoistatModuleLoaded = function () {
			$(".phone-ord-send-button").click(function(){			
				window.roistatLeadHunterShow();
				return false;
			});
		};
	
</script>
<!-- Yandex.Metrika counter Prime -->
<script type="text/javascript" >
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter48970379 = new Ya.Metrika({
                    id:48970379,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    webvisor:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<!-- /Yandex.Metrika counter -->
			<?$APPLICATION->IncludeComponent(
	"nbrains:main.feedback",
	"order-product",
	Array(
		"COMPONENT_TEMPLATE" => "order-product",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"EMAIL_TO" => "sale@polimer-vrn",
		"EVENT_MESSAGE_ID" => array(0=>"103",),
		"IBLOCK_ID" => "18",
		"IBLOCK_TYPE" => "feedback",
		"OK_TEXT" => "Спасибо, ваше сообщение принято.",
		"PROPERTY_CODE" => array(0=>"EMAIL",1=>"PHONE",2=>"FIO",3=>"PRODUCT",4=>"LINK_PRODUCT",5=>"RULE",),
		"USE_CAPTCHA" => "Y"
	)
);?>
			<?$APPLICATION->IncludeComponent(
	"nbrains:main.feedback",
	"write-mail",
	Array(
		"COMPONENT_TEMPLATE" => "write-mail",
		"EMAIL_TO" => "sale@polimer-vrn",
		"EVENT_MESSAGE_ID" => array(0=>"92",),
		"IBLOCK_ID" => "14",
		"IBLOCK_TYPE" => "feedback",
		"OK_TEXT" => "Спасибо, ваше сообщение принято.",
		"PROPERTY_CODE" => array(0=>"FIO",1=>"PHONE",2=>"EMAIL",3=>"DESC",4=>"RULE",),
		"USE_CAPTCHA" => "Y"
	)
);?>
				<?
				$arResult = getUrlProd($APPLICATION->GetCurPage(false));
				if(CFile::GetPath($arResult['MORE_PHOTO']['VALUE'][0])){
					$img = CFile::GetPath($arResult['MORE_PHOTO']['VALUE'][0]);
				}else{
					$img = CFile::GetPath($arResult['DETAIL_PICTURE']);
				}
				$APPLICATION->IncludeComponent(
					"nbrains:main.feedback",
					"buy-one-click",
					array(
						"EMAIL_TO" => "sale@polimer-vrn",
						"EVENT_MESSAGE_ID" => array(
							0 => "90",
						),
						"IBLOCK_ID" => "15",
						"IBLOCK_TYPE" => "feedback",
						"OK_TEXT" => "Спасибо, ваше сообщение принято.",
						"PROPERTY_CODE" => array(
							0 => "FIO",
							1 => "PHONE",
							2 => "EMAIL",
							3 => "RULE",
							4 => "PRODUCT",
							5 => "LINK_PRODUCT",
							6 => "IMG_PRODUCT",
							7 => "PRICE",
						),
						"USE_CAPTCHA" => "Y",
						"COMPONENT_TEMPLATE" => "buy-one-click",
						"PRODUCT" => array(
							"NAME" => $arResult['NAME'],
							"LINK" => $arResult['DETAIL_PAGE_URL'],
							"IMG" => $img,
							"PRICE" => priceDiscount($arResult['ID']),
						)
					),
					false
				);?>
				<?$APPLICATION->IncludeComponent(
	"nbrains:main.feedback",
	"buy-one-click-cart",
	Array(
		"COMPONENT_TEMPLATE" => "buy-one-click-cart",
		"EMAIL_TO" => "sale@polimer-vrn",
		"EVENT_MESSAGE_ID" => array(0=>"104",),
		"IBLOCK_ID" => "23",
		"IBLOCK_TYPE" => "feedback",
		"OK_TEXT" => "Спасибо, ваше сообщение принято.",
		"PROPERTY_CODE" => array(0=>"FIO",1=>"EMAIL",2=>"PHONE",3=>"RULE",4=>"PRODUCT_CART",),
		"USE_CAPTCHA" => "Y"
	)
);?>
				<?$APPLICATION->IncludeComponent(
	"nbrains:main.feedback",
	"free-consultant",
	Array(
		"COMPONENT_TEMPLATE" => "free-consultant",
		"EMAIL_TO" => "sale@polimer-vrn",
		"EVENT_MESSAGE_ID" => array(0=>"91",),
		"IBLOCK_ID" => "16",
		"IBLOCK_TYPE" => "feedback",
		"OK_TEXT" => "Спасибо, ваше сообщение принято.",
		"PROPERTY_CODE" => array(0=>"FIO",1=>"PHONE",2=>"EMAIL",3=>"RULE",4=>"PRODUCT",5=>"LINK_PRODUCT",),
		"USE_CAPTCHA" => "Y"
	)
);?>
