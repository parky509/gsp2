<?php
/**
 * Plugin Name: GSP2 Investment Dashboard
 * Plugin URI: https://globalswiftpay2.com
 * Description: Complete investment dashboard with admin approval workflows and glass morphism UI
 * Version: 2.0.0
 * Author: GlobalSwiftPay2
 * Author URI: https://globalswiftpay2.com
 * Text Domain: gsp2
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('GSP2_VERSION', '2.0.0');
define('GSP2_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GSP2_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GSP2_PLUGIN_FILE', __FILE__);

/**
 * Plugin activation
 */
function gsp2_activate_plugin() {
    require_once GSP2_PLUGIN_DIR . 'includes/class-gsp2-activator.php';
    GSP2_Activator::activate();
}
register_activation_hook(__FILE__, 'gsp2_activate_plugin');

/**
 * Plugin deactivation
 */
function gsp2_deactivate_plugin() {
    // Clean up if needed
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'gsp2_deactivate_plugin');

/**
 * Initialize the plugin after WordPress loads
 */
function gsp2_init_plugin() {
    // Load main plugin class
    require_once GSP2_PLUGIN_DIR . 'includes/class-gsp2-plugin.php';
    
    // Initialize plugin
    GSP2_Plugin::get_instance();
}
add_action('plugins_loaded', 'gsp2_init_plugin');
