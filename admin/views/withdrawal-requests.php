<?php
/**
 * Withdrawal Requests Page Template
 *
 * @package GlobalSwiftPay2_Investment_Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap gsp2-admin-page">
    <h1>Withdrawal Requests</h1>
    
    <div class="gsp2-admin-container">
        <?php if (empty($requests)): ?>
            <p>No pending withdrawal requests.</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Details</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo esc_html($request->id); ?></td>
                            <td><?php echo esc_html($request->user_login); ?></td>
                            <td>$<?php echo esc_html(number_format($request->amount, 2)); ?></td>
                            <td><?php echo esc_html($request->withdrawal_details); ?></td>
                            <td><?php echo esc_html($request->created_at); ?></td>
                            <td>
                                <button class="button button-primary gsp2-approve-btn" 
                                        data-id="<?php echo esc_attr($request->id); ?>" 
                                        data-type="withdrawal">
                                    Approve
                                </button>
                                <button class="button gsp2-decline-btn" 
                                        data-id="<?php echo esc_attr($request->id); ?>" 
                                        data-type="withdrawal">
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
