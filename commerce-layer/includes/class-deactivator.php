<?php
/**
 * Plugin Deactivator
 *
 * @package CommerceLayer
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CL_Deactivator {

    /**
     * Run deactivation tasks
     */
    public static function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook( 'cl_daily_cleanup' );

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
