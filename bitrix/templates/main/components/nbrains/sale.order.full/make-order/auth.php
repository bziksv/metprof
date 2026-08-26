<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>



<?
$cntBasketItems = CSaleBasket::GetList(
	array(),
	array(
		"FUSER_ID" => CSaleBasket::GetBasketUserID(),
		"LID" => SITE_ID,
		"ORDER_ID" => "NULL"
	),
	array()
);
if($cntBasketItems > 0):
?>

<div class="or__stages cl">
	<div class="stage s1 active"><span>1</span><div class="text">Контактная <br>информация</div></div>
	<div class="stage s2"><span>2</span><div class="text">Cпособ <br>получения</div></div>
	<div class="stage s3"><span>3</span><div class="text">Способ <br>оплаты</div></div>
	<div class="stage s4"><span>4</span><div class="text">Подтверждение <br>заказа</div></div>
</div>

<? endif; ?>

<div class="or__content cl s1">
	<div class="column">
		<div class="title">Личный кабинет</div>
		<div class="form_login">

			<form method="post" action="<?= $arParams["PATH_TO_ORDER"] ?>" name="order_auth_form" novalidate>
				<?=bitrix_sessid_post()?>
				<?
				$authInvalid = array_fill_keys((array)($arResult['AUTH_INVALID_FIELDS'] ?? []), true);
				$authInv = static function (string $name) use ($authInvalid): string {
					return !empty($authInvalid[$name]) ? ' is-invalid' : '';
				};
				?>

				<div class="line"><span>E-mail</span>
					<input type="text" name="USER_LOGIN" maxlength="255" size="30" value="<?=$arResult["USER_LOGIN"]?>" required class="<?=trim($authInv('USER_LOGIN'))?>" autocomplete="username">
				</div>

				<div class="line"><span>Пароль</span>
					<input type="password" name="USER_PASSWORD" maxlength="255" size="30" required class="<?=trim($authInv('USER_PASSWORD'))?>" autocomplete="current-password">
				</div>

				<input type="submit" class="login_enter" value="Войти">
				<input type="hidden" name="do_authorize" value="Y">

				<a href="/personal/info.php?forgot_password=yes" class="remind_pass">Напомнить пароль</a>

<!--				<div class="login_social cl">-->
<!--					<span>Войти через социальные сети</span>-->
<!--					<a href="#" class="go"></a>-->
<!--					<a href="#" class="tw"></a>-->
<!--					<a href="#" class="vk"></a>-->
<!--					<a href="#" class="rs"></a>-->
<!--					<a href="#" class="fb"></a>-->
<!--					<a href="#" class="ok"></a>-->
<!--				</div>-->

			</form>

		</div>
	</div>




	<div class="column">
		<div class="title">Регистрация на сайте</div>
		<a href="#" class="back2enter">Авторизация</a>

		<? if($arResult["AUTH"]["new_user_registration"]=="Y"):?>
		<form method="post" action="<?= $arParams["PATH_TO_ORDER"]?>" name="order_reg_form" novalidate>
			<?=bitrix_sessid_post()?>

		<div class="form_registration">
			<div class="face_type">
				<?
				foreach($arResult["PERSON_TYPE_INFO"] as $v)
				{
					?>
					<label>
						<input type="radio" id="PERSON_TYPE_<?= $v["ID"] ?>" name="PERSON_TYPE" value="<?= $v["ID"] ?>" <?if ($v["CHECKED"]=="Y") echo " checked";?> >
						<span><?= $v["NAME"] ?></span>
					</label>
					<?
				}
				?>
			</div>
			<?
			$regInvalid = array_fill_keys((array)($arResult['REG_INVALID_FIELDS'] ?? []), true);
			$inv = static function (string $name) use ($regInvalid): string {
				return !empty($regInvalid[$name]) ? ' is-invalid' : '';
			};
			?>
			<div class="line"><span><?echo GetMessage("STOF_LASTNAME")?></span>
				<input type="text" name="NEW_LAST_NAME" placeholder="" size="40" value="<?=$arResult["POST"]["NEW_LAST_NAME"]?>" required class="<?=trim($inv('NEW_LAST_NAME'))?>">
			</div>
			<div class="line"><span><?echo GetMessage("STOF_NAME")?></span>
				<input type="text" name="NEW_NAME" size="40" value="<?=$arResult["POST"]["NEW_NAME"]?>" required class="<?=trim($inv('NEW_NAME'))?>">
			</div>
			<div class="line"><span>E-mail</span>
				<input type="text" name="NEW_EMAIL" size="40" value="<?=$arResult["POST"]["NEW_EMAIL"]?>" required class="<?=trim($inv('NEW_EMAIL'))?>">
			</div>
			<div class="line"><span>Телефон</span>
				<input type="tel" class="phone ru_phone_check phone_check<?=$inv('USER_PERSONAL_PHONE')?>" name="USER_PERSONAL_PHONE" maxlength="255" placeholder="+7-___-___-__-__" autocomplete="tel" inputmode="tel" value="<?=htmlspecialcharsbx($arResult["POST"]["USER_PERSONAL_PHONE"] ?? '')?>" required data-prime-required="1">
			</div>

