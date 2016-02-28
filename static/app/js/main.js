if ($('#kinchRegionId').size() == 0) {
	$('#kinchGender').on('change', function() {
		$('#kinchRegionForm').submit();
	});
}
$('[data-toggle=tooltip]').tooltip();
$('#selectLang').on('change', function() {
	$('#formLang').submit();
});