<?php
/**
 * Database handler for GlobalSwiftPay Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSP_Database {
    
    /**
     * Create all necessary database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Transactions table - includes ref_id for direct linking to parent tables
        $table_transactions = $wpdb->prefix . 'gsp_transactions';
        $sql_transactions = "CREATE TABLE $table_transactions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            type varchar(50) NOT NULL,
            amount decimal(20,8) NOT NULL,
            status varchar(20) DEFAULT 'pending',
            ref_id bigint(20) DEFAULT 0,
            details longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY type (type),
            KEY status (status),
            KEY ref_id (ref_id),
            KEY type_ref (type, ref_id)
        ) $charset_collate;";
        
        // User balances table
        $table_balances = $wpdb->prefix . 'gsp_user_balances';
        $sql_balances = "CREATE TABLE $table_balances (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL UNIQUE,
            wallet_balance decimal(20,8) DEFAULT 0,
            savings_balance decimal(20,8) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        // Deposit requests table
        $table_deposits = $wpdb->prefix . 'gsp_deposits';
        $sql_deposits = "CREATE TABLE $table_deposits (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            amount decimal(20,8) NOT NULL,
            receipt_path varchar(500),
            sender_name varchar(255),
            status varchar(20) DEFAULT 'pending',
            admin_notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";
        
        // Withdrawal requests table
        $table_withdrawals = $wpdb->prefix . 'gsp_withdrawals';
        $sql_withdrawals = "CREATE TABLE $table_withdrawals (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            amount decimal(20,8) NOT NULL,
            withdrawal_method varchar(50) NOT NULL,
            details longtext,
            status varchar(20) DEFAULT 'pending',
            admin_notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";
        
        // Transfer requests table
        $table_transfers = $wpdb->prefix . 'gsp_transfers';
        $sql_transfers = "CREATE TABLE $table_transfers (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            from_user_id bigint(20) NOT NULL,
            to_user_id bigint(20) NOT NULL,
            amount decimal(20,8) NOT NULL,
            token_code varchar(100) NOT NULL,
            status varchar(20) DEFAULT 'pending',
            admin_notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY from_user_id (from_user_id),
            KEY to_user_id (to_user_id),
            KEY status (status)
        ) $charset_collate;";
        
        // Conversion requests table
        $table_conversions = $wpdb->prefix . 'gsp_conversions';
        $sql_conversions = "CREATE TABLE $table_conversions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            email varchar(255) NOT NULL,
            conversion_type varchar(50) NOT NULL,
            amount decimal(20,8) NOT NULL,
            destination_address text,
            bank_details longtext,
            security_phrase varchar(255),
            status varchar(20) DEFAULT 'pending',
            admin_notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY conversion_type (conversion_type),
            KEY status (status)
        ) $charset_collate;";
        
        // Settings table
        $table_settings = $wpdb->prefix . 'gsp_settings';
        $sql_settings = "CREATE TABLE $table_settings (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL UNIQUE,
            setting_value longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_transactions);
        dbDelta($sql_balances);
        dbDelta($sql_deposits);
        dbDelta($sql_withdrawals);
        dbDelta($sql_transfers);
        dbDelta($sql_conversions);
        dbDelta($sql_settings);
        
        // Run migrations for existing installations
        self::run_migrations();
        
        // Insert default settings
        self::insert_default_settings();
    }
    
    /**
     * Run database migrations for existing installations
     */
    private static function run_migrations() {
        global $wpdb;
        
        // Add ref_id column if it doesn't exist
        $table_transactions = $wpdb->prefix . 'gsp_transactions';
        $column_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                 WHERE TABLE_SCHEMA = %s 
                 AND TABLE_NAME = %s 
                 AND COLUMN_NAME = 'ref_id'",
                DB_NAME,
                $table_transactions
            )
        );
        
        if ($column_exists == 0) {
            $wpdb->query("ALTER TABLE $table_transactions ADD COLUMN ref_id bigint(20) DEFAULT 0 AFTER status");
            $wpdb->query("ALTER TABLE $table_transactions ADD INDEX ref_id (ref_id)");
            $wpdb->query("ALTER TABLE $table_transactions ADD INDEX type_ref (type, ref_id)");
            
            // Migrate existing data: extract ref_id from details
            self::migrate_existing_transactions();
        }
    }
    
    /**
     * Migrate existing transactions to populate ref_id from details
     */
    private static function migrate_existing_transactions() {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_transactions';
        
        // Get all transactions without ref_id
        $transactions = $wpdb->get_results("SELECT id, type, details FROM $table WHERE ref_id = 0 OR ref_id IS NULL");
        
        foreach ($transactions as $tx) {
            $details = maybe_unserialize($tx->details);
            $ref_id = 0;
            
            if (is_array($details)) {
                // Check for various ID keys
                if (isset($details['related_id'])) {
                    $ref_id = intval($details['related_id']);
                } elseif (isset($details['deposit_id'])) {
                    $ref_id = intval($details['deposit_id']);
                } elseif (isset($details['withdrawal_id'])) {
                    $ref_id = intval($details['withdrawal_id']);
                } elseif (isset($details['transfer_id'])) {
                    $ref_id = intval($details['transfer_id']);
                } elseif (isset($details['conversion_id'])) {
                    $ref_id = intval($details['conversion_id']);
                }
            }
            
            if ($ref_id > 0) {
                $wpdb->update(
                    $table,
                    array('ref_id' => $ref_id),
                    array('id' => $tx->id)
                );
            }
        }
    }
    
    /**
     * Insert default settings
     * Note: Financial details are left empty for admin to configure securely
     */
    private static function insert_default_settings() {
        $defaults = array(
            'account_number' => '',
            'account_name' => '',
            'bank_name' => '',
            'btc_address' => '',
            'usdt_address' => '',
            'company_email' => ''
        );
        
        foreach ($defaults as $key => $value) {
            // Only insert if setting doesn't already exist
            if (self::get_setting($key) === null) {
                self::set_setting($key, $value);
            }
        }
    }
    
    /**
     * Get a setting value
     */
    public static function get_setting($key) {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_settings';
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $table WHERE setting_key = %s",
            $key
        ));
        
        return $value;
    }
    
    /**
     * Set a setting value
     */
    public static function set_setting($key, $value) {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_settings';
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE setting_key = %s",
            $key
        ));
        
        if ($existing) {
            $wpdb->update(
                $table,
                array('setting_value' => $value),
                array('setting_key' => $key)
            );
        } else {
            $wpdb->insert(
                $table,
                array(
                    'setting_key' => $key,
                    'setting_value' => $value
                )
            );
        }
    }
    
    /**
     * Get all settings
     */
    public static function get_all_settings() {
        global $wpdb;
        $table = $wpdb->prefix . 'gsp_settings';
        
        $results = $wpdb->get_results("SELECT setting_key, setting_value FROM $table", ARRAY_A);
        
        $settings = array();
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
}
