if ($('#kinchRegionId').size() == 0) {
	$('#kinchGender').on('change', function() {
		$('#kinchRegionForm').submit();
	});
}
$('[data-toggle=tooltip]').tooltip();
$('#selectLang').on('change', function() {
	$('#formLang').submit();
});
$(window).on('hashchange', function() {
	var countryId = window.location.hash.substr(1);
	var countryHash = $('[name="' + countryId + '"]');
	$('tr').removeClass('warning');
	$('html, body').animate({
		scrollTop: countryHash.offset().top
	}, 200);
	countryHash.parents('tr').addClass('warning');
}).trigger('hashchange');