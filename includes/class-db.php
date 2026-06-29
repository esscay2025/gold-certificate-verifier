<?php
/**
 * Database Class - Handles table creation and CRUD operations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GCV_DB {

	/**
	 * Create the custom database table for certificates.
	 *
	 * Uses dbDelta() which is safe to call multiple times — it only
	 * creates or alters the table, never drops existing data.
	 */
	public static function create_table() {
		global $wpdb;

		$table_name      = $wpdb->prefix . GCV_TABLE_NAME;
		$charset_collate = $wpdb->get_charset_collate();

		/*
		 * IMPORTANT dbDelta() formatting rules:
		 *  - Two spaces before each field definition line.
		 *  - PRIMARY KEY must be on its own line.
		 *  - No trailing comma after the last KEY line.
		 */
		$sql = "CREATE TABLE {$table_name} (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  certificate_no varchar(50) NOT NULL DEFAULT '',
  item_code varchar(50) NOT NULL DEFAULT '',
  product_name varchar(255) NOT NULL DEFAULT '',
  metal varchar(50) NOT NULL DEFAULT 'Gold',
  weight decimal(10,3) NOT NULL DEFAULT '0.000',
  fineness varchar(20) NOT NULL DEFAULT '999.9',
  cert_date date NOT NULL,
  origin varchar(100) NOT NULL DEFAULT 'Malaysia',
  assayer varchar(255) NOT NULL DEFAULT '',
  image_url text,
  gallery_urls longtext,
  status varchar(20) NOT NULL DEFAULT 'active',
  created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY  (id),
  UNIQUE KEY certificate_no (certificate_no),
  KEY status (status)
) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$result = dbDelta( $sql );

		// Log result for debugging (visible in Tools > Site Health > Info)
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'GCV dbDelta result: ' . print_r( $result, true ) );
			if ( ! empty( $wpdb->last_error ) ) {
				error_log( 'GCV dbDelta last_error: ' . $wpdb->last_error );
			}
		}

		return $result;
	}

	/**
	 * Check whether the table exists in the database.
	 */
	public static function table_exists() {
		global $wpdb;
		$table_name = $wpdb->prefix . GCV_TABLE_NAME;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
		return ( $exists === $table_name );
	}

	/**
	 * Insert a new certificate.
	 *
	 * @param array $data Associative array of certificate fields.
	 * @return int|WP_Error Inserted row ID on success, WP_Error on failure.
	 */
	public static function insert_certificate( $data ) {
		global $wpdb;

		// Ensure table exists before attempting insert
		if ( ! self::table_exists() ) {
			self::create_table();
			update_option( 'gcv_db_version', GCV_DB_VERSION );
		}

		$table_name = $wpdb->prefix . GCV_TABLE_NAME;

		// Sanitize data
		$data = self::sanitize_certificate_data( $data );

		$result = $wpdb->insert(
			$table_name,
			$data,
			array(
				'%s', // certificate_no
				'%s', // item_code
				'%s', // product_name
				'%s', // metal
				'%f', // weight
				'%s', // fineness
				'%s', // cert_date
				'%s', // origin
				'%s', // assayer
				'%s', // image_url
				'%s', // gallery_urls (JSON)
				'%s', // status
			)
		);

		if ( false === $result ) {
			return new WP_Error( 'db_insert_error', $wpdb->last_error );
		}

		return $wpdb->insert_id;
	}

	/**
	 * Update an existing certificate.
	 *
	 * @param string $certificate_no Certificate number to update.
	 * @param array  $data           New field values.
	 * @return true|WP_Error
	 */
	public static function update_certificate( $certificate_no, $data ) {
		global $wpdb;
		$table_name = $wpdb->prefix . GCV_TABLE_NAME;

		$data = self::sanitize_certificate_data( $data );
		unset( $data['certificate_no'] ); // Never update the PK

		$result = $wpdb->update(
			$table_name,
			$data,
			array( 'certificate_no' => sanitize_text_field( $certificate_no ) ),
			null, // let wpdb infer formats from $data values
			array( '%s' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_update_error', $wpdb->last_error );
		}

		return true;
	}

	/**
	 * Get a single certificate by its certificate number.
	 *
	 * @param string $certificate_no
	 * @return object|WP_Error
	 */
	public static function get_certificate( $certificate_no ) {
		global $wpdb;
		$table_name = $wpdb->prefix . GCV_TABLE_NAME;

		$certificate_no = sanitize_text_field( $certificate_no );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM `{$table_name}` WHERE certificate_no = %s LIMIT 1",
				$certificate_no
			)
		);

		if ( null === $result ) {
			return new WP_Error( 'not_found', 'Certificate not found.' );
		}

		// Decode gallery URLs stored as JSON
		if ( ! empty( $result->gallery_urls ) ) {
			$decoded = json_decode( $result->gallery_urls, true );
			$result->gallery_urls = is_array( $decoded ) ? $decoded : array();
		} else {
			$result->gallery_urls = array();
		}

		return $result;
	}

	/**
	 * Get all certificates with optional search and pagination.
	 *
	 * @param int    $page     Current page number (1-based).
	 * @param int    $per_page Rows per page.
	 * @param string $status   Filter by status.
	 * @param string $search   Optional search term.
	 * @return array
	 */
	public static function get_certificates( $page = 1, $per_page = 20, $status = '', $search = '' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . GCV_TABLE_NAME;

		$offset = ( absint( $page ) - 1 ) * absint( $per_page );
		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $status ) ) {
			$where[]  = 'status = %s';
			$values[] = sanitize_text_field( $status );
		}

		if ( ! empty( $search ) ) {
			$where[]  = '(certificate_no LIKE %s OR item_code LIKE %s OR product_name LIKE %s)';
			$like     = '%' . $wpdb->esc_like( sanitize_text_field( $search ) ) . '%';
			$values[] = $like;
			$values[] = $like;
			$values[] = $like;
		}

		$where_sql = implode( ' AND ', $where );
		$values[]  = $per_page;
		$values[]  = $offset;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM `{$table_name}` WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$values
			)
		);

		return $results ? $results : array();
	}

	/**
	 * Count total certificates.
	 *
	 * @param string $status Filter by status.
	 * @param string $search Optional search term.
	 * @return int
	 */
	public static function count_certificates( $status = '', $search = '' ) {
		global $wpdb;
		$table_name = $wpdb->prefix . GCV_TABLE_NAME;

		$where  = array( '1=1' );
		$values = array();

		if ( ! empty( $status ) ) {
			$where[]  = 'status = %s';
			$values[] = sanitize_text_field( $status );
		}

		if ( ! empty( $search ) ) {
			$where[]  = '(certificate_no LIKE %s OR item_code LIKE %s OR product_name LIKE %s)';
			$like     = '%' . $wpdb->esc_like( sanitize_text_field( $search ) ) . '%';
			$values[] = $like;
			$values[] = $like;
			$values[] = $like;
		}

		$where_sql = implode( ' AND ', $where );

		if ( ! empty( $values ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `{$table_name}` WHERE {$where_sql}",
					$values
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table_name}`" );
		}

		return (int) $count;
	}

	/**
	 * Delete a certificate by its certificate number.
	 *
	 * @param string $certificate_no
	 * @return true|WP_Error
	 */
	public static function delete_certificate( $certificate_no ) {
		global $wpdb;
		$table_name = $wpdb->prefix . GCV_TABLE_NAME;

		$result = $wpdb->delete(
			$table_name,
			array( 'certificate_no' => sanitize_text_field( $certificate_no ) ),
			array( '%s' )
		);

		if ( false === $result ) {
			return new WP_Error( 'db_delete_error', $wpdb->last_error );
		}

		return true;
	}

	/**
	 * Sanitize all certificate input fields.
	 *
	 * @param array $data Raw input data.
	 * @return array Sanitized data.
	 */
	private static function sanitize_certificate_data( $data ) {
		$sanitized = array();

		$text_fields = array(
			'certificate_no',
			'item_code',
			'product_name',
			'metal',
			'fineness',
			'cert_date',
			'origin',
			'assayer',
			'status',
		);

		foreach ( $text_fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $data[ $field ] );
			}
		}

		if ( isset( $data['weight'] ) ) {
			$sanitized['weight'] = round( floatval( $data['weight'] ), 3 );
		}

		if ( isset( $data['image_url'] ) ) {
			$sanitized['image_url'] = esc_url_raw( $data['image_url'] );
		}

		if ( isset( $data['gallery_urls'] ) ) {
			if ( is_array( $data['gallery_urls'] ) ) {
				$sanitized['gallery_urls'] = wp_json_encode( array_map( 'esc_url_raw', $data['gallery_urls'] ) );
			} else {
				$sanitized['gallery_urls'] = sanitize_text_field( $data['gallery_urls'] );
			}
		}

		return $sanitized;
	}

	/**
	 * Import certificates from a CSV file.
	 *
	 * Expected CSV columns (with header row):
	 * certificate_no, item_code, product_name, metal, weight, fineness,
	 * cert_date, origin, assayer, image_url
	 *
	 * @param string $file_path Absolute path to the CSV file.
	 * @return array { imported: int, errors: string[] }
	 */
	public static function import_from_csv( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			return new WP_Error( 'file_not_found', 'CSV file not found.' );
		}

		$imported = 0;
		$errors   = array();

		if ( ( $handle = fopen( $file_path, 'r' ) ) !== false ) {
			// Skip header row
			fgetcsv( $handle );

			while ( ( $row = fgetcsv( $handle ) ) !== false ) {
				if ( empty( $row[0] ) ) {
					continue; // Skip empty rows
				}

				$data = array(
					'certificate_no' => $row[0] ?? '',
					'item_code'      => $row[1] ?? '',
					'product_name'   => $row[2] ?? '',
					'metal'          => $row[3] ?? 'Gold',
					'weight'         => $row[4] ?? 0,
					'fineness'       => $row[5] ?? '999.9',
					'cert_date'      => $row[6] ?? date( 'Y-m-d' ),
					'origin'         => $row[7] ?? 'Malaysia',
					'assayer'        => $row[8] ?? '',
					'image_url'      => $row[9] ?? '',
					'status'         => 'active',
				);

				$result = self::insert_certificate( $data );

				if ( is_wp_error( $result ) ) {
					$errors[] = 'Row ' . ( $imported + count( $errors ) + 2 ) . ': ' . $result->get_error_message();
				} else {
					$imported++;
				}
			}

			fclose( $handle );
		}

		return array(
			'imported' => $imported,
			'errors'   => $errors,
		);
	}
}
