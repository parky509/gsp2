<?php
/**
 * Database Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSP2_Database {
    
    /**
     * Create custom database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // User balances table
        $table_name = $wpdb->prefix . 'gsp2_user_balances';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            balance decimal(15,2) DEFAULT 0.00,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Transactions table
        $table_name = $wpdb->prefix . 'gsp2_transactions';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            type varchar(50) NOT NULL,
            amount decimal(15,2) NOT NULL,
            status varchar(20) DEFAULT 'pending',
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY type (type),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Add balance requests table
        $table_name = $wpdb->prefix . 'gsp2_add_balance_requests';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            sender_name varchar(255) NOT NULL,
            sender_email varchar(255) NOT NULL,
            amount decimal(15,2) NOT NULL,
            receipt_file varchar(255),
            status varchar(20) DEFAULT 'pending',
            admin_notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Transfer requests table
        $table_name = $wpdb->prefix . 'gsp2_transfer_requests';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            from_user_id bigint(20) UNSIGNED NOT NULL,
            to_user_id bigint(20) UNSIGNED NOT NULL,
            amount decimal(15,2) NOT NULL,
            token_code varchar(100) NOT NULL,
            status varchar(20) DEFAULT 'pending',
            admin_notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY from_user_id (from_user_id),
            KEY to_user_id (to_user_id),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Withdrawal requests table
        $table_name = $wpdb->prefix . 'gsp2_withdrawal_requests';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            amount decimal(15,2) NOT NULL,
            withdrawal_details text NOT NULL,
            status varchar(20) DEFAULT 'pending',
            admin_notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Conversion requests table (BTC, USDT, Bank)
        $table_name = $wpdb->prefix . 'gsp2_conversion_requests';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            conversion_type varchar(50) NOT NULL,
            email varchar(255) NOT NULL,
            amount decimal(15,2) NOT NULL,
            destination_address text NOT NULL,
            security_phrase varchar(255),
            bank_details text,
            status varchar(20) DEFAULT 'pending',
            admin_notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY conversion_type (conversion_type),
            KEY status (status)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Settings table
        $table_name = $wpdb->prefix . 'gsp2_settings';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            setting_key varchar(255) NOT NULL,
            setting_value longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        dbDelta($sql);
    }
    
    /**
     * Insert default data
     */
    public static function insert_default_data() {
        global $wpdb;
        
        // Insert default settings
        $settings_table = $wpdb->prefix . 'gsp2_settings';
        
        $default_settings = array(
            array('setting_key' => 'account_number', 'setting_value' => '1234567890'),
            array('setting_key' => 'btc_address', 'setting_value' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh'),
            array('setting_key' => 'usdt_address', 'setting_value' => '0x742d35Cc6634C0532925a3b844Bc9e9e7595f0bEb'),
        );
        
        foreach ($default_settings as $setting) {
            $wpdb->replace($settings_table, $setting);
        }
        
        // Create default admin user if doesn't exist
        if (!username_exists('gsp2admin')) {
            $admin_id = wp_create_user('gsp2admin', 'changeme123', 'admin@globalswiftpay2.com');
            if (!is_wp_error($admin_id)) {
                $user = new WP_User($admin_id);
                $user->set_role('administrator');
                
                // Initialize admin balance
                self::init_user_balance($admin_id, 0.00);
            }
        }
    }
    
    /**
     * Initialize user balance
     */
    public static function init_user_balance($user_id, $initial_balance = 0.00) {
        global $wpdb;
        
        $balance_table = $wpdb->prefix . 'gsp2_user_balances';
        
        $wpdb->replace($balance_table, array(
            'user_id' => $user_id,
            'balance' => $initial_balance
        ));
    }
    
    /**
     * Get user balance
     */
    public static function get_user_balance($user_id) {
        global $wpdb;
        
        $balance_table = $wpdb->prefix . 'gsp2_user_balances';
        
        $balance = $wpdb->get_var($wpdb->prepare(
            "SELECT balance FROM $balance_table WHERE user_id = %d",
            $user_id
        ));
        
        return $balance !== null ? floatval($balance) : 0.00;
    }
    
    /**
     * Update user balance
     */
    public static function update_user_balance($user_id, $new_balance) {
        global $wpdb;
        
        $balance_table = $wpdb->prefix . 'gsp2_user_balances';
        
        return $wpdb->update(
            $balance_table,
            array('balance' => $new_balance),
            array('user_id' => $user_id),
            array('%f'),
            array('%d')
        );
    }
    
    /**
     * Get setting value
     */
    public static function get_setting($key, $default = '') {
        global $wpdb;
        
        $settings_table = $wpdb->prefix . 'gsp2_settings';
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $settings_table WHERE setting_key = %s",
            $key
        ));
        
        return $value !== null ? $value : $default;
    }
    
    /**
     * Update setting value
     */
    public static function update_setting($key, $value) {
        global $wpdb;
        
        $settings_table = $wpdb->prefix . 'gsp2_settings';
        
        return $wpdb->replace($settings_table, array(
            'setting_key' => $key,
            'setting_value' => $value
        ));
    }
}
