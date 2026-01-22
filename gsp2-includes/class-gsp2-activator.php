<?php
/**
 * Plugin Activator
 * Handles plugin activation tasks
 */

class GSP2_Activator {
    
    /**
     * Activate the plugin
     */
    public static function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create user balances table
        $table_name = $wpdb->prefix . 'gsp2_user_balances';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            balance decimal(10,2) DEFAULT 0.00,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Create transactions table
        $table_name = $wpdb->prefix . 'gsp2_transactions';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            type varchar(50) NOT NULL,
            amount decimal(10,2) NOT NULL,
            status varchar(20) DEFAULT 'completed',
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Create add balance requests table
        $table_name = $wpdb->prefix . 'gsp2_add_balance_requests';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            sender_name varchar(100) NOT NULL,
            sender_email varchar(100) NOT NULL,
            amount decimal(10,2) NOT NULL,
            receipt_file varchar(255),
            status varchar(20) DEFAULT 'pending',
            admin_note text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            processed_at datetime,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Create transfer requests table
        $table_name = $wpdb->prefix . 'gsp2_transfer_requests';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            sender_id bigint(20) NOT NULL,
            recipient_username varchar(60) NOT NULL,
            amount decimal(10,2) NOT NULL,
            token_code varchar(50) NOT NULL,
            status varchar(20) DEFAULT 'pending',
            admin_note text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            processed_at datetime,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Create withdrawal requests table
        $table_name = $wpdb->prefix . 'gsp2_withdrawal_requests';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            amount decimal(10,2) NOT NULL,
            details text,
            status varchar(20) DEFAULT 'pending',
            admin_note text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            processed_at datetime,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Create conversion requests table
        $table_name = $wpdb->prefix . 'gsp2_conversion_requests';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            conversion_type varchar(20) NOT NULL,
            amount decimal(10,2) NOT NULL,
            destination_address text,
            security_phrase varchar(255),
            bank_details text,
            status varchar(20) DEFAULT 'pending',
            admin_note text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            processed_at datetime,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Create settings table
        $table_name = $wpdb->prefix . 'gsp2_settings';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL,
            setting_value text,
            PRIMARY KEY  (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Insert default settings
        $settings_table = $wpdb->prefix . 'gsp2_settings';
        $wpdb->replace($settings_table, [
            'setting_key' => 'account_number',
            'setting_value' => '1234567890'
        ]);
        $wpdb->replace($settings_table, [
            'setting_key' => 'btc_address',
            'setting_value' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh'
        ]);
        $wpdb->replace($settings_table, [
            'setting_key' => 'usdt_address',
            'setting_value' => '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb'
        ]);
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation flag
        update_option('gsp2_activated', true);
        update_option('gsp2_version', GSP2_VERSION);
    }
}
