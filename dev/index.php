<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("dev");
?>

<br />
<br />
<br />

<div style="margin:0 auto">

<?
$APPLICATION->IncludeComponent("prime:ads.btn", "", [
	"SHOW" => "Y",
	"DESCRIPTION" => "ООО «КОРПОРАЦИЯ МЕТАЛЛИНВЕСТ» <br /> erid:2Wfslghjslgk"
]); 
?>

</div>

<br />
<br />
<br />

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>