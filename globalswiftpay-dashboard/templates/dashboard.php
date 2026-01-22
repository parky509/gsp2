<?php
/**
 * Dashboard template for GlobalSwiftPay
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$balance = GSP_User::get_balance($user_id);
$transactions = GSP_Transactions::get_user_transactions($user_id, 10);
$settings = GSP_Database::get_all_settings();

$avatar_url = get_avatar_url($user_id, array('size' => 100));
$display_name = $current_user->display_name ?: $current_user->user_login;
?>

<div class="gsp-dashboard">
    <!-- Header Section -->
    <div class="gsp-header">
        <div class="gsp-header-content">
            <div class="gsp-user-info">
                <div class="gsp-avatar">
                    <img src="<?php echo esc_url($avatar_url); ?>" alt="<?php echo esc_attr($display_name); ?>">
                </div>
                <div class="gsp-user-details">
                    <h2><?php esc_html_e('Welcome back,', 'globalswiftpay-dashboard'); ?></h2>
                    <h1><?php echo esc_html($display_name); ?></h1>
                </div>
            </div>
            <div class="gsp-header-actions">
                <a href="<?php echo esc_url(wp_logout_url('https://globalswiftpay2.com')); ?>" class="gsp-btn gsp-btn-logout">
                    <span class="gsp-icon">⏻</span>
                    <?php esc_html_e('Logout', 'globalswiftpay-dashboard'); ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Balance Cards Section -->
    <div class="gsp-balance-section">
        <div class="gsp-glass-card gsp-balance-card gsp-wallet-balance">
            <div class="gsp-card-icon">
                <span>💰</span>
            </div>
            <div class="gsp-card-content">
                <h3><?php esc_html_e('Wallet Balance', 'globalswiftpay-dashboard'); ?></h3>
                <p class="gsp-amount" id="gsp-wallet-balance">$<?php echo esc_html(number_format($balance->wallet_balance, 2)); ?></p>
            </div>
        </div>
        
        <div class="gsp-glass-card gsp-balance-card gsp-savings-balance" id="savings-card">
            <div class="gsp-card-icon">
                <span>🏦</span>
            </div>
            <div class="gsp-card-content">
                <h3><?php esc_html_e('Savings', 'globalswiftpay-dashboard'); ?></h3>
                <p class="gsp-amount" id="gsp-savings-balance">$<?php echo esc_html(number_format($balance->savings_balance, 2)); ?></p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="gsp-actions-section">
        <h2 class="gsp-section-title"><?php esc_html_e('Quick Actions', 'globalswiftpay-dashboard'); ?></h2>
        <div class="gsp-actions-grid">
            <button class="gsp-glass-btn gsp-action-btn" data-modal="deposit-modal">
                <span class="gsp-btn-icon">📥</span>
                <span class="gsp-btn-text"><?php esc_html_e('Deposit', 'globalswiftpay-dashboard'); ?></span>
            </button>
            
            <button class="gsp-glass-btn gsp-action-btn" data-modal="add-balance-modal">
                <span class="gsp-btn-icon">➕</span>
                <span class="gsp-btn-text"><?php esc_html_e('Add Balance', 'globalswiftpay-dashboard'); ?></span>
            </button>
            
            <button class="gsp-glass-btn gsp-action-btn" data-modal="withdraw-modal">
                <span class="gsp-btn-icon">📤</span>
                <span class="gsp-btn-text"><?php esc_html_e('Withdraw', 'globalswiftpay-dashboard'); ?></span>
            </button>
            
            <button class="gsp-glass-btn gsp-action-btn" data-modal="transfer-modal">
                <span class="gsp-btn-icon">🔄</span>
                <span class="gsp-btn-text"><?php esc_html_e('Transfer', 'globalswiftpay-dashboard'); ?></span>
            </button>
            
            <button class="gsp-glass-btn gsp-action-btn" data-modal="convert-btc-modal">
                <span class="gsp-btn-icon">₿</span>
                <span class="gsp-btn-text"><?php esc_html_e('Convert to BTC', 'globalswiftpay-dashboard'); ?></span>
            </button>
            
            <button class="gsp-glass-btn gsp-action-btn" data-modal="convert-usdt-modal">
                <span class="gsp-btn-icon">💵</span>
                <span class="gsp-btn-text"><?php esc_html_e('Convert to USDT', 'globalswiftpay-dashboard'); ?></span>
            </button>
            
            <button class="gsp-glass-btn gsp-action-btn" data-modal="convert-bank-modal">
                <span class="gsp-btn-icon">🏛️</span>
                <span class="gsp-btn-text"><?php esc_html_e('Convert to Bank', 'globalswiftpay-dashboard'); ?></span>
            </button>
        </div>
    </div>

    <!-- Transaction History Section -->
    <div class="gsp-transactions-section">
        <h2 class="gsp-section-title"><?php esc_html_e('Transaction History', 'globalswiftpay-dashboard'); ?></h2>
        <div class="gsp-glass-card gsp-transactions-card">
            <div class="gsp-transactions-table-wrapper">
                <table class="gsp-transactions-table" id="gsp-transactions-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Type', 'globalswiftpay-dashboard'); ?></th>
                            <th><?php esc_html_e('Amount', 'globalswiftpay-dashboard'); ?></th>
                            <th><?php esc_html_e('Status', 'globalswiftpay-dashboard'); ?></th>
                            <th><?php esc_html_e('Date', 'globalswiftpay-dashboard'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="4" class="gsp-no-transactions"><?php esc_html_e('No transactions yet.', 'globalswiftpay-dashboard'); ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td>
                                        <span class="gsp-transaction-type gsp-type-<?php echo esc_attr(str_replace('_', '-', $transaction->type)); ?>">
                                            <?php echo esc_html(ucwords(str_replace('_', ' ', $transaction->type))); ?>
                                        </span>
                                    </td>
                                    <td class="gsp-transaction-amount">$<?php echo esc_html(number_format($transaction->amount, 2)); ?></td>
                                    <td>
                                        <span class="gsp-status gsp-status-<?php echo esc_attr($transaction->status); ?>">
                                            <?php echo esc_html(ucfirst($transaction->status)); ?>
                                        </span>
                                    </td>
                                    <td class="gsp-transaction-date"><?php echo esc_html(date('M j, Y g:i A', strtotime($transaction->created_at))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Deposit -->
<div id="deposit-modal" class="gsp-modal">
    <div class="gsp-modal-overlay"></div>
    <div class="gsp-modal-container gsp-glass-card">
        <button class="gsp-modal-close">&times;</button>
        <h2 class="gsp-modal-title"><?php esc_html_e('Make a Deposit', 'globalswiftpay-dashboard'); ?></h2>
        <form id="gsp-deposit-form" class="gsp-form">
            <div class="gsp-form-group">
                <label for="deposit-name"><?php esc_html_e('Full Name', 'globalswiftpay-dashboard'); ?></label>
                <input type="text" id="deposit-name" name="name" class="gsp-input" required>
            </div>
            <div class="gsp-form-group">
                <label for="deposit-email"><?php esc_html_e('Email Address', 'globalswiftpay-dashboard'); ?></label>
                <input type="email" id="deposit-email" name="email" class="gsp-input" value="<?php echo esc_attr($current_user->user_email); ?>" required>
            </div>
            <div class="gsp-form-group">
                <label for="deposit-amount"><?php esc_html_e('Deposit Amount ($)', 'globalswiftpay-dashboard'); ?></label>
                <input type="number" id="deposit-amount" name="amount" class="gsp-input" step="0.01" min="1" required>
            </div>
            <button type="submit" class="gsp-btn gsp-btn-primary gsp-btn-full">
                <?php esc_html_e('Submit Deposit', 'globalswiftpay-dashboard'); ?>
            </button>
        </form>
    </div>
</div>

<!-- Modal: Add Balance -->
<div id="add-balance-modal" class="gsp-modal">
    <div class="gsp-modal-overlay"></div>
    <div class="gsp-modal-container gsp-glass-card">
        <button class="gsp-modal-close">&times;</button>
        <h2 class="gsp-modal-title"><?php esc_html_e('Add Balance', 'globalswiftpay-dashboard'); ?></h2>
        
        <div class="gsp-payment-details">
            <h3><?php esc_html_e('Transfer to this account:', 'globalswiftpay-dashboard'); ?></h3>
            <div class="gsp-detail-row">
                <span class="gsp-detail-label"><?php esc_html_e('Bank Name:', 'globalswiftpay-dashboard'); ?></span>
                <span class="gsp-detail-value"><?php echo esc_html($settings['bank_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="gsp-detail-row">
                <span class="gsp-detail-label"><?php esc_html_e('Account Name:', 'globalswiftpay-dashboard'); ?></span>
                <span class="gsp-detail-value"><?php echo esc_html($settings['account_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="gsp-detail-row">
                <span class="gsp-detail-label"><?php esc_html_e('Account Number:', 'globalswiftpay-dashboard'); ?></span>
                <span class="gsp-detail-value gsp-copy-text" data-copy="<?php echo esc_attr($settings['account_number'] ?? ''); ?>"><?php echo esc_html($settings['account_number'] ?? 'N/A'); ?> <span class="gsp-copy-icon">📋</span></span>
            </div>
        </div>
        
        <form id="gsp-add-balance-form" class="gsp-form" enctype="multipart/form-data">
            <div class="gsp-form-group">
                <label for="add-sender-name"><?php esc_html_e('Sender Name', 'globalswiftpay-dashboard'); ?></label>
                <input type="text" id="add-sender-name" name="sender_name" class="gsp-input" required>
            </div>
            <div class="gsp-form-group">
                <label for="add-email"><?php esc_html_e('Email Address', 'globalswiftpay-dashboard'); ?></label>
                <input type="email" id="add-email" name="email" class="gsp-input" value="<?php echo esc_attr($current_user->user_email); ?>" required>
            </div>
            <div class="gsp-form-group">
                <label for="add-amount"><?php esc_html_e('Amount Transferred ($)', 'globalswiftpay-dashboard'); ?></label>
                <input type="number" id="add-amount" name="amount" class="gsp-input" step="0.01" min="1" required>
            </div>
            <div class="gsp-form-group">
                <label for="add-receipt"><?php esc_html_e('Upload Receipt (PDF/PNG/JPEG)', 'globalswiftpay-dashboard'); ?></label>
                <div class="gsp-file-upload">
                    <input type="file" id="add-receipt" name="receipt" class="gsp-file-input" accept=".pdf,.png,.jpg,.jpeg">
                    <label for="add-receipt" class="gsp-file-label">
                        <span class="gsp-file-icon">📎</span>
                        <span class="gsp-file-text"><?php esc_html_e('Choose file...', 'globalswiftpay-dashboard'); ?></span>
                    </label>
                </div>
            </div>
            <button type="submit" class="gsp-btn gsp-btn-primary gsp-btn-full">
                <?php esc_html_e('Submit for Approval', 'globalswiftpay-dashboard'); ?>
            </button>
        </form>
    </div>
</div>

<!-- Modal: Withdraw -->
<div id="withdraw-modal" class="gsp-modal">
    <div class="gsp-modal-overlay"></div>
    <div class="gsp-modal-container gsp-glass-card">
        <button class="gsp-modal-close">&times;</button>
        <h2 class="gsp-modal-title"><?php esc_html_e('Withdraw Funds', 'globalswiftpay-dashboard'); ?></h2>
        <p class="gsp-modal-subtitle"><?php esc_html_e('Available balance:', 'globalswiftpay-dashboard'); ?> <strong>$<?php echo esc_html(number_format($balance->wallet_balance, 2)); ?></strong></p>
        
        <form id="gsp-withdraw-form" class="gsp-form">
            <div class="gsp-form-group">
                <label for="withdraw-amount"><?php esc_html_e('Amount to Withdraw ($)', 'globalswiftpay-dashboard'); ?></label>
                <input type="number" id="withdraw-amount" name="amount" class="gsp-input" step="0.01" min="1" max="<?php echo esc_attr($balance->wallet_balance); ?>" required>
            </div>
            <div class="gsp-form-group">
                <label for="withdraw-method"><?php esc_html_e('Withdrawal Method', 'globalswiftpay-dashboard'); ?></label>
                <select id="withdraw-method" name="method" class="gsp-input gsp-select" required>
                    <option value=""><?php esc_html_e('Select method...', 'globalswiftpay-dashboard'); ?></option>
                    <option value="bank"><?php esc_html_e('Bank Transfer', 'globalswiftpay-dashboard'); ?></option>
                    <option value="btc"><?php esc_html_e('Bitcoin (BTC)', 'globalswiftpay-dashboard'); ?></option>
                    <option value="usdt"><?php esc_html_e('Tether (USDT)', 'globalswiftpay-dashboard'); ?></option>
                </select>
            </div>
            
            <div id="withdraw-bank-details" class="gsp-conditional-fields" style="display: none;">
                <div class="gsp-form-group">
                    <label for="withdraw-bank-name"><?php esc_html_e('Bank Name', 'globalswiftpay-dashboard'); ?></label>
                    <input type="text" id="withdraw-bank-name" name="details[bank_name]" class="gsp-input">
                </div>
                <div class="gsp-form-group">
                    <label for="withdraw-account-name"><?php esc_html_e('Account Name', 'globalswiftpay-dashboard'); ?></label>
                    <input type="text" id="withdraw-account-name" name="details[account_name]" class="gsp-input">
                </div>
                <div class="gsp-form-group">
                    <label for="withdraw-account-number"><?php esc_html_e('Account Number', 'globalswiftpay-dashboard'); ?></label>
                    <input type="text" id="withdraw-account-number" name="details[account_number]" class="gsp-input">
                </div>
            </div>
            
            <div id="withdraw-crypto-details" class="gsp-conditional-fields" style="display: none;">
                <div class="gsp-form-group">
                    <label for="withdraw-wallet-address"><?php esc_html_e('Wallet Address', 'globalswiftpay-dashboard'); ?></label>
                    <input type="text" id="withdraw-wallet-address" name="details[wallet_address]" class="gsp-input">
                </div>
            </div>
            
            <button type="submit" class="gsp-btn gsp-btn-primary gsp-btn-full">
                <?php esc_html_e('Request Withdrawal', 'globalswiftpay-dashboard'); ?>
            </button>
        </form>
    </div>
</div>

<!-- Modal: Transfer -->
<div id="transfer-modal" class="gsp-modal">
    <div class="gsp-modal-overlay"></div>
    <div class="gsp-modal-container gsp-glass-card">
        <button class="gsp-modal-close">&times;</button>
        <h2 class="gsp-modal-title"><?php esc_html_e('Transfer Funds', 'globalswiftpay-dashboard'); ?></h2>
        <p class="gsp-modal-subtitle"><?php esc_html_e('Available balance:', 'globalswiftpay-dashboard'); ?> <strong>$<?php echo esc_html(number_format($balance->wallet_balance, 2)); ?></strong></p>
        
        <form id="gsp-transfer-form" class="gsp-form">
            <div class="gsp-form-group">
                <label for="transfer-email"><?php esc_html_e('Recipient Email', 'globalswiftpay-dashboard'); ?></label>
                <input type="email" id="transfer-email" name="recipient_email" class="gsp-input" required>
            </div>
            <div class="gsp-form-group">
                <label for="transfer-amount"><?php esc_html_e('Amount to Transfer ($)', 'globalswiftpay-dashboard'); ?></label>
                <input type="number" id="transfer-amount" name="amount" class="gsp-input" step="0.01" min="1" max="<?php echo esc_attr($balance->wallet_balance); ?>" required>
            </div>
            <div class="gsp-form-group">
                <label for="transfer-token"><?php esc_html_e('Token Code', 'globalswiftpay-dashboard'); ?></label>
                <input type="text" id="transfer-token" name="token_code" class="gsp-input" placeholder="<?php esc_attr_e('Enter your security token', 'globalswiftpay-dashboard'); ?>" required>
            </div>
            <button type="submit" class="gsp-btn gsp-btn-primary gsp-btn-full">
                <?php esc_html_e('Request Transfer', 'globalswiftpay-dashboard'); ?>
            </button>
        </form>
    </div>
</div>

<!-- Modal: Convert to BTC -->
<div id="convert-btc-modal" class="gsp-modal">
    <div class="gsp-modal-overlay"></div>
    <div class="gsp-modal-container gsp-glass-card">
        <button class="gsp-modal-close">&times;</button>
        <h2 class="gsp-modal-title"><?php esc_html_e('Convert to Bitcoin (BTC)', 'globalswiftpay-dashboard'); ?></h2>
        
        <form id="gsp-convert-btc-form" class="gsp-form">
            <input type="hidden" name="conversion_type" value="btc">
            <div class="gsp-form-group">
                <label for="btc-email"><?php esc_html_e('Email Address', 'globalswiftpay-dashboard'); ?></label>
                <input type="email" id="btc-email" name="email" class="gsp-input" value="<?php echo esc_attr($current_user->user_email); ?>" required>
            </div>
            <div class="gsp-form-group">
                <label for="btc-amount"><?php esc_html_e('Amount to Convert ($)', 'globalswiftpay-dashboard'); ?></label>
                <input type="number" id="btc-amount" name="amount" class="gsp-input" step="0.01" min="1" max="<?php echo esc_attr($balance->wallet_balance); ?>" required>
            </div>
            <div class="gsp-form-group">
                <label for="btc-address"><?php esc_html_e('Bitcoin Address', 'globalswiftpay-dashboard'); ?></label>
                <input type="text" id="btc-address" name="btc_address" class="gsp-input" required>
            </div>
            <div class="gsp-form-group">
                <label for="btc-security"><?php esc_html_e('Security Phrase', 'globalswiftpay-dashboard'); ?></label>
                <input type="text" id="btc-security" name="security_phrase" class="gsp-input" required>
            </div>
            <button type="submit" class="gsp-btn gsp-btn-primary gsp-btn-full">
                <?php esc_html_e('Convert to BTC', 'globalswiftpay-dashboard'); ?>
            </button>
        </form>
    </div>
</div>

<!-- Modal: Convert to USDT -->
<div id="convert-usdt-modal" class="gsp-modal">
    <div class="gsp-modal-overlay"></div>
    <div class="gsp-modal-container gsp-glass-card">
        <button class="gsp-modal-close">&times;</button>
        <h2 class="gsp-modal-title"><?php esc_html_e('Convert to Tether (USDT)', 'globalswiftpay-dashboard'); ?></h2>
        
        <form id="gsp-convert-usdt-form" class="gsp-form">
            <input type="hidden" name="conversion_type" value="usdt">
            <div class="gsp-form-group">
                <label for="usdt-email"><?php esc_html_e('Email Address', 'globalswiftpay-dashboard'); ?></label>
                <input type="email" id="usdt-email" name="email" class="gsp-input" value="<?php echo esc_attr($current_user->user_email); ?>" required>
            </div>
            <div class="gsp-form-group">
                <label for="usdt-amount"><?php esc_html_e('Amount to Convert ($)', 'globalswiftpay-dashboard'); ?></label>
                <input type="number" id="usdt-amount" name="amount" class="gsp-input" step="0.01" min="1" max="<?php echo esc_attr($balance->wallet_balance); ?>" required>
            </div>
            <div class="gsp-form-group">
                <label for="usdt-address"><?php esc_html_e('Tether (USDT) Address', 'globalswiftpay-dashboard'); ?></label>
                <input type="text" id="usdt-address" name="usdt_address" class="gsp-input" required>
            </div>
            <div class="gsp-form-group">
                <label for="usdt-security"><?php esc_html_e('Security Phrase', 'globalswiftpay-dashboard'); ?></label>
                <input type="text" id="usdt-security" name="security_phrase" class="gsp-input" required>
            </div>
            <button type="submit" class="gsp-btn gsp-btn-primary gsp-btn-full">
                <?php esc_html_e('Convert to USDT', 'globalswiftpay-dashboard'); ?>
            </button>
        </form>
    </div>
</div>

<!-- Modal: Convert to Bank -->
<div id="convert-bank-modal" class="gsp-modal">
    <div class="gsp-modal-overlay"></div>
    <div class="gsp-modal-container gsp-glass-card gsp-modal-large">
        <button class="gsp-modal-close">&times;</button>
        <h2 class="gsp-modal-title"><?php esc_html_e('Convert to Bank Transfer', 'globalswiftpay-dashboard'); ?></h2>
        
        <form id="gsp-convert-bank-form" class="gsp-form">
            <input type="hidden" name="conversion_type" value="bank">
            <div class="gsp-form-row">
                <div class="gsp-form-group gsp-form-half">
                    <label for="bank-email"><?php esc_html_e('Email Address', 'globalswiftpay-dashboard'); ?></label>
                    <input type="email" id="bank-email" name="email" class="gsp-input" value="<?php echo esc_attr($current_user->user_email); ?>" required>
                </div>
                <div class="gsp-form-group gsp-form-half">
                    <label for="bank-amount"><?php esc_html_e('Amount in Dollars ($)', 'globalswiftpay-dashboard'); ?></label>
                    <input type="number" id="bank-amount" name="amount" class="gsp-input" step="0.01" min="1" max="<?php echo esc_attr($balance->wallet_balance); ?>" required>
                </div>
            </div>
            <div class="gsp-form-row">
                <div class="gsp-form-group gsp-form-half">
                    <label for="bank-name"><?php esc_html_e('Bank Name', 'globalswiftpay-dashboard'); ?></label>
                    <input type="text" id="bank-name" name="bank_name" class="gsp-input" required>
                </div>
                <div class="gsp-form-group gsp-form-half">
                    <label for="bank-account-name"><?php esc_html_e('Account Name', 'globalswiftpay-dashboard'); ?></label>
                    <input type="text" id="bank-account-name" name="account_name" class="gsp-input" required>
                </div>
            </div>
            <div class="gsp-form-row">
                <div class="gsp-form-group gsp-form-half">
                    <label for="bank-account-no"><?php esc_html_e('Bank Account No.', 'globalswiftpay-dashboard'); ?></label>
                    <input type="text" id="bank-account-no" name="account_number" class="gsp-input" required>
                </div>
                <div class="gsp-form-group gsp-form-half">
                    <label for="bank-swift"><?php esc_html_e('SWIFT/IFSC/Routing No./Sort Code/IBAN', 'globalswiftpay-dashboard'); ?></label>
                    <input type="text" id="bank-swift" name="swift_code" class="gsp-input" required>
                </div>
            </div>
            <div class="gsp-form-row">
                <div class="gsp-form-group gsp-form-half">
                    <label for="bank-address"><?php esc_html_e('Bank Address', 'globalswiftpay-dashboard'); ?></label>
                    <input type="text" id="bank-address" name="bank_address" class="gsp-input" required>
                </div>
                <div class="gsp-form-group gsp-form-half">
                    <label for="bank-country"><?php esc_html_e('Country', 'globalswiftpay-dashboard'); ?></label>
                    <input type="text" id="bank-country" name="country" class="gsp-input" required>
                </div>
            </div>
            <div class="gsp-form-group">
                <label for="bank-security"><?php esc_html_e('Security Phrase', 'globalswiftpay-dashboard'); ?></label>
                <input type="text" id="bank-security" name="security_phrase" class="gsp-input" required>
            </div>
            <button type="submit" class="gsp-btn gsp-btn-primary gsp-btn-full">
                <?php esc_html_e('Convert to Bank Transfer', 'globalswiftpay-dashboard'); ?>
            </button>
        </form>
    </div>
</div>

<!-- Toast Notification -->
<div id="gsp-toast" class="gsp-toast"></div>
