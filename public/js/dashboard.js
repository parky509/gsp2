// API Base URL
const API_BASE = window.location.origin;

// Check authentication and load user data
const user = checkAuth();

if (!user || user.is_admin) {
  window.location.href = '/';
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', () => {
  loadUserData();
  loadBalance();
  loadTransactions();
  loadAdminSettings();
  setupFormHandlers();
});

// Load user data
function loadUserData() {
  const profilePicture = document.getElementById('profilePicture');
  const username = document.getElementById('username');
  
  if (user.profile_picture) {
    profilePicture.innerHTML = `<img src="${user.profile_picture}" alt="${user.username}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
  } else {
    profilePicture.textContent = user.username.charAt(0).toUpperCase();
  }
  
  username.textContent = user.username;
}

// Load balance
async function loadBalance() {
  try {
    const response = await fetch(`${API_BASE}/api/user/balance`, {
      headers: getAuthHeaders()
    });
    
    const data = await response.json();
    
    if (response.ok) {
      document.getElementById('balance').textContent = `$${data.balance.toFixed(2)}`;
    }
  } catch (error) {
    console.error('Error loading balance:', error);
  }
}

// Load transactions
async function loadTransactions() {
  try {
    const response = await fetch(`${API_BASE}/api/user/transactions`, {
      headers: getAuthHeaders()
    });
    
    const transactions = await response.json();
    
    if (response.ok) {
      displayTransactions(transactions);
    }
  } catch (error) {
    console.error('Error loading transactions:', error);
  }
}

// Display transactions
function displayTransactions(transactions) {
  const list = document.getElementById('transactionList');
  
  if (transactions.length === 0) {
    list.innerHTML = '<p style="text-align: center; color: var(--text-secondary);">No transactions yet</p>';
    return;
  }
  
  list.innerHTML = transactions.map(tx => {
    const date = new Date(tx.created_at).toLocaleString();
    const amountClass = tx.type.includes('received') || tx.type === 'add_balance' || tx.type === 'deposit' ? 'positive' : 'negative';
    const amountSign = amountClass === 'positive' ? '+' : '-';
    
    return `
      <div class="transaction-item glass">
        <div class="transaction-info">
          <div class="transaction-type">${tx.type.replace(/_/g, ' ')}</div>
          <div class="transaction-date">${date}</div>
        </div>
        <div>
          <div class="transaction-amount ${amountClass}">${amountSign}$${tx.amount.toFixed(2)}</div>
          <span class="status-badge status-${tx.status}">${tx.status}</span>
        </div>
      </div>
    `;
  }).join('');
}

// Load admin settings for account number
async function loadAdminSettings() {
  try {
    const response = await fetch(`${API_BASE}/api/admin/settings`, {
      headers: getAuthHeaders()
    });
    
    if (response.ok) {
      const settings = await response.json();
      document.getElementById('adminAccountNumber').textContent = settings.account_number || 'N/A';
    }
  } catch (error) {
    console.error('Error loading settings:', error);
  }
}

// Modal functions
function openModal(modalId) {
  document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.remove('active');
}

// View balance (savings)
function viewBalance() {
  alert(`Your current balance is: $${document.getElementById('balance').textContent}`);
}

// File upload handler
document.getElementById('receipt')?.addEventListener('change', (e) => {
  const fileName = e.target.files[0]?.name;
  if (fileName) {
    document.getElementById('fileName').textContent = `Selected: ${fileName}`;
  }
});

// Setup form handlers
function setupFormHandlers() {
  // Deposit form
  document.getElementById('depositForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
      name: document.getElementById('depositName').value,
      email: document.getElementById('depositEmail').value,
      amount: parseFloat(document.getElementById('depositAmount').value)
    };
    
    try {
      const response = await fetch(`${API_BASE}/api/user/deposit`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify(formData)
      });
      
      const data = await response.json();
      
      if (response.ok) {
        alert('Deposit request submitted successfully!');
        closeModal('depositModal');
        e.target.reset();
        loadTransactions();
      } else {
        alert(data.error || 'Failed to submit deposit');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Network error. Please try again.');
    }
  });

  // BTC conversion form
  document.getElementById('btcForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
      conversion_type: 'btc',
      email: document.getElementById('btcEmail').value,
      amount: parseFloat(document.getElementById('btcAmount').value),
      destination_address: document.getElementById('btcAddress').value,
      security_phrase: document.getElementById('btcPhrase').value
    };
    
    try {
      const response = await fetch(`${API_BASE}/api/user/convert`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify(formData)
      });
      
      const data = await response.json();
      
      if (response.ok) {
        alert('BTC conversion request submitted successfully!');
        closeModal('btcModal');
        e.target.reset();
      } else {
        alert(data.error || 'Failed to submit conversion');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Network error. Please try again.');
    }
  });

  // USDT conversion form
  document.getElementById('usdtForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
      conversion_type: 'usdt',
      email: document.getElementById('usdtEmail').value,
      amount: parseFloat(document.getElementById('usdtAmount').value),
      destination_address: document.getElementById('usdtAddress').value,
      security_phrase: document.getElementById('usdtPhrase').value
    };
    
    try {
      const response = await fetch(`${API_BASE}/api/user/convert`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify(formData)
      });
      
      const data = await response.json();
      
      if (response.ok) {
        alert('USDT conversion request submitted successfully!');
        closeModal('usdtModal');
        e.target.reset();
      } else {
        alert(data.error || 'Failed to submit conversion');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Network error. Please try again.');
    }
  });

  // Bank conversion form
  document.getElementById('bankForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const bankDetails = {
      bank_name: document.getElementById('bankName').value,
      account_name: document.getElementById('accountName').value,
      account_number: document.getElementById('accountNumber').value,
      routing_number: document.getElementById('routingNumber').value,
      bank_address: document.getElementById('bankAddress').value,
      country: document.getElementById('country').value
    };
    
    const formData = {
      conversion_type: 'bank',
      email: document.getElementById('bankEmail').value,
      amount: parseFloat(document.getElementById('bankAmount').value),
      bank_details: JSON.stringify(bankDetails),
      security_phrase: document.getElementById('bankPhrase').value
    };
    
    try {
      const response = await fetch(`${API_BASE}/api/user/convert`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify(formData)
      });
      
      const data = await response.json();
      
      if (response.ok) {
        alert('Bank conversion request submitted successfully!');
        closeModal('bankModal');
        e.target.reset();
      } else {
        alert(data.error || 'Failed to submit conversion');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Network error. Please try again.');
    }
  });

  // Add balance form
  document.getElementById('addBalanceForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('sender_name', document.getElementById('senderName').value);
    formData.append('sender_email', document.getElementById('senderEmail').value);
    formData.append('receipt', document.getElementById('receipt').files[0]);
    
    try {
      const token = localStorage.getItem('token');
      const response = await fetch(`${API_BASE}/api/user/add-balance`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        },
        body: formData
      });
      
      const data = await response.json();
      
      if (response.ok) {
        alert('Add balance request submitted successfully! Please wait for admin approval.');
        closeModal('addBalanceModal');
        e.target.reset();
        document.getElementById('fileName').textContent = '';
      } else {
        alert(data.error || 'Failed to submit request');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Network error. Please try again.');
    }
  });

  // Transfer form
  document.getElementById('transferForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
      recipient_username: document.getElementById('recipientUsername').value,
      amount: parseFloat(document.getElementById('transferAmount').value),
      token_code: document.getElementById('tokenCode').value
    };
    
    try {
      const response = await fetch(`${API_BASE}/api/user/transfer`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify(formData)
      });
      
      const data = await response.json();
      
      if (response.ok) {
        alert('Transfer request submitted for admin approval!');
        closeModal('transferModal');
        e.target.reset();
      } else {
        alert(data.error || 'Failed to submit transfer');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Network error. Please try again.');
    }
  });

  // Withdraw form
  document.getElementById('withdrawForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
      amount: parseFloat(document.getElementById('withdrawAmount').value),
      details: document.getElementById('withdrawDetails').value
    };
    
    try {
      const response = await fetch(`${API_BASE}/api/user/withdraw`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify(formData)
      });
      
      const data = await response.json();
      
      if (response.ok) {
        alert('Withdrawal request submitted for admin approval!');
        closeModal('withdrawModal');
        e.target.reset();
      } else {
        alert(data.error || 'Failed to submit withdrawal');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Network error. Please try again.');
    }
  });
}

// Close modal when clicking outside
window.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal')) {
    e.target.classList.remove('active');
  }
});
