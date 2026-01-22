/**
 * GlobalSwiftPay2 Investment Dashboard JavaScript
 * Handles all frontend interactions, modals, and AJAX requests
 */

(function($) {
    'use strict';

    const GSP2Dashboard = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Action button clicks
            $('.gsp2-action-btn').on('click', this.handleActionClick);
            
            // Modal close
            $('.gsp2-modal-close').on('click', this.closeModal);
            $(window).on('click', function(e) {
                if ($(e.target).is('.gsp2-modal')) {
                    GSP2Dashboard.closeModal();
                }
            });
        },

        handleActionClick: function() {
            const action = $(this).data('action');
            GSP2Dashboard.showModal(action);
        },

        showModal: function(action) {
            let content = '';

            switch(action) {
                case 'deposit':
                    content = GSP2Dashboard.getDepositForm();
                    break;
                case 'savings':
                    content = GSP2Dashboard.getSavingsView();
                    break;
                case 'btc':
                    content = GSP2Dashboard.getBTCForm();
                    break;
                case 'usdt':
                    content = GSP2Dashboard.getUSDTForm();
                    break;
                case 'bank':
                    content = GSP2Dashboard.getBankForm();
                    break;
                case 'add-balance':
                    content = GSP2Dashboard.getAddBalanceForm();
                    break;
                case 'transfer':
                    content = GSP2Dashboard.getTransferForm();
                    break;
                case 'withdraw':
                    content = GSP2Dashboard.getWithdrawForm();
                    break;
            }

            $('#gsp2-modal-body').html(content);
            $('#gsp2-modal').fadeIn(300);
        },

        closeModal: function() {
            $('#gsp2-modal').fadeOut(300);
        },

        getDepositForm: function() {
            return `
                <h2>Deposit</h2>
                <form id="gsp2-deposit-form">
                    <div class="gsp2-form-group">
                        <label>Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Deposit Amount ($) *</label>
                        <input type="number" name="amount" min="1" step="0.01" required>
                    </div>
                    <button type="submit" class="gsp2-submit-btn">Submit Deposit</button>
                </form>
            `;
        },

        getSavingsView: function() {
            const balance = $('.gsp2-balance-amount').text();
            return `
                <h2>Savings</h2>
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 16px; color: #666; margin-bottom: 15px;">Current Balance</div>
                    <div style="font-size: 42px; font-weight: 700; color: #00a8ff;">${balance}</div>
                    <p style="margin-top: 30px; color: #999;">Your current savings balance</p>
                </div>
            `;
        },

        getBTCForm: function() {
            return `
                <h2>Convert to BTC</h2>
                <form id="gsp2-btc-form">
                    <div class="gsp2-form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Amount to Convert ($) *</label>
                        <input type="number" name="amount" min="1" step="0.01" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Bitcoin Address *</label>
                        <input type="text" name="btc_address" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Security Phrase *</label>
                        <input type="text" name="security_phrase" required>
                    </div>
                    <button type="submit" class="gsp2-submit-btn">Submit Conversion</button>
                </form>
            `;
        },

        getUSDTForm: function() {
            return `
                <h2>Convert to USDT</h2>
                <form id="gsp2-usdt-form">
                    <div class="gsp2-form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Amount to Convert ($) *</label>
                        <input type="number" name="amount" min="1" step="0.01" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>USDT (Tether) Address *</label>
                        <input type="text" name="usdt_address" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Security Phrase *</label>
                        <input type="text" name="security_phrase" required>
                    </div>
                    <button type="submit" class="gsp2-submit-btn">Submit Conversion</button>
                </form>
            `;
        },

        getBankForm: function() {
            return `
                <h2>Convert to Bank</h2>
                <form id="gsp2-bank-form">
                    <div class="gsp2-form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Amount in Dollars ($) *</label>
                        <input type="number" name="amount" min="1" step="0.01" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Bank Name *</label>
                        <input type="text" name="bank_name" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Account Name *</label>
                        <input type="text" name="account_name" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Bank Account Number *</label>
                        <input type="text" name="account_number" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>SWIFT/IFSC/Routing No/Sort Code/IBAN *</label>
                        <input type="text" name="routing_number" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Bank Address *</label>
                        <textarea name="bank_address" rows="3" required></textarea>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Country *</label>
                        <input type="text" name="country" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Security Phrase *</label>
                        <input type="text" name="security_phrase" required>
                    </div>
                    <button type="submit" class="gsp2-submit-btn">Submit Conversion</button>
                </form>
            `;
        },

        getAddBalanceForm: function() {
            return `
                <h2>Add Balance</h2>
                <div style="background: #e3f2fd; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <strong>Account Number:</strong> <span id="gsp2-account-number">Loading...</span>
                </div>
                <form id="gsp2-add-balance-form" enctype="multipart/form-data">
                    <div class="gsp2-form-group">
                        <label>Upload Receipt (PDF/PNG/JPEG) *</label>
                        <input type="file" name="receipt" accept=".pdf,.png,.jpg,.jpeg" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Sender Name *</label>
                        <input type="text" name="sender_name" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Sender Email *</label>
                        <input type="email" name="sender_email" required>
                    </div>
                    <button type="submit" class="gsp2-submit-btn">Submit for Review</button>
                </form>
            `;
        },

        getTransferForm: function() {
            return `
                <h2>Transfer Balance</h2>
                <form id="gsp2-transfer-form">
                    <div class="gsp2-form-group">
                        <label>Recipient Username *</label>
                        <input type="text" name="recipient" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Amount to Transfer ($) *</label>
                        <input type="number" name="amount" min="1" step="0.01" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Token Code *</label>
                        <input type="text" name="token_code" required>
                    </div>
                    <button type="submit" class="gsp2-submit-btn">Submit Transfer</button>
                </form>
            `;
        },

        getWithdrawForm: function() {
            return `
                <h2>Withdraw Funds</h2>
                <form id="gsp2-withdraw-form">
                    <div class="gsp2-form-group">
                        <label>Amount to Withdraw ($) *</label>
                        <input type="number" name="amount" min="1" step="0.01" required>
                    </div>
                    <div class="gsp2-form-group">
                        <label>Withdrawal Details *</label>
                        <textarea name="details" rows="4" required placeholder="Enter your withdrawal method and details"></textarea>
                    </div>
                    <button type="submit" class="gsp2-submit-btn">Submit Withdrawal</button>
                </form>
            `;
        },

        handleFormSubmit: function(form, action) {
            const formData = new FormData(form[0]);
            formData.append('action', 'gsp2_' + action);
            formData.append('nonce', gsp2_ajax.nonce);

            $.ajax({
                url: gsp2_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        GSP2Dashboard.init();

        // Handle form submissions with event delegation
        $(document).on('submit', '#gsp2-deposit-form', function(e) {
            e.preventDefault();
            GSP2Dashboard.handleFormSubmit($(this), 'deposit');
        });

        $(document).on('submit', '#gsp2-btc-form', function(e) {
            e.preventDefault();
            GSP2Dashboard.handleFormSubmit($(this), 'convert_btc');
        });

        $(document).on('submit', '#gsp2-usdt-form', function(e) {
            e.preventDefault();
            GSP2Dashboard.handleFormSubmit($(this), 'convert_usdt');
        });

        $(document).on('submit', '#gsp2-bank-form', function(e) {
            e.preventDefault();
            GSP2Dashboard.handleFormSubmit($(this), 'convert_bank');
        });

        $(document).on('submit', '#gsp2-add-balance-form', function(e) {
            e.preventDefault();
            GSP2Dashboard.handleFormSubmit($(this), 'add_balance');
        });

        $(document).on('submit', '#gsp2-transfer-form', function(e) {
            e.preventDefault();
            GSP2Dashboard.handleFormSubmit($(this), 'transfer');
        });

        $(document).on('submit', '#gsp2-withdraw-form', function(e) {
            e.preventDefault();
            GSP2Dashboard.handleFormSubmit($(this), 'withdraw');
        });

        // Load account number when add balance form is shown
        $(document).on('DOMNodeInserted', '#gsp2-account-number', function() {
            if ($(this).text() === 'Loading...') {
                $.ajax({
                    url: gsp2_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'gsp2_get_account_number',
                        nonce: gsp2_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#gsp2-account-number').text(response.data.account_number);
                        }
                    }
                });
            }
        });
    });

})(jQuery);
