<?php
/**
 * Plugin Name:       Minidoka Pricing Widget
 * Plugin URI:        https://minidokamemorial.com/ (Update with actual client site)
 * Description:       Provides a custom post type and AJAX search functionality for displaying medical procedure pricing. Designed for use with ThemeCo Pro/Cornerstone.
 * Version:           1.0.0
 * Author:            Your Agency/Developer Name
 * Author URI:        https://youragency.com/ (Update with your site)
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       minidoka-pricing-widget
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Define Constants
define( 'MPW_VERSION', '1.0.0' );
define( 'MPW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MPW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include required files
require_once MPW_PLUGIN_DIR . 'includes/cpt-setup.php';
require_once MPW_PLUGIN_DIR . 'includes/acf-fields.php'; // Requires ACF plugin to be active
require_once MPW_PLUGIN_DIR . 'includes/ajax-handler.php';
require_once MPW_PLUGIN_DIR . 'includes/data-import.php'; // Contains placeholder/instructions

/**
 * Enqueue frontend scripts and styles.
 */
function mpw_enqueue_scripts() {
    // Only enqueue on pages where the widget will be used (consider adding a condition later if needed)
    // if ( is_page('your-pricing-page-slug') ) { // Example condition

        wp_enqueue_script(
            'mpw-frontend-search',
            MPW_PLUGIN_URL . 'assets/js/frontend-search.js',
            array( 'jquery' ), // Dependency
            MPW_VERSION,
            true // Load in footer
        );

        // Localize script to pass AJAX URL and nonce
        wp_localize_script(
            'mpw-frontend-search',
            'mpw_ajax', // Object name in JS
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'mpw_search_nonce' ), // Create nonce
                'loading_message' => esc_html__( 'Searching...', 'minidoka-pricing-widget' ),
                'no_results_message' => esc_html__( 'No matching procedures found.', 'minidoka-pricing-widget' ),
                'error_message' => esc_html__( 'An error occurred. Please try again.', 'minidoka-pricing-widget' ),
            )
        );

        // Optionally enqueue basic CSS if needed (though Cornerstone should handle most styling)
        // wp_enqueue_style(
        //     'mpw-frontend-styles',
        //     MPW_PLUGIN_URL . 'assets/css/frontend-styles.css',
        //     array(),
        //     MPW_VERSION
        // );
    // }
}
add_action( 'wp_enqueue_scripts', 'mpw_enqueue_scripts' );

wp_enqueue_style(
    'mpw-frontend-styles',
    MPW_PLUGIN_URL . 'assets/css/frontend-styles.css',
    array(),
    MPW_VERSION
);

/**
 * Activation hook: Flush rewrite rules for CPT.
 */
function mpw_activate() {
	// Register CPT first
	mpw_register_pricing_item_cpt();
	// Flush rewrite rules
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'mpw_activate' );

/**
 * Deactivation hook: Flush rewrite rules.
 */
function mpw_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'mpw_deactivate' );

/**
 * Check if Advanced Custom Fields plugin is active.
 * Show an admin notice if it's not.
 */
function mpw_check_acf_dependency() {
    if ( ! class_exists('ACF') ) {
        add_action( 'admin_notices', 'mpw_acf_not_active_notice' );
    }
}
add_action( 'plugins_loaded', 'mpw_check_acf_dependency' );

function mpw_acf_not_active_notice() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e( 'The <strong>Minidoka Pricing Widget</strong> plugin requires the Advanced Custom Fields (ACF) plugin to be installed and active. Please install or activate ACF.', 'minidoka-pricing-widget' ); ?></p>
    </div>
    <?php
}

// Optional: Add shortcodes if needed for easier Cornerstone integration
// require_once MPW_PLUGIN_DIR . 'includes/shortcodes.php'; // Uncomment if you create this file
