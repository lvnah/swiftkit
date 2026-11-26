# Stack Technique : SwiftKit (Vanilla HTML/CSS/JS + PHP/SQLite Auto-CRUD)

## Règles strictes
1. NE PAS utiliser React, Vue, Tailwind, Vite ou Node.js. Uniquement du HTML, CSS et JS Vanilla.
2. Toutes les pages incluent :
   - `<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lvnah/swiftkit@main/core.css">`
   - `<script src="https://cdn.jsdelivr.net/gh/lvnah/swiftkit@main/core.js"></script>`
3. Toutes les tables SQLite et routes API sont générées automatiquement par `api.php` depuis `schema.json`. Ne jamais modifier `api.php` pour ajouter un CRUD : ajouter simplement la table et ses colonnes dans `schema.json`.

## Classes CSS disponibles (`core.css`)
- Layout : `.container`, `.navbar`, `.row`, `.between`, `.grid-2`, `.mt-1`, `.mt-2`, `.mt-3`
- Conteneurs : `.card`, `.table-wrap`, `.modal-backdrop` (+ `.open`), `.modal`
- Boutons : `.btn`, `.btn.primary`, `.btn.danger`, `.btn.sm`

## SDK JavaScript global (`core.js`)
- `$db.schema()` : retourne la configuration `schema.json` et l'utilisateur connecté
- `$db.list(table)` / `$db.get(table, id)` / `$db.create(table, obj)` / `$db.update(table, id, obj)` / `$db.delete(table, id)`
- `$auth.login(username, password)` / `$auth.logout()` / `$auth.user()`
- `$ui.qs(selector)` / `$ui.openModal(id)` / `$ui.closeModal(id)` / `$ui.toast(msg)` / `$ui.esc(str)`
