<?php
/**
 * Plugin Name: Gold Certificate Verifier
 * Plugin URI: https://yourdomain.com
 * Description: Professional gold bar product certificate verification system for WordPress
 * Version: 1.0.3
 * Author: Your Company Name
 * Author URI: https://yourdomain.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: gold-cert-verifier
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'GCV_PLUGIN_FILE', __FILE__ );
define( 'GCV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GCV_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'GCV_VERSION', '1.0.3' );
define( 'GCV_TABLE_NAME', 'gold_certificates' );
define( 'GCV_DB_VERSION', '1.0.3' );

// Require necessary files
require_once GCV_PLUGIN_DIR . 'includes/class-db.php';
require_once GCV_PLUGIN_DIR . 'includes/class-router.php';
require_once GCV_PLUGIN_DIR . 'includes/class-renderer.php';
require_once GCV_PLUGIN_DIR . 'includes/class-admin.php';

// -----------------------------------------------------------------------
// ACTIVATION HOOK — at file scope so WordPress detects it correctly
// -----------------------------------------------------------------------
register_activation_hook( __FILE__, 'gcv_activate' );

function gcv_activate() {
	// 1. Create / update the database table
	GCV_DB::create_table();
	update_option( 'gcv_db_version', GCV_DB_VERSION );

	// 2. Register rewrite rules and flush them so /cert/{code} works immediately
	GCV_Router::flush_rules();
}

register_deactivation_hook( __FILE__, 'gcv_deactivate' );

function gcv_deactivate() {
	flush_rewrite_rules();
	delete_option( 'gcv_rewrite_hash' );
}

// -----------------------------------------------------------------------
// MAIN PLUGIN CLASS
// -----------------------------------------------------------------------
class Gold_Certificate_Verifier {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	public function init() {
		// Load translations
		load_plugin_textdomain( 'gold-cert-verifier', false, dirname( plugin_basename( GCV_PLUGIN_FILE ) ) . '/languages' );

		// Safety net: recreate DB table if version mismatch (handles FTP uploads)
		if ( get_option( 'gcv_db_version' ) !== GCV_DB_VERSION ) {
			GCV_DB::create_table();
			update_option( 'gcv_db_version', GCV_DB_VERSION );
		}

		// Initialize components
		GCV_Router::get_instance();
		GCV_Renderer::get_instance();

		if ( is_admin() ) {
			GCV_Admin::get_instance();
		}
	}
}

Gold_Certificate_Verifier::get_instance();
