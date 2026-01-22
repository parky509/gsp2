const express = require('express');
const router = express.Router();
const multer = require('multer');
const path = require('path');
const fs = require('fs');
const { v4: uuidv4 } = require('uuid');
const db = require('../../database/init');
const { authenticateToken } = require('../middleware/auth');

// Create uploads directory if it doesn't exist
const uploadsDir = path.join(__dirname, '../../uploads');
if (!fs.existsSync(uploadsDir)) {
  fs.mkdirSync(uploadsDir, { recursive: true });
}

// Configure multer for file uploads
const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    cb(null, uploadsDir);
  },
  filename: function (req, file, cb) {
    const uniqueName = `${uuidv4()}${path.extname(file.originalname)}`;
    cb(null, uniqueName);
  }
});

const upload = multer({
  storage: storage,
  fileFilter: (req, file, cb) => {
    const allowedTypes = /pdf|png|jpeg|jpg/i;  // Case-insensitive
    const extname = allowedTypes.test(path.extname(file.originalname).toLowerCase());
    const mimetype = file.mimetype && (
      file.mimetype === 'application/pdf' ||
      file.mimetype === 'image/png' ||
      file.mimetype === 'image/jpeg' ||
      file.mimetype === 'image/jpg'
    );

    if (extname && mimetype) {
      return cb(null, true);
    } else {
      cb(new Error('Only PDF, PNG, and JPEG files are allowed'));
    }
  },
  limits: { fileSize: 5 * 1024 * 1024 } // 5MB limit
});

// Get user balance
router.get('/balance', authenticateToken, (req, res) => {
  db.get(
    'SELECT balance FROM users WHERE id = ?',
    [req.user.id],
    (err, user) => {
      if (err) {
        return res.status(500).json({ error: 'Database error' });
      }
      res.json({ balance: user ? user.balance : 0 });
    }
  );
});

// Create deposit request
router.post('/deposit', authenticateToken, (req, res) => {
  const { name, email, amount } = req.body;

  if (!name || !email || !amount) {
    return res.status(400).json({ error: 'All fields required' });
  }

  if (isNaN(amount) || amount <= 0) {
    return res.status(400).json({ error: 'Invalid amount' });
  }

  const details = JSON.stringify({ name, email, amount });

  db.run(
    'INSERT INTO transactions (user_id, type, amount, status, details) VALUES (?, ?, ?, ?, ?)',
    [req.user.id, 'deposit', amount, 'pending', details],
    function(err) {
      if (err) {
        return res.status(500).json({ error: 'Database error' });
      }

      res.json({
        message: 'Deposit request created',
        transactionId: this.lastID
      });
    }
  );
});

// Submit add balance request with receipt
router.post('/add-balance', authenticateToken, upload.single('receipt'), (req, res) => {
  const { sender_name, sender_email } = req.body;

  if (!sender_name || !sender_email || !req.file) {
    return res.status(400).json({ error: 'All fields and receipt required' });
  }

  db.run(
    'INSERT INTO add_balance_requests (user_id, sender_name, sender_email, receipt_path) VALUES (?, ?, ?, ?)',
    [req.user.id, sender_name, sender_email, req.file.filename],
    function(err) {
      if (err) {
        return res.status(500).json({ error: 'Database error' });
      }

      res.json({
        message: 'Add balance request submitted',
        requestId: this.lastID
      });
    }
  );
});

