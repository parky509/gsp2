<?php
/**
 * Transactions handler for GlobalSwiftPay Dashboard
 * Rebuilt for reliable transaction syncing and balance updates
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSP_Transactions {
    
    /**
     * Create a new transaction record
     * 
     * @param int $user_id User ID
     * @param string $type Transaction type
     * @param float $amount Transaction amount
     * @param array $details Additional details
     * @param int $related_id Related record ID from parent table
     * @return int Transaction ID
     */
    public static function create($user_id, $type, $amount, $details = array(), $related_id = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_transactions';
        
        // Store related_id in details for linking
        if ($related_id > 0) {
            $details['related_id'] = $related_id;
        }
        
        $wpdb->insert($table, array(
            'user_id' => $user_id,
            'type' => $type,
            'amount' => $amount,
            'status' => 'pending',
            'details' => maybe_serialize($details)
        ));
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update transaction status by ID
     */
    public static function update_status($transaction_id, $status) {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_transactions';
        
        return $wpdb->update(
            $table,
            array('status' => $status),
            array('id' => $transaction_id)
        );
    }
    
    /**
     * Find and update transaction status by type and related ID
     * This is the key function for syncing transaction records with their parent tables
     */
    public static function sync_transaction_status($user_id, $type, $related_id, $status) {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_transactions';
        
        // Get all transactions for this user and type
        $transactions = $wpdb->get_results($wpdb->prepare(
            "SELECT id, details FROM $table WHERE user_id = %d AND type = %s AND status = 'pending'",
            $user_id,
            $type
        ));
        
        foreach ($transactions as $transaction) {
            $details = maybe_unserialize($transaction->details);
            
            // Check multiple possible related ID keys
            $match = false;
            if (isset($details['related_id']) && intval($details['related_id']) === intval($related_id)) {
                $match = true;
            } elseif (isset($details['deposit_id']) && intval($details['deposit_id']) === intval($related_id)) {
                $match = true;
            } elseif (isset($details['withdrawal_id']) && intval($details['withdrawal_id']) === intval($related_id)) {
                $match = true;
            } elseif (isset($details['transfer_id']) && intval($details['transfer_id']) === intval($related_id)) {
                $match = true;
            } elseif (isset($details['conversion_id']) && intval($details['conversion_id']) === intval($related_id)) {
                $match = true;
            }
            
            if ($match) {
                $wpdb->update(
                    $table,
                    array('status' => $status),
                    array('id' => $transaction->id)
                );
                return true;
            }
        }
        
        // If no pending transaction found, check all transactions
        $all_transactions = $wpdb->get_results($wpdb->prepare(
            "SELECT id, details FROM $table WHERE user_id = %d AND type = %s",
            $user_id,
            $type
        ));
        
        foreach ($all_transactions as $transaction) {
            $details = maybe_unserialize($transaction->details);
            
            $match = false;
            if (isset($details['related_id']) && intval($details['related_id']) === intval($related_id)) {
                $match = true;
            } elseif (isset($details['deposit_id']) && intval($details['deposit_id']) === intval($related_id)) {
                $match = true;
            } elseif (isset($details['withdrawal_id']) && intval($details['withdrawal_id']) === intval($related_id)) {
                $match = true;
            } elseif (isset($details['transfer_id']) && intval($details['transfer_id']) === intval($related_id)) {
                $match = true;
            } elseif (isset($details['conversion_id']) && intval($details['conversion_id']) === intval($related_id)) {
                $match = true;
            }
            
            if ($match) {
                $wpdb->update(
                    $table,
                    array('status' => $status),
                    array('id' => $transaction->id)
                );
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get user transactions with real-time status from parent tables
     */
    public static function get_user_transactions($user_id, $limit = 20, $offset = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_transactions';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $user_id,
            $limit,
            $offset
        ));
        
        return $results;
    }
    
    /**
     * Get all transactions (for admin)
     */
    public static function get_all_transactions($limit = 50, $offset = 0, $status = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_transactions';
        
        $sql = "SELECT t.*, u.display_name, u.user_email FROM $table t 
                LEFT JOIN {$wpdb->users} u ON t.user_id = u.ID";
        
        if ($status) {
            $sql .= $wpdb->prepare(" WHERE t.status = %s", $status);
        }
        
        $sql .= " ORDER BY t.created_at DESC LIMIT %d OFFSET %d";
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $limit, $offset));
        
        return $results;
    }
    
    /**
     * Create deposit request
     */
    public static function create_deposit($user_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_deposits';
        
        $wpdb->insert($table, array(
            'user_id' => $user_id,
            'name' => sanitize_text_field($data['name']),
            'email' => sanitize_email($data['email']),
            'amount' => floatval($data['amount']),
            'receipt_path' => isset($data['receipt_path']) ? sanitize_text_field($data['receipt_path']) : '',
            'sender_name' => isset($data['sender_name']) ? sanitize_text_field($data['sender_name']) : '',
            'status' => 'pending'
        ));
        
        $deposit_id = $wpdb->insert_id;
        
        if ($deposit_id) {
            // Create transaction record with deposit_id for syncing
            self::create($user_id, 'deposit', floatval($data['amount']), array(
                'deposit_id' => $deposit_id,
                'related_id' => $deposit_id,
                'name' => $data['name'],
                'email' => $data['email']
            ), $deposit_id);
        }
        
        return $deposit_id;
    }
    
    /**
     * Get all deposits (for admin)
     */
    public static function get_all_deposits($status = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_deposits';
        
        $sql = "SELECT d.*, u.display_name, u.user_login FROM $table d 
                LEFT JOIN {$wpdb->users} u ON d.user_id = u.ID";
        
        if ($status) {
            $sql .= $wpdb->prepare(" WHERE d.status = %s", $status);
        }
        
        $sql .= " ORDER BY d.created_at DESC";
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Update deposit status - handles balance update and transaction sync atomically
     */
    public static function update_deposit_status($deposit_id, $status, $notes = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_deposits';
        
        // Get deposit details
        $deposit = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $deposit_id
        ));
        
        if (!$deposit) {
            return false;
        }
        
        // Start transaction for atomicity
        $wpdb->query('START TRANSACTION');
        
        try {
            // Update deposit status
            $result = $wpdb->update(
                $table,
                array(
                    'status' => $status,
                    'admin_notes' => $notes
                ),
                array('id' => $deposit_id)
            );
            
            if ($result === false) {
                throw new Exception('Failed to update deposit status');
            }
            
            // Sync transaction status
            self::sync_transaction_status($deposit->user_id, 'deposit', $deposit_id, $status);
            
            // If approved, add to user balance
            if ($status === 'approved') {
                GSP_User::update_wallet_balance($deposit->user_id, $deposit->amount, 'add');
            }
            
            $wpdb->query('COMMIT');
            
            // Send email notification
            GSP_Email::send_deposit_status($deposit->email, $status, $deposit->amount);
            
            return true;
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }
    
    /**
     * Create withdrawal request
     */
    public static function create_withdrawal($user_id, $amount, $method, $details = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_withdrawals';
        
        $wpdb->insert($table, array(
            'user_id' => $user_id,
            'amount' => floatval($amount),
            'withdrawal_method' => $method,
            'details' => maybe_serialize($details),
            'status' => 'pending'
        ));
        
        $withdrawal_id = $wpdb->insert_id;
        
        if ($withdrawal_id) {
            // Create transaction record
            self::create($user_id, 'withdrawal', floatval($amount), array(
                'withdrawal_id' => $withdrawal_id,
                'related_id' => $withdrawal_id,
                'method' => $method
            ), $withdrawal_id);
        }
        
        return $withdrawal_id;
    }
    
    /**
     * Get all withdrawals (for admin)
     */
    public static function get_all_withdrawals($status = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_withdrawals';
        
        $sql = "SELECT w.*, u.display_name, u.user_email, u.user_login FROM $table w 
                LEFT JOIN {$wpdb->users} u ON w.user_id = u.ID";
        
        if ($status) {
            $sql .= $wpdb->prepare(" WHERE w.status = %s", $status);
        }
        
        $sql .= " ORDER BY w.created_at DESC";
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Update withdrawal status
     */
    public static function update_withdrawal_status($withdrawal_id, $status, $notes = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_withdrawals';
        
        $withdrawal = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $withdrawal_id
        ));
        
        if (!$withdrawal) {
            return false;
        }
        
        // If approving, verify user still has sufficient balance
        if ($status === 'approved') {
            $current_balance = GSP_User::get_balance($withdrawal->user_id);
            if ($current_balance->wallet_balance < $withdrawal->amount) {
                // Decline due to insufficient funds
                $wpdb->update(
                    $table,
                    array(
                        'status' => 'declined',
                        'admin_notes' => 'Insufficient funds at time of approval. User balance: $' . number_format($current_balance->wallet_balance, 2)
                    ),
                    array('id' => $withdrawal_id)
                );
                
                self::sync_transaction_status($withdrawal->user_id, 'withdrawal', $withdrawal_id, 'declined');
                
                $user = get_userdata($withdrawal->user_id);
                if ($user) {
                    GSP_Email::send_withdrawal_status($user->user_email, 'declined', $withdrawal->amount);
                }
                return false;
            }
        }
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Update withdrawal status
            $wpdb->update(
                $table,
                array(
                    'status' => $status,
                    'admin_notes' => $notes
                ),
                array('id' => $withdrawal_id)
            );
            
            // Sync transaction status
            self::sync_transaction_status($withdrawal->user_id, 'withdrawal', $withdrawal_id, $status);
            
            // If approved, deduct from user balance
            if ($status === 'approved') {
                GSP_User::update_wallet_balance($withdrawal->user_id, $withdrawal->amount, 'subtract');
            }
            
            $wpdb->query('COMMIT');
            
            // Send email
            $user = get_userdata($withdrawal->user_id);
            if ($user) {
                GSP_Email::send_withdrawal_status($user->user_email, $status, $withdrawal->amount);
            }
            
            return true;
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }
    
    /**
     * Create transfer request
     */
    public static function create_transfer($from_user_id, $to_user_id, $amount, $token_code) {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_transfers';
        
        $wpdb->insert($table, array(
            'from_user_id' => $from_user_id,
            'to_user_id' => $to_user_id,
            'amount' => floatval($amount),
            'token_code' => sanitize_text_field($token_code),
            'status' => 'pending'
        ));
        
        $transfer_id = $wpdb->insert_id;
        
        if ($transfer_id) {
            // Create outgoing transaction record for sender
            self::create($from_user_id, 'transfer_out', floatval($amount), array(
                'transfer_id' => $transfer_id,
                'related_id' => $transfer_id,
                'to_user_id' => $to_user_id
            ), $transfer_id);
        }
        
        return $transfer_id;
    }
    
    /**
     * Get all transfers (for admin)
     */
    public static function get_all_transfers($status = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_transfers';
        
        $sql = "SELECT t.*, 
                u1.display_name as from_name, u1.user_email as from_email,
                u2.display_name as to_name, u2.user_email as to_email
                FROM $table t 
                LEFT JOIN {$wpdb->users} u1 ON t.from_user_id = u1.ID
                LEFT JOIN {$wpdb->users} u2 ON t.to_user_id = u2.ID";
        
        if ($status) {
            $sql .= $wpdb->prepare(" WHERE t.status = %s", $status);
        }
        
        $sql .= " ORDER BY t.created_at DESC";
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Update transfer status with atomic handling
     */
    public static function update_transfer_status($transfer_id, $status, $notes = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_transfers';
        
        $transfer = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $transfer_id
        ));
        
        if (!$transfer) {
            return false;
        }
        
        // If approving, verify sender has sufficient balance
        if ($status === 'approved') {
            $sender_balance = GSP_User::get_balance($transfer->from_user_id);
            if ($sender_balance->wallet_balance < $transfer->amount) {
                $wpdb->update(
                    $table,
                    array(
                        'status' => 'declined',
                        'admin_notes' => 'Insufficient funds at time of approval. Sender balance: $' . number_format($sender_balance->wallet_balance, 2)
                    ),
                    array('id' => $transfer_id)
                );
                
                self::sync_transaction_status($transfer->from_user_id, 'transfer_out', $transfer_id, 'declined');
                
                $from_user = get_userdata($transfer->from_user_id);
                if ($from_user) {
                    GSP_Email::send_transfer_status($from_user->user_email, 'declined', $transfer->amount, 'sender');
                }
                return false;
            }
        }
        
        // Start transaction for atomicity
        $wpdb->query('START TRANSACTION');
        
        try {
            // Update transfer status
            $wpdb->update(
                $table,
                array(
                    'status' => $status,
                    'admin_notes' => $notes
                ),
                array('id' => $transfer_id)
            );
            
            // Sync sender's transaction status
            self::sync_transaction_status($transfer->from_user_id, 'transfer_out', $transfer_id, $status);
            
            if ($status === 'approved') {
                // Deduct from sender
                GSP_User::update_wallet_balance($transfer->from_user_id, $transfer->amount, 'subtract');
                
                // Add to receiver
                GSP_User::update_wallet_balance($transfer->to_user_id, $transfer->amount, 'add');
                
                // Create incoming transaction for receiver
                $wpdb->insert($wpdb->prefix . 'gsp_transactions', array(
                    'user_id' => $transfer->to_user_id,
                    'type' => 'transfer_in',
                    'amount' => $transfer->amount,
                    'status' => 'approved',
                    'details' => maybe_serialize(array(
                        'transfer_id' => $transfer_id,
                        'related_id' => $transfer_id,
                        'from_user_id' => $transfer->from_user_id
                    ))
                ));
            }
            
            $wpdb->query('COMMIT');
            
            // Send emails
            $from_user = get_userdata($transfer->from_user_id);
            $to_user = get_userdata($transfer->to_user_id);
            
            if ($from_user) {
                GSP_Email::send_transfer_status($from_user->user_email, $status, $transfer->amount, 'sender');
            }
            if ($to_user && $status === 'approved') {
                GSP_Email::send_transfer_status($to_user->user_email, $status, $transfer->amount, 'receiver');
            }
            
            return true;
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }
    
    /**
     * Create conversion request
     */
    public static function create_conversion($user_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_conversions';
        
        $insert_data = array(
            'user_id' => $user_id,
            'email' => sanitize_email($data['email']),
            'conversion_type' => sanitize_text_field($data['conversion_type']),
            'amount' => floatval($data['amount']),
            'security_phrase' => sanitize_text_field($data['security_phrase']),
            'status' => 'pending'
        );
        
        if (isset($data['destination_address'])) {
            $insert_data['destination_address'] = sanitize_text_field($data['destination_address']);
        }
        
        if (isset($data['bank_details'])) {
            $insert_data['bank_details'] = maybe_serialize($data['bank_details']);
        }
        
        $wpdb->insert($table, $insert_data);
        
        $conversion_id = $wpdb->insert_id;
        
        if ($conversion_id) {
            // Create transaction record
            $tx_type = 'conversion_' . $data['conversion_type'];
            self::create($user_id, $tx_type, floatval($data['amount']), array(
                'conversion_id' => $conversion_id,
                'related_id' => $conversion_id,
                'type' => $data['conversion_type']
            ), $conversion_id);
        }
        
        return $conversion_id;
    }
    
    /**
     * Get all conversions (for admin)
     */
    public static function get_all_conversions($status = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_conversions';
        
        $sql = "SELECT c.*, u.display_name, u.user_login FROM $table c 
                LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID";
        
        if ($status) {
            $sql .= $wpdb->prepare(" WHERE c.status = %s", $status);
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Update conversion status
     */
    public static function update_conversion_status($conversion_id, $status, $notes = '') {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_conversions';
        
        $conversion = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $conversion_id
        ));
        
        if (!$conversion) {
            return false;
        }
        
        // If approving, verify user has sufficient balance
        if ($status === 'approved') {
            $current_balance = GSP_User::get_balance($conversion->user_id);
            if ($current_balance->wallet_balance < $conversion->amount) {
                $wpdb->update(
                    $table,
                    array(
                        'status' => 'declined',
                        'admin_notes' => 'Insufficient funds at time of approval. User balance: $' . number_format($current_balance->wallet_balance, 2)
                    ),
                    array('id' => $conversion_id)
                );
                
                $tx_type = 'conversion_' . $conversion->conversion_type;
                self::sync_transaction_status($conversion->user_id, $tx_type, $conversion_id, 'declined');
                
                GSP_Email::send_conversion_status($conversion->email, 'declined', $conversion->amount, $conversion->conversion_type);
                return false;
            }
        }
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Update conversion status
            $wpdb->update(
                $table,
                array(
                    'status' => $status,
                    'admin_notes' => $notes
                ),
                array('id' => $conversion_id)
            );
            
            // Sync transaction status
            $tx_type = 'conversion_' . $conversion->conversion_type;
            self::sync_transaction_status($conversion->user_id, $tx_type, $conversion_id, $status);
            
            // If approved, deduct from balance
            if ($status === 'approved') {
                GSP_User::update_wallet_balance($conversion->user_id, $conversion->amount, 'subtract');
            }
            
            $wpdb->query('COMMIT');
            
            // Send email
            GSP_Email::send_conversion_status($conversion->email, $status, $conversion->amount, $conversion->conversion_type);
            
            return true;
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return false;
        }
    }
}
