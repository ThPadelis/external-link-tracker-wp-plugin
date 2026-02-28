<?php
/**
 * Plugin Name: External Link Tracker
 * Description: Track outbound link clicks in real time. Record clicks via client-side JavaScript and analyze them with Link and Domain views.
 * Version: 1.1.0
 * Requires at least: 5.0
 * Requires PHP: 5.6
 * Author: Pantelis Theodosiou
 * Author URI: https://pantelis.theodosiou.me
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: external-link-tracker
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ELT_VERSION', '1.1.0' );
define( 'ELT_DB_VERSION', 1 );
define( 'ELT_PLUGIN_FILE', __FILE__ );
define( 'ELT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once ELT_PLUGIN_DIR . 'includes/class-elt-activator.php';
require_once ELT_PLUGIN_DIR . 'includes/class-elt-upgrader.php';
require_once ELT_PLUGIN_DIR . 'includes/class-elt-logger.php';
require_once ELT_PLUGIN_DIR . 'includes/class-elt-core.php';

register_activation_hook( __FILE__, array( 'ExternalLinkTracker\ELT_Activator', 'activate' ) );

add_action( 'init', array( 'ExternalLinkTracker\ELT_Core', 'init' ), 5 );
