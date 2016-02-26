$('#kinchGender').on('change', function() {
	if ($('#kinchRegionId').size() == 0) {
		$('#kinchRegionForm').submit();
	}
});
$('[data-toggle=tooltip]').tooltip();