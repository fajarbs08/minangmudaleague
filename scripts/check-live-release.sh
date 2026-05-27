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

run_ssh() {
    if [[ -n "${SSHPASS:-}" ]]; then
        sshpass -e ssh -o StrictHostKeyChecking=accept-new -p "$DEPLOY_SSH_PORT" "$DEPLOY_SSH_USER@$DEPLOY_SSH_HOST" "$@"
    else
        ssh -o StrictHostKeyChecking=accept-new -p "$DEPLOY_SSH_PORT" "$DEPLOY_SSH_USER@$DEPLOY_SSH_HOST" "$@"
    fi
}

require_env DEPLOY_SSH_HOST
require_env DEPLOY_SSH_USER
require_env DEPLOY_REMOTE_APP_PATH

DEPLOY_SSH_PORT="${DEPLOY_SSH_PORT:-22}"

git -C "$ROOT_DIR" fetch origin --quiet

manifest_file="$(mktemp)"
trap 'rm -f "$manifest_file"' EXIT

run_ssh "test -f '$DEPLOY_REMOTE_APP_PATH/.deploy-manifest.json' && cat '$DEPLOY_REMOTE_APP_PATH/.deploy-manifest.json'" > "$manifest_file"

live_commit="$(php -r '$data = json_decode(file_get_contents($argv[1]), true); if (!is_array($data) || !isset($data["git"]["commit"])) { fwrite(STDERR, "Manifest deploy tidak valid.\n"); exit(1);} echo $data["git"]["commit"];' "$manifest_file")"
live_branch="$(php -r '$data = json_decode(file_get_contents($argv[1]), true); echo $data["git"]["branch"] ?? "";' "$manifest_file")"
live_deployed_at="$(php -r '$data = json_decode(file_get_contents($argv[1]), true); echo $data["deployed_at_utc"] ?? "";' "$manifest_file")"

local_commit="$(git -C "$ROOT_DIR" rev-parse HEAD)"
origin_commit="$(git -C "$ROOT_DIR" rev-parse origin/main)"

printf 'Live branch      : %s\n' "$live_branch"
printf 'Live commit      : %s\n' "$live_commit"
printf 'Live deployed at : %s\n' "$live_deployed_at"
printf 'Local HEAD       : %s\n' "$local_commit"
printf 'Origin main      : %s\n' "$origin_commit"

if [[ "$live_commit" == "$local_commit" && "$live_commit" == "$origin_commit" ]]; then
    printf 'Status           : sinkron\n'
else
    printf 'Status           : tidak sinkron\n'
    exit 1
fi
