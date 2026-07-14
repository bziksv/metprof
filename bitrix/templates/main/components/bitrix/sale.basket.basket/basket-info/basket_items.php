<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($normalCount > 0)
{?>

	<div class="basket-info">
		<div class="list-info">

			<ul>
				<li>
					<ol>
						<li>
							<div>Сумма:</div>
							<div></div>
							<div><span class="info-weight"><?=str_replace('руб.','',$arResult['PRICE_WITHOUT_DISCOUNT'])?></span></div>
						</li>
						<li>
							<div>Товаров:</div>
							<div></div>
							<div><span class="info-weight"><?=count($arResult["GRID"]["ROWS"]);?></span> шт.</div>
						</li>
						<?
						$arProcent = array();
						foreach($arResult["ITEMS"]["AnDelCanBuy"] as $v){$arProcent[] = $v['DISCOUNT_PRICE_PERCENT'];}
						?>
						<?if(count(array_unique($arProcent)) < 2):?>
						<li>
							<div>Скидка:</div>
							<div></div>
							<div><?=$arResult["ITEMS"]["AnDelCanBuy"][0]['DISCOUNT_PRICE_PERCENT']?> %</div>
						</li>
						<? endif; ?>
					</ol>
				</li>
			</ul>

			<ul>
				<li>
					<ol>
						<li>
							<div><span class="info-weight">Итого:</span></div>
							<div></div>
							<div><span class="info-weight itog"><?=$arResult["allSum"]?></span> &#8381;</div>
						</li>
					</ol>
				</li>
			</ul>

			<a href="/personal/cart/" class="control prev">Редактировать заказ</a>

			<div style="margin-top: 15px; font-size: 12px; line-height: 1.4; color: #666;">
				Нажимая на эту кнопку, я даю свое согласие на обработку персональных данных и соглашаюсь с условиями <a href="/upload/politics.pdf" target="_blank" style="text-decoration: underline;">политики обработки персональных данных</a>.
			</div>

		</div>
	</div>

<?}?>