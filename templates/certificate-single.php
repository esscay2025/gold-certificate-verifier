<?php
/**
 * Certificate Single Page Template
 *
 * Displays individual gold bar certificates with professional styling.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get certificate data from query variable
$cert_code = get_query_var( 'gcv_cert' );
$q_param   = get_query_var( 'q' );

if ( empty( $cert_code ) && ! empty( $q_param ) ) {
	$cert_code = $q_param;
}

$certificate = GCV_DB::get_certificate( $cert_code );

if ( is_wp_error( $certificate ) ) {
	status_header( 404 );
	get_template_part( '404' );
	exit;
}

// -----------------------------------------------------------------------
// Helper: format a numeric value — strip trailing zeros after decimal
// e.g. 100.000 → "100"   |   999.900000 → "999.9"   |   50.500 → "50.5"
// -----------------------------------------------------------------------
function gcv_format_number( $value ) {
	$float = (float) $value;
	// rtrim removes trailing zeros; rtrim the dot if nothing left after it
	return rtrim( rtrim( number_format( $float, 4, '.', '' ), '0' ), '.' );
}

$company_phone   = get_option( 'gcv_company_phone', '' );
$company_email   = get_option( 'gcv_company_email', '' );
$company_website = get_option( 'gcv_company_website', '' );
$logo_url        = GCV_PLUGIN_URL . 'assets/images/logo.webp';
$brand_color     = '#211C35';
$gold_color      = '#D4AF37';
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Gold Bar Certificate Verification - <?php echo esc_attr( $certificate->product_name ); ?>">
	<meta name="robots" content="noindex, follow">
	<title><?php echo esc_html( $certificate->product_name ) . ' — Certificate ' . esc_html( $certificate->certificate_no ); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo esc_url( GCV_PLUGIN_URL . 'assets/css/public.css' ); ?>?v=<?php echo GCV_VERSION; ?>">
	<?php wp_head(); ?>
	<style>
		/* ================================================================
		   RESET & BASE
		================================================================ */
		*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

		body.gcv-certificate-page {
			font-family: 'Inter', sans-serif;
			background: #f4f2ee;
			color: #1a1a1a;
			min-height: 100vh;
		}

		/* ================================================================
		   HEADER
		================================================================ */
		.gcv-header {
			background: <?php echo $brand_color; ?>;
			padding: 0;
			position: relative;
			overflow: hidden;
		}

		/* Subtle diagonal gold line accent */
		.gcv-header::before {
			content: '';
			position: absolute;
			top: 0; left: 0; right: 0; bottom: 0;
			background: linear-gradient(135deg, rgba(212,175,55,0.08) 0%, transparent 60%);
			pointer-events: none;
		}

		.gcv-header-inner {
			max-width: 960px;
			margin: 0 auto;
			padding: 28px 40px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 24px;
			position: relative;
			z-index: 1;
		}

		.gcv-header-logo img {
			height: 56px;
			width: auto;
			display: block;
			filter: brightness(1.05);
		}

		.gcv-header-brand {
			text-align: right;
		}

		.gcv-header-brand .gcv-tagline {
			font-family: 'Cormorant Garamond', serif;
			font-size: 1.05rem;
			font-weight: 400;
			color: rgba(255,255,255,0.65);
			letter-spacing: 0.12em;
			text-transform: uppercase;
		}

		/* Gold divider line below header */
		.gcv-header-divider {
			height: 3px;
			background: linear-gradient(90deg, transparent 0%, <?php echo $gold_color; ?> 20%, <?php echo $gold_color; ?> 80%, transparent 100%);
		}

		/* ================================================================
		   MAIN CONTENT WRAPPER
		================================================================ */
		.gcv-body {
			max-width: 960px;
			margin: 0 auto;
			padding: 40px 24px 60px;
		}

		/* ================================================================
		   VERIFIED BADGE
		================================================================ */
		.gcv-verified-banner {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 10px;
			background: linear-gradient(135deg, #1a4a1a 0%, #1e5c1e 100%);
			border: 1px solid #2d7a2d;
			border-radius: 8px;
			padding: 14px 24px;
			margin-bottom: 32px;
			color: #fff;
		}

		.gcv-verified-banner .gcv-check-icon {
			width: 28px;
			height: 28px;
			background: #2d7a2d;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1rem;
			flex-shrink: 0;
		}

		.gcv-verified-banner .gcv-verified-text {
			font-size: 1rem;
			font-weight: 600;
			letter-spacing: 0.04em;
		}

		.gcv-verified-banner .gcv-verified-sub {
			font-size: 0.82rem;
			opacity: 0.75;
			margin-top: 2px;
		}

		/* ================================================================
		   PRODUCT TITLE
		================================================================ */
		.gcv-product-title {
			font-family: 'Cormorant Garamond', serif;
			font-size: 2rem;
			font-weight: 700;
			color: #1a1a1a;
			text-align: center;
			margin-bottom: 8px;
			letter-spacing: 0.02em;
		}

		.gcv-cert-number-display {
			text-align: center;
			font-size: 0.9rem;
			color: #666;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			margin-bottom: 36px;
		}

		.gcv-cert-number-display span {
			color: <?php echo $gold_color; ?>;
			font-weight: 600;
		}

		/* ================================================================
		   CERTIFICATE CARD
		================================================================ */
		.gcv-certificate-card {
			background: #fff;
			border-radius: 12px;
			box-shadow: 0 4px 24px rgba(0,0,0,0.08);
			overflow: hidden;
			margin-bottom: 32px;
		}

		/* Gold top border accent */
		.gcv-certificate-card::before {
			content: '';
			display: block;
			height: 4px;
			background: linear-gradient(90deg, <?php echo $brand_color; ?> 0%, <?php echo $gold_color; ?> 50%, <?php echo $brand_color; ?> 100%);
		}

		.gcv-certificate-card-inner {
			display: grid;
			grid-template-columns: 1fr 1.6fr;
			gap: 0;
		}

		/* ================================================================
		   PRODUCT IMAGE PANEL
		================================================================ */
		.gcv-image-panel {
			background: #f8f7f4;
			border-right: 1px solid #ece9e0;
			padding: 32px 24px;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			gap: 16px;
		}

		.gcv-product-image {
			width: 100%;
			max-width: 240px;
			height: auto;
			border-radius: 8px;
			border: 2px solid #ece9e0;
			display: block;
		}

		.gcv-no-image {
			width: 200px;
			height: 200px;
			background: #ece9e0;
			border-radius: 8px;
			display: flex;
			align-items: center;
			justify-content: center;
			color: #999;
			font-size: 0.85rem;
		}

		.gcv-gallery {
			display: flex;
			gap: 8px;
			flex-wrap: wrap;
			justify-content: center;
		}

		.gcv-gallery-thumb {
			width: 56px;
			height: 56px;
			object-fit: cover;
			border-radius: 4px;
			border: 2px solid #ece9e0;
			cursor: pointer;
			transition: border-color 0.2s;
		}

		.gcv-gallery-thumb:hover {
			border-color: <?php echo $gold_color; ?>;
		}

		/* ================================================================
		   DETAILS PANEL
		================================================================ */
		.gcv-details-panel {
			padding: 32px 32px 32px 28px;
		}

		.gcv-details-panel h3 {
			font-family: 'Cormorant Garamond', serif;
			font-size: 1.1rem;
			font-weight: 600;
			color: <?php echo $brand_color; ?>;
			text-transform: uppercase;
			letter-spacing: 0.1em;
			margin-bottom: 20px;
			padding-bottom: 10px;
			border-bottom: 1px solid #ece9e0;
		}

		.gcv-details-table {
			width: 100%;
			border-collapse: collapse;
		}

		.gcv-details-table tr {
			border-bottom: 1px solid #f0ede6;
		}

		.gcv-details-table tr:last-child {
			border-bottom: none;
		}

		.gcv-details-table td {
			padding: 11px 8px;
			vertical-align: top;
			font-size: 0.9rem;
			line-height: 1.5;
		}

		.gcv-details-table td.gcv-label {
			color: #888;
			font-weight: 500;
			white-space: nowrap;
			width: 44%;
			font-size: 0.82rem;
			text-transform: uppercase;
			letter-spacing: 0.04em;
			padding-right: 16px;
		}

		.gcv-details-table td.gcv-value {
			color: #1a1a1a;
			font-weight: 500;
		}

		.gcv-details-table td.gcv-value strong {
			color: <?php echo $brand_color; ?>;
			font-size: 1rem;
		}

		/* Highlight weight and fineness rows */
		.gcv-details-table tr.gcv-highlight td.gcv-value {
			color: <?php echo $brand_color; ?>;
			font-weight: 700;
			font-size: 1.05rem;
		}

		/* ================================================================
		   QR + ACTIONS ROW
		================================================================ */
		.gcv-bottom-row {
			display: grid;
			grid-template-columns: auto 1fr;
			gap: 32px;
			align-items: start;
			margin-bottom: 32px;
		}

		.gcv-qr-box {
			background: #fff;
			border-radius: 12px;
			box-shadow: 0 4px 24px rgba(0,0,0,0.08);
			padding: 24px;
			text-align: center;
			min-width: 180px;
		}

		#gcv-qrcode img,
		#gcv-qrcode canvas {
			display: block;
			margin: 0 auto;
		}

		.gcv-qr-label {
			font-size: 0.78rem;
			color: #888;
			text-transform: uppercase;
			letter-spacing: 0.08em;
			margin-top: 10px;
		}

		.gcv-qr-cert-no {
			font-size: 0.82rem;
			color: <?php echo $brand_color; ?>;
			font-weight: 600;
			margin-top: 6px;
		}

		.gcv-actions-box {
			background: #fff;
			border-radius: 12px;
			box-shadow: 0 4px 24px rgba(0,0,0,0.08);
			padding: 28px;
		}

		.gcv-actions-box h4 {
			font-family: 'Cormorant Garamond', serif;
			font-size: 1rem;
			font-weight: 600;
			color: <?php echo $brand_color; ?>;
			text-transform: uppercase;
			letter-spacing: 0.08em;
			margin-bottom: 16px;
		}

		.gcv-btn-row {
			display: flex;
			gap: 12px;
			flex-wrap: wrap;
		}

		.gcv-btn {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			padding: 11px 22px;
			border-radius: 6px;
			font-size: 0.88rem;
			font-weight: 600;
			cursor: pointer;
			border: none;
			text-decoration: none;
			transition: all 0.2s;
			letter-spacing: 0.03em;
		}

		.gcv-btn-print {
			background: <?php echo $brand_color; ?>;
			color: #fff;
		}

		.gcv-btn-print:hover {
			background: #2d2548;
		}

		.gcv-btn-pdf {
			background: <?php echo $gold_color; ?>;
			color: #1a1a1a;
		}

		.gcv-btn-pdf:hover {
			background: #c9a227;
		}

		/* Security features list */
		.gcv-security-list {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 8px;
			margin-top: 16px;
			padding-top: 16px;
			border-top: 1px solid #f0ede6;
		}

		.gcv-security-item {
			display: flex;
			align-items: center;
			gap: 8px;
			font-size: 0.82rem;
			color: #555;
		}

		.gcv-security-item::before {
			content: '✓';
			color: #2d7a2d;
			font-weight: 700;
			flex-shrink: 0;
		}

		/* ================================================================
		   FOOTER
		================================================================ */
		.gcv-footer {
			background: <?php echo $brand_color; ?>;
			position: relative;
			overflow: hidden;
		}

		/* Subtle top gold accent */
		.gcv-footer-divider {
			height: 3px;
			background: linear-gradient(90deg, transparent 0%, <?php echo $gold_color; ?> 20%, <?php echo $gold_color; ?> 80%, transparent 100%);
		}

		.gcv-footer::before {
			content: '';
			position: absolute;
			bottom: 0; left: 0; right: 0; top: 0;
			background: linear-gradient(135deg, transparent 40%, rgba(212,175,55,0.06) 100%);
			pointer-events: none;
		}

		.gcv-footer-inner {
			max-width: 960px;
			margin: 0 auto;
			padding: 36px 40px 28px;
			position: relative;
			z-index: 1;
		}

		.gcv-footer-top {
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
			gap: 32px;
			margin-bottom: 28px;
		}

		.gcv-footer-logo img {
			height: 48px;
			width: auto;
			display: block;
			opacity: 0.95;
		}

		.gcv-footer-contact {
			text-align: right;
		}

		.gcv-footer-contact p {
			font-size: 0.85rem;
			color: rgba(255,255,255,0.7);
			margin-bottom: 4px;
			line-height: 1.6;
		}

		.gcv-footer-contact a {
			color: <?php echo $gold_color; ?>;
			text-decoration: none;
		}

		.gcv-footer-contact a:hover {
			text-decoration: underline;
		}

		.gcv-footer-bottom {
			border-top: 1px solid rgba(255,255,255,0.1);
			padding-top: 20px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 16px;
			flex-wrap: wrap;
		}

		.gcv-footer-bottom p {
			font-size: 0.78rem;
			color: rgba(255,255,255,0.45);
			letter-spacing: 0.04em;
		}

		.gcv-footer-badge {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			background: rgba(212,175,55,0.12);
			border: 1px solid rgba(212,175,55,0.3);
			border-radius: 20px;
			padding: 5px 14px;
			font-size: 0.75rem;
			color: <?php echo $gold_color; ?>;
			letter-spacing: 0.06em;
			text-transform: uppercase;
			font-weight: 600;
		}

		/* ================================================================
		   RESPONSIVE
		================================================================ */
		@media (max-width: 720px) {
			.gcv-header-inner {
				flex-direction: column;
				align-items: flex-start;
				padding: 20px 20px;
				gap: 12px;
			}

			.gcv-header-brand {
				text-align: left;
			}

			.gcv-certificate-card-inner {
				grid-template-columns: 1fr;
			}

			.gcv-image-panel {
				border-right: none;
				border-bottom: 1px solid #ece9e0;
				padding: 24px;
			}

			.gcv-details-panel {
				padding: 24px 20px;
			}

			.gcv-bottom-row {
				grid-template-columns: 1fr;
			}

			.gcv-qr-box {
				min-width: unset;
			}

			.gcv-security-list {
				grid-template-columns: 1fr;
			}

			.gcv-footer-top {
				flex-direction: column;
				gap: 20px;
			}

			.gcv-footer-contact {
				text-align: left;
			}

			.gcv-footer-bottom {
				flex-direction: column;
				align-items: flex-start;
			}

			.gcv-body {
				padding: 24px 16px 40px;
			}
		}

		/* ================================================================
		   PRINT STYLES
		================================================================ */
		@media print {
			body { background: #fff; }
			.gcv-btn-row, .gcv-actions-box h4 { display: none; }
			.gcv-certificate-card, .gcv-qr-box, .gcv-actions-box {
				box-shadow: none;
				border: 1px solid #ddd;
			}
		}
	</style>
</head>
<body class="gcv-certificate-page">

	<!-- ================================================================
	     HEADER
	================================================================ -->
	<header class="gcv-header">
		<div class="gcv-header-inner">
			<div class="gcv-header-logo">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				</a>
			</div>
			<div class="gcv-header-brand">
				<p class="gcv-tagline"><?php esc_html_e( 'Product Certification &amp; Verification', 'gold-cert-verifier' ); ?></p>
			</div>
		</div>
	</header>
	<div class="gcv-header-divider"></div>

	<!-- ================================================================
	     MAIN BODY
	================================================================ -->
	<main class="gcv-body">

		<!-- Verified Banner -->
		<div class="gcv-verified-banner">
			<div class="gcv-check-icon">&#10003;</div>
			<div>
				<div class="gcv-verified-text"><?php esc_html_e( 'Certificate Verified', 'gold-cert-verifier' ); ?></div>
				<div class="gcv-verified-sub"><?php esc_html_e( 'This product certificate is authentic and registered in our system.', 'gold-cert-verifier' ); ?></div>
			</div>
		</div>

		<!-- Product Title -->
		<h1 class="gcv-product-title"><?php echo esc_html( $certificate->product_name ); ?></h1>
		<p class="gcv-cert-number-display">
			<?php esc_html_e( 'Certificate No.', 'gold-cert-verifier' ); ?>
			<span><?php echo esc_html( $certificate->certificate_no ); ?></span>
		</p>

		<!-- Certificate Card -->
		<div class="gcv-certificate-card">
			<div class="gcv-certificate-card-inner">

				<!-- Image Panel -->
				<div class="gcv-image-panel">
					<?php if ( ! empty( $certificate->image_url ) ) : ?>
						<img src="<?php echo esc_url( $certificate->image_url ); ?>"
							 alt="<?php echo esc_attr( $certificate->product_name ); ?>"
							 class="gcv-product-image"
							 id="gcv-main-image"
							 loading="lazy">
					<?php else : ?>
						<div class="gcv-no-image">
							<span><?php esc_html_e( 'No image available', 'gold-cert-verifier' ); ?></span>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $certificate->gallery_urls ) && is_array( $certificate->gallery_urls ) ) : ?>
						<div class="gcv-gallery">
							<?php foreach ( $certificate->gallery_urls as $idx => $gurl ) : ?>
								<img src="<?php echo esc_url( $gurl ); ?>"
									 alt="<?php echo esc_attr( $certificate->product_name . ' view ' . ( $idx + 1 ) ); ?>"
									 class="gcv-gallery-thumb"
									 data-full="<?php echo esc_url( $gurl ); ?>"
									 loading="lazy">
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<!-- Details Panel -->
				<div class="gcv-details-panel">
					<h3><?php esc_html_e( 'Certificate Details', 'gold-cert-verifier' ); ?></h3>
					<table class="gcv-details-table">
						<tbody>
							<tr>
								<td class="gcv-label"><?php esc_html_e( 'Certificate No.', 'gold-cert-verifier' ); ?></td>
								<td class="gcv-value"><strong><?php echo esc_html( $certificate->certificate_no ); ?></strong></td>
							</tr>
							<tr>
								<td class="gcv-label"><?php esc_html_e( 'Item Code', 'gold-cert-verifier' ); ?></td>
								<td class="gcv-value"><?php echo esc_html( $certificate->item_code ); ?></td>
							</tr>
							<tr>
								<td class="gcv-label"><?php esc_html_e( 'Product Name', 'gold-cert-verifier' ); ?></td>
								<td class="gcv-value"><?php echo esc_html( $certificate->product_name ); ?></td>
							</tr>
							<tr>
								<td class="gcv-label"><?php esc_html_e( 'Metal', 'gold-cert-verifier' ); ?></td>
								<td class="gcv-value"><?php echo esc_html( $certificate->metal ); ?></td>
							</tr>
							<tr class="gcv-highlight">
								<td class="gcv-label"><?php esc_html_e( 'Weight (Gram)', 'gold-cert-verifier' ); ?></td>
								<td class="gcv-value"><?php echo esc_html( gcv_format_number( $certificate->weight ) ); ?> g</td>
							</tr>
							<tr class="gcv-highlight">
								<td class="gcv-label"><?php esc_html_e( 'Fineness', 'gold-cert-verifier' ); ?></td>
								<td class="gcv-value"><?php echo esc_html( gcv_format_number( $certificate->fineness ) ); ?></td>
							</tr>
							<tr>
								<td class="gcv-label"><?php esc_html_e( 'Certification Date', 'gold-cert-verifier' ); ?></td>
								<td class="gcv-value"><?php echo esc_html( wp_date( 'j F Y', strtotime( $certificate->cert_date ) ) ); ?></td>
							</tr>
							<tr>
								<td class="gcv-label"><?php esc_html_e( 'Cast Bar Origin', 'gold-cert-verifier' ); ?></td>
								<td class="gcv-value"><?php echo esc_html( $certificate->origin ); ?></td>
							</tr>
							<tr>
								<td class="gcv-label"><?php esc_html_e( 'Certified Assayer', 'gold-cert-verifier' ); ?></td>
								<td class="gcv-value"><?php echo esc_html( $certificate->assayer ); ?></td>
							</tr>
						</tbody>
					</table>
				</div>

			</div><!-- /.gcv-certificate-card-inner -->
		</div><!-- /.gcv-certificate-card -->

		<!-- QR Code + Actions Row -->
		<div class="gcv-bottom-row">

			<!-- QR Code -->
			<div class="gcv-qr-box">
				<div id="gcv-qrcode"></div>
				<p class="gcv-qr-label"><?php esc_html_e( 'Scan to Verify', 'gold-cert-verifier' ); ?></p>
				<p class="gcv-qr-cert-no"><?php echo esc_html( $certificate->certificate_no ); ?></p>
			</div>

			<!-- Actions + Security -->
			<div class="gcv-actions-box">
				<h4><?php esc_html_e( 'Actions', 'gold-cert-verifier' ); ?></h4>
				<div class="gcv-btn-row">
					<button class="gcv-btn gcv-btn-print" onclick="window.print();">
						&#128438; <?php esc_html_e( 'Print Certificate', 'gold-cert-verifier' ); ?>
					</button>
					<button class="gcv-btn gcv-btn-pdf" id="gcv-pdf-btn">
						&#128196; <?php esc_html_e( 'Download PDF', 'gold-cert-verifier' ); ?>
					</button>
				</div>
				<div class="gcv-security-list">
					<span class="gcv-security-item"><?php esc_html_e( 'Unique Certificate Number', 'gold-cert-verifier' ); ?></span>
					<span class="gcv-security-item"><?php esc_html_e( 'Verified by Certified Assayer', 'gold-cert-verifier' ); ?></span>
					<span class="gcv-security-item"><?php esc_html_e( 'QR Code Verification', 'gold-cert-verifier' ); ?></span>
					<span class="gcv-security-item"><?php esc_html_e( 'Tamper-Proof Digital Record', 'gold-cert-verifier' ); ?></span>
				</div>
			</div>

		</div><!-- /.gcv-bottom-row -->

	</main>

	<!-- ================================================================
	     FOOTER
	================================================================ -->
	<div class="gcv-footer-divider"></div>
	<footer class="gcv-footer">
		<div class="gcv-footer-inner">

			<div class="gcv-footer-top">
				<!-- Logo -->
				<div class="gcv-footer-logo">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					</a>
				</div>

				<!-- Contact Info -->
				<div class="gcv-footer-contact">
					<?php if ( ! empty( $company_phone ) ) : ?>
						<p>&#128222; <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $company_phone ) ); ?>"><?php echo esc_html( $company_phone ); ?></a></p>
					<?php endif; ?>
					<?php if ( ! empty( $company_email ) ) : ?>
						<p>&#9993; <a href="mailto:<?php echo esc_attr( $company_email ); ?>"><?php echo esc_html( $company_email ); ?></a></p>
					<?php endif; ?>
					<?php if ( ! empty( $company_website ) ) : ?>
						<p>&#127760; <a href="<?php echo esc_url( $company_website ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $company_website ); ?></a></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="gcv-footer-bottom">
				<p>&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. <?php esc_html_e( 'All rights reserved.', 'gold-cert-verifier' ); ?></p>
				<span class="gcv-footer-badge">&#128274; <?php esc_html_e( 'Verified Certificate', 'gold-cert-verifier' ); ?></span>
			</div>

		</div>
	</footer>

	<!-- Scripts -->
	<script src="<?php echo esc_url( GCV_PLUGIN_URL . 'assets/js/qrcode.min.js' ); ?>"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function () {

			// QR Code generation
			var qrcodeEl = document.getElementById('gcv-qrcode');
			var certUrl  = '<?php echo esc_js( home_url( '/cert/' . $certificate->certificate_no ) ); ?>';

			if (typeof QRCode !== 'undefined' && qrcodeEl) {
				new QRCode(qrcodeEl, {
					text: certUrl,
					width: 140,
					height: 140,
					colorDark: '<?php echo $brand_color; ?>',
					colorLight: '#ffffff',
					correctLevel: QRCode.CorrectLevel.H
				});
			}

			// Gallery thumbnail click → swap main image
			var mainImg = document.getElementById('gcv-main-image');
			document.querySelectorAll('.gcv-gallery-thumb').forEach(function (thumb) {
				thumb.addEventListener('click', function () {
					if (mainImg) {
						mainImg.src = this.dataset.full;
					}
				});
			});

			// PDF download
			var pdfBtn = document.getElementById('gcv-pdf-btn');
			if (pdfBtn) {
				pdfBtn.addEventListener('click', function () {
					window.location.href = '<?php echo esc_js( add_query_arg( array( "gcv_pdf" => "1", "cert" => $certificate->certificate_no ), home_url( "/cert/" . $certificate->certificate_no ) ) ); ?>';
				});
			}
		});
	</script>

	<?php wp_footer(); ?>
</body>
</html>
