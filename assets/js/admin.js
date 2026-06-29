/**
 * Gold Certificate Verifier - Admin JavaScript
 */

jQuery(document).ready(function ($) {

	// -----------------------------------------------------------------------
	// Media Uploader for Product Image
	// -----------------------------------------------------------------------
	var mediaUploader;

	$('#upload_image_btn').on('click', function (e) {
		e.preventDefault();

		if (mediaUploader) {
			mediaUploader.open();
			return;
		}

		mediaUploader = wp.media({
			title: 'Choose Product Image',
			button: { text: 'Use This Image' },
			multiple: false
		});

		mediaUploader.on('select', function () {
			var attachment = mediaUploader.state().get('selection').first().toJSON();

			// Set hidden field value (submitted with the form)
			$('#image_url').val(attachment.url);

			// Show the URL in the read-only display field
			$('#image_url_display').val(attachment.url);

			// Show preview
			$('#image_preview').html(
				'<img src="' + attachment.url + '" alt="Preview" style="max-width:200px;max-height:200px;border:2px solid #d4af37;border-radius:4px;margin-top:8px;">'
			);
		});

		mediaUploader.open();
	});

	// -----------------------------------------------------------------------
	// Delete confirmation (for inline delete links)
	// -----------------------------------------------------------------------
	$(document).on('click', 'a[href*="gcv_delete_certificate"]', function (e) {
		if (!confirm('Are you sure you want to delete this certificate? This cannot be undone.')) {
			e.preventDefault();
		}
	});

	// -----------------------------------------------------------------------
	// Live search filter on the list table (client-side, instant)
	// -----------------------------------------------------------------------
	$('#certificate-search').on('keyup', function () {
		var term = $(this).val().toLowerCase();
		$('.widefat tbody tr').each(function () {
			$(this).toggle($(this).text().toLowerCase().indexOf(term) > -1);
		});
	});

});