// Create transfer request
router.post('/transfer', authenticateToken, (req, res) => {
  const { recipient_username, amount, token_code } = req.body;

  if (!recipient_username || !amount || !token_code) {
    return res.status(400).json({ error: 'All fields required' });
  }

  if (isNaN(amount) || amount <= 0) {
    return res.status(400).json({ error: 'Invalid amount' });
  }

  // Check if sender has sufficient balance
  db.get('SELECT balance FROM users WHERE id = ?', [req.user.id], (err, user) => {
    if (err) {
      return res.status(500).json({ error: 'Database error' });
    }

    if (!user || user.balance < amount) {
      return res.status(400).json({ error: 'Insufficient balance' });
    }

    // Find recipient
    db.get('SELECT id FROM users WHERE username = ?', [recipient_username], (err, recipient) => {
      if (err) {
        return res.status(500).json({ error: 'Database error' });
      }

      if (!recipient) {
        return res.status(404).json({ error: 'Recipient not found' });
      }

      if (recipient.id === req.user.id) {
        return res.status(400).json({ error: 'Cannot transfer to yourself' });
      }

      db.run(
        'INSERT INTO transfer_requests (from_user_id, to_user_id, amount, token_code) VALUES (?, ?, ?, ?)',
        [req.user.id, recipient.id, amount, token_code],
        function(err) {
          if (err) {
            return res.status(500).json({ error: 'Database error' });
          }

          res.json({
            message: 'Transfer request submitted for admin approval',
            requestId: this.lastID
          });
        }
      );
    });
  });
});

// Create withdrawal request
router.post('/withdraw', authenticateToken, (req, res) => {
  const { amount, details } = req.body;

  if (!amount || !details) {
    return res.status(400).json({ error: 'Amount and details required' });
  }

  if (isNaN(amount) || amount <= 0) {
    return res.status(400).json({ error: 'Invalid amount' });
  }

  // Check if user has sufficient balance
  db.get('SELECT balance FROM users WHERE id = ?', [req.user.id], (err, user) => {
    if (err) {
      return res.status(500).json({ error: 'Database error' });
    }

    if (!user || user.balance < amount) {
      return res.status(400).json({ error: 'Insufficient balance' });
    }

    db.run(
      'INSERT INTO withdrawal_requests (user_id, amount, details) VALUES (?, ?, ?)',
      [req.user.id, amount, details],
      function(err) {
        if (err) {
          return res.status(500).json({ error: 'Database error' });
        }

        res.json({
          message: 'Withdrawal request submitted for admin approval',
          requestId: this.lastID
        });
      }
    );
  });
});

// Create conversion request (BTC, USDT, Bank)
router.post('/convert', authenticateToken, (req, res) => {
  const { conversion_type, email, amount, destination_address, bank_details, security_phrase } = req.body;

  if (!conversion_type || !email || !amount || !security_phrase) {
    return res.status(400).json({ error: 'Required fields missing' });
  }

  if (isNaN(amount) || amount <= 0) {
    return res.status(400).json({ error: 'Invalid amount' });
  }

  const validTypes = ['btc', 'usdt', 'bank'];
  if (!validTypes.includes(conversion_type)) {
    return res.status(400).json({ error: 'Invalid conversion type' });
  }

  if ((conversion_type === 'btc' || conversion_type === 'usdt') && !destination_address) {
    return res.status(400).json({ error: 'Destination address required' });
  }

  if (conversion_type === 'bank' && !bank_details) {
    return res.status(400).json({ error: 'Bank details required' });
  }

  db.run(
    'INSERT INTO conversion_requests (user_id, conversion_type, email, amount, destination_address, bank_details, security_phrase) VALUES (?, ?, ?, ?, ?, ?, ?)',
    [req.user.id, conversion_type, email, amount, destination_address, bank_details, security_phrase],
    function(err) {
      if (err) {
        return res.status(500).json({ error: 'Database error' });
      }

      res.json({
        message: 'Conversion request submitted',
        requestId: this.lastID
      });
    }
  );
});

// Get user transactions
router.get('/transactions', authenticateToken, (req, res) => {
  db.all(
    'SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50',
    [req.user.id],
    (err, transactions) => {
      if (err) {
        return res.status(500).json({ error: 'Database error' });
      }
      res.json(transactions);
    }
  );
});

// Get all users (for transfer recipient selection)
router.get('/users', authenticateToken, (req, res) => {
  db.all(
    'SELECT id, username, email FROM users WHERE id != ? AND is_admin = 0',
    [req.user.id],
    (err, users) => {
      if (err) {
        return res.status(500).json({ error: 'Database error' });
      }
      res.json(users);
    }
  );
});

module.exports = router;
