<?php
/**
 * Security Class - Handles certificate security and anti-tampering features
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GCV_Security {

	/**
	 * Generate a unique verification token for a certificate
	 */
	public static function generate_verification_token( $certificate_no ) {
		$token = hash( 'sha256', $certificate_no . wp_salt() . time() );
		return substr( $token, 0, 32 );
	}

	/**
	 * Verify a certificate token
	 */
	public static function verify_token( $certificate_no, $token ) {
		$stored_token = get_transient( 'gcv_token_' . $certificate_no );

		if ( ! $stored_token ) {
			return false;
		}

		return hash_equals( $stored_token, $token );
	}

	/**
	 * Rate limiting - prevent brute force verification attempts
	 */
	public static function check_rate_limit( $certificate_no ) {
		$ip = self::get_client_ip();
		$key = 'gcv_rate_limit_' . $ip . '_' . $certificate_no;
		$attempts = get_transient( $key );

		if ( $attempts === false ) {
			$attempts = 0;
		}

		// Allow 10 requests per minute
		if ( $attempts >= 10 ) {
			return new WP_Error( 'rate_limit_exceeded', __( 'Too many verification attempts. Please try again later.', 'gold-cert-verifier' ) );
		}

		// Increment attempts and set 1 minute expiry
		set_transient( $key, $attempts + 1, MINUTE_IN_SECONDS );

		return true;
	}

	/**
	 * Get client IP address
	 */
	public static function get_client_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return sanitize_text_field( $ip );
	}

	/**
	 * Log certificate access for audit trail
	 */
	public static function log_certificate_access( $certificate_no ) {
		$log_data = array(
			'certificate_no' => $certificate_no,
			'ip_address'     => self::get_client_ip(),
			'user_agent'     => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ),
			'timestamp'      => current_time( 'mysql' ),
		);

		// Store in transient for 30 days
		$logs = get_option( 'gcv_access_logs', array() );
		$logs[] = $log_data;

		// Keep only last 1000 entries
		if ( count( $logs ) > 1000 ) {
			$logs = array_slice( $logs, -1000 );
		}

		update_option( 'gcv_access_logs', $logs );
	}

	/**
	 * Validate certificate code format
	 */
	public static function validate_certificate_code( $code ) {
		// Allow alphanumeric, hyphens, and underscores
		if ( ! preg_match( '/^[a-zA-Z0-9_-]{1,50}$/', $code ) ) {
			return new WP_Error( 'invalid_format', __( 'Invalid certificate code format.', 'gold-cert-verifier' ) );
		}

		return true;
	}

	/**
	 * Check if certificate is suspended
	 */
	public static function is_certificate_active( $certificate ) {
		if ( $certificate->status !== 'active' ) {
			return new WP_Error( 'certificate_suspended', __( 'This certificate has been suspended.', 'gold-cert-verifier' ) );
		}

		return true;
	}

	/**
	 * Add watermark to certificate pages
	 */
	public static function add_watermark() {
		?>
		<style>
			.gcv-watermark {
				position: fixed;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%) rotate(-45deg);
				font-size: 120px;
				color: rgba(212, 175, 55, 0.1);
				font-weight: bold;
				z-index: 0;
				pointer-events: none;
				white-space: nowrap;
				font-family: Arial, sans-serif;
			}

			.gcv-container {
				position: relative;
				z-index: 1;
			}

			@media print {
				.gcv-watermark {
					display: none;
				}
			}
		</style>
		<div class="gcv-watermark"><?php esc_html_e( 'VERIFIED', 'gold-cert-verifier' ); ?></div>
		<?php
	}

	/**
	 * Disable right-click and copy on certificate pages
	 */
	public static function disable_copy_protection() {
		?>
		<script>
			// Disable right-click context menu
			document.addEventListener('contextmenu', function(e) {
				// Allow right-click on links and buttons
				if (e.target.tagName !== 'A' && e.target.tagName !== 'BUTTON') {
					e.preventDefault();
				}
			});

			// Disable text selection copy
			document.addEventListener('copy', function(e) {
				// Allow copy but add watermark text
				const selection = window.getSelection();
				const text = selection.toString();
				
				if (text.length > 0) {
					const watermark = '\n\n--- Certificate copied from: ' + window.location.href + ' ---';
					e.clipboardData.setData('text/plain', text + watermark);
					e.preventDefault();
				}
			});

			// Disable keyboard shortcuts for copy
			document.addEventListener('keydown', function(e) {
				if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'C')) {
					// Allow copy but it will be handled above
				}
			});
		</script>
		<?php
	}

	/**
	 * Get certificate access logs
	 */
	public static function get_access_logs( $certificate_no = null, $limit = 100 ) {
		$logs = get_option( 'gcv_access_logs', array() );

		if ( ! empty( $certificate_no ) ) {
			$logs = array_filter( $logs, function( $log ) use ( $certificate_no ) {
				return $log['certificate_no'] === $certificate_no;
			});
		}

		// Return most recent first
		$logs = array_reverse( $logs );

		return array_slice( $logs, 0, $limit );
	}

	/**
	 * Clear old access logs
	 */
	public static function cleanup_old_logs( $days = 90 ) {
		$logs = get_option( 'gcv_access_logs', array() );
		$cutoff_time = strtotime( "-{$days} days" );

		$logs = array_filter( $logs, function( $log ) use ( $cutoff_time ) {
			$log_time = strtotime( $log['timestamp'] );
			return $log_time > $cutoff_time;
		});

		update_option( 'gcv_access_logs', array_values( $logs ) );
	}

	/**
	 * Generate certificate hash for integrity verification
	 */
	public static function generate_certificate_hash( $certificate ) {
		$data = $certificate->certificate_no . 
				$certificate->item_code . 
				$certificate->product_name . 
				$certificate->weight . 
				$certificate->fineness . 
				$certificate->cert_date;

		return hash( 'sha256', $data );
	}

	/**
	 * Verify certificate integrity
	 */
	public static function verify_certificate_integrity( $certificate ) {
		$current_hash = self::generate_certificate_hash( $certificate );
		$stored_hash = get_option( 'gcv_cert_hash_' . $certificate->id );

		if ( empty( $stored_hash ) ) {
			// First time - store hash
			update_option( 'gcv_cert_hash_' . $certificate->id, $current_hash );
			return true;
		}

		return hash_equals( $stored_hash, $current_hash );
	}
}
