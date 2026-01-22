// API Base URL
const API_BASE = window.location.origin;

// Check authentication
const user = checkAuth();

if (!user || !user.is_admin) {
  window.location.href = '/';
}

// Initialize admin dashboard
document.addEventListener('DOMContentLoaded', () => {
  loadSettings();
  loadAddBalanceRequests();
  loadTransferRequests();
  loadWithdrawalRequests();
  loadConversionRequests();
  loadUsers();
  setupFormHandlers();
});

// Tab switching
function switchTab(tabName) {
  // Remove active class from all tabs and content
  document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
  
  // Add active class to selected tab
  event.target.classList.add('active');
  document.getElementById(`${tabName}Tab`).classList.add('active');
}

// Load settings
async function loadSettings() {
  try {
    const response = await fetch(`${API_BASE}/api/admin/settings`, {
      headers: getAuthHeaders()
    });
    
    if (response.ok) {
      const settings = await response.json();
      document.getElementById('accountNumber').value = settings.account_number || '';
      document.getElementById('btcAddress').value = settings.btc_address || '';
      document.getElementById('usdtAddress').value = settings.usdt_address || '';
    }
  } catch (error) {
    console.error('Error loading settings:', error);
  }
}

// Load add balance requests
async function loadAddBalanceRequests() {
  try {
    const response = await fetch(`${API_BASE}/api/admin/add-balance-requests`, {
      headers: getAuthHeaders()
    });
    
    if (response.ok) {
      const requests = await response.json();
      displayAddBalanceRequests(requests);
    }
  } catch (error) {
    console.error('Error loading add balance requests:', error);
  }
}

// Display add balance requests
function displayAddBalanceRequests(requests) {
  const list = document.getElementById('addBalanceList');
  
  if (requests.length === 0) {
    list.innerHTML = '<p style="text-align: center; color: var(--text-secondary);">No pending requests</p>';
    return;
  }
  
  list.innerHTML = requests.map(req => `
    <div class="request-item glass">
      <div class="request-header">
        <div>
          <strong>${req.username}</strong> (${req.user_email})
          <br><small style="color: var(--text-secondary);">${new Date(req.created_at).toLocaleString()}</small>
        </div>
        <span class="status-badge status-${req.status}">${req.status}</span>
      </div>
      <div class="request-details">
        <div class="detail-item">
          <div class="detail-label">Sender Name</div>
          <div class="detail-value">${req.sender_name}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Sender Email</div>
          <div class="detail-value">${req.sender_email}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Receipt</div>
          <div class="detail-value"><a href="/uploads/${req.receipt_path}" target="_blank" style="color: var(--primary-color);">View Receipt</a></div>
        </div>
      </div>
      ${req.status === 'pending' ? `
        <div class="request-actions">
          <button class="btn btn-success" onclick="approveAddBalance(${req.id})">Approve</button>
          <button class="btn btn-danger" onclick="declineAddBalance(${req.id})">Decline</button>
        </div>
      ` : ''}
    </div>
  `).join('');
}

// Load transfer requests
async function loadTransferRequests() {
  try {
    const response = await fetch(`${API_BASE}/api/admin/transfer-requests`, {
      headers: getAuthHeaders()
    });
    
    if (response.ok) {
      const requests = await response.json();
      displayTransferRequests(requests);
    }
  } catch (error) {
    console.error('Error loading transfer requests:', error);
  }
}

// Display transfer requests
function displayTransferRequests(requests) {
  const list = document.getElementById('transfersList');
  
  if (requests.length === 0) {
    list.innerHTML = '<p style="text-align: center; color: var(--text-secondary);">No pending requests</p>';
    return;
  }
  
  list.innerHTML = requests.map(req => `
    <div class="request-item glass">
      <div class="request-header">
        <div>
          <strong>${req.from_username}</strong> → <strong>${req.to_username}</strong>
          <br><small style="color: var(--text-secondary);">${new Date(req.created_at).toLocaleString()}</small>
        </div>
        <span class="status-badge status-${req.status}">${req.status}</span>
      </div>
      <div class="request-details">
        <div class="detail-item">
          <div class="detail-label">Amount</div>
          <div class="detail-value">$${req.amount.toFixed(2)}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Token Code</div>
          <div class="detail-value">${req.token_code}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">From Email</div>
          <div class="detail-value">${req.from_email}</div>
        </div>
      </div>
      ${req.status === 'pending' ? `
        <div class="request-actions">
          <button class="btn btn-success" onclick="approveTransfer(${req.id})">Approve</button>
          <button class="btn btn-danger" onclick="declineTransfer(${req.id})">Decline</button>
        </div>
      ` : ''}
    </div>
  `).join('');
}

