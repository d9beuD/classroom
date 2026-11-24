#!/usr/bin/env bash
# PostToolUse hook: after Claude edits a PHP file, format it with php-cs-fixer and check it with PHPStan.
# PHPStan errors exit 2 so Claude sees them and fixes them; anything else never blocks the edit.
set -uo pipefail

file=$(jq -r '.tool_input.file_path // empty')
root=${CLAUDE_PROJECT_DIR:-$(pwd)}
rel=${file#"$root"/}

case "$rel" in
    config/reference.php) exit 0 ;;
    bin/*.php | config/*.php | public/*.php | src/*.php | tests/*.php) ;;
    *) exit 0 ;;
esac

cd "$root" || exit 0
# The tools only run inside the app container: skip quietly when it is down.
docker compose exec -T app true 2>/dev/null || exit 0

docker compose exec -T app vendor/bin/php-cs-fixer fix --quiet "$rel" >/dev/null 2>&1

if ! errors=$(docker compose exec -T app vendor/bin/phpstan analyse --memory-limit=1G --no-progress --error-format=raw "$rel" 2>&1); then
    echo "PHPStan found errors in $rel:" >&2
    grep -v '^Note: ' <<<"$errors" >&2
    exit 2
fi
