<?php
/**
 * Simple debug logging for External Link Tracker.
 *
 * Logs to PHP error_log when WP_DEBUG is true or when ELT_DEBUG constant is defined and true.
 * To enable: in wp-config.php set define( 'WP_DEBUG', true ); and define( 'WP_DEBUG_LOG', true );
 * Logs are written to wp-content/debug.log. Alternatively define( 'ELT_DEBUG', true ); to log
 * even when WP_DEBUG is off.
 *
 * @package ExternalLinkTracker
 */

namespace ExternalLinkTracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ELT_Logger
 */
class ELT_Logger {

	const PREFIX = '[ELT]';

	/**
	 * Check if logging is enabled.
	 *
	 * @return bool
	 */
	public static function enabled() {
		if ( defined( 'ELT_DEBUG' ) && ELT_DEBUG ) {
			return true;
		}
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	/**
	 * Write a log message.
	 *
	 * @param string $message Message to log.
	 * @param array  $context Optional context (e.g. array of variables to append).
	 */
	public static function log( $message, $context = array() ) {
		if ( ! self::enabled() ) {
			return;
		}
		$line = self::PREFIX . ' ' . $message;
		if ( ! empty( $context ) ) {
			$line .= ' ' . wp_json_encode( $context );
		}
		error_log( $line );
	}

	/**
	 * Log an error (e.g. DB error).
	 *
	 * @param string $message Error message.
	 * @param array  $context Optional context.
	 */
	public static function error( $message, $context = array() ) {
		$context['level'] = 'error';
		self::log( $message, $context );
	}
}
