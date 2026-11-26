// 1. Client HTTP centralisé vers /Back/api.php
async function _req(query, options = {}) {
  const res = await fetch(`/Back/api.php?${query}`, {
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
    ...options
  });
  const text = await res.text();
  let data = {};
  try { data = JSON.parse(text); } catch { data = { error: text.slice(0, 200) }; }

  if (res.status === 401 && !query.includes('action=login')) {
    $router.go('login');
  }
  if (!res.ok) throw new Error(data.error || `HTTP ${res.status}`);
  return data;
}

// 2. Micro-routeur SPA (0 rechargement, URL fixe sur localhost:8000)
window.$router = {
  routes: {
    index: '/Front/Index/view.html',
    login: '/Front/Login/view.html',
    admin: '/Front/Admin/view.html'
  },
  async go(page) {
    const file = this.routes[page] || this.routes.index;
    const html = await fetch(file).then(r => r.text());
    const root = document.getElementById('app');
    root.innerHTML = html;

    // Exécuter les balises <script> contenues dans la vue chargée
    root.querySelectorAll('script').forEach(oldScript => {
      const s = document.createElement('script');
      s.textContent = oldScript.textContent;
      document.body.appendChild(s);
      s.remove();
    });
  }
};

// 3. SDK Base de données ($db)
window.$db = {
  schema: () => _req('action=schema'),
  list:   (table) => _req(`table=${table}`),
  get:    (table, id) => _req(`table=${table}&id=${id}`),
  create: (table, payload) => _req(`table=${table}`, { method: 'POST', body: JSON.stringify(payload) }),
  update: (table, id, payload) => _req(`table=${table}&id=${id}`, { method: 'PUT', body: JSON.stringify(payload) }),
  delete: (table, id) => _req(`table=${table}&id=${id}`, { method: 'DELETE' }),
  visualAll: () => _req('action=visual_all')
};

// 4. SDK Auth ($auth)
window.$auth = {
  async login(username, password) {
    const res = await _req('action=login', {
      method: 'POST',
      body: JSON.stringify({ username, password })
    });
    localStorage.setItem('sk_user', JSON.stringify(res.user));
    $router.go('index');
    return res.user;
  },
  async logout() {
    await _req('action=logout');
    localStorage.removeItem('sk_user');
    $router.go('login');
  },
  user() {
    try { return JSON.parse(localStorage.getItem('sk_user')); } catch { return null; }
  }
};

// 5. Helpers UI ($ui)
window.$ui = {
  qs: (sel) => document.querySelector(sel),
  openModal: (id) => document.getElementById(id)?.classList.remove('hidden'),
  closeModal: (id) => document.getElementById(id)?.classList.add('hidden'),
  toast(msg) {
    let el = document.getElementById('sk-toast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'sk-toast';
      el.className = 'fixed bottom-5 right-5 bg-zinc-900 border border-violet-500 text-white px-4 py-2.5 rounded-xl text-sm shadow-xl transition-opacity duration-200 z-50';
      document.body.appendChild(el);
    }
    el.textContent = msg;
    el.style.opacity = '1';
    setTimeout(() => { el.style.opacity = '0'; }, 2500);
  },
  esc(str) {
    return String(str ?? '').replace(/[&<>"']/g, m => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
  }
};
