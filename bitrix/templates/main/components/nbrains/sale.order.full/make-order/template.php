<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$USER->IsAuthorized())
{
	$regInvalid = [];
	$authInvalid = [];
	$errMsg = (string)($arResult["ERROR_MESSAGE"] ?? '');
	$fieldErrorMap = [
		'NEW_NAME' => 'STOF_ERROR_REG_NAME',
		'NEW_LAST_NAME' => 'STOF_ERROR_REG_LASTNAME',
		'NEW_EMAIL' => ['STOF_ERROR_REG_EMAIL', 'STOF_ERROR_REG_BAD_EMAIL'],
		'USER_PERSONAL_PHONE' => ['STOF_ERROR_REG_PHONE', 'STOF_ERROR_REG_BAD_PHONE'],
		'NEW_PASSWORD' => ['STOF_ERROR_REG_FLAG1', 'STOF_ERROR_REG_PASS'],
		'NEW_PASSWORD_CONFIRM' => 'STOF_ERROR_REG_PASS',
	];
	$authErrorMap = [
		'USER_LOGIN' => ['STOF_ERROR_AUTH_LOGIN', 'STOF_ERROR_AUTH'],
		'USER_PASSWORD' => ['STOF_ERROR_AUTH_PASSWORD', 'STOF_ERROR_AUTH'],
	];
	$displayError = $errMsg;
	foreach ($fieldErrorMap as $field => $codes) {
		foreach ((array)$codes as $code) {
			$text = GetMessage($code);
			if ($text !== '' && $text !== $code && $errMsg !== '' && mb_stripos($errMsg, $text) !== false) {
				$regInvalid[$field] = true;
				$displayError = str_ireplace($text . '.<br />', '', $displayError);
				$displayError = str_ireplace($text . '<br />', '', $displayError);
				$displayError = str_ireplace($text . '.', '', $displayError);
				$displayError = str_ireplace($text, '', $displayError);
			}
		}
	}
	foreach ($authErrorMap as $field => $codes) {
		foreach ((array)$codes as $code) {
			$text = GetMessage($code);
			if ($text === '' || $text === $code || $errMsg === '') {
				continue;
			}
			if (mb_stripos($errMsg, $text) !== false) {
				$authInvalid[$field] = true;
				// убираем и полную строку с хвостом Bitrix ("...: Неверный логин...")
				$displayError = preg_replace(
					'/' . preg_quote($text, '/') . '(?:[^<]*)(?:\.<br\s*\/?>|<br\s*\/?>|\.)?/iu',
					'',
					$displayError
				);
			}
		}
	}
	$displayError = trim(preg_replace('/(<br\s*\/?>\s*)+/i', '<br />', $displayError), " \t\n\r\0\x0B.");
	$displayError = preg_replace('/^(<br\s*\/?>)+|(<br\s*\/?>)+$/i', '', $displayError);
	$arResult['REG_INVALID_FIELDS'] = array_keys($regInvalid);
	$arResult['AUTH_INVALID_FIELDS'] = array_keys($authInvalid);
	if ($displayError !== '') {
		echo ShowError($displayError);
	}
	include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/auth.php");
}
else
{
	if ($arResult["CurrentStep"] < 6):?>
		<form method="post" action="<?= htmlspecialcharsbx($arParams["PATH_TO_ORDER"]) ?>" name="order_form" id="order_form">
			<?=bitrix_sessid_post()?>
	<?endif;?>


	<?
	if($arParams["SHOW_MENU"] == "Y")
	{
		?>		

			<?if ($arResult["CurrentStep"] < 6):?>
				
				<?
				$arMenuLine = array(
						0 => GetMessage("STOF_PERSON_TYPE"),
						1 => GetMessage("STOF_MAKING"),
						2 => GetMessage("STOF_DELIVERY"),
						3 => GetMessage("STOF_PAYMENT"),
						4 => GetMessage("STOF_CONFIRM")
					);
				for ($i = 0; $i < count($arMenuLine); $i++)
				{
					
					//if ($arResult["SKIP_FIRST_STEP"] == "Y" && $i == 0)
						//continue;
					if ($arResult["SKIP_FIRST_STEP"] == "Y" && $i == 0)
						continue;
					if ($arResult["SKIP_SECOND_STEP"] == "Y" && $i == 1)
						continue;
					if ($arResult["SKIP_THIRD_STEP"] == "Y" && $i == 2)
						continue;
					if ($arResult["SKIP_FORTH_STEP"] == "Y" && $i == 3)
						continue;

					
					if ($i > 0)
						echo " &gt; ";

					if ($arResult["CurrentStep"] > $i + 1)
						echo "<a href=\"#\" OnClick=\"document.order_form.CurrentStep.value='".($i + 1)."'; document.order_form.BACK.value='Y'; document.order_form.submit();\">".$arMenuLine[$i]."</a>";
					elseif ($arResult["CurrentStep"] == $i + 1)
						echo "<b>".$arMenuLine[$i]."</b>";
					else
						echo $arMenuLine[$i];
				}
				?>
				
			<?endif;?>

		<?
	}
	?>

			<? echo ShowError($arResult["ERROR_MESSAGE"]); 
			if ($arResult["CurrentStep"] == 1)
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/step1.php");
			elseif ($arResult["CurrentStep"] == 2)
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/step2.php");
			elseif ($arResult["CurrentStep"] == 3)
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/step3.php");
			elseif ($arResult["CurrentStep"] == 4)
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/step4.php");
			elseif ($arResult["CurrentStep"] == 5)
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/step5.php");
			elseif ($arResult["CurrentStep"] >= 6)
				include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/step6.php");
			?>



	<?if ($arResult["CurrentStep"] > 0 && $arResult["CurrentStep"] <= 7):?>
		<input type="hidden" name="ORDER_PRICE" value="<?= $arResult["ORDER_PRICE"] ?>">
		<input type="hidden" name="ORDER_WEIGHT" value="<?= $arResult["ORDER_WEIGHT"] ?>">
		<input type="hidden" name="SKIP_FIRST_STEP" value="<?= $arResult["SKIP_FIRST_STEP"] ?>">
		<input type="hidden" name="SKIP_SECOND_STEP" value="<?= $arResult["SKIP_SECOND_STEP"] ?>">
		<input type="hidden" name="SKIP_THIRD_STEP" value="<?= $arResult["SKIP_THIRD_STEP"] ?>">
		<input type="hidden" name="SKIP_FORTH_STEP" value="<?= $arResult["SKIP_FORTH_STEP"] ?>">
	<?endif?>

	<?if ($arResult["CurrentStep"] > 1 && $arResult["CurrentStep"] <= 6):?>
		<input type="hidden" name="PERSON_TYPE" value="<?= $arResult["PERSON_TYPE"] ?>">
		<input type="hidden" name="BACK" value="">
	<?endif?>

	<?if ($arResult["CurrentStep"] > 2 && $arResult["CurrentStep"] <= 6):?>
		<input type="hidden" name="PROFILE_ID" value="<?= $arResult["PROFILE_ID"] ?>">
		<input type="hidden" name="DELIVERY_LOCATION" value="<?= $arResult["DELIVERY_LOCATION"] ?>">
		<?
		$dbOrderProps = CSaleOrderProps::GetList(
				array("SORT" => "ASC"),
				array("PERSON_TYPE_ID" => $arResult["PERSON_TYPE"], "ACTIVE" => "Y", "UTIL" => "N"),
				false,
				false,
				array("ID", "TYPE", "SORT")
			);
		while ($arOrderProps = $dbOrderProps->Fetch())
		{
			if ($arOrderProps["TYPE"] == "MULTISELECT")
			{
				if (count($arResult["POST"]["ORDER_PROP_".$arOrderProps["ID"]]) > 0)
				{
					for ($i = 0; $i < count($arResult["POST"]["ORDER_PROP_".$arOrderProps["ID"]]); $i++)
					{
						?><input type="hidden" name="ORDER_PROP_<?= $arOrderProps["ID"] ?>[]" value="<?= $arResult["POST"]["ORDER_PROP_".$arOrderProps["ID"]][$i] ?>"><?
					}
				}
				else
				{
					?><input type="hidden" name="ORDER_PROP_<?= $arOrderProps["ID"] ?>[]" value=""><?
				}
			}
			else
			{
				?><input type="hidden" name="ORDER_PROP_<?= $arOrderProps["ID"] ?>" value="<?= $arResult["POST"]["ORDER_PROP_".$arOrderProps["ID"]] ?>"><?
			}
		}
		?>
	<?endif?>

	<?if ($arResult["CurrentStep"] > 3 && $arResult["CurrentStep"] < 6):?>
		<input type="hidden" name="DELIVERY_ID" value="<?= is_array($arResult["DELIVERY_ID"]) ? implode(":", $arResult["DELIVERY_ID"]) : IntVal($arResult["DELIVERY_ID"]) ?>">
	<?endif?>

	<?if ($arResult["CurrentStep"] > 4 && $arResult["CurrentStep"] < 6):?>
		<input type="hidden" name="TAX_EXEMPT" value="<?= $arResult["TAX_EXEMPT"] ?>">
		<input type="hidden" name="PAY_SYSTEM_ID" value="<?= $arResult["PAY_SYSTEM_ID"] ?>">
		<input type="hidden" name="PAY_CURRENT_ACCOUNT" value="<?= $arResult["PAY_CURRENT_ACCOUNT"] ?>">
	<?endif?>
	<?if ($arResult["CurrentStep"] < 6):?>
		<input type="hidden" name="CurrentStep" value="<?= ($arResult["CurrentStep"] + 1) ?>">
	<?endif?>

	<?if ($arResult["CurrentStep"] < 6):?>
		</form>
	<?endif;?>
<?
}
?>
