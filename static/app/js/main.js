$('#kinchGender').on('change', function() {
	if ($('#kinchRegionId').size() == 0) {
		$('#kinchRegionForm').submit();
	}
})