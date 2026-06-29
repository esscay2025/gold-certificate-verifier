<?php
/**
 * Certificate Lookup Landing Page Template
 *
 * Displayed at /cert — lets customers enter their certificate number
 * and be redirected to the full certificate page at /cert/{number}.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_name    = get_bloginfo( 'name' );
$logo_url     = GCV_PLUGIN_URL . 'assets/images/logo.webp';
$brand_color  = '#211C35';
$gold_color   = '#D4AF37';
$company_phone   = get_option( 'gcv_company_phone', '' );
$company_email   = get_option( 'gcv_company_email', '' );
$company_website = get_option( 'gcv_company_website', '' );
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Verify the authenticity of your Britania gold bar using the unique certificate number printed on your product packaging.">
	<meta name="robots" content="index, follow">
	<title><?php esc_html_e( 'Certificate Verification', 'gold-cert-verifier' ); ?> &mdash; <?php echo esc_html( $site_name ); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
	<?php wp_head(); ?>
	<style>
		/* ================================================================
		   RESET & BASE
		================================================================ */
		*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

		html { scroll-behavior: smooth; }

		body.gcv-lookup-page {
			font-family: 'Inter', sans-serif;
			background: #0e0b1a;
			color: #f0ede6;
			min-height: 100vh;
			display: flex;
			flex-direction: column;
		}

		/* ================================================================
		   HEADER
		================================================================ */
		.gcv-header {
			background: <?php echo $brand_color; ?>;
			position: sticky;
			top: 0;
			z-index: 100;
			border-bottom: 1px solid rgba(212,175,55,0.2);
		}

		.gcv-header-inner {
			max-width: 1100px;
			margin: 0 auto;
			padding: 18px 40px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 20px;
		}

		.gcv-header-logo img {
			height: 48px;
			width: auto;
			display: block;
		}

		.gcv-header-nav {
			display: flex;
			align-items: center;
			gap: 28px;
		}

		.gcv-header-nav a {
			color: rgba(255,255,255,0.7);
			text-decoration: none;
			font-size: 0.88rem;
			font-weight: 500;
			letter-spacing: 0.04em;
			transition: color 0.2s;
		}

		.gcv-header-nav a:hover {
			color: <?php echo $gold_color; ?>;
		}

		.gcv-header-divider {
			height: 2px;
			background: linear-gradient(90deg, transparent 0%, <?php echo $gold_color; ?> 30%, <?php echo $gold_color; ?> 70%, transparent 100%);
		}

		/* ================================================================
		   HERO SECTION
		================================================================ */
		.gcv-hero {
			position: relative;
			background: linear-gradient(160deg, #1a1230 0%, #0e0b1a 40%, #1a1230 100%);
			padding: 100px 24px 80px;
			text-align: center;
			overflow: hidden;
			flex: 1;
		}

		/* Decorative radial glow */
		.gcv-hero::before {
			content: '';
			position: absolute;
			top: -120px;
			left: 50%;
			transform: translateX(-50%);
			width: 700px;
			height: 700px;
			background: radial-gradient(circle, rgba(212,175,55,0.08) 0%, transparent 70%);
			pointer-events: none;
		}

		/* Subtle grid pattern overlay */
		.gcv-hero::after {
			content: '';
			position: absolute;
			inset: 0;
			background-image:
				linear-gradient(rgba(212,175,55,0.03) 1px, transparent 1px),
				linear-gradient(90deg, rgba(212,175,55,0.03) 1px, transparent 1px);
			background-size: 60px 60px;
			pointer-events: none;
		}

		.gcv-hero-content {
			position: relative;
			z-index: 1;
			max-width: 680px;
			margin: 0 auto;
		}

		/* Gold pill badge */
		.gcv-hero-badge {
			display: inline-flex;
			align-items: center;
			gap: 8px;
			background: rgba(212,175,55,0.1);
			border: 1px solid rgba(212,175,55,0.35);
			border-radius: 40px;
			padding: 7px 20px;
			font-size: 0.78rem;
			font-weight: 600;
			color: <?php echo $gold_color; ?>;
			letter-spacing: 0.1em;
			text-transform: uppercase;
			margin-bottom: 32px;
		}

		.gcv-hero-badge::before {
			content: '';
			width: 6px;
			height: 6px;
			background: <?php echo $gold_color; ?>;
			border-radius: 50%;
			animation: gcv-pulse 2s infinite;
		}

		@keyframes gcv-pulse {
			0%, 100% { opacity: 1; transform: scale(1); }
			50% { opacity: 0.5; transform: scale(0.8); }
		}

		.gcv-hero-title {
			font-family: 'Cormorant Garamond', serif;
			font-size: clamp(2.4rem, 5vw, 3.6rem);
			font-weight: 700;
			line-height: 1.15;
			color: #fff;
			margin-bottom: 20px;
			letter-spacing: -0.01em;
		}

		.gcv-hero-title span {
			color: <?php echo $gold_color; ?>;
		}

		.gcv-hero-subtitle {
			font-size: 1.05rem;
			color: rgba(255,255,255,0.6);
			line-height: 1.7;
			margin-bottom: 52px;
			font-weight: 300;
		}

		/* ================================================================
		   LOOKUP CARD
		================================================================ */
		.gcv-lookup-card {
			background: rgba(255,255,255,0.04);
			border: 1px solid rgba(212,175,55,0.2);
			border-radius: 16px;
			padding: 44px 48px;
			backdrop-filter: blur(12px);
			-webkit-backdrop-filter: blur(12px);
			box-shadow: 0 24px 80px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.06);
			text-align: left;
			margin-bottom: 20px;
		}

		.gcv-lookup-label {
			display: block;
			font-size: 0.8rem;
			font-weight: 600;
			color: <?php echo $gold_color; ?>;
			text-transform: uppercase;
			letter-spacing: 0.12em;
			margin-bottom: 10px;
		}

		.gcv-lookup-hint {
			font-size: 0.82rem;
			color: rgba(255,255,255,0.4);
			margin-bottom: 20px;
			line-height: 1.5;
		}

		.gcv-lookup-hint code {
			background: rgba(212,175,55,0.1);
			border: 1px solid rgba(212,175,55,0.2);
			border-radius: 4px;
			padding: 2px 8px;
			font-family: 'Courier New', monospace;
			font-size: 0.85em;
			color: <?php echo $gold_color; ?>;
		}

		.gcv-input-row {
			display: flex;
			gap: 12px;
			align-items: stretch;
		}

		.gcv-cert-input {
			flex: 1;
			background: rgba(255,255,255,0.06);
			border: 1.5px solid rgba(212,175,55,0.25);
			border-radius: 10px;
			padding: 16px 20px;
			font-family: 'Inter', sans-serif;
			font-size: 1rem;
			font-weight: 500;
			color: #fff;
			letter-spacing: 0.06em;
			outline: none;
			transition: border-color 0.25s, background 0.25s, box-shadow 0.25s;
		}

		.gcv-cert-input::placeholder {
			color: rgba(255,255,255,0.25);
			font-weight: 400;
			letter-spacing: 0.04em;
		}

		.gcv-cert-input:focus {
			border-color: <?php echo $gold_color; ?>;
			background: rgba(212,175,55,0.06);
			box-shadow: 0 0 0 3px rgba(212,175,55,0.12);
		}

		.gcv-verify-btn {
			background: linear-gradient(135deg, <?php echo $gold_color; ?> 0%, #c9a227 100%);
			color: #1a1a1a;
			border: none;
			border-radius: 10px;
			padding: 16px 32px;
			font-family: 'Inter', sans-serif;
			font-size: 0.95rem;
			font-weight: 700;
			cursor: pointer;
			letter-spacing: 0.04em;
			white-space: nowrap;
			transition: all 0.25s;
			box-shadow: 0 4px 20px rgba(212,175,55,0.3);
			display: flex;
			align-items: center;
			gap: 8px;
		}

		.gcv-verify-btn:hover {
			transform: translateY(-1px);
			box-shadow: 0 8px 28px rgba(212,175,55,0.45);
		}

		.gcv-verify-btn:active {
			transform: translateY(0);
		}

		/* Error message */
		.gcv-error-msg {
			display: none;
			margin-top: 12px;
			padding: 10px 16px;
			background: rgba(220,50,50,0.12);
			border: 1px solid rgba(220,50,50,0.3);
			border-radius: 8px;
			font-size: 0.85rem;
			color: #ff8080;
		}

		/* ================================================================
		   HOW IT WORKS
		================================================================ */
		.gcv-how-section {
			background: linear-gradient(180deg, #0e0b1a 0%, #13102a 100%);
			padding: 80px 24px;
		}

		.gcv-section-inner {
			max-width: 1000px;
			margin: 0 auto;
		}

		.gcv-section-label {
			text-align: center;
			font-size: 0.78rem;
			font-weight: 600;
			color: <?php echo $gold_color; ?>;
			text-transform: uppercase;
			letter-spacing: 0.14em;
			margin-bottom: 14px;
		}

		.gcv-section-title {
			font-family: 'Cormorant Garamond', serif;
			font-size: clamp(1.8rem, 3.5vw, 2.6rem);
			font-weight: 600;
			color: #fff;
			text-align: center;
			margin-bottom: 12px;
		}

		.gcv-section-desc {
			text-align: center;
			color: rgba(255,255,255,0.5);
			font-size: 0.95rem;
			line-height: 1.7;
			max-width: 560px;
			margin: 0 auto 56px;
		}

		.gcv-steps-grid {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 24px;
		}

		.gcv-step-card {
			background: rgba(255,255,255,0.03);
			border: 1px solid rgba(255,255,255,0.07);
			border-radius: 14px;
			padding: 32px 28px;
			position: relative;
			transition: border-color 0.3s, background 0.3s;
		}

		.gcv-step-card:hover {
			border-color: rgba(212,175,55,0.25);
			background: rgba(212,175,55,0.03);
		}

		.gcv-step-number {
			font-family: 'Cormorant Garamond', serif;
			font-size: 3rem;
			font-weight: 700;
			color: rgba(212,175,55,0.15);
			line-height: 1;
			margin-bottom: 16px;
		}

		.gcv-step-icon {
			font-size: 1.8rem;
			margin-bottom: 16px;
			display: block;
		}

		.gcv-step-title {
			font-size: 1rem;
			font-weight: 600;
			color: #fff;
			margin-bottom: 10px;
		}

		.gcv-step-desc {
			font-size: 0.85rem;
			color: rgba(255,255,255,0.5);
			line-height: 1.65;
		}

		/* ================================================================
		   TRUST BADGES
		================================================================ */
		.gcv-trust-section {
			background: <?php echo $brand_color; ?>;
			padding: 60px 24px;
			border-top: 1px solid rgba(212,175,55,0.15);
			border-bottom: 1px solid rgba(212,175,55,0.15);
		}

		.gcv-trust-grid {
			max-width: 1000px;
			margin: 0 auto;
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 32px;
			text-align: center;
		}

		.gcv-trust-item {
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 12px;
		}

		.gcv-trust-icon {
			width: 52px;
			height: 52px;
			background: rgba(212,175,55,0.1);
			border: 1px solid rgba(212,175,55,0.25);
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.4rem;
		}

		.gcv-trust-title {
			font-size: 0.92rem;
			font-weight: 600;
			color: #fff;
		}

		.gcv-trust-desc {
			font-size: 0.78rem;
			color: rgba(255,255,255,0.5);
			line-height: 1.5;
		}

		/* ================================================================
		   FAQ SECTION
		================================================================ */
		.gcv-faq-section {
			background: #0e0b1a;
			padding: 80px 24px;
		}

		.gcv-faq-grid {
			max-width: 760px;
			margin: 0 auto;
			display: flex;
			flex-direction: column;
			gap: 16px;
		}

		.gcv-faq-item {
			background: rgba(255,255,255,0.03);
			border: 1px solid rgba(255,255,255,0.07);
			border-radius: 10px;
			overflow: hidden;
		}

		.gcv-faq-question {
			width: 100%;
			background: none;
			border: none;
			padding: 20px 24px;
			text-align: left;
			font-family: 'Inter', sans-serif;
			font-size: 0.95rem;
			font-weight: 600;
			color: #fff;
			cursor: pointer;
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 16px;
			transition: color 0.2s;
		}

		.gcv-faq-question:hover {
			color: <?php echo $gold_color; ?>;
		}

		.gcv-faq-arrow {
			font-size: 0.75rem;
			color: <?php echo $gold_color; ?>;
			transition: transform 0.3s;
			flex-shrink: 0;
		}

		.gcv-faq-item.open .gcv-faq-arrow {
			transform: rotate(180deg);
		}

		.gcv-faq-answer {
			max-height: 0;
			overflow: hidden;
			transition: max-height 0.35s ease, padding 0.35s ease;
			padding: 0 24px;
		}

		.gcv-faq-item.open .gcv-faq-answer {
			max-height: 300px;
			padding: 0 24px 20px;
		}

		.gcv-faq-answer p {
			font-size: 0.88rem;
			color: rgba(255,255,255,0.55);
			line-height: 1.7;
		}

		/* ================================================================
		   FOOTER
		================================================================ */
		.gcv-footer-divider {
			height: 2px;
			background: linear-gradient(90deg, transparent 0%, <?php echo $gold_color; ?> 30%, <?php echo $gold_color; ?> 70%, transparent 100%);
		}

		.gcv-footer {
			background: <?php echo $brand_color; ?>;
			position: relative;
			overflow: hidden;
		}

		.gcv-footer::before {
			content: '';
			position: absolute;
			inset: 0;
			background: linear-gradient(135deg, transparent 40%, rgba(212,175,55,0.04) 100%);
			pointer-events: none;
		}

		.gcv-footer-inner {
			max-width: 1100px;
			margin: 0 auto;
			padding: 40px 40px 28px;
			position: relative;
			z-index: 1;
		}

		.gcv-footer-top {
			display: flex;
			align-items: flex-start;
			justify-content: space-between;
			gap: 40px;
			margin-bottom: 32px;
		}

		.gcv-footer-logo img {
			height: 44px;
			width: auto;
			opacity: 0.9;
		}

		.gcv-footer-tagline {
			font-size: 0.82rem;
			color: rgba(255,255,255,0.45);
			margin-top: 10px;
			line-height: 1.6;
			max-width: 240px;
		}

		.gcv-footer-contact {
			text-align: right;
		}

		.gcv-footer-contact p {
			font-size: 0.85rem;
			color: rgba(255,255,255,0.6);
			margin-bottom: 6px;
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
			border-top: 1px solid rgba(255,255,255,0.08);
			padding-top: 20px;
			display: flex;
			align-items: center;
			justify-content: space-between;
			gap: 16px;
			flex-wrap: wrap;
		}

		.gcv-footer-bottom p {
			font-size: 0.78rem;
			color: rgba(255,255,255,0.35);
			letter-spacing: 0.03em;
		}

		.gcv-footer-badge {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			background: rgba(212,175,55,0.1);
			border: 1px solid rgba(212,175,55,0.25);
			border-radius: 20px;
			padding: 5px 14px;
			font-size: 0.72rem;
			color: <?php echo $gold_color; ?>;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			font-weight: 600;
		}

		/* ================================================================
		   RESPONSIVE
		================================================================ */
		@media (max-width: 768px) {
			.gcv-header-inner { padding: 16px 20px; }
			.gcv-header-nav { display: none; }
			.gcv-hero { padding: 70px 20px 60px; }
			.gcv-lookup-card { padding: 28px 24px; }
			.gcv-input-row { flex-direction: column; }
			.gcv-verify-btn { justify-content: center; }
			.gcv-steps-grid { grid-template-columns: 1fr; }
			.gcv-trust-grid { grid-template-columns: repeat(2, 1fr); }
			.gcv-footer-top { flex-direction: column; gap: 24px; }
			.gcv-footer-contact { text-align: left; }
			.gcv-footer-inner { padding: 32px 20px 20px; }
		}

		@media (max-width: 480px) {
			.gcv-trust-grid { grid-template-columns: 1fr 1fr; }
		}
	</style>
</head>
<body class="gcv-lookup-page">

	<!-- ================================================================
	     HEADER
	================================================================ -->
	<header class="gcv-header">
		<div class="gcv-header-inner">
			<div class="gcv-header-logo">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $site_name ); ?>">
				</a>
			</div>
			<nav class="gcv-header-nav">
				<a href="<?php echo esc_url( home_url( '/cert' ) ); ?>"><?php esc_html_e( 'Verify Certificate', 'gold-cert-verifier' ); ?></a>
				<?php if ( ! empty( $company_website ) ) : ?>
					<a href="<?php echo esc_url( $company_website ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Our Website', 'gold-cert-verifier' ); ?></a>
				<?php endif; ?>
				<?php if ( ! empty( $company_phone ) ) : ?>
					<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $company_phone ) ); ?>"><?php echo esc_html( $company_phone ); ?></a>
				<?php endif; ?>
			</nav>
		</div>
	</header>
	<div class="gcv-header-divider"></div>

	<!-- ================================================================
	     HERO / LOOKUP SECTION
	================================================================ -->
	<section class="gcv-hero">
		<div class="gcv-hero-content">

			<div class="gcv-hero-badge">
				&#128274; <?php esc_html_e( 'Official Certificate Verification Portal', 'gold-cert-verifier' ); ?>
			</div>

			<h1 class="gcv-hero-title">
				<?php esc_html_e( 'Verify Your', 'gold-cert-verifier' ); ?><br>
				<span><?php esc_html_e( 'Gold Bar Certificate', 'gold-cert-verifier' ); ?></span>
			</h1>

			<p class="gcv-hero-subtitle">
				<?php esc_html_e( 'Every Britania gold bar comes with a unique certificate number. Enter it below to instantly verify your product\'s authenticity, weight, fineness, and full certification details.', 'gold-cert-verifier' ); ?>
			</p>

			<!-- Lookup Card -->
			<div class="gcv-lookup-card">
				<label class="gcv-lookup-label" for="gcv-cert-input">
					&#128196; <?php esc_html_e( 'Certificate Number', 'gold-cert-verifier' ); ?>
				</label>
				<p class="gcv-lookup-hint">
					<?php esc_html_e( 'Find your certificate number on the product packaging or authentication card. Example:', 'gold-cert-verifier' ); ?>
					<code>U0302258</code>
				</p>
				<div class="gcv-input-row">
					<input
						type="text"
						id="gcv-cert-input"
						class="gcv-cert-input"
						placeholder="<?php esc_attr_e( 'Enter certificate number e.g. U0302258', 'gold-cert-verifier' ); ?>"
						autocomplete="off"
						autocorrect="off"
						autocapitalize="characters"
						spellcheck="false"
						maxlength="50"
					>
					<button class="gcv-verify-btn" id="gcv-verify-btn" type="button">
						&#128269; <?php esc_html_e( 'Verify Now', 'gold-cert-verifier' ); ?>
					</button>
				</div>
				<div class="gcv-error-msg" id="gcv-error-msg">
					&#9888; <?php esc_html_e( 'Please enter a valid certificate number before verifying.', 'gold-cert-verifier' ); ?>
				</div>
			</div>

			<p style="font-size:0.78rem;color:rgba(255,255,255,0.3);margin-top:16px;letter-spacing:0.04em;">
				<?php esc_html_e( 'This portal is operated exclusively by', 'gold-cert-verifier' ); ?>
				<?php echo esc_html( $site_name ); ?>.
				<?php esc_html_e( 'Certificate data is secured and tamper-proof.', 'gold-cert-verifier' ); ?>
			</p>

		</div>
	</section>

	<!-- ================================================================
	     HOW IT WORKS
	================================================================ -->
	<section class="gcv-how-section">
		<div class="gcv-section-inner">
			<p class="gcv-section-label"><?php esc_html_e( 'Simple Process', 'gold-cert-verifier' ); ?></p>
			<h2 class="gcv-section-title"><?php esc_html_e( 'How Verification Works', 'gold-cert-verifier' ); ?></h2>
			<p class="gcv-section-desc">
				<?php esc_html_e( 'Our digital verification system gives you instant, tamper-proof confirmation of your gold bar\'s authenticity in three simple steps.', 'gold-cert-verifier' ); ?>
			</p>

			<div class="gcv-steps-grid">
				<div class="gcv-step-card">
					<div class="gcv-step-number">01</div>
					<span class="gcv-step-icon">&#128230;</span>
					<h3 class="gcv-step-title"><?php esc_html_e( 'Locate Your Certificate Number', 'gold-cert-verifier' ); ?></h3>
					<p class="gcv-step-desc">
						<?php esc_html_e( 'Find the unique certificate number printed on the authentication card included with your gold bar, or on the tamper-evident packaging label.', 'gold-cert-verifier' ); ?>
					</p>
				</div>
				<div class="gcv-step-card">
					<div class="gcv-step-number">02</div>
					<span class="gcv-step-icon">&#9000;</span>
					<h3 class="gcv-step-title"><?php esc_html_e( 'Enter & Submit', 'gold-cert-verifier' ); ?></h3>
					<p class="gcv-step-desc">
						<?php esc_html_e( 'Type the certificate number exactly as printed into the field above and click "Verify Now". The system queries our secure database instantly.', 'gold-cert-verifier' ); ?>
					</p>
				</div>
				<div class="gcv-step-card">
					<div class="gcv-step-number">03</div>
					<span class="gcv-step-icon">&#9989;</span>
					<h3 class="gcv-step-title"><?php esc_html_e( 'View Full Certificate', 'gold-cert-verifier' ); ?></h3>
					<p class="gcv-step-desc">
						<?php esc_html_e( 'Your certificate page loads instantly, displaying product name, weight, fineness, certification date, assayer details, and a scannable QR code.', 'gold-cert-verifier' ); ?>
					</p>
				</div>
			</div>
		</div>
	</section>

	<!-- ================================================================
	     TRUST BADGES
	================================================================ -->
	<section class="gcv-trust-section">
		<div class="gcv-trust-grid">
			<div class="gcv-trust-item">
				<div class="gcv-trust-icon">&#127941;</div>
				<div class="gcv-trust-title"><?php esc_html_e( 'Certified Purity', 'gold-cert-verifier' ); ?></div>
				<div class="gcv-trust-desc"><?php esc_html_e( '999.9 fine gold verified by accredited assayers', 'gold-cert-verifier' ); ?></div>
			</div>
			<div class="gcv-trust-item">
				<div class="gcv-trust-icon">&#128274;</div>
				<div class="gcv-trust-title"><?php esc_html_e( 'Tamper-Proof Records', 'gold-cert-verifier' ); ?></div>
				<div class="gcv-trust-desc"><?php esc_html_e( 'SHA-256 integrity hashing on every certificate', 'gold-cert-verifier' ); ?></div>
			</div>
			<div class="gcv-trust-item">
				<div class="gcv-trust-icon">&#9889;</div>
				<div class="gcv-trust-title"><?php esc_html_e( 'Instant Verification', 'gold-cert-verifier' ); ?></div>
				<div class="gcv-trust-desc"><?php esc_html_e( 'Real-time database lookup — results in under a second', 'gold-cert-verifier' ); ?></div>
			</div>
			<div class="gcv-trust-item">
				<div class="gcv-trust-icon">&#128247;</div>
				<div class="gcv-trust-title"><?php esc_html_e( 'QR Code Enabled', 'gold-cert-verifier' ); ?></div>
				<div class="gcv-trust-desc"><?php esc_html_e( 'Scan the QR code on your certificate for mobile verification', 'gold-cert-verifier' ); ?></div>
			</div>
		</div>
	</section>

	<!-- ================================================================
	     FAQ
	================================================================ -->
	<section class="gcv-faq-section">
		<div class="gcv-section-inner">
			<p class="gcv-section-label"><?php esc_html_e( 'Common Questions', 'gold-cert-verifier' ); ?></p>
			<h2 class="gcv-section-title"><?php esc_html_e( 'Frequently Asked Questions', 'gold-cert-verifier' ); ?></h2>
			<p class="gcv-section-desc"><?php esc_html_e( 'Everything you need to know about our gold bar certification and verification process.', 'gold-cert-verifier' ); ?></p>

			<div class="gcv-faq-grid">

				<div class="gcv-faq-item">
					<button class="gcv-faq-question">
						<?php esc_html_e( 'Where do I find my certificate number?', 'gold-cert-verifier' ); ?>
						<span class="gcv-faq-arrow">&#9660;</span>
					</button>
					<div class="gcv-faq-answer">
						<p><?php esc_html_e( 'Your certificate number is printed on the authentication card included inside the product box, and also on the tamper-evident holographic label affixed to the packaging. It typically begins with a letter followed by 7 digits (e.g. U0302258).', 'gold-cert-verifier' ); ?></p>
					</div>
				</div>

				<div class="gcv-faq-item">
					<button class="gcv-faq-question">
						<?php esc_html_e( 'What does the certificate verify?', 'gold-cert-verifier' ); ?>
						<span class="gcv-faq-arrow">&#9660;</span>
					</button>
					<div class="gcv-faq-answer">
						<p><?php esc_html_e( 'The certificate confirms the product name, item code, metal type, exact weight in grams, fineness (purity), certification date, country of origin, and the name of the certified assayer who tested and approved the bar.', 'gold-cert-verifier' ); ?></p>
					</div>
				</div>

				<div class="gcv-faq-item">
					<button class="gcv-faq-question">
						<?php esc_html_e( 'What if my certificate number is not found?', 'gold-cert-verifier' ); ?>
						<span class="gcv-faq-arrow">&#9660;</span>
					</button>
					<div class="gcv-faq-answer">
						<p><?php esc_html_e( 'Please double-check the number for typos and ensure you are entering it exactly as printed (case-sensitive). If the issue persists, contact our support team immediately — this may indicate a counterfeit product and should be reported.', 'gold-cert-verifier' ); ?></p>
					</div>
				</div>

				<div class="gcv-faq-item">
					<button class="gcv-faq-question">
						<?php esc_html_e( 'Is this verification system secure?', 'gold-cert-verifier' ); ?>
						<span class="gcv-faq-arrow">&#9660;</span>
					</button>
					<div class="gcv-faq-answer">
						<p><?php esc_html_e( 'Yes. Each certificate record is protected with SHA-256 integrity hashing. Any tampering with the underlying data is automatically detected. The system also logs all verification requests for audit purposes.', 'gold-cert-verifier' ); ?></p>
					</div>
				</div>

				<div class="gcv-faq-item">
					<button class="gcv-faq-question">
						<?php esc_html_e( 'Can I download or print my certificate?', 'gold-cert-verifier' ); ?>
						<span class="gcv-faq-arrow">&#9660;</span>
					</button>
					<div class="gcv-faq-answer">
						<p><?php esc_html_e( 'Yes. Once your certificate is displayed, you can use the "Print Certificate" button to print a physical copy, or the "Download PDF" button to save a digital copy for your records.', 'gold-cert-verifier' ); ?></p>
					</div>
				</div>

			</div>
		</div>
	</section>

	<!-- ================================================================
	     FOOTER
	================================================================ -->
	<div class="gcv-footer-divider"></div>
	<footer class="gcv-footer">
		<div class="gcv-footer-inner">
			<div class="gcv-footer-top">
				<div>
					<div class="gcv-footer-logo">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
							<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $site_name ); ?>">
						</a>
					</div>
					<p class="gcv-footer-tagline">
						<?php esc_html_e( 'Official gold bar certification and digital verification portal.', 'gold-cert-verifier' ); ?>
					</p>
				</div>
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
				<p>&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( $site_name ); ?>. <?php esc_html_e( 'All rights reserved.', 'gold-cert-verifier' ); ?></p>
				<span class="gcv-footer-badge">&#128274; <?php esc_html_e( 'Secure Verification Portal', 'gold-cert-verifier' ); ?></span>
			</div>
		</div>
	</footer>

	<!-- ================================================================
	     JAVASCRIPT
	================================================================ -->
	<script>
	(function () {
		var input   = document.getElementById('gcv-cert-input');
		var btn     = document.getElementById('gcv-verify-btn');
		var errMsg  = document.getElementById('gcv-error-msg');
		var baseUrl = '<?php echo esc_js( home_url( '/cert/' ) ); ?>';

		function doVerify() {
			var val = input.value.trim().toUpperCase().replace(/\s+/g, '');

			if (!val) {
				errMsg.style.display = 'block';
				input.focus();
				return;
			}

			// Basic format validation: alphanumeric + hyphens + underscores
			if (!/^[A-Z0-9_-]{1,50}$/.test(val)) {
				errMsg.textContent = '⚠ <?php echo esc_js( __( 'Invalid format. Use only letters, numbers, hyphens, or underscores.', 'gold-cert-verifier' ) ); ?>';
				errMsg.style.display = 'block';
				input.focus();
				return;
			}

			errMsg.style.display = 'none';

			// Show loading state
			btn.textContent = '⏳ <?php echo esc_js( __( 'Verifying…', 'gold-cert-verifier' ) ); ?>';
			btn.disabled = true;

			// Redirect to certificate page
			window.location.href = baseUrl + encodeURIComponent(val);
		}

		// Button click
		btn.addEventListener('click', doVerify);

		// Enter key in input
		input.addEventListener('keydown', function (e) {
			if (e.key === 'Enter') {
				doVerify();
			}
		});

		// Hide error on input change
		input.addEventListener('input', function () {
			errMsg.style.display = 'none';
		});

		// Auto-uppercase as user types
		input.addEventListener('input', function () {
			var pos = this.selectionStart;
			this.value = this.value.toUpperCase();
			this.setSelectionRange(pos, pos);
		});

		// FAQ accordion
		document.querySelectorAll('.gcv-faq-question').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var item = this.closest('.gcv-faq-item');
				var isOpen = item.classList.contains('open');
				// Close all
				document.querySelectorAll('.gcv-faq-item.open').forEach(function (el) {
					el.classList.remove('open');
				});
				// Toggle current
				if (!isOpen) {
					item.classList.add('open');
				}
			});
		});
	})();
	</script>

	<?php wp_footer(); ?>
</body>
</html>
