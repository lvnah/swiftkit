<?php
session_start();
$schema = json_decode(file_get_contents(__DIR__ . '/schema.json'), true);

// Vérifie que l'utilisateur est connecté et possède le rôle 'admin'
if (!empty($schema['auth'])) {
    if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
        header('Location: /Front/Login/');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VisualData — Explorateur BDD</title>
  <script src="https://cdn.jsdelivr.net/gh/lvnah/swiftkit@main/core.js"></script>
</head>
<body class="bg-[#0e0f11] text-gray-100 min-h-screen font-sans">
  <nav class="border-b border-zinc-800 bg-zinc-950/80 px-6 py-4 flex justify-between items-center">
    <div class="flex items-center gap-3">
      <span class="bg-violet-600 text-xs font-bold px-2.5 py-1 rounded-full">SQLITE LIVE</span>
      <strong class="text-lg">VisualData Explorer</strong>
    </div>
    <div class="flex gap-4 text-sm">
      <a href="/Front/Index/" class="text-gray-400 hover:text-white">Application</a>
      <a href="/Front/Admin/" class="text-gray-400 hover:text-white">Admin</a>
      <button onclick="$auth.logout()" class="text-red-400 hover:text-red-300">Déconnexion</button>
    </div>
  </nav>

  <main class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between items-center flex-wrap gap-4 mb-6">
      <div id="vd-tabs" class="flex gap-2 flex-wrap"></div>
      <button onclick="openVdModal()" class="bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
        + Insérer une ligne
      </button>
    </div>

    <div class="bg-zinc-900/70 border border-zinc-800 rounded-xl overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead id="vd-head" class="border-b border-zinc-800 text-xs uppercase text-gray-400 bg-zinc-950/50"></thead>
        <tbody id="vd-body" class="divide-y divide-zinc-800/70"></tbody>
      </table>
    </div>
  </main>

  <!-- Modale Édition / Ajout -->
  <div id="vd-modal" class="hidden fixed inset-0 bg-black/75 flex items-center justify-center p-4 z-50">
    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6 w-full max-w-md">
      <h3 id="vd-modal-title" class="text-lg font-bold mb-4">Édition</h3>
      <form id="vd-form" class="space-y-3">
        <input type="hidden" id="vd-id">
        <div id="vd-fields" class="space-y-3"></div>
        <div class="flex justify-end gap-2 pt-4">
          <button type="button" onclick="$ui.closeModal('vd-modal')" class="px-4 py-2 rounded-lg border border-zinc-700 text-sm hover:bg-zinc-800">Annuler</button>
          <button type="submit" class="px-4 py-2 rounded-lg bg-violet-600 hover:bg-violet-500 text-white text-sm font-medium">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    let allTables = {}, activeTable = '', rowsCache = [];

    async function initVisualData() {
      const res = await $db.visualAll();
      allTables = res.tables;
      const names = Object.keys(allTables);
      $ui.qs('#vd-tabs').innerHTML = names.map(t => `
        <button id="vtab-${t}" onclick="loadTable('${t}')"
          class="px-4 py-2 rounded-lg text-xs font-semibold uppercase tracking-wider border border-zinc-800 bg-zinc-900 hover:border-violet-500 transition">
          ${t}
        </button>
      `).join('');
      if (names.length) loadTable(names[0]);
    }

    async function loadTable(table) {
      activeTable = table;
      document.querySelectorAll('#vd-tabs button').forEach(b => b.classList.remove('bg-violet-600', 'border-violet-500'));
      $ui.qs(`#vtab-${table}`)?.classList.add('bg-violet-600', 'border-violet-500');

      const cols = allTables[table];
      $ui.qs('#vd-head').innerHTML = `<tr><th class="p-3">ID</th>${cols.map(c => `<th class="p-3">${c}</th>`).join('')}<th class="p-3">Actions</th></tr>`;

      rowsCache = await $db.list(table);
      $ui.qs('#vd-body').innerHTML = rowsCache.map(r => `
        <tr class="hover:bg-zinc-800/40">
          <td class="p-3 text-gray-400">#${r.id}</td>
          ${cols.map(c => `<td class="p-3 max-w-xs truncate">${$ui.esc(r[c])}</td>`).join('')}
          <td class="p-3 flex gap-2">
            <button onclick="openVdModal(${r.id})" class="px-2.5 py-1 rounded bg-zinc-800 hover:bg-zinc-700 text-xs">Éditer</button>
            <button onclick="delVdRow(${r.id})" class="px-2.5 py-1 rounded border border-red-500/40 text-red-400 hover:bg-red-500 hover:text-white text-xs">Suppr</button>
          </td>
        </tr>
      `).join('');
    }

    function openVdModal(id = null) {
      const cols = allTables[activeTable];
      const row = rowsCache.find(r => r.id == id);
      $ui.qs('#vd-id').value = row ? row.id : '';
      $ui.qs('#vd-modal-title').textContent = row ? `Modifier #${id} (${activeTable})` : `Nouvelle ligne (${activeTable})`;
      $ui.qs('#vd-fields').innerHTML = cols.map(c => `
        <div>
          <label class="block text-xs text-gray-400 mb-1">${c}</label>
          <input id="vdf-${c}" value="${row ? $ui.esc(row[c]) : ''}" placeholder="${c === 'password' && row ? 'Laisser inchangé ou nouveau' : ''}"
            class="w-full bg-zinc-950 border border-zinc-800 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-violet-500">
        </div>
      `).join('');
      $ui.openModal('vd-modal');
    }

    $ui.qs('#vd-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const id = $ui.qs('#vd-id').value;
      const payload = {};
      allTables[activeTable].forEach(c => payload[c] = $ui.qs(`#vdf-${c}`).value);
      if (id) await $db.update(activeTable, id, payload);
      else await $db.create(activeTable, payload);
      $ui.closeModal('vd-modal');
      $ui.toast('Base de données mise à jour');
      loadTable(activeTable);
    });

    async function delVdRow(id) {
      if (!confirm('Supprimer définitivement cette ligne ?')) return;
      await $db.delete(activeTable, id);
      $ui.toast('Ligne supprimée');
      loadTable(activeTable);
    }

    initVisualData();
  </script>
</body>
</html>
