// API Base URL
const API_BASE = window.location.origin;

// Show alert message
function showAlert(message, type = 'info') {
  const alertDiv = document.getElementById('alert');
  if (alertDiv) {
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    alertDiv.classList.remove('hidden');
    
    setTimeout(() => {
      alertDiv.classList.add('hidden');
    }, 5000);
  }
}

// Login form handler
document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const username = document.getElementById('username').value;
  const password = document.getElementById('password').value;
  
  try {
    const response = await fetch(`${API_BASE}/api/auth/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ username, password })
    });
    
    const data = await response.json();
    
    if (response.ok) {
      // Store token and user data
      localStorage.setItem('token', data.token);
      localStorage.setItem('user', JSON.stringify(data.user));
      
      // Redirect based on user type
      if (data.user.is_admin) {
        window.location.href = '/admin';
      } else {
        window.location.href = '/dashboard';
      }
    } else {
      showAlert(data.error || 'Login failed', 'error');
    }
  } catch (error) {
    console.error('Login error:', error);
    showAlert('Network error. Please try again.', 'error');
  }
});

// Logout function
function logout() {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  window.location.href = '/';
}

// Check authentication
function checkAuth() {
  const token = localStorage.getItem('token');
  const user = localStorage.getItem('user');
  
  if (!token || !user) {
    window.location.href = '/';
    return null;
  }
  
  return JSON.parse(user);
}

// Get auth headers
function getAuthHeaders() {
  const token = localStorage.getItem('token');
  return {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  };
}
