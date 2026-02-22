#!/usr/bin/env bash
set -euo pipefail

mkdir -p /var/www/html/storage/backups
chmod -R 0777 /var/www/html/storage || true

exec apache2-foreground
