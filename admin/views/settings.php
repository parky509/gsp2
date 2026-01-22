<?php
/**
 * Admin Settings Page Template
 *
 * @package GlobalSwiftPay2_Investment_Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap gsp2-admin-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="gsp2-admin-container">
        <form id="gsp2-settings-form">
            <?php wp_nonce_field('gsp2_admin_nonce', 'gsp2_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="account_number">Account Number</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="account_number" 
                               name="account_number" 
                               value="<?php echo esc_attr($settings['account_number']); ?>" 
                               class="regular-text" />
                        <p class="description">Bank account number for add balance feature</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="btc_address">BTC Address</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="btc_address" 
                               name="btc_address" 
                               value="<?php echo esc_attr($settings['btc_address']); ?>" 
                               class="regular-text" />
                        <p class="description">Bitcoin address for conversions</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="usdt_address">USDT Address</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="usdt_address" 
                               name="usdt_address" 
                               value="<?php echo esc_attr($settings['usdt_address']); ?>" 
                               class="regular-text" />
                        <p class="description">Tether (USDT) address for conversions</p>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="button button-primary">Update Settings</button>
            </p>
        </form>
        
        <div id="gsp2-message" style="display:none;"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#gsp2-settings-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'gsp2_update_settings',
            nonce: $('#gsp2_nonce').val(),
            account_number: $('#account_number').val(),
            btc_address: $('#btc_address').val(),
            usdt_address: $('#usdt_address').val()
        };
        
        $.post(ajaxurl, formData, function(response) {
            var messageDiv = $('#gsp2-message');
            if (response.success) {
                messageDiv.html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
            } else {
                messageDiv.html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
            }
            messageDiv.show();
            setTimeout(function() { messageDiv.fadeOut(); }, 3000);
        });
    });
});
</script>
