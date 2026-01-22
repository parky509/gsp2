<?php
/**
 * Conversion Requests Page Template
 *
 * @package GlobalSwiftPay2_Investment_Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap gsp2-admin-page">
    <h1>Conversion Requests</h1>
    
    <div class="gsp2-admin-container">
        <?php if (empty($requests)): ?>
            <p>No pending conversion requests.</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Address/Details</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo esc_html($request->id); ?></td>
                            <td><?php echo esc_html($request->user_login); ?></td>
                            <td><?php echo esc_html(strtoupper($request->conversion_type)); ?></td>
                            <td>$<?php echo esc_html(number_format($request->amount, 2)); ?></td>
                            <td>
                                <?php 
                                if ($request->conversion_type === 'btc') {
                                    echo esc_html($request->btc_address);
                                } elseif ($request->conversion_type === 'usdt') {
                                    echo esc_html($request->usdt_address);
                                } elseif ($request->conversion_type === 'bank') {
                                    $bank_details = json_decode($request->bank_details, true);
                                    echo esc_html($bank_details['bank_name'] . ' - ' . $bank_details['account_number']);
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html($request->created_at); ?></td>
                            <td>
                                <button class="button button-primary gsp2-approve-btn" 
                                        data-id="<?php echo esc_attr($request->id); ?>" 
                                        data-type="conversion">
                                    Approve
                                </button>
                                <button class="button gsp2-decline-btn" 
                                        data-id="<?php echo esc_attr($request->id); ?>" 
                                        data-type="conversion">
                                    Decline
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.gsp2-approve-btn, .gsp2-decline-btn').on('click', function() {
        var btn = $(this);
        var requestId = btn.data('id');
        var type = btn.data('type');
        var action = btn.hasClass('gsp2-approve-btn') ? 'approve' : 'decline';
        var adminNote = prompt('Enter admin note (optional):');
        
        if (action === 'decline' && !adminNote) {
            adminNote = prompt('Please enter a reason for declining:');
            if (!adminNote) return;
        }
        
        $.post(ajaxurl, {
            action: 'gsp2_process_request',
            nonce: '<?php echo wp_create_nonce('gsp2_admin_nonce'); ?>',
            request_id: requestId,
            type: type,
            action: action,
            admin_note: adminNote
        }, function(response) {
            if (response.success) {
                alert(response.data);
                location.reload();
            } else {
                alert('Error: ' + response.data);
            }
        });
    });
});
</script>
