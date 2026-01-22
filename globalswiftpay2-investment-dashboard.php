<?php
/**
 * Plugin Name: GlobalSwiftPay2 Investment Dashboard
 * Plugin URI: https://globalswiftpay2.com
 * Description: Comprehensive investment dashboard with deposits, withdrawals, crypto/bank conversions, P2P transfers, and admin approval workflows
 * Version: 1.0.0
 * Author: GlobalSwiftPay2
 * Author URI: https://globalswiftpay2.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gsp2-investment-dashboard
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('GSP2_VERSION', '1.0.0');
define('GSP2_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GSP2_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GSP2_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class GSP2_Investment_Dashboard {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'plugins_loaded_handler'), 10);
    }
    
    /**
     * Handler for plugins_loaded action
     */
    public function plugins_loaded_handler() {
        $this->load_textdomain();
        
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('wp_ajax_gsp2_user_action', array($this, 'handle_ajax_request'));
        add_action('wp_ajax_gsp2_admin_action', array($this, 'handle_admin_ajax'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        $includes = array(
            'includes/class-gsp2-database.php',
            'includes/class-gsp2-user.php',
            'includes/class-gsp2-transaction.php',
            'includes/class-gsp2-admin.php',
            'includes/class-gsp2-email.php',
            'includes/class-gsp2-shortcodes.php',
        );
        
        foreach ($includes as $file) {
            $filepath = GSP2_PLUGIN_DIR . $file;
            if (file_exists($filepath)) {
                require_once $filepath;
            }
        }
    }
    
    /**
     * Plugin activation - removed (now handled by global function)
     */
    
    /**
     * Plugin deactivation - removed (now handled by global function)
     */
    
    /**
     * Load plugin text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'gsp2-investment-dashboard',
            false,
            dirname(GSP2_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Register custom user roles
        $this->register_user_roles();
        
        // Initialize shortcodes
        GSP2_Shortcodes::init();
    }
    
    /**
     * Register custom user roles
     */
    private function register_user_roles() {
        // Add investor capability to subscribers
        $role = get_role('subscriber');
        if ($role) {
            $role->add_cap('gsp2_investor');
        }
        
        // Admin capabilities
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_role->add_cap('gsp2_admin');
            $admin_role->add_cap('gsp2_manage_settings');
            $admin_role->add_cap('gsp2_approve_requests');
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Investment Dashboard', 'gsp2-investment-dashboard'),
            __('Investment Dashboard', 'gsp2-investment-dashboard'),
            'gsp2_admin',
            'gsp2-dashboard',
            array($this, 'render_admin_dashboard'),
            'dashicons-chart-line',
            30
        );
        
        add_submenu_page(
            'gsp2-dashboard',
            __('Settings', 'gsp2-investment-dashboard'),
            __('Settings', 'gsp2-investment-dashboard'),
            'gsp2_manage_settings',
            'gsp2-settings',
            array('GSP2_Admin', 'render_settings_page')
        );
        
        add_submenu_page(
            'gsp2-dashboard',
            __('Add Balance Requests', 'gsp2-investment-dashboard'),
            __('Add Balance Requests', 'gsp2-investment-dashboard'),
            'gsp2_approve_requests',
            'gsp2-add-balance-requests',
            array('GSP2_Admin', 'render_add_balance_requests')
        );
        
        add_submenu_page(
            'gsp2-dashboard',
            __('Transfer Requests', 'gsp2-investment-dashboard'),
            __('Transfer Requests', 'gsp2-investment-dashboard'),
            'gsp2_approve_requests',
            'gsp2-transfer-requests',
            array('GSP2_Admin', 'render_transfer_requests')
        );
        
        add_submenu_page(
            'gsp2-dashboard',
            __('Withdrawal Requests', 'gsp2-investment-dashboard'),
            __('Withdrawal Requests', 'gsp2-investment-dashboard'),
            'gsp2_approve_requests',
            'gsp2-withdrawal-requests',
            array('GSP2_Admin', 'render_withdrawal_requests')
        );
        
        add_submenu_page(
            'gsp2-dashboard',
            __('Conversion Requests', 'gsp2-investment-dashboard'),
            __('Conversion Requests', 'gsp2-investment-dashboard'),
            'gsp2_approve_requests',
            'gsp2-conversion-requests',
            array('GSP2_Admin', 'render_conversion_requests')
        );
        
        add_submenu_page(
            'gsp2-dashboard',
            __('Users', 'gsp2-investment-dashboard'),
            __('Users', 'gsp2-investment-dashboard'),
            'gsp2_admin',
            'gsp2-users',
            array('GSP2_Admin', 'render_users_page')
        );
    }
    
    /**
     * Render admin dashboard - redirect to settings
     */
    public function render_admin_dashboard() {
        // Redirect to settings page as we don't have a separate dashboard view
        wp_redirect(admin_url('admin.php?page=gsp2-settings'));
        exit;
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'gsp2-') === false) {
            return;
        }
        
        wp_enqueue_style(
            'gsp2-admin-style',
            GSP2_PLUGIN_URL . 'assets/css/styles.css',
            array(),
            GSP2_VERSION
        );
        
        wp_enqueue_script(
            'gsp2-admin-script',
            GSP2_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            GSP2_VERSION,
            true
        );
        
        wp_localize_script('gsp2-admin-script', 'gsp2Admin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gsp2_admin_nonce'),
            'strings' => array(
                'confirm_approve' => __('Are you sure you want to approve this request?', 'gsp2-investment-dashboard'),
                'confirm_decline' => __('Are you sure you want to decline this request?', 'gsp2-investment-dashboard'),
            )
        ));
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        wp_enqueue_style(
            'gsp2-frontend-style',
            GSP2_PLUGIN_URL . 'assets/css/styles.css',
            array(),
            GSP2_VERSION
        );
        
        wp_enqueue_script(
            'gsp2-frontend-script',
            GSP2_PLUGIN_URL . 'assets/js/dashboard.js',
            array('jquery'),
            GSP2_VERSION,
            true
        );
        
        if (is_user_logged_in()) {
            wp_localize_script('gsp2-frontend-script', 'gsp2Frontend', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('gsp2_user_nonce'),
                'userId' => get_current_user_id(),
            ));
        }
    }
    
    /**
     * Handle AJAX requests
     */
    public function handle_ajax_request() {
        check_ajax_referer('gsp2_user_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('Unauthorized', 'gsp2-investment-dashboard')));
        }
        
        $action = isset($_POST['sub_action']) ? sanitize_text_field($_POST['sub_action']) : '';
        
        switch ($action) {
            case 'get_balance':
                GSP2_User::ajax_get_balance();
                break;
            case 'submit_deposit':
                GSP2_Transaction::ajax_submit_deposit();
                break;
            case 'submit_withdrawal':
                GSP2_Transaction::ajax_submit_withdrawal();
                break;
            case 'submit_transfer':
                GSP2_Transaction::ajax_submit_transfer();
                break;
            case 'submit_conversion':
                GSP2_Transaction::ajax_submit_conversion();
                break;
            case 'submit_add_balance':
                GSP2_Transaction::ajax_submit_add_balance();
                break;
            case 'get_transactions':
                GSP2_Transaction::ajax_get_transactions();
                break;
            default:
                wp_send_json_error(array('message' => __('Invalid action', 'gsp2-investment-dashboard')));
        }
    }
    
    /**
     * Handle admin AJAX requests
     */
    public function handle_admin_ajax() {
        check_ajax_referer('gsp2_admin_nonce', 'nonce');
        
        if (!current_user_can('gsp2_admin')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'gsp2-investment-dashboard')));
        }
        
        $action = isset($_POST['sub_action']) ? sanitize_text_field($_POST['sub_action']) : '';
        
        switch ($action) {
            case 'update_settings':
                GSP2_Admin::ajax_update_settings();
                break;
            case 'approve_request':
                GSP2_Admin::ajax_approve_request();
                break;
            case 'decline_request':
                GSP2_Admin::ajax_decline_request();
                break;
            case 'get_requests':
                GSP2_Admin::ajax_get_requests();
                break;
            case 'get_users':
                GSP2_Admin::ajax_get_users();
                break;
            default:
                wp_send_json_error(array('message' => __('Invalid action', 'gsp2-investment-dashboard')));
        }
    }
}

/**
 * Plugin activation hook
 */
function gsp2_activate_plugin() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-gsp2-database.php';
    GSP2_Database::create_tables();
    GSP2_Database::insert_default_data();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'gsp2_activate_plugin');

/**
 * Plugin deactivation hook
 */
function gsp2_deactivate_plugin() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'gsp2_deactivate_plugin');

/**
 * Initialize the plugin
 */
function gsp2_init() {
    return GSP2_Investment_Dashboard::get_instance();
}

// Start the plugin
gsp2_init();
