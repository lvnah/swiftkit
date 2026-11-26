async function _req(url, options = {}) {
  const res = await fetch(url, {
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
    ...options
  });
  const data = await res.json().catch(() => ({}));

  if (res.status === 401 && !url.includes('action=login')) {
    if (!location.pathname.endsWith('login.html')) location.href = 'login.html';
  }
  if (!res.ok) throw new Error(data.error || 'Erreur serveur');
  return data;
}

window.$db = {
  schema: () => _req('api.php?action=schema'),
  list:   (table) => _req(`api.php?table=${table}`),
  get:    (table, id) => _req(`api.php?table=${table}&id=${id}`),
  create: (table, payload) => _req(`api.php?table=${table}`, { method: 'POST', body: JSON.stringify(payload) }),
  update: (table, id, payload) => _req(`api.php?table=${table}&id=${id}`, { method: 'PUT', body: JSON.stringify(payload) }),
  delete: (table, id) => _req(`api.php?table=${table}&id=${id}`, { method: 'DELETE' })
};

window.$auth = {
  async login(username, password) {
    const res = await _req('api.php?action=login', {
      method: 'POST',
      body: JSON.stringify({ username, password })
    });
    localStorage.setItem('sk_user', JSON.stringify(res.user));
    return res.user;
  },
  async logout() {
    await _req('api.php?action=logout');
    localStorage.removeItem('sk_user');
    location.href = 'login.html';
  },
  user() {
    try { return JSON.parse(localStorage.getItem('sk_user')); } catch { return null; }
  }
};

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
