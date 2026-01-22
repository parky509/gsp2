<?php
/**
 * Admin Panel Class
 *
 * Handles admin dashboard pages and functionality
 *
 * @package GlobalSwiftPay2_Investment_Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSP2_Admin {
    
    private $db_handler;
    private $transaction_handler;
    private $user_handler;
    
    public function __construct() {
        $this->db_handler = new GSP2_Database();
        $this->transaction_handler = new GSP2_Transaction();
        $this->user_handler = new GSP2_User();
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $settings = $this->db_handler->get_all_settings();
        
        include GSP2_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    /**
     * Render add balance requests page
     */
    public function render_add_balance_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $requests = $this->transaction_handler->get_pending_requests('add_balance');
        
        include GSP2_PLUGIN_DIR . 'admin/views/add-balance-requests.php';
    }
    
    /**
     * Render transfer requests page
     */
    public function render_transfer_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $requests = $this->transaction_handler->get_pending_requests('transfer');
        
        include GSP2_PLUGIN_DIR . 'admin/views/transfer-requests.php';
    }
    
    /**
     * Render withdrawal requests page
     */
    public function render_withdrawal_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $requests = $this->transaction_handler->get_pending_requests('withdrawal');
        
        include GSP2_PLUGIN_DIR . 'admin/views/withdrawal-requests.php';
    }
    
    /**
     * Render conversion requests page
     */
    public function render_conversion_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $requests = $this->transaction_handler->get_pending_requests('conversion');
        
        include GSP2_PLUGIN_DIR . 'admin/views/conversion-requests.php';
    }
    
    /**
     * Render users page
     */
    public function render_users_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $users = $this->user_handler->get_all_users_with_balances();
        
        include GSP2_PLUGIN_DIR . 'admin/views/users.php';
    }
    
    /**
     * Handle settings update
     */
    public function update_settings() {
        check_ajax_referer('gsp2_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $account_number = sanitize_text_field($_POST['account_number']);
        $btc_address = sanitize_text_field($_POST['btc_address']);
        $usdt_address = sanitize_text_field($_POST['usdt_address']);
        
        $this->db_handler->update_setting('account_number', $account_number);
        $this->db_handler->update_setting('btc_address', $btc_address);
        $this->db_handler->update_setting('usdt_address', $usdt_address);
        
        wp_send_json_success('Settings updated successfully');
    }
    
    /**
     * Handle request approval/decline
     */
    public function process_request() {
        check_ajax_referer('gsp2_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $request_id = intval($_POST['request_id']);
        $type = sanitize_text_field($_POST['type']);
        $action = sanitize_text_field($_POST['action']); // 'approve' or 'decline'
        $admin_note = isset($_POST['admin_note']) ? sanitize_textarea_field($_POST['admin_note']) : '';
        
        $status = ($action === 'approve') ? 'approved' : 'declined';
        
        $result = $this->transaction_handler->update_request_status(
            $request_id,
            $type,
            $status,
            $admin_note
        );
        
        if ($result) {
            wp_send_json_success('Request ' . $status . ' successfully');
        } else {
            wp_send_json_error('Failed to process request');
        }
    }
}
