<?php
/**
 * REST API: register route and record clicks.
 *
 * @package ExternalLinkTracker
 */

namespace ExternalLinkTracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ELT_REST
 */
class ELT_REST {

	/**
	 * Default and allowed per-page values for reports.
	 */
	const REPORTS_PER_PAGE_DEFAULT = 20;
	const REPORTS_PER_PAGE_OPTIONS = array( 10, 20, 50, 100 );

	/** Links report: allowed orderby columns (DB/alias names). */
	const LINKS_ORDERBY_ALLOWED = array( 'link_url', 'anchor_text', 'source_url', 'source_post_id', 'click_count' );

	/** Domains report: allowed orderby columns. */
	const DOMAINS_ORDERBY_ALLOWED = array( 'domain', 'total_clicks', 'unique_links' );

	/**
	 * Register REST routes.
	 */
	public static function register_routes() {
		register_rest_route(
			'elt/v1',
			'/clicks',
			array(
				'methods'             => 'POST',
				'callback'            => array( self::class, 'record_click' ),
				'permission_callback' => '__return_true',
				'args'                => array(),
			)
		);

		register_rest_route(
			'elt/v1',
			'/reports/links',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'get_links_report' ),
				'permission_callback' => array( self::class, 'can_view_admin_reports' ),
				'args'                => array(),
			)
		);

		register_rest_route(
			'elt/v1',
			'/reports/domains',
			array(
				'methods'             => 'GET',
				'callback'            => array( self::class, 'get_domains_report' ),
				'permission_callback' => array( self::class, 'can_view_admin_reports' ),
				'args'                => array(),
			)
		);
	}

	/**
	 * Record a single click from the request body.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function record_click( $request ) {
		$body = $request->get_json_params();
		if ( ! is_array( $body ) ) {
			return new \WP_Error( 'elt_invalid_body', __( 'Invalid JSON body.', 'external-link-tracker' ), array( 'status' => 400 ) );
		}

		$link_url       = isset( $body['link_url'] ) ? esc_url_raw( $body['link_url'] ) : '';
		$anchor_text    = isset( $body['anchor_text'] ) ? sanitize_text_field( $body['anchor_text'] ) : '';
		$source_url     = isset( $body['source_url'] ) ? esc_url_raw( $body['source_url'] ) : '';
		$source_post_id = isset( $body['source_post_id'] ) ? absint( $body['source_post_id'] ) : null;

		if ( empty( $link_url ) ) {
			return new \WP_Error( 'elt_missing_link_url', __( 'Link URL is required.', 'external-link-tracker' ), array( 'status' => 400 ) );
		}

		$anchor_text = mb_substr( $anchor_text, 0, 500 );

		$domain = '';
		$parsed = wp_parse_url( $link_url );
		if ( ! empty( $parsed['host'] ) ) {
			$domain = $parsed['host'];
		}
		$domain = sanitize_text_field( $domain );
		$domain = mb_substr( $domain, 0, 255 );

		global $wpdb;
		$table = $wpdb->prefix . 'elt_clicks';
		$ok    = $wpdb->insert(
			$table,
			array(
				'link_url'       => $link_url,
				'anchor_text'    => $anchor_text,
				'source_url'     => $source_url,
				'source_post_id' => $source_post_id ? $source_post_id : null,
				'domain'         => $domain,
				'clicked_at'     => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%d', '%s', '%s' )
		);

		if ( ! $ok ) {
			return new \WP_Error( 'elt_insert_failed', __( 'Failed to record click.', 'external-link-tracker' ), array( 'status' => 500 ) );
		}

		return new \WP_REST_Response( array( 'success' => true ), 201 );
	}

	/**
	 * Check permission for admin reports endpoints.
	 *
	 * @return bool
	 */
	public static function can_view_admin_reports() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Return link-based report data.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function get_links_report( $request ) {
		global $wpdb;

		$date_range = self::get_date_range( $request );
		if ( is_wp_error( $date_range ) ) {
			return $date_range;
		}

		if ( ! ELT_Activator::table_exists() ) {
			return new \WP_REST_Response(
				array(
					'items'    => array(),
					'total'    => 0,
					'page'     => 1,
					'per_page' => self::REPORTS_PER_PAGE_DEFAULT,
					'from'     => $date_range['from'],
					'to'       => $date_range['to'],
				)
			);
		}

		$page    = self::get_page_number( $request );
		$per_page = self::get_per_page( $request );
		$offset   = ( $page - 1 ) * $per_page;
		$table    = $wpdb->prefix . 'elt_clicks';
		$where    = $wpdb->prepare( 'clicked_at >= %s AND clicked_at <= %s', $date_range['from'] . ' 00:00:00', $date_range['to'] . ' 23:59:59' );
		$orderby  = self::get_orderby( $request, self::LINKS_ORDERBY_ALLOWED, 'click_count' );
		$order    = self::get_order( $request );

		$count_sql = "SELECT COUNT(*) FROM (SELECT 1 FROM $table WHERE $where GROUP BY link_url, anchor_text, source_url, COALESCE(source_post_id, 0)) AS g";
		$total     = (int) $wpdb->get_var( $count_sql );

		$orderby_esc = sanitize_sql_orderby( $orderby . ' ' . $order );
		if ( ! $orderby_esc ) {
			$orderby_esc = 'click_count DESC';
		}

		$sql = $wpdb->prepare(
			"SELECT link_url, anchor_text, source_url, source_post_id, COUNT(*) AS click_count
			FROM $table
			WHERE $where
			GROUP BY link_url, anchor_text, source_url, COALESCE(source_post_id, 0)
			ORDER BY $orderby_esc
			LIMIT %d OFFSET %d",
			$per_page,
			$offset
		);

		$items = $wpdb->get_results( $sql, ARRAY_A );
		if ( ! is_array( $items ) ) {
			$items = array();
		}

		return new \WP_REST_Response(
			array(
				'items'    => $items,
				'total'    => $total,
				'page'     => $page,
				'per_page' => $per_page,
				'from'     => $date_range['from'],
				'to'       => $date_range['to'],
			)
		);
	}

	/**
	 * Return domain-based report data.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public static function get_domains_report( $request ) {
		global $wpdb;

		$date_range = self::get_date_range( $request );
		if ( is_wp_error( $date_range ) ) {
			return $date_range;
		}

		if ( ! ELT_Activator::table_exists() ) {
			return new \WP_REST_Response(
				array(
					'items'    => array(),
					'total'    => 0,
					'page'     => 1,
					'per_page' => self::REPORTS_PER_PAGE_DEFAULT,
					'from'     => $date_range['from'],
					'to'       => $date_range['to'],
				)
			);
		}

		$page    = self::get_page_number( $request );
		$per_page = self::get_per_page( $request );
		$offset   = ( $page - 1 ) * $per_page;
		$table    = $wpdb->prefix . 'elt_clicks';
		$where    = $wpdb->prepare( 'clicked_at >= %s AND clicked_at <= %s', $date_range['from'] . ' 00:00:00', $date_range['to'] . ' 23:59:59' );
		$orderby  = self::get_orderby( $request, self::DOMAINS_ORDERBY_ALLOWED, 'total_clicks' );
		$order    = self::get_order( $request );

		$count_sql = "SELECT COUNT(*) FROM (SELECT 1 FROM $table WHERE $where AND domain != '' GROUP BY domain) AS g";
		$total     = (int) $wpdb->get_var( $count_sql );

		$orderby_esc = sanitize_sql_orderby( $orderby . ' ' . $order );
		if ( ! $orderby_esc ) {
			$orderby_esc = 'total_clicks DESC';
		}

		$sql = $wpdb->prepare(
			"SELECT domain, COUNT(*) AS total_clicks, COUNT(DISTINCT link_url) AS unique_links
			FROM $table
			WHERE $where AND domain != ''
			GROUP BY domain
			ORDER BY $orderby_esc
			LIMIT %d OFFSET %d",
			$per_page,
			$offset
		);

		$items = $wpdb->get_results( $sql, ARRAY_A );
		if ( ! is_array( $items ) ) {
			$items = array();
		}

		return new \WP_REST_Response(
			array(
				'items'    => $items,
				'total'    => $total,
				'page'     => $page,
				'per_page' => $per_page,
				'from'     => $date_range['from'],
				'to'       => $date_range['to'],
			)
		);
	}

	/**
	 * Get validated per_page from request.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return int
	 */
	private static function get_per_page( $request ) {
		$per_page = absint( $request->get_param( 'per_page' ) );
		if ( $per_page <= 0 ) {
			return self::REPORTS_PER_PAGE_DEFAULT;
		}
		if ( ! in_array( $per_page, self::REPORTS_PER_PAGE_OPTIONS, true ) ) {
			return self::REPORTS_PER_PAGE_DEFAULT;
		}
		return $per_page;
	}

	/**
	 * Get validated orderby from request.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @param array            $allowed Allowed column names.
	 * @param string           $default Default column when missing or invalid.
	 * @return string
	 */
	private static function get_orderby( $request, $allowed, $default = '' ) {
		$orderby = sanitize_key( (string) $request->get_param( 'orderby' ) );
		if ( '' === $orderby || ! in_array( $orderby, $allowed, true ) ) {
			return ( '' !== $default && in_array( $default, $allowed, true ) ) ? $default : $allowed[ count( $allowed ) - 1 ];
		}
		return $orderby;
	}

	/**
	 * Get validated order (asc/desc).
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return string
	 */
	private static function get_order( $request ) {
		$order = strtoupper( sanitize_key( (string) $request->get_param( 'order' ) ) );
		return ( 'ASC' === $order ) ? 'ASC' : 'DESC';
	}

	/**
	 * Get and validate report date range from request.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return array|\WP_Error
	 */
	private static function get_date_range( $request ) {
		$from = sanitize_text_field( (string) $request->get_param( 'from' ) );
		$to   = sanitize_text_field( (string) $request->get_param( 'to' ) );

		if ( '' === $from || '' === $to ) {
			$to   = gmdate( 'Y-m-d' );
			$from = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		}

		if ( ! self::is_valid_ymd_date( $from ) || ! self::is_valid_ymd_date( $to ) ) {
			return new \WP_Error( 'elt_invalid_dates', __( 'Dates must use YYYY-MM-DD format.', 'external-link-tracker' ), array( 'status' => 400 ) );
		}

		if ( $from > $to ) {
			return new \WP_Error( 'elt_invalid_date_range', __( 'From date cannot be after To date.', 'external-link-tracker' ), array( 'status' => 400 ) );
		}

		return array(
			'from' => $from,
			'to'   => $to,
		);
	}

	/**
	 * Parse and sanitize page number.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return int
	 */
	private static function get_page_number( $request ) {
		$page = absint( $request->get_param( 'page' ) );
		return $page > 0 ? $page : 1;
	}

	/**
	 * Validate YYYY-MM-DD date format.
	 *
	 * @param string $date Date candidate.
	 * @return bool
	 */
	private static function is_valid_ymd_date( $date ) {
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return false;
		}

		$parsed = gmdate( 'Y-m-d', strtotime( $date ) );
		return $parsed === $date;
	}
}