// Load withdrawal requests
async function loadWithdrawalRequests() {
  try {
    const response = await fetch(`${API_BASE}/api/admin/withdrawal-requests`, {
      headers: getAuthHeaders()
    });
    
    if (response.ok) {
      const requests = await response.json();
      displayWithdrawalRequests(requests);
    }
  } catch (error) {
    console.error('Error loading withdrawal requests:', error);
  }
}

// Display withdrawal requests
function displayWithdrawalRequests(requests) {
  const list = document.getElementById('withdrawalsList');
  
  if (requests.length === 0) {
    list.innerHTML = '<p style="text-align: center; color: var(--text-secondary);">No pending requests</p>';
    return;
  }
  
  list.innerHTML = requests.map(req => `
    <div class="request-item glass">
      <div class="request-header">
        <div>
          <strong>${req.username}</strong> (${req.user_email})
          <br><small style="color: var(--text-secondary);">${new Date(req.created_at).toLocaleString()}</small>
        </div>
        <span class="status-badge status-${req.status}">${req.status}</span>
      </div>
      <div class="request-details">
        <div class="detail-item">
          <div class="detail-label">Amount</div>
          <div class="detail-value">$${req.amount.toFixed(2)}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Details</div>
          <div class="detail-value">${req.details}</div>
        </div>
      </div>
      ${req.status === 'pending' ? `
        <div class="request-actions">
          <button class="btn btn-success" onclick="approveWithdrawal(${req.id})">Approve</button>
          <button class="btn btn-danger" onclick="declineWithdrawal(${req.id})">Decline</button>
        </div>
      ` : ''}
    </div>
  `).join('');
}

// Load conversion requests
async function loadConversionRequests() {
  try {
    const response = await fetch(`${API_BASE}/api/admin/conversion-requests`, {
      headers: getAuthHeaders()
    });
    
    if (response.ok) {
      const requests = await response.json();
      displayConversionRequests(requests);
    }
  } catch (error) {
    console.error('Error loading conversion requests:', error);
  }
}

// Display conversion requests
function displayConversionRequests(requests) {
  const list = document.getElementById('conversionsList');
  
  if (requests.length === 0) {
    list.innerHTML = '<p style="text-align: center; color: var(--text-secondary);">No requests</p>';
    return;
  }
  
  list.innerHTML = requests.map(req => {
    let details = '';
    if (req.conversion_type === 'bank') {
      const bankDetails = JSON.parse(req.bank_details || '{}');
      details = `
        <div class="detail-item">
          <div class="detail-label">Bank</div>
          <div class="detail-value">${bankDetails.bank_name || 'N/A'}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Account</div>
          <div class="detail-value">${bankDetails.account_number || 'N/A'}</div>
        </div>
      `;
    } else {
      details = `
        <div class="detail-item">
          <div class="detail-label">Address</div>
          <div class="detail-value">${req.destination_address || 'N/A'}</div>
        </div>
      `;
    }
    
    return `
      <div class="request-item glass">
        <div class="request-header">
          <div>
            <strong>${req.username}</strong> - ${req.conversion_type.toUpperCase()}
            <br><small style="color: var(--text-secondary);">${new Date(req.created_at).toLocaleString()}</small>
          </div>
          <span class="status-badge status-${req.status}">${req.status}</span>
        </div>
        <div class="request-details">
          <div class="detail-item">
            <div class="detail-label">Amount</div>
            <div class="detail-value">$${req.amount.toFixed(2)}</div>
          </div>
          <div class="detail-item">
            <div class="detail-label">Email</div>
            <div class="detail-value">${req.email}</div>
          </div>
          ${details}
        </div>
      </div>
    `;
  }).join('');
}

// Load users
async function loadUsers() {
  try {
    const response = await fetch(`${API_BASE}/api/admin/users`, {
      headers: getAuthHeaders()
    });
    
    if (response.ok) {
      const users = await response.json();
      displayUsers(users);
    }
  } catch (error) {
    console.error('Error loading users:', error);
  }
}

