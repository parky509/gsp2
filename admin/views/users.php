<?php
/**
 * Users Page Template
 *
 * @package GlobalSwiftPay2_Investment_Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap gsp2-admin-page">
    <h1>Users</h1>
    
    <div class="gsp2-admin-container">
        <?php if (empty($users)): ?>
            <p>No users found.</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Balance</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo esc_html($user->ID); ?></td>
                            <td><?php echo esc_html($user->user_login); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td>$<?php echo esc_html(number_format($user->balance, 2)); ?></td>
                            <td><?php echo esc_html(date('Y-m-d', strtotime($user->user_registered))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