<!--			<div class="line"><span>--><?//echo GetMessage("STOF_LOGIN")?><!--</span>-->
<!--				<input type="text" name="NEW_LOGIN" size="30" value="--><?//=$arResult["POST"]["NEW_LOGIN"]?><!--">-->
<!--			</div>-->
			<div class="line"><span><?echo GetMessage("STOF_PASSWORD")?></span>
				<input type="password" class="pass<?=$inv('NEW_PASSWORD')?>" name="NEW_PASSWORD" size="30" required>
			</div>
			<div class="line pass_rep">
				<span><?echo GetMessage("STOF_RE_PASSWORD")?></span>
				<input type="password" class="pass<?=$inv('NEW_PASSWORD_CONFIRM')?>" name="NEW_PASSWORD_CONFIRM" size="30" required>
				<span class="req">Пароль должен содержать не менее 6 символов ,  кроме спец. символов и кириллицы</span>
			</div>
			<?
			if($arResult["AUTH"]["captcha_registration"] == "Y") //CAPTCHA
			{
				?>
				<tr>
					<td><br /><b><?=GetMessage("CAPTCHA_REGF_TITLE")?></b></td>
				</tr>
				<tr>
					<td>
						<input type="hidden" name="captcha_sid" value="<?=$arResult["AUTH"]["capCode"]?>">
						<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["AUTH"]["capCode"]?>" width="180" height="40" alt="CAPTCHA">
					</td>
				</tr>
				<tr valign="middle">
					<td>
						<span class="sof-req">*</span><?=GetMessage("CAPTCHA_REGF_PROMT")?>:<br />
						<input type="text" name="captcha_word" size="30" maxlength="50" value="">
					</td>
				</tr>
				<?
			}
			?>

			<div class="agent">
				<label>
					<input type="checkbox" name="WORK_POSITION" value="представитель юридического лица или ИП">
					<span>Я &mdash; представитель юридического лица или ИП</span>
				</label>
			</div>

			<div class="form_registration__actions">
				<div class="agent agent--consent">
					<label>
						<input type="checkbox" name="rule" id="rule" value="Y" required>
						<span><?php require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/legal/legal_helpers.php'; echo metprofLegalFormConsentLabel(); ?></span>
					</label>
					<div class="agent__error" data-role="rule-error" hidden>Отметьте согласие на обработку персональных данных.</div>
				</div>

				<input type="submit" class="registrate" value="<?echo GetMessage("STOF_NEXT_STEP")?>">
			</div>
			<input type="hidden" name="do_register" value="Y">

		</div>

		</form>
		<?endif;?>


	</div>
</div>

