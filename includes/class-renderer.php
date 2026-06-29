<?php
/**
 * Renderer Class - Handles front-end certificate display
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GCV_Renderer {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		// Enqueue public styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );
	}

	/**
	 * Enqueue public CSS and JS
	 */
	public function enqueue_public_assets() {
		wp_enqueue_style(
			'gcv-public',
			GCV_PLUGIN_URL . 'assets/css/public.css',
			array(),
			GCV_VERSION
		);

		wp_enqueue_script(
			'gcv-qrcode',
			GCV_PLUGIN_URL . 'assets/js/qrcode.min.js',
			array(),
			GCV_VERSION,
			true
		);

		wp_enqueue_script(
			'gcv-public',
			GCV_PLUGIN_URL . 'assets/js/public.js',
			array( 'gcv-qrcode' ),
			GCV_VERSION,
			true
		);
	}

	/**
	 * Render the certificate page
	 */
	public static function render_certificate( $certificate ) {
		// Set up document
		?>
		<!DOCTYPE html>
		<html lang="<?php echo esc_attr( get_bloginfo( 'language' ) ); ?>">
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo esc_html( $certificate->product_name ) . ' - ' . esc_html( $certificate->certificate_no ); ?></title>
			<link rel="stylesheet" href="<?php echo esc_url( GCV_PLUGIN_URL . 'assets/css/public.css' ); ?>">
			<?php wp_head(); ?>
		</head>
		<body class="gcv-certificate-page">
			<div class="gcv-container">
				<!-- Header -->
				<div class="gcv-header">
					<h1><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
					<p class="gcv-subtitle">Product Certification</p>
				</div>

				<!-- Main Content -->
				<div class="gcv-content">
					<!-- Product Title -->
					<h2 class="gcv-product-title">
						<?php echo esc_html( $certificate->fineness ) . ' - ' . esc_html( $certificate->product_name ); ?>
					</h2>

					<div class="gcv-certificate-wrapper">
						<!-- Left Section: Product Image -->
						<div class="gcv-left-section">
							<div class="gcv-image-container">
								<?php if ( ! empty( $certificate->image_url ) ) : ?>
									<img src="<?php echo esc_url( $certificate->image_url ); ?>" 
										 alt="<?php echo esc_attr( $certificate->product_name ); ?>" 
										 class="gcv-product-image">
								<?php else : ?>
									<div class="gcv-no-image">
										<p><?php esc_html_e( 'No image available', 'gold-cert-verifier' ); ?></p>
									</div>
								<?php endif; ?>
							</div>

							<!-- Gallery -->
							<?php if ( ! empty( $certificate->gallery_urls ) && is_array( $certificate->gallery_urls ) ) : ?>
								<div class="gcv-gallery">
									<?php foreach ( $certificate->gallery_urls as $index => $image_url ) : ?>
										<img src="<?php echo esc_url( $image_url ); ?>" 
											 alt="<?php echo esc_attr( $certificate->product_name . ' - ' . ( $index + 1 ) ); ?>" 
											 class="gcv-gallery-thumb" 
											 data-full="<?php echo esc_url( $image_url ); ?>">
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>

						<!-- Right Section: Certificate Details -->
						<div class="gcv-right-section">
							<table class="gcv-details-table">
								<tr>
									<td class="gcv-label"><?php esc_html_e( 'Certificate No.', 'gold-cert-verifier' ); ?></td>
									<td class="gcv-value"><?php echo esc_html( $certificate->certificate_no ); ?></td>
								</tr>
								<tr>
									<td class="gcv-label"><?php esc_html_e( 'Item Code', 'gold-cert-verifier' ); ?></td>
									<td class="gcv-value"><?php echo esc_html( $certificate->item_code ); ?></td>
								</tr>
								<tr>
									<td class="gcv-label"><?php esc_html_e( 'Metal', 'gold-cert-verifier' ); ?></td>
									<td class="gcv-value"><?php echo esc_html( $certificate->metal ); ?></td>
								</tr>
								<tr>
									<td class="gcv-label"><?php esc_html_e( 'Weight (Gram)', 'gold-cert-verifier' ); ?></td>
									<td class="gcv-value"><?php echo esc_html( $certificate->weight ); ?></td>
								</tr>
								<tr>
									<td class="gcv-label"><?php esc_html_e( 'Fineness', 'gold-cert-verifier' ); ?></td>
									<td class="gcv-value"><?php echo esc_html( $certificate->fineness ); ?></td>
								</tr>
								<tr>
									<td class="gcv-label"><?php esc_html_e( 'Date', 'gold-cert-verifier' ); ?></td>
									<td class="gcv-value"><?php echo esc_html( wp_date( 'j F, Y', strtotime( $certificate->cert_date ) ) ); ?></td>
								</tr>
								<tr>
									<td class="gcv-label"><?php esc_html_e( 'Cast Bar Origin', 'gold-cert-verifier' ); ?></td>
									<td class="gcv-value"><?php echo esc_html( $certificate->origin ); ?></td>
								</tr>
								<tr>
									<td class="gcv-label"><?php esc_html_e( 'Certified Assayer', 'gold-cert-verifier' ); ?></td>
									<td class="gcv-value"><?php echo esc_html( $certificate->assayer ); ?></td>
								</tr>
							</table>
						</div>
					</div>

					<!-- QR Code Section -->
					<div class="gcv-qr-section">
						<div class="gcv-qr-container">
							<div id="gcv-qrcode"></div>
							<p class="gcv-qr-label"><?php echo esc_html( $certificate->certificate_no ); ?></p>
						</div>
					</div>

					<!-- Action Buttons -->
					<div class="gcv-actions">
						<button class="gcv-btn gcv-btn-print" onclick="window.print();">
							<?php esc_html_e( 'Print Certificate', 'gold-cert-verifier' ); ?>
						</button>
						<button class="gcv-btn gcv-btn-pdf" id="gcv-pdf-btn">
							<?php esc_html_e( 'Download PDF', 'gold-cert-verifier' ); ?>
						</button>
					</div>

					<!-- Contact Section -->
					<div class="gcv-contact-section">
						<h3><?php esc_html_e( 'Questions?', 'gold-cert-verifier' ); ?></h3>
						<p><?php esc_html_e( 'Contact us for verification or inquiries:', 'gold-cert-verifier' ); ?></p>
						<div class="gcv-contact-info">
							<?php
							$phone = get_option( 'gcv_company_phone' );
							$email = get_option( 'gcv_company_email' );
							$website = get_option( 'gcv_company_website' );

							if ( ! empty( $phone ) ) {
								echo '<p><strong>' . esc_html__( 'Phone:', 'gold-cert-verifier' ) . '</strong> <a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a></p>';
							}

							if ( ! empty( $email ) ) {
								echo '<p><strong>' . esc_html__( 'Email:', 'gold-cert-verifier' ) . '</strong> <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></p>';
							}

							if ( ! empty( $website ) ) {
								echo '<p><strong>' . esc_html__( 'Website:', 'gold-cert-verifier' ) . '</strong> <a href="' . esc_url( $website ) . '" target="_blank">' . esc_html( $website ) . '</a></p>';
							}
							?>
						</div>
					</div>
				</div>

				<!-- Footer -->
				<div class="gcv-footer">
					<p><?php echo esc_html( get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' ) ); ?></p>
				</div>
			</div>

			<script>
				// Generate QR code
				document.addEventListener('DOMContentLoaded', function() {
					var qrcodeContainer = document.getElementById('gcv-qrcode');
					var certificateUrl = '<?php echo esc_js( home_url( '/cert/' . $certificate->certificate_no ) ); ?>';
					
					if (typeof QRCode !== 'undefined' && qrcodeContainer) {
						new QRCode(qrcodeContainer, {
							text: certificateUrl,
							width: 200,
							height: 200,
							colorDark: '#000000',
							colorLight: '#ffffff',
							correctLevel: QRCode.CorrectLevel.H
						});
					}
				});
			</script>

			<?php wp_footer(); ?>
		</body>
		</html>
		<?php
	}
}
