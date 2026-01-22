<?php
/**
 * Email Notification Class
 *
 * Handles email notifications for request approvals/declines
 *
 * @package GlobalSwiftPay2_Investment_Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}

class GSP2_Email {
    
    /**
     * Send request status email to user
     */
    public function send_request_status_email($user_id, $request_type, $status, $admin_note = '') {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return false;
        }
        
        $to = $user->user_email;
        $site_name = get_bloginfo('name');
        
        $type_labels = array(
            'add_balance' => 'Add Balance',
            'transfer' => 'Transfer',
            'withdrawal' => 'Withdrawal',
            'conversion' => 'Conversion'
        );
        
        $type_label = isset($type_labels[$request_type]) ? $type_labels[$request_type] : $request_type;
        $status_label = ucfirst($status);
        
        $subject = sprintf(
            '[%s] Your %s Request has been %s',
            $site_name,
            $type_label,
            $status_label
        );
        
        $message = sprintf(
            "Hello %s,\n\nYour %s request has been %s.\n\n",
            $user->display_name,
            $type_label,
            strtolower($status_label)
        );
        
        if (!empty($admin_note)) {
            $message .= sprintf("Admin Note: %s\n\n", $admin_note);
        }
        
        $message .= sprintf(
            "You can view your dashboard at: %s\n\n",
            home_url('/investment-dashboard/')
        );
        
        $message .= sprintf("Best regards,\n%s Team", $site_name);
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Send deposit confirmation email
     */
    public function send_deposit_confirmation($user_id, $amount) {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return false;
        }
        
        $to = $user->user_email;
        $site_name = get_bloginfo('name');
        
        $subject = sprintf('[%s] Deposit Request Submitted', $site_name);
        
        $message = sprintf(
            "Hello %s,\n\nYour deposit request has been submitted successfully.\n\n",
            $user->display_name
        );
        
        $message .= "Our team will review your request and update you shortly.\n\n";
        
        $message .= sprintf(
            "You can track the status in your dashboard at: %s\n\n",
            home_url('/investment-dashboard/')
        );
        
        $message .= sprintf("Best regards,\n%s Team", $site_name);
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        return wp_mail($to, $subject, $message, $headers);
    }
}
