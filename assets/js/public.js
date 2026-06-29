/**
 * Gold Certificate Verifier - Public JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
	// Gallery image click handler
	const galleryThumbs = document.querySelectorAll('.gcv-gallery-thumb');
	const mainImage = document.querySelector('.gcv-product-image');

	if (galleryThumbs.length > 0 && mainImage) {
		galleryThumbs.forEach(thumb => {
			thumb.addEventListener('click', function() {
				const fullImageUrl = this.getAttribute('data-full');
				mainImage.src = fullImageUrl;
				mainImage.alt = this.alt;

				// Update active state
				galleryThumbs.forEach(t => t.style.opacity = '0.6');
				this.style.opacity = '1';
			});
		});

		// Set first thumbnail as active
		if (galleryThumbs.length > 0) {
			galleryThumbs[0].style.opacity = '1';
		}
	}

	// Main image zoom handler
	if (mainImage) {
		mainImage.addEventListener('click', function() {
			openImageModal(this.src, this.alt);
		});
	}

	// PDF download button
	const pdfBtn = document.getElementById('gcv-pdf-btn');
	if (pdfBtn) {
		pdfBtn.addEventListener('click', function() {
			downloadPDF();
		});
	}

	// Print button
	const printBtn = document.querySelector('.gcv-btn-print');
	if (printBtn) {
		printBtn.addEventListener('click', function() {
			window.print();
		});
	}
});

/**
 * Open image modal for zoomed view
 */
function openImageModal(imageSrc, imageAlt) {
	// Create modal
	const modal = document.createElement('div');
	modal.className = 'gcv-image-modal';
	modal.innerHTML = `
		<div class="gcv-modal-content">
			<span class="gcv-modal-close">&times;</span>
			<img src="${imageSrc}" alt="${imageAlt}" class="gcv-modal-image">
		</div>
	`;

	// Add styles if not already present
	if (!document.getElementById('gcv-modal-styles')) {
		const style = document.createElement('style');
		style.id = 'gcv-modal-styles';
		style.textContent = `
			.gcv-image-modal {
				display: flex;
				position: fixed;
				z-index: 9999;
				left: 0;
				top: 0;
				width: 100%;
				height: 100%;
				background-color: rgba(0, 0, 0, 0.9);
				align-items: center;
				justify-content: center;
			}

			.gcv-modal-content {
				position: relative;
				max-width: 90%;
				max-height: 90%;
			}

			.gcv-modal-image {
				max-width: 100%;
				max-height: 90vh;
				object-fit: contain;
			}

			.gcv-modal-close {
				position: absolute;
				top: 20px;
				right: 30px;
				color: #f1f1f1;
				font-size: 40px;
				font-weight: bold;
				cursor: pointer;
				transition: color 0.3s ease;
			}

			.gcv-modal-close:hover {
				color: #d4af37;
			}
		`;
		document.head.appendChild(style);
	}

	document.body.appendChild(modal);

	// Close modal on close button click
	const closeBtn = modal.querySelector('.gcv-modal-close');
	closeBtn.addEventListener('click', function() {
		modal.remove();
	});

	// Close modal on background click
	modal.addEventListener('click', function(event) {
		if (event.target === modal) {
			modal.remove();
		}
	});

	// Close modal on Escape key
	document.addEventListener('keydown', function(event) {
		if (event.key === 'Escape' && modal.parentElement) {
			modal.remove();
		}
	});
}

/**
 * Download certificate as PDF
 */
function downloadPDF() {
	// Get certificate information
	const title = document.querySelector('.gcv-product-title')?.textContent || 'Certificate';
	const certificateNo = document.querySelector('.gcv-details-table tr:first-child .gcv-value')?.textContent || 'UNKNOWN';

	// Create a simple PDF using window.print
	// For a more advanced solution, consider using a library like jsPDF or html2pdf
	alert('PDF download feature coming soon. Use the Print button to save as PDF.');
}

/**
 * Copy certificate URL to clipboard
 */
function copyCertificateUrl() {
	const url = window.location.href;
	navigator.clipboard.writeText(url).then(function() {
		alert('Certificate URL copied to clipboard!');
	}).catch(function() {
		alert('Failed to copy URL');
	});
}

/**
 * Share certificate
 */
function shareCertificate(platform) {
	const url = window.location.href;
	const title = document.querySelector('.gcv-product-title')?.textContent || 'Gold Certificate';
	let shareUrl = '';

	switch (platform) {
		case 'facebook':
			shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
			break;
		case 'twitter':
			shareUrl = `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`;
			break;
		case 'linkedin':
			shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
			break;
		case 'whatsapp':
			shareUrl = `https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`;
			break;
		case 'email':
			shareUrl = `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(url)}`;
			break;
		default:
			return;
	}

	window.open(shareUrl, '_blank', 'width=600,height=400');
}
