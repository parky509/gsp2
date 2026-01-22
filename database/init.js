const sqlite3 = require('sqlite3').verbose();
const path = require('path');
const bcrypt = require('bcryptjs');

const dbPath = path.join(__dirname, 'dashboard.db');

const db = new sqlite3.Database(dbPath, (err) => {
  if (err) {
    console.error('Error opening database:', err.message);
  } else {
    console.log('Connected to SQLite database');
    initDatabase();
  }
});

function initDatabase() {
  db.serialize(() => {
    // Users table
    db.run(`
      CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        profile_picture TEXT,
        balance REAL DEFAULT 0,
        is_admin INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // Transactions table
    db.run(`
      CREATE TABLE IF NOT EXISTS transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        type TEXT NOT NULL,
        amount REAL NOT NULL,
        status TEXT DEFAULT 'pending',
        details TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id)
      )
    `);

    // Add balance requests table
    db.run(`
      CREATE TABLE IF NOT EXISTS add_balance_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        sender_name TEXT NOT NULL,
        sender_email TEXT NOT NULL,
        receipt_path TEXT NOT NULL,
        status TEXT DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        processed_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users (id)
      )
    `);

    // Transfer requests table
    db.run(`
      CREATE TABLE IF NOT EXISTS transfer_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        from_user_id INTEGER NOT NULL,
        to_user_id INTEGER NOT NULL,
        amount REAL NOT NULL,
        token_code TEXT NOT NULL,
        status TEXT DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        processed_at DATETIME,
        FOREIGN KEY (from_user_id) REFERENCES users (id),
        FOREIGN KEY (to_user_id) REFERENCES users (id)
      )
    `);

    // Withdrawal requests table
    db.run(`
      CREATE TABLE IF NOT EXISTS withdrawal_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        amount REAL NOT NULL,
        details TEXT,
        status TEXT DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        processed_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users (id)
      )
    `);

    // Conversion requests table (BTC, USDT, Bank)
    db.run(`
      CREATE TABLE IF NOT EXISTS conversion_requests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        conversion_type TEXT NOT NULL,
        email TEXT NOT NULL,
        amount REAL NOT NULL,
        destination_address TEXT,
        bank_details TEXT,
        security_phrase TEXT NOT NULL,
        status TEXT DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        processed_at DATETIME,
        FOREIGN KEY (user_id) REFERENCES users (id)
      )
    `);

    // Admin settings table
    db.run(`
      CREATE TABLE IF NOT EXISTS admin_settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        key TEXT UNIQUE NOT NULL,
        value TEXT NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
      )
    `);

    // Insert default admin settings
    const defaultSettings = [
      ['account_number', process.env.ADMIN_ACCOUNT_NUMBER || '1234567890'],
      ['btc_address', process.env.ADMIN_BTC_ADDRESS || 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh'],
      ['usdt_address', process.env.ADMIN_USDT_ADDRESS || '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb']
    ];

    const stmt = db.prepare(`
      INSERT OR IGNORE INTO admin_settings (key, value) VALUES (?, ?)
    `);

    defaultSettings.forEach(([key, value]) => {
      stmt.run(key, value);
    });

    stmt.finalize();

    // Create default admin user (username: admin, password: admin123)
    // IMPORTANT: Change the admin password after first login in production!
    const adminPassword = bcrypt.hashSync('admin123', 10);
    db.run(`
      INSERT OR IGNORE INTO users (username, email, password, balance, is_admin)
      VALUES (?, ?, ?, ?, ?)
    `, ['admin', 'admin@globalswiftpay2.com', adminPassword, 0, 1], function(err) {
      if (!err && this.changes > 0) {
        console.warn('⚠️  WARNING: Default admin account created with password "admin123"');
        console.warn('⚠️  IMPORTANT: Change this password immediately in production!');
      }
    });

    // Create default test user (username: testuser, password: test123)
    const userPassword = bcrypt.hashSync('test123', 10);
    db.run(`
      INSERT OR IGNORE INTO users (username, email, password, balance, is_admin)
      VALUES (?, ?, ?, ?, ?)
    `, ['testuser', 'testuser@example.com', userPassword, 1000.00, 0]);

    console.log('Database initialized successfully');
  });
}

module.exports = db;
