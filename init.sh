#!/bin/bash
REPO="https://raw.githubusercontent.com/lvnah/swiftkit/main/templates"

read -p "Nom du projet : " APP < /dev/tty
read -p "Activer Login ? (o/n) : " AUTH < /dev/tty
read -p "Activer Panel Admin ? (o/n) : " ADMIN < /dev/tty

mkdir -p "$APP" && cd "$APP" || exit

A="false"; [[ "$AUTH" =~ ^[oO] ]] && A="true"
D="false"; [[ "$ADMIN" =~ ^[oO] ]] && D="true"

echo "{\"appName\":\"$APP\",\"auth\":$A,\"admin\":$D,\"tables\":{\"items\":[\"titre\",\"description\",\"statut\"]}}" > schema.json

curl -sL "$REPO/api.php" -o api.php
curl -sL "$REPO/index.html" -o index.html
curl -sL "$REPO/STACK.md" -o STACK.md
[ "$A" = "true" ] && curl -sL "$REPO/login.html" -o login.html
[ "$D" = "true" ] && curl -sL "$REPO/admin.html" -o admin.html

echo "Prêt ! Lance : cd $APP && php -S localhost:8000"
