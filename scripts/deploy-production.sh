#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

require_env() {
    local name="$1"
    if [[ -z "${!name:-}" ]]; then
        printf 'Environment variable %s wajib diisi.\n' "$name" >&2
        exit 1
    fi
}

require_clean_worktree() {
    if ! git -C "$ROOT_DIR" diff --quiet || ! git -C "$ROOT_DIR" diff --cached --quiet; then
        printf 'Working tree harus bersih sebelum deploy. Commit atau stash perubahan dulu.\n' >&2
        exit 1
    fi
}

require_pushed_head() {
    git -C "$ROOT_DIR" fetch origin --quiet

    local head_commit
    local origin_commit

    head_commit="$(git -C "$ROOT_DIR" rev-parse HEAD)"
    origin_commit="$(git -C "$ROOT_DIR" rev-parse origin/main)"

    if [[ "$head_commit" != "$origin_commit" ]]; then
        printf 'HEAD lokal belum sama dengan origin/main. Push ke GitHub dulu sebelum deploy.\n' >&2
        exit 1
    fi
}

run_ssh() {
    if [[ -n "${SSHPASS:-}" ]]; then
        sshpass -e ssh -o StrictHostKeyChecking=accept-new -p "$DEPLOY_SSH_PORT" "$DEPLOY_SSH_USER@$DEPLOY_SSH_HOST" "$@"
    else
        ssh -o StrictHostKeyChecking=accept-new -p "$DEPLOY_SSH_PORT" "$DEPLOY_SSH_USER@$DEPLOY_SSH_HOST" "$@"
    fi
}

run_rsync() {
    if [[ -n "${SSHPASS:-}" ]]; then
        sshpass -e rsync "$@"
    else
        rsync "$@"
    fi
}

require_env DEPLOY_SSH_HOST
require_env DEPLOY_SSH_USER
require_env DEPLOY_REMOTE_APP_PATH
require_env DEPLOY_REMOTE_PUBLIC_PATH

DEPLOY_SSH_PORT="${DEPLOY_SSH_PORT:-22}"
DEPLOY_BUILD="${DEPLOY_BUILD:-1}"
DEPLOY_COMPOSER_INSTALL="${DEPLOY_COMPOSER_INSTALL:-1}"
DEPLOY_MIGRATE="${DEPLOY_MIGRATE:-1}"
DEPLOY_CACHE="${DEPLOY_CACHE:-1}"
DEPLOY_DRY_RUN="${DEPLOY_DRY_RUN:-0}"

require_clean_worktree
require_pushed_head

cd "$ROOT_DIR"

if [[ "$DEPLOY_BUILD" == "1" ]]; then
    npm run build
fi

branch="$(git rev-parse --abbrev-ref HEAD)"
commit_sha="$(git rev-parse HEAD)"
commit_short="$(git rev-parse --short HEAD)"
origin_url="$(git remote get-url origin 2>/dev/null || true)"
deployed_at_utc="$(date -u +"%Y-%m-%dT%H:%M:%SZ")"
deployed_by="$(git config user.name 2>/dev/null || whoami)"

manifest_file="$(mktemp)"
trap 'rm -f "$manifest_file"' EXIT

cat > "$manifest_file" <<EOF
{
  "app": "Liga Anak Piaman Laweh",
  "git": {
    "branch": "$branch",
    "commit": "$commit_sha",
    "commit_short": "$commit_short",
    "origin": "$origin_url"
  },
  "deployed_at_utc": "$deployed_at_utc",
  "deployed_by": "$deployed_by"
}
EOF

rsync_args=(
    -az
    --delete
    --exclude=.git/
    --exclude=.env
    --exclude=vendor/
    --exclude=node_modules/
    --exclude=storage/
    --exclude=bootstrap/cache/
    --exclude=artifacts-trash/
    --exclude=create_admin.php
    --exclude=patch_public_index.php
    -e
    "ssh -o StrictHostKeyChecking=accept-new -p $DEPLOY_SSH_PORT"
)

public_rsync_args=(
    -az
    --delete
    --exclude=index.php
    --exclude=storage
    -e
    "ssh -o StrictHostKeyChecking=accept-new -p $DEPLOY_SSH_PORT"
)

if [[ "$DEPLOY_DRY_RUN" == "1" ]]; then
    rsync_args+=(--dry-run)
    public_rsync_args+=(--dry-run)
fi

run_ssh "mkdir -p '$DEPLOY_REMOTE_APP_PATH' '$DEPLOY_REMOTE_PUBLIC_PATH'"

run_rsync "${rsync_args[@]}" "$ROOT_DIR/" "$DEPLOY_SSH_USER@$DEPLOY_SSH_HOST:$DEPLOY_REMOTE_APP_PATH/"
run_rsync "${public_rsync_args[@]}" "$ROOT_DIR/public/" "$DEPLOY_SSH_USER@$DEPLOY_SSH_HOST:$DEPLOY_REMOTE_PUBLIC_PATH/"

if [[ "$DEPLOY_DRY_RUN" == "1" ]]; then
    printf 'Dry run selesai. Manifest tidak ditulis dan command server tidak dijalankan.\n'
    exit 0
fi

run_rsync -az -e "ssh -o StrictHostKeyChecking=accept-new -p $DEPLOY_SSH_PORT" "$manifest_file" "$DEPLOY_SSH_USER@$DEPLOY_SSH_HOST:$DEPLOY_REMOTE_APP_PATH/.deploy-manifest.json"

remote_commands=(
    "cd '$DEPLOY_REMOTE_APP_PATH'"
)

if [[ "$DEPLOY_COMPOSER_INSTALL" == "1" ]]; then
    remote_commands+=("composer install --no-dev --optimize-autoloader --no-interaction")
fi

if [[ "$DEPLOY_MIGRATE" == "1" ]]; then
    remote_commands+=("php artisan migrate --force")
fi

if [[ "$DEPLOY_CACHE" == "1" ]]; then
    remote_commands+=("composer run production:cache")
fi

remote_command="${remote_commands[0]}"

for ((i = 1; i < ${#remote_commands[@]}; i++)); do
    remote_command+=" && ${remote_commands[i]}"
done

run_ssh "$remote_command"

printf 'Deploy selesai. Live commit: %s\n' "$commit_sha"
