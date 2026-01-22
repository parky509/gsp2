<?php
/**
 * Frontend User Dashboard Template
 * 
 * This template displays the user dashboard with balance, action buttons, and transaction history.
 * Used by the [gsp2_dashboard] shortcode.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$user = new GSP2_User();
$balance = $user->get_balance($user_id);
$transactions = $user->get_transaction_history($user_id);
?>

<div class="gsp2-dashboard-wrapper">
    <!-- Header -->
    <div class="gsp2-header">
        <div class="gsp2-user-info">
            <div class="gsp2-avatar">
                <?php echo esc_html(strtoupper(substr($current_user->display_name, 0, 1))); ?>
            </div>
            <span class="gsp2-username"><?php echo esc_html($current_user->display_name); ?></span>
        </div>
        <button class="gsp2-logout-btn" onclick="window.location.href='<?php echo esc_url(wp_logout_url(home_url())); ?>'">
            Logout
        </button>
    </div>

    <!-- Wallet Balance -->
    <div class="gsp2-balance-card">
        <div class="gsp2-balance-label">Wallet Balance</div>
        <div class="gsp2-balance-amount">$<?php echo number_format($balance, 2); ?></div>
    </div>

    <!-- Action Buttons Grid -->
    <div class="gsp2-actions-grid">
        <button class="gsp2-action-btn" data-action="deposit">
            <svg class="gsp2-icon" viewBox="0 0 24 24"><path d="M3,6H21V18H3V6M12,9A3,3 0 0,1 15,12A3,3 0 0,1 12,15A3,3 0 0,1 9,12A3,3 0 0,1 12,9M7,8A2,2 0 0,1 5,10V14A2,2 0 0,1 7,16H17A2,2 0 0,1 19,14V10A2,2 0 0,1 17,8H7Z" fill="currentColor"/></svg>
            <span>Deposit</span>
        </button>

        <button class="gsp2-action-btn" data-action="savings">
            <svg class="gsp2-icon" viewBox="0 0 24 24"><path d="M20,8H4V6H20M20,18H4V12H20M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" fill="currentColor"/></svg>
            <span>Savings</span>
        </button>

        <button class="gsp2-action-btn" data-action="btc">
            <svg class="gsp2-icon" viewBox="0 0 24 24"><path d="M17.06,11.57C17.65,10.88 18,9.98 18,9A5,5 0 0,0 13,4H7V21H14A5,5 0 0,0 19,16C19,14.12 18.17,12.43 17.06,11.57M10,7H13A2,2 0 0,1 15,9A2,2 0 0,1 13,11H10V7M14,18H10V14H14A2,2 0 0,1 16,16A2,2 0 0,1 14,18Z" fill="currentColor"/></svg>
            <span>Convert to BTC</span>
        </button>

        <button class="gsp2-action-btn" data-action="usdt">
            <svg class="gsp2-icon" viewBox="0 0 24 24"><path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6Z" fill="currentColor"/></svg>
            <span>Convert to USDT</span>
        </button>

        <button class="gsp2-action-btn" data-action="bank">
            <svg class="gsp2-icon" viewBox="0 0 24 24"><path d="M11.5,1L2,6V8H21V6M16,10V17H19V10M2,22H21V19H2M10,10V17H13V10M4,10V17H7V10H4Z" fill="currentColor"/></svg>
            <span>Convert to Bank</span>
        </button>

        <button class="gsp2-action-btn" data-action="add-balance">
            <svg class="gsp2-icon" viewBox="0 0 24 24"><path d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M11,7H13V11H17V13H13V17H11V13H7V11H11V7Z" fill="currentColor"/></svg>
            <span>Add Balance</span>
        </button>

        <button class="gsp2-action-btn" data-action="transfer">
            <svg class="gsp2-icon" viewBox="0 0 24 24"><path d="M6.99,11L3,15L6.99,19V16H14V14H6.99V11M21,9L17.01,5V8H10V10H17.01V13L21,9Z" fill="currentColor"/></svg>
            <span>Transfer</span>
        </button>

        <button class="gsp2-action-btn" data-action="withdraw">
            <svg class="gsp2-icon" viewBox="0 0 24 24"><path d="M3,6H21V18H3V6M12,9A3,3 0 0,1 15,12A3,3 0 0,1 12,15A3,3 0 0,1 9,12A3,3 0 0,1 12,9M7,8A2,2 0 0,1 5,10V14A2,2 0 0,1 7,16H17A2,2 0 0,1 19,14V10A2,2 0 0,1 17,8H7M16,13.5L17.5,12L16,10.5V13.5M8,10.5L6.5,12L8,13.5V10.5Z" fill="currentColor"/></svg>
            <span>Withdraw</span>
        </button>
    </div>

    <!-- Transaction History -->
    <div class="gsp2-transactions-card">
        <h3>Transaction History</h3>
        <?php if (empty($transactions)): ?>
            <p class="gsp2-no-transactions">No transactions yet.</p>
        <?php else: ?>
            <div class="gsp2-transactions-list">
                <?php foreach ($transactions as $transaction): ?>
                    <div class="gsp2-transaction-item">
                        <div class="gsp2-transaction-info">
                            <div class="gsp2-transaction-type"><?php echo esc_html($transaction->type); ?></div>
                            <div class="gsp2-transaction-date"><?php echo esc_html(date('M d, Y H:i', strtotime($transaction->created_at))); ?></div>
                        </div>
                        <div class="gsp2-transaction-amount <?php echo $transaction->amount >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $transaction->amount >= 0 ? '+' : ''; ?>$<?php echo number_format(abs($transaction->amount), 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modals -->
<div id="gsp2-modal" class="gsp2-modal">
    <div class="gsp2-modal-content">
        <span class="gsp2-modal-close">&times;</span>
        <div id="gsp2-modal-body"></div>
    </div>
</div>
