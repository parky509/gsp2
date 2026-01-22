const express = require('express');
const router = express.Router();
const db = require('../../database/init');
const { authenticateToken, authenticateAdmin } = require('../middleware/auth');
const { sendEmail } = require('../services/email');

// All routes require authentication and admin privileges
router.use(authenticateToken);
router.use(authenticateAdmin);

// Get admin settings
router.get('/settings', (req, res) => {
  db.all('SELECT key, value FROM admin_settings', [], (err, settings) => {
    if (err) {
      return res.status(500).json({ error: 'Database error' });
    }

    const settingsObj = {};
    settings.forEach(setting => {
      settingsObj[setting.key] = setting.value;
    });

    res.json(settingsObj);
  });
});

// Update admin settings
router.put('/settings', (req, res) => {
  const { account_number, btc_address, usdt_address } = req.body;

  const updates = [];
  if (account_number) updates.push(['account_number', account_number]);
  if (btc_address) updates.push(['btc_address', btc_address]);
  if (usdt_address) updates.push(['usdt_address', usdt_address]);

  if (updates.length === 0) {
    return res.status(400).json({ error: 'No settings to update' });
  }

  const stmt = db.prepare(`
    INSERT OR REPLACE INTO admin_settings (key, value, updated_at)
    VALUES (?, ?, CURRENT_TIMESTAMP)
  `);

  updates.forEach(([key, value]) => {
    stmt.run(key, value);
  });

  stmt.finalize((err) => {
    if (err) {
      return res.status(500).json({ error: 'Database error' });
    }
    res.json({ message: 'Settings updated successfully' });
  });
});

// Get all add balance requests
router.get('/add-balance-requests', (req, res) => {
  db.all(`
    SELECT abr.*, u.username, u.email as user_email
    FROM add_balance_requests abr
    JOIN users u ON abr.user_id = u.id
    ORDER BY abr.created_at DESC
  `, [], (err, requests) => {
    if (err) {
      return res.status(500).json({ error: 'Database error' });
    }
    res.json(requests);
  });
});

// Approve/decline add balance request
router.post('/add-balance-requests/:id/:action', (req, res) => {
  const { id, action } = req.params;

  if (action !== 'approve' && action !== 'decline') {
    return res.status(400).json({ error: 'Invalid action' });
  }

  db.get('SELECT * FROM add_balance_requests WHERE id = ?', [id], (err, request) => {
    if (err) {
      return res.status(500).json({ error: 'Database error' });
    }

    if (!request) {
      return res.status(404).json({ error: 'Request not found' });
    }

    if (request.status !== 'pending') {
      return res.status(400).json({ error: 'Request already processed' });
    }

    const status = action === 'approve' ? 'approved' : 'declined';

    db.run(
      'UPDATE add_balance_requests SET status = ?, processed_at = CURRENT_TIMESTAMP WHERE id = ?',
      [status, id],
      (err) => {
        if (err) {
          return res.status(500).json({ error: 'Database error' });
        }

        if (action === 'approve') {
          // Get amount from sender_name or default to 0 (should be passed separately in production)
          const amount = parseFloat(req.body.amount) || 0;

          db.run(
            'UPDATE users SET balance = balance + ? WHERE id = ?',
            [amount, request.user_id],
            (err) => {
              if (err) {
                return res.status(500).json({ error: 'Database error updating balance' });
              }

              // Add transaction record
              db.run(
                'INSERT INTO transactions (user_id, type, amount, status, details) VALUES (?, ?, ?, ?, ?)',
                [request.user_id, 'add_balance', amount, 'completed', JSON.stringify({ request_id: id })],
                () => {}
              );

              // Send email notification
              db.get('SELECT username, email FROM users WHERE id = ?', [request.user_id], (err, user) => {
                if (user) {
                  sendEmail(user.email, 'addBalanceApproved', user.username, amount);
                }
              });

              res.json({ message: 'Add balance request approved' });
            }
          );
        } else {
          // Send decline email
          db.get('SELECT username, email FROM users WHERE id = ?', [request.user_id], (err, user) => {
            if (user) {
              sendEmail(user.email, 'addBalanceDeclined', user.username);
            }
          });

          res.json({ message: 'Add balance request declined' });
        }
      }
    );
  });
});

// Get all transfer requests
router.get('/transfer-requests', (req, res) => {
  db.all(`
    SELECT tr.*, 
           u1.username as from_username, u1.email as from_email,
           u2.username as to_username, u2.email as to_email
    FROM transfer_requests tr
    JOIN users u1 ON tr.from_user_id = u1.id
    JOIN users u2 ON tr.to_user_id = u2.id
    ORDER BY tr.created_at DESC
  `, [], (err, requests) => {
    if (err) {
      return res.status(500).json({ error: 'Database error' });
    }
    res.json(requests);
  });
});

