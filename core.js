// Client HTTP interne avec gestion automatique du token Auth
async function _req(url, options = {}) {
  const token = localStorage.getItem('sk_token');
  const headers = { 'Content-Type': 'application/json', ...(options.headers || {}) };
  if (token) headers['Authorization'] = `Bearer ${token}`;

  const res = await fetch(url, { ...options, headers });
  const data = await res.json().catch(() => ({}));

  if (res.status === 401 && !url.includes('/api/auth/login')) {
    localStorage.removeItem('sk_token');
    if (location.pathname !== '/login.html') location.href = '/login.html';
  }
  if (!res.ok) throw new Error(data.error || 'Erreur serveur');
  return data;
}

// 1. SDK Base de données (CRUD universel)
window.$db = {
  schema: () => _req('/api/_schema'),
  list: (table) => _req(`/api/${table}`),
  get: (table, id) => _req(`/api/${table}/${id}`),
  create: (table, payload) => _req(`/api/${table}`, { method: 'POST', body: JSON.stringify(payload) }),
  update: (table, id, payload) => _req(`/api/${table}/${id}`, { method: 'PUT', body: JSON.stringify(payload) }),
  delete: (table, id) => _req(`/api/${table}/${id}`, { method: 'DELETE' })
};

// 2. SDK Authentification
window.$auth = {
  async login(username, password) {
    const res = await _req('/api/auth/login', {
      method: 'POST',
      body: JSON.stringify({ username, password })
    });
    localStorage.setItem('sk_token', res.token);
    localStorage.setItem('sk_user', JSON.stringify(res.user));
    return res.user;
  },
  logout() {
    localStorage.removeItem('sk_token');
    localStorage.removeItem('sk_user');
    location.href = '/login.html';
  },
  user() {
    try { return JSON.parse(localStorage.getItem('sk_user')); } catch { return null; }
  },
  requireAuth() {
    if (!localStorage.getItem('sk_token')) location.href = '/login.html';
  }
};

// 3. Helpers UI ultra-simples
window.$ui = {
  qs: (sel) => document.querySelector(sel),
  openModal: (id) => document.getElementById(id)?.classList.add('open'),
  closeModal: (id) => document.getElementById(id)?.classList.remove('open'),
  toast(msg) {
    let el = document.getElementById('sk-toast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'sk-toast';
      el.className = 'toast';
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.classList.add('show');
    setTimeout(() => el.classList.remove('show'), 2500);
  },
  esc(str) {
    return String(str ?? '').replace(/[&<>"']/g, m => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
  }
};