<script>
	$(function(){

		(function () {
			var authForm = document.forms.order_auth_form;
			if (!authForm) return;

			function mark(inp, on) {
				if (!inp) return;
				inp.classList.toggle('is-invalid', !!on);
			}

			function validateAuth() {
				authForm.querySelectorAll('.is-invalid').forEach(function (el) {
					el.classList.remove('is-invalid');
				});
				var first = null;
				var login = authForm.USER_LOGIN;
				var password = authForm.USER_PASSWORD;
				if (login && !String(login.value || '').trim()) {
					mark(login, true);
					first = login;
				}
				if (password && !String(password.value || '').length) {
					mark(password, true);
					if (!first) first = password;
				}
				if (first) {
					try { first.focus(); } catch (e) {}
					return false;
				}
				return true;
			}

			authForm.addEventListener('submit', function (e) {
				if (!validateAuth()) {
					e.preventDefault();
					e.stopPropagation();
				}
			}, true);

			authForm.addEventListener('input', function (e) {
				if (e.target && e.target.tagName === 'INPUT') {
					e.target.classList.remove('is-invalid');
				}
			}, true);
		})();

		(function () {
			var form = document.forms.order_reg_form;
			if (!form) return;
			var rule = form.querySelector('#rule');
			var ruleBox = form.querySelector('.agent--consent');
			var ruleError = form.querySelector('[data-role="rule-error"]');

			function phoneDigits(value) {
				var digits = String(value || '').replace(/\D/g, '');
				if (digits.length === 11 && (digits.charAt(0) === '7' || digits.charAt(0) === '8')) {
					digits = digits.slice(1);
				}
				return digits;
			}

			function isValidRuPhone(value) {
				var digits = phoneDigits(value);
				return digits.length === 10 && /^[3-9]\d{9}$/.test(digits);
			}

			function mark(inp, on) {
				if (!inp) return;
				inp.classList.toggle('is-invalid', !!on);
			}

			function markRule(on) {
				if (ruleBox) ruleBox.classList.toggle('is-invalid', !!on);
				if (ruleError) ruleError.hidden = !on;
			}

			function clearAll() {
				form.querySelectorAll('.is-invalid').forEach(function (el) {
					el.classList.remove('is-invalid');
				});
				markRule(false);
			}

			function validate() {
				clearAll();
				var first = null;
				var last = form.NEW_LAST_NAME;
				var name = form.NEW_NAME;
				var email = form.NEW_EMAIL;
				var phone = form.USER_PERSONAL_PHONE;
				var password = form.NEW_PASSWORD;
				var confirm = form.NEW_PASSWORD_CONFIRM;

				if (last && !String(last.value || '').trim()) {
					mark(last, true);
					if (!first) first = last;
				}
				if (name && !String(name.value || '').trim()) {
					mark(name, true);
					if (!first) first = name;
				}

				var emailVal = email ? String(email.value || '').trim() : '';
				if (email) email.value = emailVal;
				var emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i.test(emailVal);
				var policyOk = true;
				if (typeof window.primeAlertsIsEmailAllowed === 'function') {
					policyOk = window.primeAlertsIsEmailAllowed(emailVal);
				}
				if (!emailOk || (emailOk && !policyOk)) {
					mark(email, true);
					if (!first) first = email;
					if (window.primeAlertsCheckRegistrationEmail) {
						window.primeAlertsCheckRegistrationEmail(email);
					}
				}

				if (phone && !isValidRuPhone(phone.value)) {
					mark(phone, true);
					if (!first) first = phone;
				}

				var pw = password ? String(password.value || '') : '';
				var conf = confirm ? String(confirm.value || '') : '';
				if (!pw.length) {
					mark(password, true);
					if (!first) first = password;
				}
				if (!conf.length || conf !== pw) {
					mark(confirm, true);
					if (!pw.length) mark(password, true);
					if (!first) first = !pw.length ? password : confirm;
				}

			if (rule && !rule.checked) {
				markRule(true);
				if (!first) first = ruleBox || rule;
			}

			if (first) {
				try {
					if (first.focus) first.focus();
					else if (first.querySelector) {
						var cb = first.querySelector('input[type="checkbox"]');
						if (cb) cb.focus();
					}
				} catch (e) {}
				if (first === ruleBox || first === rule) {
					try { ruleBox.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e2) {}
				}
				return false;
			}
				return true;
			}

			form.addEventListener('submit', function (e) {
				if (!validate()) {
					e.preventDefault();
					e.stopPropagation();
				}
			}, true);

			form.addEventListener('input', function (e) {
				if (e.target && e.target.tagName === 'INPUT') {
					e.target.classList.remove('is-invalid');
				}
			}, true);

			if (rule) {
				rule.addEventListener('change', function () {
					if (rule.checked) markRule(false);
				});
			}

			if (window.jQuery && form.USER_PERSONAL_PHONE) {
				try {
					jQuery(form.USER_PERSONAL_PHONE).mask('+7-999-999-99-99', { placeholder: '_', autoclear: false });
				} catch (e) {}
			}
		})();
	});
</script>

<br />
<br />


<?if($arResult["AUTH"]["new_user_registration"]=="Y"):?>
	<?echo GetMessage("STOF_EMAIL_NOTE")?><br /><br />
<?endif;?>
<?echo GetMessage("STOF_PRIVATE_NOTES")?>
