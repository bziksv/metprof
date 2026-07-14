<?php
/* Smarty version 4.3.1, created on 2026-01-27 19:56:13
  from '/var/www/fastuser/data/www/metprof-vrn.ru/bitrix/modules/thebrainstech.copyiblock/templates/dialog.tpl' */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '4.3.1',
  'unifunc' => 'content_6978ee2dc58da3_29576762',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'fb8a776ebe731a38504cf3616f1a0fd0f7c39068' => 
    array (
      0 => '/var/www/fastuser/data/www/metprof-vrn.ru/bitrix/modules/thebrainstech.copyiblock/templates/dialog.tpl',
      1 => 1768554423,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:content.tpl' => 1,
  ),
),false)) {
function content_6978ee2dc58da3_29576762 (Smarty_Internal_Template $_smarty_tpl) {
ob_start();
$_smarty_tpl->_subTemplateRender('file:content.tpl', $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
$_smarty_tpl->assign('content', ob_get_clean());
?>
javascript:(
    new BX.CDialog({
    content_url: "<?php echo $_smarty_tpl->tpl_vars['params']->value;?>
",
    width: 500,
    head: "",
    height: 260,
    resizable: false,
    draggable: true,
    content: "<?php echo strtr((string)$_smarty_tpl->tpl_vars['content']->value, array("\\" => "\\\\", "'" => "\\'", "\"" => "\\\"", "\r" => "\\r", 
                       "\n" => "\\n", "</" => "<\/", "<!--" => "<\!--", "<s" => "<\s", "<S" => "<\S",
                       "`" => "\\`", "\${" => "\\\$\{"));?>
",
    buttons: [BX.CDialog.btnSave, BX.CDialog.btnCancel]})
).Show();
<?php }
}
