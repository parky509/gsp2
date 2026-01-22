<?php
/**
 * User Management Class
 *
 * Handles user balance operations and user-related functionality
 *
 * @package GlobalSwiftPay2_Investment_Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSP2_User {
    
    private $db;
    
    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }
    
    /**
     * Get user balance
     */
    public function get_balance($user_id) {
        $table = $this->db->prefix . 'gsp2_user_balances';
        
        $balance = $this->db->get_var($this->db->prepare(
            "SELECT balance FROM $table WHERE user_id = %d",
            $user_id
        ));
        
        // If no balance record exists, create one
        if ($balance === null) {
            $this->db->insert(
                $table,
                array(
                    'user_id' => $user_id,
                    'balance' => 0.00
                ),
                array('%d', '%f')
            );
            return 0.00;
        }
        
        return floatval($balance);
    }
    
    /**
     * Update user balance
     */
    public function update_balance($user_id, $amount, $operation = 'add') {
        $table = $this->db->prefix . 'gsp2_user_balances';
        $current_balance = $this->get_balance($user_id);
        
        if ($operation === 'add') {
            $new_balance = $current_balance + floatval($amount);
        } else {
            $new_balance = $current_balance - floatval($amount);
        }
        
        // Don't allow negative balance
        if ($new_balance < 0) {
            return false;
        }
        
        $result = $this->db->update(
            $table,
            array('balance' => $new_balance),
            array('user_id' => $user_id),
            array('%f'),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Add transaction record
     */
    public function add_transaction($user_id, $type, $amount, $status, $details = '') {
        $table = $this->db->prefix . 'gsp2_transactions';
        
        $result = $this->db->insert(
            $table,
            array(
                'user_id' => $user_id,
                'type' => sanitize_text_field($type),
                'amount' => floatval($amount),
                'status' => sanitize_text_field($status),
                'details' => sanitize_textarea_field($details),
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%f', '%s', '%s', '%s')
        );
        
        return $result ? $this->db->insert_id : false;
    }
    
    /**
     * Get user transactions
     */
    public function get_transactions($user_id, $limit = 50) {
        $table = $this->db->prefix . 'gsp2_transactions';
        
        $transactions = $this->db->get_results($this->db->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
            $user_id,
            $limit
        ));
        
        return $transactions ? $transactions : array();
    }
    
    /**
     * Get all users with balances (admin only)
     */
    public function get_all_users_with_balances() {
        $users_table = $this->db->users;
        $balance_table = $this->db->prefix . 'gsp2_user_balances';
        
        $results = $this->db->get_results(
            "SELECT u.ID, u.user_login, u.user_email, u.user_registered, 
                    COALESCE(b.balance, 0) as balance
             FROM $users_table u
             LEFT JOIN $balance_table b ON u.ID = b.user_id
             ORDER BY u.user_registered DESC"
        );
        
        return $results ? $results : array();
    }
}
