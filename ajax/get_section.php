<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(CModule::IncludeModule("iblock")){
    $res = CIBlockSection::GetByID($_REQUEST['id']);
    if($ar_res = $res->GetNext())
        echo json_encode(array_merge($ar_res, ['status' => true]));
    else
        echo json_encode(['status' => false]);
}
?>
