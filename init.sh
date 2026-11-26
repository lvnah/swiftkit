#!/bin/bash
REPO="https://raw.githubusercontent.com/lvnah/swiftkit/main/templates"

read -p "Nom du projet : " APP < /dev/tty
APP=${APP:-mon-app}
read -p "Activer Login ? (o/n) [o] : " AUTH < /dev/tty
AUTH=${AUTH:-o}
read -p "Activer Panel Admin ? (o/n) [o] : " ADMIN < /dev/tty
ADMIN=${ADMIN:-o}

mkdir -p "$APP/Back" "$APP/Front/Index" && cd "$APP" || exit

A="false"; [[ "$AUTH" =~ ^[oO] ]] && A="true"
D="false"; [[ "$ADMIN" =~ ^[oO] ]] && D="true"

# 1. Config dans Back/schema.json
echo "{\"appName\":\"$APP\",\"auth\":$A,\"admin\":$D,\"tables\":{\"items\":[\"titre\",\"description\",\"statut\"]}}" > Back/schema.json

# 2. Téléchargement des fichiers
curl -sL "$REPO/index.php" -o index.php
curl -sL "$REPO/STACK.md" -o STACK.md
curl -sL "$REPO/Back/api.php" -o Back/api.php
curl -sL "$REPO/Back/visualdata.php" -o Back/visualdata.php
curl -sL "$REPO/Front/Index/index.html" -o Front/Index/index.html

if [ "$A" = "true" ]; then
  mkdir -p Front/Login
  curl -sL "$REPO/Front/Login/index.html" -o Front/Login/index.html
fi

if [ "$D" = "true" ]; then
  mkdir -p Front/Admin
  curl -sL "$REPO/Front/Admin/index.html" -o Front/Admin/index.html
fi

echo ""
echo "Projet '$APP' prêt !"
echo "Lance-le avec : cd $APP && php -S localhost:8000 index.php"
