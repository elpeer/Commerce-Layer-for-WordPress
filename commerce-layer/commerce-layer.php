<?php
/**
 * Plugin Name: Commerce Layer
 * Plugin URI: https://example.com/commerce-layer
 * Description: הוספת יכולות קומרס מלאות לתוכן קיים - מחיר, וריאציות, סל, תשלום וניהול הזמנות
 * Version: 1.0.8
 * Author: Commerce Layer Team
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: commerce-layer
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'CL_VERSION', '1.0.8' );
define( 'CL_PLUGIN_FILE', __FILE__ );
define( 'CL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'CL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoloader for plugin classes
 */
spl_autoload_register( function ( $class ) {
    // Check if class starts with CL_ prefix
    if ( strpos( $class, 'CL_' ) !== 0 ) {
        return;
    }

    // Convert class name to file name
    $class_name = strtolower( str_replace( '_', '-', substr( $class, 3 ) ) );

    // Define possible paths
    $paths = array(
        CL_PLUGIN_DIR . 'includes/class-' . $class_name . '.php',
        CL_PLUGIN_DIR . 'admin/class-' . $class_name . '.php',
        CL_PLUGIN_DIR . 'public/class-' . $class_name . '.php',
    );

    foreach ( $paths as $path ) {
        if ( file_exists( $path ) ) {
            require_once $path;
            return;
        }
    }
});

/**
 * Plugin activation hook
 */
function cl_activate() {
    require_once CL_PLUGIN_DIR . 'includes/class-activator.php';
    CL_Activator::activate();
}
register_activation_hook( __FILE__, 'cl_activate' );

/**
 * Plugin deactivation hook
 */
function cl_deactivate() {
    require_once CL_PLUGIN_DIR . 'includes/class-deactivator.php';
    CL_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'cl_deactivate' );

/**
 * Initialize the plugin
 */
function cl_init() {
    // Load text domain for translations
    load_plugin_textdomain( 'commerce-layer', false, dirname( CL_PLUGIN_BASENAME ) . '/languages' );

    // Initialize main plugin class
    $plugin = CL_Core::get_instance();
    $plugin->run();
}
add_action( 'plugins_loaded', 'cl_init' );

/**
 * WP-CLI commands
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    // Future CLI commands can be added here
}