// Approve/decline transfer request
router.post('/transfer-requests/:id/:action', (req, res) => {
  const { id, action } = req.params;

  if (action !== 'approve' && action !== 'decline') {
    return res.status(400).json({ error: 'Invalid action' });
  }

  db.get(`
    SELECT tr.*, u1.username as from_username, u1.email as from_email, u2.username as to_username
    FROM transfer_requests tr
    JOIN users u1 ON tr.from_user_id = u1.id
    JOIN users u2 ON tr.to_user_id = u2.id
    WHERE tr.id = ?
  `, [id], (err, request) => {
    if (err) {
      return res.status(500).json({ error: 'Database error' });
    }

    if (!request) {
      return res.status(404).json({ error: 'Request not found' });
    }

    if (request.status !== 'pending') {
      return res.status(400).json({ error: 'Request already processed' });
    }

    const status = action === 'approve' ? 'approved' : 'declined';

    db.run(
      'UPDATE transfer_requests SET status = ?, processed_at = CURRENT_TIMESTAMP WHERE id = ?',
      [status, id],
      (err) => {
        if (err) {
          return res.status(500).json({ error: 'Database error' });
        }

        if (action === 'approve') {
          // Deduct from sender and add to recipient
          db.run('UPDATE users SET balance = balance - ? WHERE id = ?', [request.amount, request.from_user_id]);
          db.run('UPDATE users SET balance = balance + ? WHERE id = ?', [request.amount, request.to_user_id]);

          // Add transaction records
          db.run(
            'INSERT INTO transactions (user_id, type, amount, status, details) VALUES (?, ?, ?, ?, ?)',
            [request.from_user_id, 'transfer_sent', request.amount, 'completed', JSON.stringify({ to: request.to_username, request_id: id })]
          );
          db.run(
            'INSERT INTO transactions (user_id, type, amount, status, details) VALUES (?, ?, ?, ?, ?)',
            [request.to_user_id, 'transfer_received', request.amount, 'completed', JSON.stringify({ from: request.from_username, request_id: id })]
          );

          // Send email notification
          sendEmail(request.from_email, 'transferApproved', request.from_username, request.amount, request.to_username);
        } else {
          // Send decline email
          sendEmail(request.from_email, 'transferDeclined', request.from_username, request.amount);
        }

        res.json({ message: `Transfer request ${status}` });
      }
    );
  });
});

// Get all withdrawal requests
router.get('/withdrawal-requests', (req, res) => {
  db.all(`
    SELECT wr.*, u.username, u.email as user_email
    FROM withdrawal_requests wr
    JOIN users u ON wr.user_id = u.id
    ORDER BY wr.created_at DESC
  `, [], (err, requests) => {
    if (err) {
      return res.status(500).json({ error: 'Database error' });
    }
    res.json(requests);
  });
});

// Approve/decline withdrawal request
router.post('/withdrawal-requests/:id/:action', (req, res) => {
  const { id, action } = req.params;

  if (action !== 'approve' && action !== 'decline') {
    return res.status(400).json({ error: 'Invalid action' });
  }

  db.get(`
    SELECT wr.*, u.username, u.email as user_email
    FROM withdrawal_requests wr
    JOIN users u ON wr.user_id = u.id
    WHERE wr.id = ?
  `, [id], (err, request) => {
    if (err) {
      return res.status(500).json({ error: 'Database error' });
    }

    if (!request) {
      return res.status(404).json({ error: 'Request not found' });
    }

    if (request.status !== 'pending') {
      return res.status(400).json({ error: 'Request already processed' });
    }

    const status = action === 'approve' ? 'approved' : 'declined';

    db.run(
      'UPDATE withdrawal_requests SET status = ?, processed_at = CURRENT_TIMESTAMP WHERE id = ?',
      [status, id],
      (err) => {
        if (err) {
          return res.status(500).json({ error: 'Database error' });
        }

        if (action === 'approve') {
          // Deduct from user balance
          db.run(
            'UPDATE users SET balance = balance - ? WHERE id = ?',
            [request.amount, request.user_id],
            (err) => {
              if (err) {
                return res.status(500).json({ error: 'Database error updating balance' });
              }

              // Add transaction record
              db.run(
                'INSERT INTO transactions (user_id, type, amount, status, details) VALUES (?, ?, ?, ?, ?)',
                [request.user_id, 'withdrawal', request.amount, 'completed', JSON.stringify({ request_id: id })]
              );

              // Send email notification
              sendEmail(request.user_email, 'withdrawalApproved', request.username, request.amount);

              res.json({ message: 'Withdrawal request approved' });
            }
          );
        } else {
          // Send decline email
          sendEmail(request.user_email, 'withdrawalDeclined', request.username, request.amount);
          res.json({ message: 'Withdrawal request declined' });
        }
      }
    );
  });
});

// Get all users
router.get('/users', (req, res) => {
  db.all(`
    SELECT id, username, email, balance, is_admin, created_at
    FROM users
    ORDER BY created_at DESC
  `, [], (err, users) => {
    if (err) {
      return res.status(500).json({ error: 'Database error' });
    }
    res.json(users);
  });
});

// Get all conversion requests
router.get('/conversion-requests', (req, res) => {
  db.all(`
    SELECT cr.*, u.username, u.email as user_email
    FROM conversion_requests cr
    JOIN users u ON cr.user_id = u.id
    ORDER BY cr.created_at DESC
  `, [], (err, requests) => {
    if (err) {
      return res.status(500).json({ error: 'Database error' });
    }
    res.json(requests);
  });
});

module.exports = router;
