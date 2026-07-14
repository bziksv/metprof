#!/bin/sh
set -e
cd "$(dirname "$0")/.."
HOST=metprof
PROD_PATH=/var/www/fastuser/data/www/metprof-vrn.ru

echo "==> Push to GitHub"
git push origin main

echo "==> Deploy on production"
ssh "$HOST" "cd $PROD_PATH && set -e
  SECRET_DIR=/tmp/metprof-secrets-\$(date +%s)
  mkdir -p \"\$SECRET_DIR\"
  cp -a bitrix/license_key.php \"\$SECRET_DIR/\" 2>/dev/null || true
  cp -a bitrix/.settings.php \"\$SECRET_DIR/\" 2>/dev/null || true
  cp -a bitrix/php_interface/dbconn.php \"\$SECRET_DIR/\" 2>/dev/null || true
  cp -a bitrix/php_interface/dbconn.local.php \"\$SECRET_DIR/\" 2>/dev/null || true

  git fetch origin
  git reset --hard origin/main

  cp -a \"\$SECRET_DIR/license_key.php\" bitrix/license_key.php 2>/dev/null || true
  cp -a \"\$SECRET_DIR/.settings.php\" bitrix/.settings.php 2>/dev/null || true
  cp -a \"\$SECRET_DIR/dbconn.php\" bitrix/php_interface/dbconn.php 2>/dev/null || true
  cp -a \"\$SECRET_DIR/dbconn.local.php\" bitrix/php_interface/dbconn.local.php 2>/dev/null || true
  rm -rf \"\$SECRET_DIR\"

  chown -R fastuser:fastuser .
  rm -rf bitrix/cache/* bitrix/managed_cache/* bitrix/stack_cache/* 2>/dev/null || true
  git log -1 --oneline
  echo cache_cleared
"

echo "==> Done: https://metprof-vrn.ru/"
