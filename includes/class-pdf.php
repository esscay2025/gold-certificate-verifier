<?php
/**
 * PDF Export Class - Handles PDF generation for certificates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GCV_PDF {

	/**
	 * Generate PDF certificate
	 */
	public static function generate_pdf( $certificate_no ) {
		// Get certificate data
		$certificate = GCV_DB::get_certificate( $certificate_no );

		if ( is_wp_error( $certificate ) ) {
			return $certificate;
		}

		// Check if TCPDF or similar library is available
		// For now, we'll provide a method that uses WordPress hooks
		// In production, you might want to use a library like TCPDF or mPDF

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title><?php echo esc_html( $certificate->certificate_no ); ?></title>
			<style>
				* {
					margin: 0;
					padding: 0;
					box-sizing: border-box;
				}

				body {
					font-family: Arial, sans-serif;
					background-color: white;
					color: #333;
					line-height: 1.6;
				}

				.pdf-container {
					max-width: 800px;
					margin: 0 auto;
					padding: 40px;
					background-color: white;
				}

				.pdf-header {
					text-align: center;
					border-bottom: 3px solid #d4af37;
					padding-bottom: 20px;
					margin-bottom: 30px;
				}

				.pdf-header h1 {
					font-size: 28px;
					color: #2c2c2c;
					margin-bottom: 5px;
				}

				.pdf-header p {
					font-size: 14px;
					color: #d4af37;
				}

				.pdf-title {
					text-align: center;
					font-size: 22px;
					color: #2c2c2c;
					margin: 30px 0;
					font-weight: bold;
				}

				.pdf-content {
					display: table;
					width: 100%;
					margin: 30px 0;
				}

				.pdf-left {
					display: table-cell;
					width: 40%;
					vertical-align: top;
					padding-right: 20px;
				}

				.pdf-right {
					display: table-cell;
					width: 60%;
					vertical-align: top;
				}

				.pdf-image {
					border: 2px dashed #d4af37;
					padding: 10px;
					text-align: center;
					background-color: #f5f5f5;
					min-height: 300px;
					display: flex;
					align-items: center;
					justify-content: center;
				}

				.pdf-image img {
					max-width: 100%;
					max-height: 300px;
					object-fit: contain;
				}

				.pdf-details-table {
					width: 100%;
					border-collapse: collapse;
				}

				.pdf-details-table tr {
					border-bottom: 1px solid #ddd;
				}

				.pdf-details-table tr:nth-child(even) {
					background-color: rgba(212, 175, 55, 0.05);
				}

				.pdf-details-table td {
					padding: 12px;
					vertical-align: middle;
				}

				.pdf-label {
					font-weight: bold;
					width: 40%;
					background-color: rgba(212, 175, 55, 0.1);
					color: #2c2c2c;
				}

				.pdf-value {
					color: #333;
				}

				.pdf-qr-section {
					text-align: center;
					margin: 30px 0;
					padding: 20px;
					background-color: #f5f5f5;
					border-radius: 5px;
				}

				.pdf-qr-section p {
					font-size: 12px;
					color: #666;
					margin-top: 10px;
				}

				.pdf-footer {
					text-align: center;
					margin-top: 40px;
					padding-top: 20px;
					border-top: 1px solid #ddd;
					font-size: 12px;
					color: #666;
				}

				.pdf-badge {
					display: inline-block;
					background-color: #d4af37;
					color: #2c2c2c;
					padding: 8px 16px;
					border-radius: 20px;
					font-weight: bold;
					font-size: 12px;
					margin-bottom: 20px;
				}

				@page {
					size: A4;
					margin: 20mm;
				}

				@media print {
					body {
						background-color: white;
					}

					.pdf-container {
						box-shadow: none;
					}
				}
			</style>
		</head>
		<body>
			<div class="pdf-container">
				<!-- Header -->
				<div class="pdf-header">
					<h1><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
					<p><?php esc_html_e( 'Product Certification', 'gold-cert-verifier' ); ?></p>
				</div>

				<!-- Verification Badge -->
				<div style="text-align: center;">
					<span class="pdf-badge">✓ <?php esc_html_e( 'Verified Certificate', 'gold-cert-verifier' ); ?></span>
				</div>

				<!-- Title -->
				<div class="pdf-title">
					<?php echo esc_html( $certificate->fineness ) . ' - ' . esc_html( $certificate->product_name ); ?>
				</div>

				<!-- Content -->
				<div class="pdf-content">
					<!-- Left: Image -->
					<div class="pdf-left">
						<div class="pdf-image">
							<?php if ( ! empty( $certificate->image_url ) ) : ?>
								<img src="<?php echo esc_url( $certificate->image_url ); ?>" 
									 alt="<?php echo esc_attr( $certificate->product_name ); ?>">
							<?php else : ?>
								<p><?php esc_html_e( 'No image available', 'gold-cert-verifier' ); ?></p>
							<?php endif; ?>
						</div>
					</div>

					<!-- Right: Details -->
					<div class="pdf-right">
						<table class="pdf-details-table">
							<tr>
								<td class="pdf-label"><?php esc_html_e( 'Certificate No.', 'gold-cert-verifier' ); ?></td>
								<td class="pdf-value"><strong><?php echo esc_html( $certificate->certificate_no ); ?></strong></td>
							</tr>
							<tr>
								<td class="pdf-label"><?php esc_html_e( 'Item Code', 'gold-cert-verifier' ); ?></td>
								<td class="pdf-value"><?php echo esc_html( $certificate->item_code ); ?></td>
							</tr>
							<tr>
								<td class="pdf-label"><?php esc_html_e( 'Metal', 'gold-cert-verifier' ); ?></td>
								<td class="pdf-value"><?php echo esc_html( $certificate->metal ); ?></td>
							</tr>
							<tr>
								<td class="pdf-label"><?php esc_html_e( 'Weight (Gram)', 'gold-cert-verifier' ); ?></td>
								<td class="pdf-value"><?php echo esc_html( $certificate->weight ); ?> g</td>
							</tr>
							<tr>
								<td class="pdf-label"><?php esc_html_e( 'Fineness', 'gold-cert-verifier' ); ?></td>
								<td class="pdf-value"><?php echo esc_html( $certificate->fineness ); ?></td>
							</tr>
							<tr>
								<td class="pdf-label"><?php esc_html_e( 'Date', 'gold-cert-verifier' ); ?></td>
								<td class="pdf-value"><?php echo esc_html( wp_date( 'j F, Y', strtotime( $certificate->cert_date ) ) ); ?></td>
							</tr>
							<tr>
								<td class="pdf-label"><?php esc_html_e( 'Cast Bar Origin', 'gold-cert-verifier' ); ?></td>
								<td class="pdf-value"><?php echo esc_html( $certificate->origin ); ?></td>
							</tr>
							<tr>
								<td class="pdf-label"><?php esc_html_e( 'Certified Assayer', 'gold-cert-verifier' ); ?></td>
								<td class="pdf-value"><?php echo esc_html( $certificate->assayer ); ?></td>
							</tr>
						</table>
					</div>
				</div>

				<!-- QR Code Section -->
				<div class="pdf-qr-section">
					<p><?php esc_html_e( 'Scan QR code to verify this certificate', 'gold-cert-verifier' ); ?></p>
					<p><?php echo esc_html( $certificate->certificate_no ); ?></p>
				</div>

				<!-- Footer -->
				<div class="pdf-footer">
					<p><?php echo esc_html( get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' ) ); ?></p>
					<p><?php esc_html_e( 'Certificate Verification System', 'gold-cert-verifier' ); ?> &copy; <?php echo esc_html( date( 'Y' ) ); ?></p>
					<p><?php echo esc_html( home_url( '/cert/' . $certificate->certificate_no ) ); ?></p>
				</div>
			</div>
		</body>
		</html>
		<?php

		$html = ob_get_clean();

		// Try to use mPDF if available
		if ( class_exists( 'Mpdf\Mpdf' ) ) {
			try {
				$mpdf = new \Mpdf\Mpdf();
				$mpdf->WriteHTML( $html );
				return $mpdf->Output( $certificate->certificate_no . '.pdf', 'S' );
			} catch ( Exception $e ) {
				return new WP_Error( 'pdf_error', $e->getMessage() );
			}
		}

		// Fallback: Return HTML for browser printing
		return $html;
	}

	/**
	 * Download certificate as PDF
	 */
	public static function download_pdf( $certificate_no ) {
		$pdf = self::generate_pdf( $certificate_no );

		if ( is_wp_error( $pdf ) ) {
			wp_die( 'Error: ' . esc_html( $pdf->get_error_message() ) );
		}

		// Set headers for PDF download
		header( 'Content-Type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $certificate_no ) . '.pdf"' );
		header( 'Content-Length: ' . strlen( $pdf ) );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo $pdf;
		exit;
	}
}
