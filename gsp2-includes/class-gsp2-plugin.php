<?php
/**
 * Main Plugin Class
 */

class GSP2_Plugin {
    
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
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('init', [$this, 'register_shortcodes']);
        add_action('wp_ajax_gsp2_submit_deposit', [$this, 'ajax_submit_deposit']);
        add_action('wp_ajax_gsp2_submit_withdrawal', [$this, 'ajax_submit_withdrawal']);
        add_action('wp_ajax_gsp2_submit_transfer', [$this, 'ajax_submit_transfer']);
        add_action('wp_ajax_gsp2_submit_conversion', [$this, 'ajax_submit_conversion']);
        add_action('wp_ajax_gsp2_submit_add_balance', [$this, 'ajax_submit_add_balance']);
        add_action('wp_ajax_gsp2_update_settings', [$this, 'ajax_update_settings']);
        add_action('wp_ajax_gsp2_approve_request', [$this, 'ajax_approve_request']);
        add_action('wp_ajax_gsp2_decline_request', [$this, 'ajax_decline_request']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'GSP2 Dashboard',
            'GSP2 Dashboard',
            'manage_options',
            'gsp2-dashboard',
            [$this, 'render_settings_page'],
            'dashicons-money-alt',
            30
        );
        
        add_submenu_page(
            'gsp2-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'gsp2-dashboard',
            [$this, 'render_settings_page']
        );
        
        add_submenu_page(
            'gsp2-dashboard',
            'Add Balance Requests',
            'Add Balance',
            'manage_options',
            'gsp2-add-balance',
            [$this, 'render_add_balance_page']
        );
        
        add_submenu_page(
            'gsp2-dashboard',
            'Transfer Requests',
            'Transfers',
            'manage_options',
            'gsp2-transfers',
            [$this, 'render_transfers_page']
        );
        
        add_submenu_page(
            'gsp2-dashboard',
            'Withdrawal Requests',
            'Withdrawals',
            'manage_options',
            'gsp2-withdrawals',
            [$this, 'render_withdrawals_page']
        );
        
        add_submenu_page(
            'gsp2-dashboard',
            'Conversion Requests',
            'Conversions',
            'manage_options',
            'gsp2-conversions',
            [$this, 'render_conversions_page']
        );
        
        add_submenu_page(
            'gsp2-dashboard',
            'Users',
            'Users',
            'manage_options',
            'gsp2-users',
            [$this, 'render_users_page']
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'gsp2') === false) {
            return;
        }
        
        wp_enqueue_style('gsp2-admin-style', GSP2_PLUGIN_URL . 'assets/css/admin-style.css', [], GSP2_VERSION);
        wp_enqueue_script('gsp2-admin-script', GSP2_PLUGIN_URL . 'assets/js/admin-script.js', ['jquery'], GSP2_VERSION, true);
        
        wp_localize_script('gsp2-admin-script', 'gsp2Admin', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gsp2_admin_nonce')
        ]);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        if (!is_user_logged_in()) {
            return;
        }
        
        wp_enqueue_style('gsp2-frontend-style', GSP2_PLUGIN_URL . 'assets/css/frontend-style.css', [], GSP2_VERSION);
        wp_enqueue_script('gsp2-frontend-script', GSP2_PLUGIN_URL . 'assets/js/frontend-script.js', ['jquery'], GSP2_VERSION, true);
        
        wp_localize_script('gsp2-frontend-script', 'gsp2Data', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gsp2_user_nonce'),
            'userId' => get_current_user_id()
        ]);
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('gsp2_dashboard', [$this, 'render_dashboard_shortcode']);
        add_shortcode('gsp2_balance', [$this, 'render_balance_shortcode']);
    }
    
    /**
     * Render dashboard shortcode
     */
    public function render_dashboard_shortcode() {
        if (!is_user_logged_in()) {
            return '<p>Please log in to view your dashboard.</p>';
        }
        
        ob_start();
        include GSP2_PLUGIN_DIR . 'templates/dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * Render balance shortcode
     */
    public function render_balance_shortcode() {
        if (!is_user_logged_in()) {
            return '<p>Please log in to view your balance.</p>';
        }
        
        $balance = $this->get_user_balance(get_current_user_id());
        return '<div class="gsp2-balance">$' . number_format($balance, 2) . '</div>';
    }
    
    /**
     * Get user balance
     */
    private function get_user_balance($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp2_user_balances';
        $balance = $wpdb->get_var($wpdb->prepare("SELECT balance FROM $table WHERE user_id = %d", $user_id));
        return $balance ? floatval($balance) : 0.00;
    }
    
    // Placeholder methods for admin pages - simplified versions
    public function render_settings_page() {
        echo '<div class="wrap"><h1>GSP2 Settings</h1><p>Admin settings page</p></div>';
    }
    
    public function render_add_balance_page() {
        echo '<div class="wrap"><h1>Add Balance Requests</h1><p>Pending requests will appear here</p></div>';
    }
    
    public function render_transfers_page() {
        echo '<div class="wrap"><h1>Transfer Requests</h1><p>Pending transfers will appear here</p></div>';
    }
    
    public function render_withdrawals_page() {
        echo '<div class="wrap"><h1>Withdrawal Requests</h1><p>Pending withdrawals will appear here</p></div>';
    }
    
    public function render_conversions_page() {
        echo '<div class="wrap"><h1>Conversion Requests</h1><p>Pending conversions will appear here</p></div>';
    }
    
    public function render_users_page() {
        echo '<div class="wrap"><h1>Users</h1><p>All users will be listed here</p></div>';
    }
    
    // Placeholder AJAX handlers
    public function ajax_submit_deposit() {
        check_ajax_referer('gsp2_user_nonce', 'nonce');
        wp_send_json_success(['message' => 'Deposit submitted']);
    }
    
    public function ajax_submit_withdrawal() {
        check_ajax_referer('gsp2_user_nonce', 'nonce');
        wp_send_json_success(['message' => 'Withdrawal submitted']);
    }
    
    public function ajax_submit_transfer() {
        check_ajax_referer('gsp2_user_nonce', 'nonce');
        wp_send_json_success(['message' => 'Transfer submitted']);
    }
    
    public function ajax_submit_conversion() {
        check_ajax_referer('gsp2_user_nonce', 'nonce');
        wp_send_json_success(['message' => 'Conversion submitted']);
    }
    
    public function ajax_submit_add_balance() {
        check_ajax_referer('gsp2_user_nonce', 'nonce');
        wp_send_json_success(['message' => 'Add balance request submitted']);
    }
    
    public function ajax_update_settings() {
        check_ajax_referer('gsp2_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        wp_send_json_success(['message' => 'Settings updated']);
    }
    
    public function ajax_approve_request() {
        check_ajax_referer('gsp2_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        wp_send_json_success(['message' => 'Request approved']);
    }
    
    public function ajax_decline_request() {
        check_ajax_referer('gsp2_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }
        wp_send_json_success(['message' => 'Request declined']);
    }
}
