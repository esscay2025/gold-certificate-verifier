<?php
/**
 * Router Class - Handles custom rewrite rules and URL routing
 *
 * How WordPress rewrite rules work:
 * 1. add_rewrite_rule() registers the rule in memory on every 'init'.
 * 2. flush_rewrite_rules() writes them to .htaccess / the DB option.
 *    This MUST be called after registering rules, but only once (on
 *    activation) — calling it on every request is very slow.
 * 3. We detect whether a flush is needed by comparing a stored hash of
 *    the registered rules against the current rules.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GCV_Router {

	private static $instance = null;

	/** Slug used in the pretty-permalink URL: /cert/{code} */
	const CERT_SLUG = 'cert';

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		// 1. Register rewrite rules on every 'init' (required by WordPress)
		add_action( 'init', array( $this, 'add_rewrite_rules' ), 10 );

		// 2. Register our custom query vars so WordPress passes them through
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

		// 3. Intercept matching requests and serve the certificate page
		add_action( 'template_redirect', array( $this, 'handle_certificate_request' ), 1 );

		// 4. After rules are registered, flush if they have changed
		add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 99 );
	}

	// -----------------------------------------------------------------------
	// Rewrite Rules
	// -----------------------------------------------------------------------

	/**
	 * Register the custom rewrite rules.
	 * Called on every 'init' — WordPress needs this every request so it
	 * can build the in-memory rewrite table.
	 */
	public function add_rewrite_rules() {
		// Lookup landing page:  https://yourdomain.com/cert
		add_rewrite_rule(
			'^' . self::CERT_SLUG . '/?$',
			'index.php?gcv_lookup=1',
			'top'
		);

		// Pretty URL:  https://yourdomain.com/cert/U0302258
		add_rewrite_rule(
			'^' . self::CERT_SLUG . '/([a-zA-Z0-9_-]+)/?$',
			'index.php?gcv_cert=$matches[1]',
			'top'
		);

		// Legacy query-string URL:  https://yourdomain.com/verify/?q=U0302258
		add_rewrite_rule(
			'^verify/?$',
			'index.php?gcv_verify=1',
			'top'
		);
	}

	/**
	 * Flush rewrite rules only when they have actually changed.
	 * Avoids the performance hit of flushing on every page load.
	 */
	public function maybe_flush_rewrite_rules() {
		global $wp_rewrite;

		$current_hash = md5( serialize( $wp_rewrite->rules ) );
		$stored_hash  = get_option( 'gcv_rewrite_hash', '' );

		if ( $current_hash !== $stored_hash ) {
			flush_rewrite_rules( false ); // false = don't write .htaccess (safer on shared hosts)
			update_option( 'gcv_rewrite_hash', md5( serialize( $wp_rewrite->rules ) ) );
		}
	}

	// -----------------------------------------------------------------------
	// Query Vars
	// -----------------------------------------------------------------------

	/**
	 * Tell WordPress to recognise our custom query variables.
	 * Without this, get_query_var() always returns an empty string.
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'gcv_cert';    // set by the /cert/{code} rewrite rule
		$vars[] = 'gcv_lookup';  // set by the /cert landing page rule
		$vars[] = 'gcv_verify';  // set by the /verify/ rewrite rule
		$vars[] = 'q';           // legacy ?q= parameter
		return $vars;
	}

	// -----------------------------------------------------------------------
	// Request Handler
	// -----------------------------------------------------------------------

	/**
	 * Intercept certificate requests and serve the certificate template.
	 * Runs on 'template_redirect' (priority 1, before themes take over).
	 */
	public function handle_certificate_request() {
		$cert_code = get_query_var( 'gcv_cert' );
		$lookup    = get_query_var( 'gcv_lookup' );
		$verify    = get_query_var( 'gcv_verify' );
		$q_param   = get_query_var( 'q' );

		// Serve the lookup landing page at /cert
		if ( ! empty( $lookup ) ) {
			self::render_lookup_page();
			exit;
		}

		// Also support raw ?q= without the /verify/ prefix
		if ( empty( $cert_code ) && empty( $verify ) && ! empty( $q_param ) ) {
			$cert_code = $q_param;
		}

		// Not a certificate request — let WordPress continue normally
		if ( empty( $cert_code ) && empty( $verify ) ) {
			return;
		}

		// Resolve the certificate code
		if ( ! empty( $cert_code ) ) {
			$certificate_code = $cert_code;
		} elseif ( ! empty( $verify ) && ! empty( $q_param ) ) {
			$certificate_code = $q_param;
		} else {
			// /verify/ hit without ?q= — show a search form or 404
			self::render_not_found( '' );
			exit;
		}

		$certificate_code = sanitize_text_field( $certificate_code );

		// Validate format (alphanumeric, hyphens, underscores, 1-50 chars)
		if ( ! preg_match( '/^[a-zA-Z0-9_-]{1,50}$/', $certificate_code ) ) {
			self::render_not_found( $certificate_code );
			exit;
		}

		// Fetch certificate
		$certificate = GCV_DB::get_certificate( $certificate_code );

		if ( is_wp_error( $certificate ) ) {
			self::render_not_found( $certificate_code );
			exit;
		}

		// Serve the certificate template
		self::render_certificate_template( $certificate );
		exit;
	}

	// -----------------------------------------------------------------------
	// Template Loaders
	// -----------------------------------------------------------------------

	/**
	 * Load and render the certificate lookup landing page.
	 */
	private static function render_lookup_page() {
		$template_path = GCV_PLUGIN_DIR . 'templates/certificate-lookup.php';

		if ( file_exists( $template_path ) ) {
			// Allow theme override: {theme}/gold-cert-verifier/certificate-lookup.php
			$theme_template = locate_template( 'gold-cert-verifier/certificate-lookup.php' );
			if ( $theme_template ) {
				$template_path = $theme_template;
			}
			status_header( 200 );
			include $template_path;
		} else {
			// Minimal fallback
			status_header( 200 );
			echo '<form method="get" action="" onsubmit="window.location.href=\'' . esc_js( home_url( '/cert/' ) ) . '\'+document.getElementById(\'cn\').value;return false;"><input id="cn" type="text" placeholder="Certificate Number"><button type="submit">Verify</button></form>';
		}
	}

	/**
	 * Load and render the certificate template.
	 * Looks for the template in the plugin's /templates/ directory.
	 */
	private static function render_certificate_template( $certificate ) {
		// Make $certificate available inside the template via a global
		global $gcv_current_certificate;
		$gcv_current_certificate = $certificate;

		$template_path = GCV_PLUGIN_DIR . 'templates/certificate-single.php';

		if ( file_exists( $template_path ) ) {
			// Allow child/parent themes to override: place file at
			// {theme}/gold-cert-verifier/certificate-single.php
			$theme_template = locate_template( 'gold-cert-verifier/certificate-single.php' );
			if ( $theme_template ) {
				$template_path = $theme_template;
			}

			status_header( 200 );
			include $template_path;
		} else {
			// Fallback: inline minimal output if template file is missing
			status_header( 200 );
			echo '<p>Certificate: ' . esc_html( $certificate->certificate_no ) . '</p>';
		}
	}

	/**
	 * Render a "certificate not found" page with a 404 status.
	 */
	private static function render_not_found( $code ) {
		status_header( 404 );
		$site_name = get_bloginfo( 'name' );
		$brand_color = '#211C35';
		$logo_url    = GCV_PLUGIN_URL . 'assets/images/logo.webp';
		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php esc_html_e( 'Certificate Not Found', 'gold-cert-verifier' ); ?></title>
			<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
			<style>
				*{box-sizing:border-box;margin:0;padding:0}
				body{font-family:'Inter',sans-serif;background:#f4f2ee;min-height:100vh;display:flex;flex-direction:column}
				.gcv-hdr{background:<?php echo esc_attr( $brand_color ); ?>;padding:20px 40px;display:flex;align-items:center}
				.gcv-hdr img{height:48px}
				.gcv-hdr-div{height:3px;background:linear-gradient(90deg,transparent,#D4AF37,transparent)}
				.gcv-body{flex:1;display:flex;align-items:center;justify-content:center;padding:60px 24px}
				.gcv-card{background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.08);padding:48px 40px;max-width:480px;width:100%;text-align:center}
				.gcv-icon{font-size:3rem;margin-bottom:20px}
				h1{font-size:1.5rem;color:<?php echo esc_attr( $brand_color ); ?>;margin-bottom:12px}
				p{color:#666;font-size:.95rem;line-height:1.6;margin-bottom:8px}
				.gcv-code{display:inline-block;background:#f4f2ee;border:1px solid #ddd;border-radius:4px;padding:4px 12px;font-family:monospace;font-size:.9rem;margin:8px 0 20px}
				a.gcv-back{display:inline-block;background:<?php echo esc_attr( $brand_color ); ?>;color:#fff;padding:11px 28px;border-radius:6px;text-decoration:none;font-weight:600;font-size:.9rem;margin-top:8px}
				a.gcv-back:hover{background:#2d2548}
				.gcv-ftr{background:<?php echo esc_attr( $brand_color ); ?>;padding:20px 40px;text-align:center}
				.gcv-ftr img{height:36px;opacity:.8}
			</style>
		</head>
		<body>
			<div class="gcv-hdr">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $site_name ); ?>">
				</a>
			</div>
			<div class="gcv-hdr-div"></div>
			<div class="gcv-body">
				<div class="gcv-card">
					<div class="gcv-icon">&#128269;</div>
					<h1><?php esc_html_e( 'Certificate Not Found', 'gold-cert-verifier' ); ?></h1>
					<?php if ( ! empty( $code ) ) : ?>
						<p><?php esc_html_e( 'No certificate was found for:', 'gold-cert-verifier' ); ?></p>
						<span class="gcv-code"><?php echo esc_html( $code ); ?></span>
					<?php endif; ?>
					<p><?php esc_html_e( 'Please check the certificate number and try again. If you believe this is an error, contact us.', 'gold-cert-verifier' ); ?></p>
					<a class="gcv-back" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						&larr; <?php esc_html_e( 'Back to Home', 'gold-cert-verifier' ); ?>
					</a>
				</div>
			</div>
			<div class="gcv-ftr">
				<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $site_name ); ?>">
			</div>
		</body>
		</html>
		<?php
	}

	// -----------------------------------------------------------------------
	// Static Helpers
	// -----------------------------------------------------------------------

	/**
	 * Force a full rewrite rules flush.
	 * Called from the activation hook and the Settings "Repair" button.
	 */
	public static function flush_rules() {
		// Register rules first so they exist before flushing
		add_rewrite_rule(
			'^' . self::CERT_SLUG . '/?$',
			'index.php?gcv_lookup=1',
			'top'
		);
		add_rewrite_rule(
			'^' . self::CERT_SLUG . '/([a-zA-Z0-9_-]+)/?$',
			'index.php?gcv_cert=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'^verify/?$',
			'index.php?gcv_verify=1',
			'top'
		);
		flush_rewrite_rules( false );
		delete_option( 'gcv_rewrite_hash' ); // force re-check on next load
	}
}
