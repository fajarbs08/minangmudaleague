#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
RENDERER_DIR="$ROOT_DIR/storage/app/id-card-node"

mkdir -p "$RENDERER_DIR"
cd "$RENDERER_DIR"

if [ ! -f package.json ]; then
  npm init -y >/dev/null 2>&1
fi

PUPPETEER_SKIP_DOWNLOAD=1 npm install puppeteer

echo "ID card renderer dependencies installed in $RENDERER_DIR"
