# GlobalSwiftPay2 Dashboard Plugin - Download Instructions

## Download the Plugin

The complete plugin is available as a zip file: `globalswiftpay2-dashboard-plugin.zip` (57KB)

## Installation Steps

1. **Extract the zip file** to your desired location

2. **Install dependencies:**
   ```bash
   cd globalswiftpay2-dashboard-plugin
   npm install
   ```

3. **Configure environment variables:**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

4. **Start the server:**
   ```bash
   npm start
   ```

5. **Access the dashboard:**
   Open your browser to `http://localhost:3000`

## What's Included

- ✅ Complete backend API (Express.js + SQLite)
- ✅ User dashboard with SVG icons (no emojis)
- ✅ Admin dashboard with management panels
- ✅ Glass morphism UI design
- ✅ JWT authentication
- ✅ Email notification system
- ✅ File upload handling
- ✅ Rate limiting middleware
- ✅ Security patches applied (Multer 2.0.2, Nodemailer 7.0.7)

## Demo Accounts

**User Account:**
- Username: `testuser`
- Password: `test123`
- Starting Balance: $1,000.00

**Admin Account:**
- Username: `admin`
- Password: `admin123` ⚠️ **Change in production!**

## Features

### User Dashboard
- Real-time wallet balance display
- Deposit functionality
- Savings view
- Convert to Bitcoin (BTC)
- Convert to USDT (Tether)
- Convert to Bank account
- Add balance with receipt upload (PDF/PNG/JPEG)
- Transfer funds to other users
- Withdraw funds
- Transaction history

### Admin Dashboard
- Settings management (account numbers, crypto addresses)
- Approve/decline add balance requests
- Approve/decline transfer requests
- Approve/decline withdrawal requests
- View conversion requests
- User management

## Production Deployment

⚠️ **Before deploying to production:**

1. Change the default admin password
2. Configure email settings in `.env`
3. Use strong JWT and session secrets
4. Set up SSL/TLS certificates
5. Use PostgreSQL/MySQL instead of SQLite
6. Implement Redis-backed rate limiting
7. Enable helmet.js for HTTP security headers
8. Set up proper logging and monitoring

## Support

For issues or questions, please refer to the README.md file included in the package.

## Package Contents

```
globalswiftpay2-dashboard-plugin/
├── server/               # Backend API
│   ├── routes/          # API endpoints
│   ├── middleware/      # Auth & rate limiting
│   ├── services/        # Email service
│   └── index.js         # Server entry point
├── public/              # Frontend files
│   ├── css/            # Glass morphism styles
│   ├── js/             # Dashboard logic
│   ├── index.html      # Login page
│   ├── dashboard.html  # User dashboard
│   └── admin.html      # Admin dashboard
├── database/            # Database initialization
├── uploads/             # File uploads directory
├── package.json         # Dependencies
├── .env.example         # Configuration template
└── README.md           # Full documentation
```

## Version

Current version: 1.0.0
Last updated: January 22, 2026

## License

ISC
