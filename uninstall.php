<?php
/**
 * Uninstall: remove options and drop clicks table.
 *
 * @package ExternalLinkTracker
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}elt_clicks" );

$options = $wpdb->get_col( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'elt_%'" );
if ( ! empty( $options ) ) {
	foreach ( $options as $option_name ) {
		delete_option( $option_name );
	}
}
