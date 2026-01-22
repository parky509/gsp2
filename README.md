# GlobalSwiftPay2 Investment Dashboard

A comprehensive investment dashboard plugin for GlobalSwiftPay2.com with modern glass morphism design.

## Features

### User Dashboard
- 💰 Real-time wallet balance display
- 📥 Deposit functionality
- 💳 Savings view
- ₿ Convert to Bitcoin (BTC)
- 💵 Convert to USDT (Tether)
- 🏦 Convert to Bank account
- ➕ Add balance with receipt upload
- 🔄 Transfer funds to other users
- 💸 Withdraw funds
- 📊 Transaction history with real-time updates

### Admin Dashboard
- ⚙️ Settings management (account number, BTC/USDT addresses)
- ✅ Approve/decline add balance requests
- ✅ Approve/decline transfer requests
- ✅ Approve/decline withdrawal requests
- 👥 User management
- 📧 Automated email notifications

## Design Features
- **Crystal blue** primary color theme
- **Glass morphism** effects on all UI elements
- **Responsive design** for all screen sizes
- **Modern and professional** aesthetic
- **Smooth animations** and transitions

## Tech Stack
- **Backend**: Node.js, Express.js
- **Database**: SQLite3
- **Frontend**: Vanilla JavaScript, HTML5, CSS3
- **Authentication**: JWT (JSON Web Tokens)
- **File Upload**: Multer
- **Email**: Nodemailer

## Installation

1. Clone the repository:
```bash
git clone https://github.com/parky509/gsp2.git
cd gsp2
```

2. Install dependencies:
```bash
npm install
```

3. Configure environment variables:
```bash
cp .env.example .env
# Edit .env with your configuration
```

4. Start the server:
```bash
npm start
```

Or for development with auto-reload:
```bash
npm run dev
```

5. Open your browser and navigate to:
```
http://localhost:3000
```

## Default Accounts

### User Account
- **Username**: `testuser`
- **Password**: `test123`
- **Balance**: $1,000.00

### Admin Account
- **Username**: `admin`
- **Password**: `admin123`

## Configuration

Edit the `.env` file to configure:

- `PORT`: Server port (default: 3000)
- `SESSION_SECRET`: Session secret key
- `JWT_SECRET`: JWT secret key
- `EMAIL_HOST`: SMTP email host
- `EMAIL_PORT`: SMTP port
- `EMAIL_USER`: Email username
- `EMAIL_PASSWORD`: Email password
- `ADMIN_ACCOUNT_NUMBER`: Account number for deposits
- `ADMIN_BTC_ADDRESS`: Bitcoin address for conversions
- `ADMIN_USDT_ADDRESS`: USDT address for conversions

## API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `GET /api/auth/me` - Get current user

### User Operations
- `GET /api/user/balance` - Get user balance
- `POST /api/user/deposit` - Create deposit request
- `POST /api/user/add-balance` - Submit add balance request with receipt
- `POST /api/user/transfer` - Create transfer request
- `POST /api/user/withdraw` - Create withdrawal request
- `POST /api/user/convert` - Create conversion request (BTC/USDT/Bank)
- `GET /api/user/transactions` - Get transaction history

### Admin Operations
- `GET /api/admin/settings` - Get admin settings
- `PUT /api/admin/settings` - Update admin settings
- `GET /api/admin/add-balance-requests` - Get all add balance requests
- `POST /api/admin/add-balance-requests/:id/:action` - Approve/decline request
- `GET /api/admin/transfer-requests` - Get all transfer requests
- `POST /api/admin/transfer-requests/:id/:action` - Approve/decline transfer
- `GET /api/admin/withdrawal-requests` - Get all withdrawal requests
- `POST /api/admin/withdrawal-requests/:id/:action` - Approve/decline withdrawal
- `GET /api/admin/conversion-requests` - Get all conversion requests
- `GET /api/admin/users` - Get all users

## Usage

### User Dashboard
1. Login with user credentials
2. View your wallet balance
3. Use action buttons to:
   - Make deposits
   - View savings
   - Convert funds to crypto or bank
   - Add balance by uploading receipt
   - Transfer to other users
   - Withdraw funds
4. View transaction history in real-time

### Admin Dashboard
1. Login with admin credentials
2. Navigate through tabs:
   - **Settings**: Update account numbers and addresses
   - **Add Balance Requests**: Review and approve/decline requests with receipts
   - **Transfer Requests**: Approve/decline user transfers
   - **Withdrawal Requests**: Approve/decline withdrawals
   - **Conversion Requests**: View all conversion requests
   - **Users**: Manage user accounts

## Security Features
- Password hashing with bcryptjs
- JWT authentication
- Session management
- Input validation
- File upload restrictions (MIME type and size validation)
- Secure API endpoints
- Rate limiting (100 requests per 15 minutes per IP for API, 5 login attempts per 15 minutes)

**Dependencies Security:**
- ✅ **Multer upgraded to 2.0.2** - Fixes DoS vulnerabilities (CVE-2024-XXXX)
- ✅ **Nodemailer upgraded to 7.0.7** - Fixes email domain interpretation conflict
- ⚠️ **SQLite3 transitive dependencies** - Has vulnerabilities in build-time dependencies (tar, node-gyp). These do not affect runtime security but should be monitored.
- For production, consider using `express-rate-limit` with Redis for distributed rate limiting

## Email Notifications
Users receive email notifications for:
- Add balance approval/decline
- Transfer approval/decline
- Withdrawal approval/decline

## Database Schema
- `users` - User accounts and balances
- `transactions` - Transaction history
- `add_balance_requests` - Add balance requests with receipts
- `transfer_requests` - Transfer requests between users
- `withdrawal_requests` - Withdrawal requests
- `conversion_requests` - Crypto and bank conversion requests
- `admin_settings` - Configurable admin settings

## Production Security Checklist

⚠️ **IMPORTANT FOR PRODUCTION:**
- [ ] Change the default admin password immediately
- [ ] Configure email settings in `.env`
- [ ] Use environment-specific secrets for JWT and sessions
- [ ] Monitor SQLite3 build dependencies for updates
- [ ] Upgrade to distributed rate limiting with Redis for multi-server deployments
- [ ] Set up proper SSL/TLS certificates
- [ ] Use a production-grade database (PostgreSQL/MySQL) instead of SQLite
- [ ] Implement proper logging and monitoring
- [ ] Add CSRF protection for state-changing operations
- [ ] Enable helmet.js for additional HTTP security headers
- [ ] Run `npm audit` regularly and address any runtime vulnerabilities

## License
ISC

## Support
For support, please contact the development team at support@globalswiftpay2.com
