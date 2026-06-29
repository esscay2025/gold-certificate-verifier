<?php
/**
 * Admin Class - Handles admin panel and certificate management
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GCV_Admin {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_post_gcv_add_certificate', array( $this, 'handle_add_certificate' ) );
		add_action( 'admin_post_gcv_save_settings', array( $this, 'handle_save_settings' ) );
		add_action( 'admin_post_gcv_repair_db', array( $this, 'handle_repair_db' ) );
		add_action( 'admin_post_gcv_flush_rules', array( $this, 'handle_flush_rules' ) );
		add_action( 'admin_post_gcv_delete_certificate', array( $this, 'handle_delete_certificate' ) );
	}

	// -----------------------------------------------------------------------
	// Admin Menu
	// -----------------------------------------------------------------------

	public function add_admin_menu() {
		add_menu_page(
			__( 'Gold Certificates', 'gold-cert-verifier' ),
			__( 'Gold Certificates', 'gold-cert-verifier' ),
			'manage_options',
			'gold-certificates',
			array( $this, 'render_list_page' ),
			'dashicons-shield-alt',
			30
		);

		add_submenu_page(
			'gold-certificates',
			__( 'All Certificates', 'gold-cert-verifier' ),
			__( 'All Certificates', 'gold-cert-verifier' ),
			'manage_options',
			'gold-certificates',
			array( $this, 'render_list_page' )
		);

		add_submenu_page(
			'gold-certificates',
			__( 'Add Certificate', 'gold-cert-verifier' ),
			__( 'Add Certificate', 'gold-cert-verifier' ),
			'manage_options',
			'gold-certificates-add',
			array( $this, 'render_add_page' )
		);

		add_submenu_page(
			'gold-certificates',
			__( 'Settings', 'gold-cert-verifier' ),
			__( 'Settings', 'gold-cert-verifier' ),
			'manage_options',
			'gold-certificates-settings',
			array( $this, 'render_settings_page' )
		);
	}

	// -----------------------------------------------------------------------
	// Assets
	// -----------------------------------------------------------------------

	public function enqueue_admin_assets( $hook ) {
		// Only load on our plugin pages
		if ( strpos( $hook, 'gold-certificates' ) === false ) {
			return;
		}

		wp_enqueue_style( 'gcv-admin', GCV_PLUGIN_URL . 'assets/css/admin.css', array(), GCV_VERSION );
		wp_enqueue_script( 'gcv-admin', GCV_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), GCV_VERSION, true );
		wp_enqueue_media();
	}

	// -----------------------------------------------------------------------
	// Form Handlers (admin-post.php actions)
	// -----------------------------------------------------------------------

	/**
	 * Handle Add Certificate form submission
	 */
	public function handle_add_certificate() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'gold-cert-verifier' ) );
		}

		check_admin_referer( 'gcv_add_cert_nonce' );

		$data = array(
			'certificate_no' => isset( $_POST['certificate_no'] ) ? sanitize_text_field( wp_unslash( $_POST['certificate_no'] ) ) : '',
			'item_code'      => isset( $_POST['item_code'] ) ? sanitize_text_field( wp_unslash( $_POST['item_code'] ) ) : '',
			'product_name'   => isset( $_POST['product_name'] ) ? sanitize_text_field( wp_unslash( $_POST['product_name'] ) ) : '',
			'metal'          => isset( $_POST['metal'] ) ? sanitize_text_field( wp_unslash( $_POST['metal'] ) ) : 'Gold',
			'weight'         => isset( $_POST['weight'] ) ? floatval( $_POST['weight'] ) : 0,
			'fineness'       => isset( $_POST['fineness'] ) ? sanitize_text_field( wp_unslash( $_POST['fineness'] ) ) : '999.9',
			'cert_date'      => isset( $_POST['cert_date'] ) ? sanitize_text_field( wp_unslash( $_POST['cert_date'] ) ) : date( 'Y-m-d' ),
			'origin'         => isset( $_POST['origin'] ) ? sanitize_text_field( wp_unslash( $_POST['origin'] ) ) : 'Malaysia',
			'assayer'        => isset( $_POST['assayer'] ) ? sanitize_text_field( wp_unslash( $_POST['assayer'] ) ) : '',
			'image_url'      => isset( $_POST['image_url'] ) ? esc_url_raw( wp_unslash( $_POST['image_url'] ) ) : '',
			'status'         => 'active',
		);

		// Validate required fields
		if ( empty( $data['certificate_no'] ) || empty( $data['item_code'] ) || empty( $data['product_name'] ) || empty( $data['assayer'] ) ) {
			wp_safe_redirect( add_query_arg(
				array( 'page' => 'gold-certificates-add', 'gcv_error' => 'missing_fields' ),
				admin_url( 'admin.php' )
			) );
			exit;
		}

		$result = GCV_DB::insert_certificate( $data );

		if ( is_wp_error( $result ) ) {
			wp_safe_redirect( add_query_arg(
				array( 'page' => 'gold-certificates-add', 'gcv_error' => urlencode( $result->get_error_message() ) ),
				admin_url( 'admin.php' )
			) );
			exit;
		}

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'gold-certificates', 'gcv_added' => '1' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Handle Delete Certificate
	 */
	public function handle_delete_certificate() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'gold-cert-verifier' ) );
		}

		$cert_no = isset( $_GET['cert_id'] ) ? sanitize_text_field( wp_unslash( $_GET['cert_id'] ) ) : '';
		check_admin_referer( 'gcv_delete_cert_' . $cert_no );

		GCV_DB::delete_certificate( $cert_no );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'gold-certificates', 'gcv_deleted' => '1' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Handle Save Settings
	 */
	public function handle_save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'gold-cert-verifier' ) );
		}

		check_admin_referer( 'gcv_settings_nonce' );

		update_option( 'gcv_company_phone', sanitize_text_field( wp_unslash( $_POST['company_phone'] ?? '' ) ) );
		update_option( 'gcv_company_email', sanitize_email( wp_unslash( $_POST['company_email'] ?? '' ) ) );
		update_option( 'gcv_company_website', esc_url_raw( wp_unslash( $_POST['company_website'] ?? '' ) ) );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'gold-certificates-settings', 'gcv_saved' => '1' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Handle manual flush rewrite rules
	 */
	public function handle_flush_rules() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'gold-cert-verifier' ) );
		}

		check_admin_referer( 'gcv_flush_rules_nonce' );

		GCV_Router::flush_rules();

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'gold-certificates-settings', 'gcv_flushed' => '1' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	/**
	 * Handle manual DB repair
	 */
	public function handle_repair_db() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'gold-cert-verifier' ) );
		}

		check_admin_referer( 'gcv_repair_db_nonce' );

		GCV_DB::create_table();
		update_option( 'gcv_db_version', GCV_DB_VERSION );

		wp_safe_redirect( add_query_arg(
			array( 'page' => 'gold-certificates-settings', 'gcv_repaired' => '1' ),
			admin_url( 'admin.php' )
		) );
		exit;
	}

	// -----------------------------------------------------------------------
	// Page Renderers
	// -----------------------------------------------------------------------

	/**
	 * Render All Certificates list page
	 */
	public function render_list_page() {
		$page       = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$per_page   = 20;
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$certs      = GCV_DB::get_certificates( $page, $per_page, '', $search );
		$total      = GCV_DB::count_certificates( '', $search );
		$total_pages = ceil( $total / $per_page );
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Gold Certificates', 'gold-cert-verifier' ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=gold-certificates-add' ) ); ?>" class="page-title-action">
				<?php esc_html_e( '+ Add New', 'gold-cert-verifier' ); ?>
			</a>
			<hr class="wp-header-end">

			<?php if ( isset( $_GET['gcv_added'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Certificate added successfully!', 'gold-cert-verifier' ); ?></p></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['gcv_deleted'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Certificate deleted.', 'gold-cert-verifier' ); ?></p></div>
			<?php endif; ?>

			<!-- Search form -->
			<form method="get" action="">
				<input type="hidden" name="page" value="gold-certificates">
				<p class="search-box">
					<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search certificates…', 'gold-cert-verifier' ); ?>">
					<?php submit_button( __( 'Search', 'gold-cert-verifier' ), 'button', '', false ); ?>
				</p>
			</form>

			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Certificate No.', 'gold-cert-verifier' ); ?></th>
						<th><?php esc_html_e( 'Item Code', 'gold-cert-verifier' ); ?></th>
						<th><?php esc_html_e( 'Product Name', 'gold-cert-verifier' ); ?></th>
						<th><?php esc_html_e( 'Metal', 'gold-cert-verifier' ); ?></th>
						<th><?php esc_html_e( 'Weight (g)', 'gold-cert-verifier' ); ?></th>
						<th><?php esc_html_e( 'Fineness', 'gold-cert-verifier' ); ?></th>
						<th><?php esc_html_e( 'Date', 'gold-cert-verifier' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'gold-cert-verifier' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $certs ) ) : ?>
						<?php foreach ( $certs as $cert ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $cert->certificate_no ); ?></strong></td>
								<td><?php echo esc_html( $cert->item_code ); ?></td>
								<td><?php echo esc_html( $cert->product_name ); ?></td>
								<td><?php echo esc_html( $cert->metal ); ?></td>
								<td><?php echo esc_html( $cert->weight ); ?></td>
								<td><?php echo esc_html( $cert->fineness ); ?></td>
								<td><?php echo esc_html( wp_date( 'd M Y', strtotime( $cert->cert_date ) ) ); ?></td>
								<td>
									<a href="<?php echo esc_url( home_url( '/cert/' . $cert->certificate_no ) ); ?>" target="_blank">
										<?php esc_html_e( 'View', 'gold-cert-verifier' ); ?>
									</a>
									&nbsp;|&nbsp;
									<a href="<?php echo esc_url( wp_nonce_url(
										add_query_arg(
											array(
												'action'  => 'gcv_delete_certificate',
												'cert_id' => $cert->certificate_no,
											),
											admin_url( 'admin-post.php' )
										),
										'gcv_delete_cert_' . $cert->certificate_no
									) ); ?>"
									onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this certificate?', 'gold-cert-verifier' ); ?>');"
									style="color:#a00;">
										<?php esc_html_e( 'Delete', 'gold-cert-verifier' ); ?>
									</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="8" style="text-align:center;"><?php esc_html_e( 'No certificates found.', 'gold-cert-verifier' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $total_pages > 1 ) : ?>
				<div class="tablenav bottom">
					<div class="tablenav-pages">
						<?php
						echo paginate_links( array(
							'base'    => add_query_arg( 'paged', '%#%' ),
							'format'  => '',
							'current' => $page,
							'total'   => $total_pages,
						) );
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render Add Certificate page
	 */
	public function render_add_page() {
		$error = isset( $_GET['gcv_error'] ) ? sanitize_text_field( wp_unslash( $_GET['gcv_error'] ) ) : '';
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Add New Certificate', 'gold-cert-verifier' ); ?></h1>

			<?php if ( $error === 'missing_fields' ) : ?>
				<div class="notice notice-error"><p><?php esc_html_e( 'Please fill in all required fields.', 'gold-cert-verifier' ); ?></p></div>
			<?php elseif ( ! empty( $error ) ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( urldecode( $error ) ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="gcv_add_certificate">
				<?php wp_nonce_field( 'gcv_add_cert_nonce' ); ?>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="certificate_no"><?php esc_html_e( 'Certificate No.', 'gold-cert-verifier' ); ?> <span style="color:red;">*</span></label></th>
						<td>
							<input type="text" id="certificate_no" name="certificate_no" class="regular-text" required placeholder="e.g. U0302258">
							<p class="description"><?php esc_html_e( 'Unique certificate serial number. This will be part of the verification URL.', 'gold-cert-verifier' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="item_code"><?php esc_html_e( 'Item Code', 'gold-cert-verifier' ); ?> <span style="color:red;">*</span></label></th>
						<td>
							<input type="text" id="item_code" name="item_code" class="regular-text" required placeholder="e.g. GCB460">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="product_name"><?php esc_html_e( 'Product Name', 'gold-cert-verifier' ); ?> <span style="color:red;">*</span></label></th>
						<td>
							<input type="text" id="product_name" name="product_name" class="regular-text" required placeholder="e.g. Gold Cast Bar">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="metal"><?php esc_html_e( 'Metal', 'gold-cert-verifier' ); ?></label></th>
						<td>
							<input type="text" id="metal" name="metal" class="regular-text" value="Gold">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="weight"><?php esc_html_e( 'Weight (Gram)', 'gold-cert-verifier' ); ?> <span style="color:red;">*</span></label></th>
						<td>
							<input type="number" id="weight" name="weight" class="regular-text" step="0.001" min="0" required placeholder="e.g. 100">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="fineness"><?php esc_html_e( 'Fineness', 'gold-cert-verifier' ); ?></label></th>
						<td>
							<input type="text" id="fineness" name="fineness" class="regular-text" value="999.9" placeholder="e.g. 999.9">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="cert_date"><?php esc_html_e( 'Certification Date', 'gold-cert-verifier' ); ?> <span style="color:red;">*</span></label></th>
						<td>
							<input type="date" id="cert_date" name="cert_date" value="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>" required>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="origin"><?php esc_html_e( 'Cast Bar Origin', 'gold-cert-verifier' ); ?></label></th>
						<td>
							<input type="text" id="origin" name="origin" class="regular-text" value="Malaysia">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="assayer"><?php esc_html_e( 'Certified Assayer', 'gold-cert-verifier' ); ?> <span style="color:red;">*</span></label></th>
						<td>
							<input type="text" id="assayer" name="assayer" class="regular-text" required placeholder="e.g. Your Company Sdn Bhd (123456-K)">
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="image_url"><?php esc_html_e( 'Product Image', 'gold-cert-verifier' ); ?></label></th>
						<td>
							<input type="hidden" id="image_url" name="image_url" value="">
							<input type="text" id="image_url_display" class="regular-text" placeholder="<?php esc_attr_e( 'Click Upload to choose an image', 'gold-cert-verifier' ); ?>" readonly>
							<button type="button" class="button" id="upload_image_btn"><?php esc_html_e( 'Upload / Choose Image', 'gold-cert-verifier' ); ?></button>
							<div id="image_preview" style="margin-top:10px;"></div>
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Add Certificate', 'gold-cert-verifier' ), 'primary large' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render Settings page
	 */
	public function render_settings_page() {
		$phone   = get_option( 'gcv_company_phone', '' );
		$email   = get_option( 'gcv_company_email', '' );
		$website = get_option( 'gcv_company_website', '' );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Gold Certificate Settings', 'gold-cert-verifier' ); ?></h1>

			<?php if ( isset( $_GET['gcv_saved'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved!', 'gold-cert-verifier' ); ?></p></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['gcv_flushed'] ) ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Rewrite rules flushed! The /cert/ URL should now work.', 'gold-cert-verifier' ); ?></p></div>
		<?php endif; ?>
		<?php if ( isset( $_GET['gcv_repaired'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Database table repaired/created successfully!', 'gold-cert-verifier' ); ?></p></div>
			<?php endif; ?>

			<!-- Company Info Settings -->
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="gcv_save_settings">
				<?php wp_nonce_field( 'gcv_settings_nonce' ); ?>

				<h2><?php esc_html_e( 'Company Contact Information', 'gold-cert-verifier' ); ?></h2>
				<p><?php esc_html_e( 'This information is displayed at the bottom of every certificate page.', 'gold-cert-verifier' ); ?></p>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="company_phone"><?php esc_html_e( 'Phone Number', 'gold-cert-verifier' ); ?></label></th>
						<td><input type="tel" id="company_phone" name="company_phone" class="regular-text" value="<?php echo esc_attr( $phone ); ?>" placeholder="+60 3-XXXX XXXX"></td>
					</tr>
					<tr>
						<th scope="row"><label for="company_email"><?php esc_html_e( 'Email Address', 'gold-cert-verifier' ); ?></label></th>
						<td><input type="email" id="company_email" name="company_email" class="regular-text" value="<?php echo esc_attr( $email ); ?>" placeholder="info@yourdomain.com"></td>
					</tr>
					<tr>
						<th scope="row"><label for="company_website"><?php esc_html_e( 'Website URL', 'gold-cert-verifier' ); ?></label></th>
						<td><input type="url" id="company_website" name="company_website" class="regular-text" value="<?php echo esc_attr( $website ); ?>" placeholder="https://yourdomain.com"></td>
					</tr>
				</table>

				<?php submit_button( __( 'Save Settings', 'gold-cert-verifier' ), 'primary' ); ?>
			</form>

			<hr>

			<!-- Database Tools -->
			<h2><?php esc_html_e( 'Database Tools', 'gold-cert-verifier' ); ?></h2>
			<p><?php esc_html_e( 'If you see a "table doesn\'t exist" error, click the button below to create or repair the database table.', 'gold-cert-verifier' ); ?></p>

			<?php
			$table_status = GCV_DB::table_exists()
				? '<span style="color:green;font-weight:bold;">&#10003; ' . esc_html__( 'Table exists', 'gold-cert-verifier' ) . '</span>'
				: '<span style="color:red;font-weight:bold;">&#10007; ' . esc_html__( 'Table NOT found', 'gold-cert-verifier' ) . '</span>';
			?>
			<p><?php esc_html_e( 'Table status:', 'gold-cert-verifier' ); ?> <?php echo wp_kses_post( $table_status ); ?></p>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="gcv_repair_db">
				<?php wp_nonce_field( 'gcv_repair_db_nonce' ); ?>
				<?php submit_button( __( 'Create / Repair Database Table', 'gold-cert-verifier' ), 'secondary' ); ?>
		</form>

		<hr>

		<h2><?php esc_html_e( 'URL / Permalink Tools', 'gold-cert-verifier' ); ?></h2>
		<p><?php esc_html_e( 'If the certificate URL (e.g. /cert/U0302258) returns a 404 error, click the button below to flush and rebuild WordPress rewrite rules.', 'gold-cert-verifier' ); ?></p>
		<p><strong><?php esc_html_e( 'Certificate URL format:', 'gold-cert-verifier' ); ?></strong> <code><?php echo esc_html( home_url( '/cert/{CERTIFICATE_NO}' ) ); ?></code></p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="gcv_flush_rules">
			<?php wp_nonce_field( 'gcv_flush_rules_nonce' ); ?>
			<?php submit_button( __( 'Flush Rewrite Rules (Fix 404 on /cert/ URLs)', 'gold-cert-verifier' ), 'primary' ); ?>
			</form>
		</div>
		<?php
	}
}