// Display users
function displayUsers(users) {
  const list = document.getElementById('usersList');
  
  list.innerHTML = users.map(user => `
    <div class="request-item glass">
      <div class="request-details">
        <div class="detail-item">
          <div class="detail-label">Username</div>
          <div class="detail-value">${user.username}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Email</div>
          <div class="detail-value">${user.email}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Balance</div>
          <div class="detail-value">$${user.balance.toFixed(2)}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Role</div>
          <div class="detail-value">${user.is_admin ? 'Admin' : 'User'}</div>
        </div>
        <div class="detail-item">
          <div class="detail-label">Joined</div>
          <div class="detail-value">${new Date(user.created_at).toLocaleDateString()}</div>
        </div>
      </div>
    </div>
  `).join('');
}

// Approve/Decline functions
let currentRequestId = null;

function approveAddBalance(requestId) {
  currentRequestId = requestId;
  openModal('amountModal');
}

async function declineAddBalance(requestId) {
  if (!confirm('Are you sure you want to decline this request?')) return;
  
  try {
    const response = await fetch(`${API_BASE}/api/admin/add-balance-requests/${requestId}/decline`, {
      method: 'POST',
      headers: getAuthHeaders()
    });
    
    if (response.ok) {
      alert('Request declined');
      loadAddBalanceRequests();
    } else {
      const data = await response.json();
      alert(data.error || 'Failed to decline request');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Network error. Please try again.');
  }
}

async function approveTransfer(requestId) {
  if (!confirm('Are you sure you want to approve this transfer?')) return;
  
  try {
    const response = await fetch(`${API_BASE}/api/admin/transfer-requests/${requestId}/approve`, {
      method: 'POST',
      headers: getAuthHeaders()
    });
    
    if (response.ok) {
      alert('Transfer approved');
      loadTransferRequests();
    } else {
      const data = await response.json();
      alert(data.error || 'Failed to approve transfer');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Network error. Please try again.');
  }
}

async function declineTransfer(requestId) {
  if (!confirm('Are you sure you want to decline this transfer?')) return;
  
  try {
    const response = await fetch(`${API_BASE}/api/admin/transfer-requests/${requestId}/decline`, {
      method: 'POST',
      headers: getAuthHeaders()
    });
    
    if (response.ok) {
      alert('Transfer declined');
      loadTransferRequests();
    } else {
      const data = await response.json();
      alert(data.error || 'Failed to decline transfer');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Network error. Please try again.');
  }
}

async function approveWithdrawal(requestId) {
  if (!confirm('Are you sure you want to approve this withdrawal?')) return;
  
  try {
    const response = await fetch(`${API_BASE}/api/admin/withdrawal-requests/${requestId}/approve`, {
      method: 'POST',
      headers: getAuthHeaders()
    });
    
    if (response.ok) {
      alert('Withdrawal approved');
      loadWithdrawalRequests();
    } else {
      const data = await response.json();
      alert(data.error || 'Failed to approve withdrawal');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Network error. Please try again.');
  }
}

async function declineWithdrawal(requestId) {
  if (!confirm('Are you sure you want to decline this withdrawal?')) return;
  
  try {
    const response = await fetch(`${API_BASE}/api/admin/withdrawal-requests/${requestId}/decline`, {
      method: 'POST',
      headers: getAuthHeaders()
    });
    
    if (response.ok) {
      alert('Withdrawal declined');
      loadWithdrawalRequests();
    } else {
      const data = await response.json();
      alert(data.error || 'Failed to decline withdrawal');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Network error. Please try again.');
  }
}

// Modal functions
function openModal(modalId) {
  document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.remove('active');
}

// Setup form handlers
function setupFormHandlers() {
  // Settings form
  document.getElementById('settingsForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
      account_number: document.getElementById('accountNumber').value,
      btc_address: document.getElementById('btcAddress').value,
      usdt_address: document.getElementById('usdtAddress').value
    };
    
    try {
      const response = await fetch(`${API_BASE}/api/admin/settings`, {
        method: 'PUT',
        headers: getAuthHeaders(),
        body: JSON.stringify(formData)
      });
      
      if (response.ok) {
        alert('Settings updated successfully!');
      } else {
        const data = await response.json();
        alert(data.error || 'Failed to update settings');
      }
    } catch (error) {
      console.error('Error:', error);
      alert('Network error. Please try again.');
    }
  });

  // Amount form for add balance approval
  document.getElementById('amountForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const amount = parseFloat(document.getElementById('approvalAmount').value);
    
    try {
      const response = await fetch(`${API_BASE}/api/admin/add-balance-requests/${currentRequestId}/approve`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ amount })
      });
      
      if (response.ok) {
        alert('Request approved');
        closeModal('amountModal');
        e.target.reset();
        loadAddBalanceRequests();
      } else {
        const data = await response.json();
        alert(data.error || 'Failed to approve request');
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
