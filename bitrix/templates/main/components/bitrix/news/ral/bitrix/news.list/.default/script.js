

$(document).on("click", ".show_brand", function () {
	$(this).parents('.brand-list').children('.brand-item').removeClass('brand-hidden');
	$(this).remove();
	return false;
});