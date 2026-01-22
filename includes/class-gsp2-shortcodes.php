<?php
/**
 * Shortcodes Class
 *
 * Handles frontend shortcodes for user dashboard
 *
 * @package GlobalSwiftPay2_Investment_Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSP2_Shortcodes {
    
    private $user_handler;
    private $transaction_handler;
    private $db_handler;
    
    public function __construct() {
        $this->user_handler = new GSP2_User();
        $this->transaction_handler = new GSP2_Transaction();
        $this->db_handler = new GSP2_Database();
        
        add_shortcode('gsp2_dashboard', array($this, 'render_dashboard'));
        add_shortcode('gsp2_balance', array($this, 'render_balance'));
    }
    
    /**
     * Render user dashboard
     */
    public function render_dashboard($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please <a href="' . wp_login_url(get_permalink()) . '">login</a> to view your dashboard.</p>';
        }
        
        $user_id = get_current_user_id();
        $user = wp_get_current_user();
        $balance = $this->user_handler->get_balance($user_id);
        $transactions = $this->user_handler->get_transactions($user_id, 20);
        $settings = $this->db_handler->get_all_settings();
        
        ob_start();
        include GSP2_PLUGIN_DIR . 'public/views/dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * Render balance widget
     */
    public function render_balance($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please login to view your balance.</p>';
        }
        
        $user_id = get_current_user_id();
        $balance = $this->user_handler->get_balance($user_id);
        
        return sprintf(
            '<div class="gsp2-balance-widget">
                <span class="gsp2-balance-label">Wallet Balance:</span>
                <span class="gsp2-balance-amount">$%s</span>
            </div>',
            number_format($balance, 2)
        );
    }
    
    /**
     * Handle AJAX - Submit deposit request
     */
    public function ajax_submit_deposit() {
        check_ajax_referer('gsp2_user_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Please login');
        }
        
        $user_id = get_current_user_id();
        
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $amount = floatval($_POST['amount']);
        
        // Create deposit transaction record
        $this->user_handler->add_transaction(
            $user_id,
            'deposit',
            $amount,
            'pending',
            'Deposit request submitted'
        );
        
        $email_handler = new GSP2_Email();
        $email_handler->send_deposit_confirmation($user_id, $amount);
        
        wp_send_json_success('Deposit request submitted successfully');
    }
    
    /**
     * Handle AJAX - Submit add balance request
     */
    public function ajax_add_balance() {
        check_ajax_referer('gsp2_user_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Please login');
        }
        
        $user_id = get_current_user_id();
        
        // Handle file upload
        if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('Please upload a valid receipt');
        }
        
        $upload = wp_handle_upload($_FILES['receipt'], array('test_form' => false));
        
        if (isset($upload['error'])) {
            wp_send_json_error($upload['error']);
        }
        
        $data = array(
            'sender_name' => sanitize_text_field($_POST['sender_name']),
            'sender_email' => sanitize_email($_POST['sender_email'])
        );
        
        $result = $this->transaction_handler->create_add_balance_request(
            $user_id,
            $data,
            $upload['file']
        );
        
        if ($result) {
            wp_send_json_success('Add balance request submitted successfully');
        } else {
            wp_send_json_error('Failed to submit request');
        }
    }
    
    /**
     * Handle AJAX - Submit transfer request
     */
    public function ajax_transfer() {
        check_ajax_referer('gsp2_user_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Please login');
        }
        
        $user_id = get_current_user_id();
        $amount = floatval($_POST['amount']);
        
        // Check if user has sufficient balance
        $balance = $this->user_handler->get_balance($user_id);
        if ($balance < $amount) {
            wp_send_json_error('Insufficient balance');
        }
        
        $data = array(
            'recipient_email' => sanitize_email($_POST['recipient_email']),
            'amount' => $amount,
            'token_code' => sanitize_text_field($_POST['token_code'])
        );
        
        $result = $this->transaction_handler->create_transfer_request($user_id, $data);
        
        if ($result) {
            wp_send_json_success('Transfer request submitted for admin approval');
        } else {
            wp_send_json_error('Failed to submit transfer request');
        }
    }
    
    /**
     * Handle AJAX - Submit withdrawal request
     */
    public function ajax_withdraw() {
        check_ajax_referer('gsp2_user_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Please login');
        }
        
        $user_id = get_current_user_id();
        $amount = floatval($_POST['amount']);
        
        // Check if user has sufficient balance
        $balance = $this->user_handler->get_balance($user_id);
        if ($balance < $amount) {
            wp_send_json_error('Insufficient balance');
        }
        
        $data = array(
            'amount' => $amount,
            'details' => sanitize_textarea_field($_POST['details'])
        );
        
        $result = $this->transaction_handler->create_withdrawal_request($user_id, $data);
        
        if ($result) {
            wp_send_json_success('Withdrawal request submitted for admin approval');
        } else {
            wp_send_json_error('Failed to submit withdrawal request');
        }
    }
    
    /**
     * Handle AJAX - Submit conversion request
     */
    public function ajax_convert() {
        check_ajax_referer('gsp2_user_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Please login');
        }
        
        $user_id = get_current_user_id();
        $amount = floatval($_POST['amount']);
        $type = sanitize_text_field($_POST['conversion_type']); // 'btc', 'usdt', 'bank'
        
        // Check if user has sufficient balance
        $balance = $this->user_handler->get_balance($user_id);
        if ($balance < $amount) {
            wp_send_json_error('Insufficient balance');
        }
        
        $data = array(
            'email' => sanitize_email($_POST['email']),
            'amount' => $amount,
            'security_phrase' => sanitize_text_field($_POST['security_phrase'])
        );
        
        // Add type-specific fields
        if ($type === 'btc') {
            $data['btc_address'] = sanitize_text_field($_POST['btc_address']);
        } elseif ($type === 'usdt') {
            $data['usdt_address'] = sanitize_text_field($_POST['usdt_address']);
        } elseif ($type === 'bank') {
            $data['bank_name'] = sanitize_text_field($_POST['bank_name']);
            $data['account_name'] = sanitize_text_field($_POST['account_name']);
            $data['account_number'] = sanitize_text_field($_POST['account_number']);
            $data['routing_code'] = sanitize_text_field($_POST['routing_code']);
            $data['bank_address'] = sanitize_textarea_field($_POST['bank_address']);
            $data['country'] = sanitize_text_field($_POST['country']);
        }
        
        $result = $this->transaction_handler->create_conversion_request($user_id, $data, $type);
        
        if ($result) {
            wp_send_json_success('Conversion request submitted successfully');
        } else {
            wp_send_json_error('Failed to submit conversion request');
        }
    }
}
