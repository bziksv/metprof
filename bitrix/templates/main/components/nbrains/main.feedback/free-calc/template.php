<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
?>



<form action="<?=POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data">
		<?=bitrix_sessid_post()?>

		<?if(!empty($arResult["ERROR_MESSAGE"]))
		{
			foreach($arResult["ERROR_MESSAGE"] as $v)
				ShowError($v);
		}

		if(strlen($arResult["OK_MESSAGE"]) > 0)
		{
			?><div class="mf-ok-text"><?=$arResult["OK_MESSAGE"]?></div><?
		}
		?>

		<div class="line">
			<span>ФИО</span>
			<input type="text" class="fio name" placeholder="ФИО" name="FIO" value="<?=$_POST['FIO']?>">
		</div>

		<div class="line pl">
			<span>Телефон</span>
			<input type="text" class="phone" name="PHONE" placeholder="+7 (473) 234-03-01" value="<?=$_POST['PHONE']?>">
			<span class="txt_ct">Время звонка, до</span>
			<input type="text" class="call_time" name="TIME_AFTER" value="<?=$_POST['TIME_AFTER']?>">
		</div>

		<div class="line">
			<span>E-mail</span>
			<input type="text" class="mail" name="EMAIL" value="<?=$_POST['EMAIL']?>">
		</div>

		<div class="line textarea">
			<span>Текст заявки</span>
			<textarea name="DESC" cols="30" rows="7" placeholder="Введите краткий текст"><?=$_POST['DESC']?></textarea>
		</div>


		<a href="#" class="attach" style="position: relative;"><input style="opacity: 0;position: absolute;min-width: 495px;cursor: pointer;" name="FILE" type="file" />Прикрепите план здания или техническое задание <span>(файл до 50 мб)</span></a>

		<div class="rule">
			<input type="checkbox" class="fio" name="RULE" value="Y" checked>
			<span>
				Нажимая на эту кнопку, я даю свое согласие на обработку персональных данных и соглашаюсь с условиями <a href="/upload/politics.pdf" target="_blank">политики обработки персональных данных</a>.
<!--					Я прочитал правила-->
<!--					<a href="#" class="show-popup" data-id="--><?//=$arParams["IBLOCK_TYPE"].$arParams["IBLOCK_ID"]?><!--">Правила</a>-->
<!--					и даю свое согласие на обработку персональных данных-->
			</span>
		</div>

			<?if($arParams["USE_CAPTCHA"] == "Y"):?>
				<div class="mf-captcha">
 <?/*<div class="g-recaptcha" data-sitekey="6LddjoopAAAAAAfUXWqqqdUyh-C_79qKA5EbhKSj"></div>*/?>
<div class="g-recaptcha" data-sitekey="6LeUbdsqAAAAAEPIqSZ3jxKfu3PI4OYMfeTvjC-K"></div>
				</div>
			<?endif;?>

		<input type="hidden" name="PARAMS_HASH" value="<?=$arResult["PARAMS_HASH"]?>">
        <input type="submit" class="send roi_free_calc" name="submit" value="<?=GetMessage("MFT_SUBMIT")?>">

</form>





<div class="popup" id="<?=$arParams["IBLOCK_TYPE"].$arParams["IBLOCK_ID"]?>" style="display: none;width: 650px;margin: 0 0 0 -325px;">
	<a href="#" class="close">&nbsp;</a>
	<div class="title"></div>
	<div class="subtitle">
		<?$APPLICATION->IncludeComponent(
			"bitrix:main.include",
			"",
			Array(
				"AREA_FILE_SHOW" => "file",
				"AREA_FILE_SUFFIX" => "inc",
				"EDIT_TEMPLATE" => "",
				"PATH" => "/include/rule.php"
			)
		);?>
	</div>
</div>
