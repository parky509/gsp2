<?php
/**
 * Transaction Management Class
 *
 * Handles all transaction types (deposits, withdrawals, transfers, conversions)
 *
 * @package GlobalSwiftPay2_Investment_Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSP2_Transaction {
    
    private $db;
    private $user_handler;
    
    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        $this->user_handler = new GSP2_User();
    }
    
    /**
     * Create add balance request
     */
    public function create_add_balance_request($user_id, $data, $file_path) {
        $table = $this->db->prefix . 'gsp2_add_balance_requests';
        
        $result = $this->db->insert(
            $table,
            array(
                'user_id' => $user_id,
                'sender_name' => sanitize_text_field($data['sender_name']),
                'sender_email' => sanitize_email($data['sender_email']),
                'receipt_path' => sanitize_text_field($file_path),
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );
        
        return $result ? $this->db->insert_id : false;
    }
    
    /**
     * Create transfer request
     */
    public function create_transfer_request($user_id, $data) {
        $table = $this->db->prefix . 'gsp2_transfer_requests';
        
        $result = $this->db->insert(
            $table,
            array(
                'user_id' => $user_id,
                'recipient_email' => sanitize_email($data['recipient_email']),
                'amount' => floatval($data['amount']),
                'token_code' => sanitize_text_field($data['token_code']),
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%f', '%s', '%s', '%s')
        );
        
        return $result ? $this->db->insert_id : false;
    }
    
    /**
     * Create withdrawal request
     */
    public function create_withdrawal_request($user_id, $data) {
        $table = $this->db->prefix . 'gsp2_withdrawal_requests';
        
        $result = $this->db->insert(
            $table,
            array(
                'user_id' => $user_id,
                'amount' => floatval($data['amount']),
                'withdrawal_details' => sanitize_textarea_field($data['details']),
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%f', '%s', '%s', '%s')
        );
        
        return $result ? $this->db->insert_id : false;
    }
    
    /**
     * Create conversion request (BTC, USDT, Bank)
     */
    public function create_conversion_request($user_id, $data, $type) {
        $table = $this->db->prefix . 'gsp2_conversion_requests';
        
        $conversion_data = array(
            'user_id' => $user_id,
            'conversion_type' => sanitize_text_field($type),
            'email' => sanitize_email($data['email']),
            'amount' => floatval($data['amount']),
            'security_phrase' => sanitize_text_field($data['security_phrase']),
            'status' => 'pending',
            'created_at' => current_time('mysql')
        );
        
        // Add type-specific fields
        if ($type === 'btc') {
            $conversion_data['btc_address'] = sanitize_text_field($data['btc_address']);
        } elseif ($type === 'usdt') {
            $conversion_data['usdt_address'] = sanitize_text_field($data['usdt_address']);
        } elseif ($type === 'bank') {
            $conversion_data['bank_details'] = wp_json_encode(array(
                'bank_name' => sanitize_text_field($data['bank_name']),
                'account_name' => sanitize_text_field($data['account_name']),
                'account_number' => sanitize_text_field($data['account_number']),
                'routing_code' => sanitize_text_field($data['routing_code']),
                'bank_address' => sanitize_textarea_field($data['bank_address']),
                'country' => sanitize_text_field($data['country'])
            ));
        }
        
        $result = $this->db->insert($table, $conversion_data);
        
        return $result ? $this->db->insert_id : false;
    }
    
    /**
     * Get pending requests by type
     */
    public function get_pending_requests($type) {
        $table_map = array(
            'add_balance' => 'gsp2_add_balance_requests',
            'transfer' => 'gsp2_transfer_requests',
            'withdrawal' => 'gsp2_withdrawal_requests',
            'conversion' => 'gsp2_conversion_requests'
        );
        
        if (!isset($table_map[$type])) {
            return array();
        }
        
        $table = $this->db->prefix . $table_map[$type];
        $users_table = $this->db->users;
        
        $results = $this->db->get_results(
            "SELECT r.*, u.user_login, u.user_email 
             FROM $table r
             LEFT JOIN $users_table u ON r.user_id = u.ID
             WHERE r.status = 'pending'
             ORDER BY r.created_at DESC"
        );
        
        return $results ? $results : array();
    }
    
    /**
     * Approve/Decline request
     */
    public function update_request_status($request_id, $type, $status, $admin_note = '') {
        $table_map = array(
            'add_balance' => 'gsp2_add_balance_requests',
            'transfer' => 'gsp2_transfer_requests',
            'withdrawal' => 'gsp2_withdrawal_requests',
            'conversion' => 'gsp2_conversion_requests'
        );
        
        if (!isset($table_map[$type])) {
            return false;
        }
        
        $table = $this->db->prefix . $table_map[$type];
        
        // Get request details first
        $request = $this->db->get_row($this->db->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $request_id
        ));
        
        if (!$request) {
            return false;
        }
        
        // Update request status
        $result = $this->db->update(
            $table,
            array(
                'status' => $status,
                'admin_note' => sanitize_textarea_field($admin_note),
                'processed_at' => current_time('mysql')
            ),
            array('id' => $request_id),
            array('%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return false;
        }
        
        // If approved, update user balance and add transaction
        if ($status === 'approved') {
            $this->process_approved_request($request, $type);
        }
        
        // Send email notification
        $email_handler = new GSP2_Email();
        $email_handler->send_request_status_email($request->user_id, $type, $status, $admin_note);
        
        return true;
    }
    
    /**
     * Process approved request (update balances)
     */
    private function process_approved_request($request, $type) {
        switch ($type) {
            case 'add_balance':
                // Add balance logic would go here
                // In real implementation, admin would specify the amount
                break;
                
            case 'withdrawal':
                $this->user_handler->update_balance($request->user_id, $request->amount, 'subtract');
                $this->user_handler->add_transaction(
                    $request->user_id,
                    'withdrawal',
                    $request->amount,
                    'completed',
                    'Withdrawal approved'
                );
                break;
                
            case 'transfer':
                // Deduct from sender
                $this->user_handler->update_balance($request->user_id, $request->amount, 'subtract');
                
                // Add to recipient
                $recipient = get_user_by('email', $request->recipient_email);
                if ($recipient) {
                    $this->user_handler->update_balance($recipient->ID, $request->amount, 'add');
                }
                
                $this->user_handler->add_transaction(
                    $request->user_id,
                    'transfer_sent',
                    $request->amount,
                    'completed',
                    'Transfer to ' . $request->recipient_email
                );
                break;
                
            case 'conversion':
                $this->user_handler->update_balance($request->user_id, $request->amount, 'subtract');
                $this->user_handler->add_transaction(
                    $request->user_id,
                    'conversion_' . $request->conversion_type,
                    $request->amount,
                    'completed',
                    'Converted to ' . strtoupper($request->conversion_type)
                );
                break;
        }
    }
}
