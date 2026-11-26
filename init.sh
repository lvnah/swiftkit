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

echo "{\"appName\":\"$APP\",\"auth\":$A,\"admin\":$D,\"tables\":{\"items\":[\"titre\",\"description\",\"statut\"]}}" > Back/schema.json

curl -sL "$REPO/index.html" -o index.html
curl -sL "$REPO/STACK.md" -o STACK.md
curl -sL "$REPO/Back/api.php" -o Back/api.php
curl -sL "$REPO/Back/visualdata.php" -o Back/visualdata.php
curl -sL "$REPO/Front/Index/view.html" -o Front/Index/view.html

if [ "$A" = "true" ]; then
  mkdir -p Front/Login
  curl -sL "$REPO/Front/Login/view.html" -o Front/Login/view.html
fi

if [ "$D" = "true" ]; then
  mkdir -p Front/Admin
  curl -sL "$REPO/Front/Admin/view.html" -o Front/Admin/view.html
fi

echo ""
echo "Projet '$APP' prêt !"
echo "Lance-le avec : cd $APP && php -S localhost:8000"
