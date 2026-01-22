<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$user_id = get_current_user_id();

global $wpdb;
$balance_table = $wpdb->prefix . 'gsp2_user_balances';
$balance = $wpdb->get_var($wpdb->prepare("SELECT balance FROM $balance_table WHERE user_id = %d", $user_id));
if ($balance === null) {
    $wpdb->insert($balance_table, ['user_id' => $user_id, 'balance' => 0.00]);
    $balance = 0.00;
}
?>
<div class="gsp2-dashboard">
    <div class="gsp2-header">
        <div class="gsp2-user-info">
            <div class="gsp2-avatar"><?php echo strtoupper(substr($current_user->user_login, 0, 1)); ?></div>
            <span class="gsp2-username"><?php echo esc_html($current_user->user_login); ?></span>
        </div>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="gsp2-logout-btn">Logout</a>
    </div>
    
    <div class="gsp2-balance-card">
        <h3>Wallet Balance</h3>
        <div class="gsp2-balance-amount">$<?php echo number_format($balance, 2); ?></div>
    </div>
    
    <div class="gsp2-actions-grid">
        <button class="gsp2-action-btn" data-action="deposit">
            <span class="gsp2-icon">💰</span> Deposit
        </button>
        <button class="gsp2-action-btn" data-action="savings">
            <span class="gsp2-icon">💳</span> Savings
        </button>
        <button class="gsp2-action-btn" data-action="btc">
            <span class="gsp2-icon">₿</span> Convert to BTC
        </button>
        <button class="gsp2-action-btn" data-action="usdt">
            <span class="gsp2-icon">💵</span> Convert to USDT
        </button>
        <button class="gsp2-action-btn" data-action="bank">
            <span class="gsp2-icon">🏦</span> Convert to Bank
        </button>
        <button class="gsp2-action-btn" data-action="add-balance">
            <span class="gsp2-icon">➕</span> Add Balance
        </button>
        <button class="gsp2-action-btn" data-action="transfer">
            <span class="gsp2-icon">🔄</span> Transfer
        </button>
        <button class="gsp2-action-btn" data-action="withdraw">
            <span class="gsp2-icon">💸</span> Withdraw
        </button>
    </div>
    
    <div class="gsp2-transactions">
        <h3>Transaction History</h3>
        <div class="gsp2-transactions-list">
            <p>No transactions yet</p>
        </div>
    </div>
</div>
