# GlobalSwiftPay2 Investment Dashboard - WordPress Plugin

A comprehensive investment dashboard plugin for WordPress with admin approval workflows, transaction management, and glass morphism UI design.

## Features

### User Features
- **Wallet Balance** - Real-time balance display
- **Deposit Requests** - Submit deposit information for admin review
- **Savings View** - View current balance
- **Crypto Conversions**:
  - Convert to Bitcoin (BTC)
  - Convert to USDT (Tether)
- **Bank Transfers** - Complete bank transfer with full details
- **Add Balance** - Upload receipts for balance additions (PDF/PNG/JPEG)
- **P2P Transfers** - Transfer funds to other users with token verification
- **Withdrawals** - Request fund withdrawals
- **Transaction History** - Real-time transaction tracking

### Admin Features
- **Settings Management** - Configure account numbers and crypto addresses
- **Add Balance Requests** - Review uploaded receipts and approve/decline
- **Transfer Requests** - Manage P2P transfer requests
- **Withdrawal Requests** - Process withdrawal requests with balance updates
- **Conversion Requests** - View and manage all conversion requests
- **User Management** - View all users with their current balances
- **Email Notifications** - Automatic notifications on request status changes

### Design
- **Glass Morphism Theme** throughout
- **Crystal Blue (#00a8ff)** primary color
- **Ash Background (#f5f5f5)** for clean, professional look
- **SVG Icons** from Material Design Icons
- **Responsive Design** - Mobile-friendly layout
- **Backdrop Blur Effects** on all cards and modals
- **Smooth Animations** and hover effects

## Installation

### Method 1: Through WordPress Admin (Recommended)

1. Download the plugin zip file
2. Log in to your WordPress admin panel
3. Navigate to **Plugins → Add New → Upload Plugin**
4. Click "Choose File" and select the downloaded zip file
5. Click "Install Now"
6. After installation, click "Activate Plugin"

### Method 2: Manual Installation

1. Download and extract the plugin zip file
2. Upload the `globalswiftpay2-investment-dashboard` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress

## Configuration

### 1. Admin Setup

After activation, go to **GSP2 Dashboard → Settings** in your WordPress admin panel:

- Set your **Account Number** (for add balance feature)
- Set your **BTC Address** (for Bitcoin conversions)
- Set your **USDT Address** (for Tether conversions)

### 2. User Roles

The plugin creates two custom roles:

- **GSP2 User** - Can access dashboard and submit requests
- **GSP2 Admin** - Can approve/decline requests and manage settings

### 3. Email Configuration

The plugin uses WordPress's built-in `wp_mail()` function. For best results:

- Configure SMTP settings using a plugin like "WP Mail SMTP"
- Test email delivery to ensure notifications work

## Usage

### For Users

Add the shortcode to any page or post to display the user dashboard:

```
[gsp2_dashboard]
```

Or for just the balance widget:

```
[gsp2_balance]
```

### For Admins

Access the admin panel through **GSP2 Dashboard** in the WordPress admin menu:

1. **Settings** - Configure account details
2. **Add Balance Requests** - Review receipt uploads and approve/decline
3. **Transfer Requests** - Process P2P transfers between users
4. **Withdrawal Requests** - Approve/decline withdrawal requests
5. **Conversion Requests** - View BTC/USDT/Bank conversion requests
6. **Users** - View all registered users and their balances

## Database Tables

The plugin creates 7 custom database tables:

- `wp_gsp2_user_balances` - User wallet balances
- `wp_gsp2_transactions` - Transaction history
- `wp_gsp2_add_balance_requests` - Balance addition requests
- `wp_gsp2_transfer_requests` - P2P transfer requests
- `wp_gsp2_withdrawal_requests` - Withdrawal requests
- `wp_gsp2_conversion_requests` - Conversion requests (BTC/USDT/Bank)
- `wp_gsp2_settings` - Plugin settings

## Requirements

- **WordPress**: 5.8 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher

## File Upload Support

Supported formats for receipt uploads:
- PDF (.pdf)
- PNG (.png)
- JPEG (.jpg, .jpeg)

Maximum file size: 5MB (configurable in WordPress settings)

## Security

- **WordPress Nonces** - All AJAX requests verified with nonces
- **Capability Checks** - Role-based access control
- **Data Sanitization** - All input sanitized and validated
- **SQL Injection Protection** - Using WordPress $wpdb prepared statements
- **XSS Protection** - All output escaped using esc_html(), esc_attr(), etc.

## Support

For issues, questions, or feature requests, please contact support@globalswiftpay2.com

## Changelog

### Version 1.0.0
- Initial release
- User dashboard with 8 transaction types
- Admin approval workflows
- Email notification system
- Glass morphism UI design
- Mobile-responsive layout
- File upload support

## License

This plugin is proprietary software. All rights reserved.

## Credits

- **SVG Icons**: Material Design Icons
- **Design**: Glass Morphism theme with backdrop blur effects
- **Development**: Built for GlobalSwiftPay2

## Screenshots

1. User Dashboard - Balance and action buttons with glass morphism design
2. Transaction History - Real-time transaction tracking
3. Admin Settings - Configure account numbers and crypto addresses
4. Admin Requests Panel - Review and approve/decline requests
5. User Management - View all users with balances

## Technical Details

### Shortcodes

- `[gsp2_dashboard]` - Full user dashboard
- `[gsp2_balance]` - Balance widget only

### AJAX Actions

**User Actions:**
- `gsp2_deposit` - Submit deposit request
- `gsp2_convert_btc` - Submit BTC conversion
- `gsp2_convert_usdt` - Submit USDT conversion
- `gsp2_convert_bank` - Submit bank conversion
- `gsp2_add_balance` - Submit balance addition with receipt
- `gsp2_transfer` - Submit P2P transfer
- `gsp2_withdraw` - Submit withdrawal request
- `gsp2_get_account_number` - Get admin account number

**Admin Actions:**
- `gsp2_update_settings` - Update plugin settings
- `gsp2_approve_add_balance` - Approve balance addition
- `gsp2_decline_add_balance` - Decline balance addition
- `gsp2_approve_transfer` - Approve P2P transfer
- `gsp2_decline_transfer` - Decline P2P transfer
- `gsp2_approve_withdrawal` - Approve withdrawal
- `gsp2_decline_withdrawal` - Decline withdrawal
- `gsp2_approve_conversion` - Approve conversion
- `gsp2_decline_conversion` - Decline conversion

### Filters

- `gsp2_max_upload_size` - Modify maximum file upload size
- `gsp2_allowed_file_types` - Modify allowed file types for receipts
- `gsp2_email_from_name` - Customize email sender name
- `gsp2_email_from_address` - Customize email sender address

### Actions

- `gsp2_after_activation` - Fires after plugin activation
- `gsp2_after_deactivation` - Fires after plugin deactivation
- `gsp2_request_approved` - Fires when a request is approved
- `gsp2_request_declined` - Fires when a request is declined
- `gsp2_balance_updated` - Fires when user balance is updated

## Troubleshooting

### Emails Not Sending

1. Install and configure an SMTP plugin like "WP Mail SMTP"
2. Test email delivery from WordPress settings
3. Check spam folders

### File Uploads Failing

1. Check PHP upload_max_filesize setting
2. Verify WordPress media upload settings
3. Ensure uploads directory is writable

### Database Errors

1. Deactivate and reactivate the plugin
2. Check database user permissions
3. Verify MySQL version compatibility

### Styles Not Loading

1. Clear WordPress cache
2. Clear browser cache
3. Check file permissions on assets directory
4. Verify theme compatibility

---

**Version**: 1.0.0
**Author**: GlobalSwiftPay2
**Requires at least**: WordPress 5.8
**Tested up to**: WordPress 6.4
**Requires PHP**: 7.4
