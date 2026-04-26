#!/usr/bin/env sh
set -eu

mkdir -p .git/hooks
cat > .git/hooks/pre-push <<'HOOK'
#!/usr/bin/env sh
set -eu

sh scripts/git/pre-push.sh
HOOK

chmod +x .git/hooks/pre-push

echo "Installed .git/hooks/pre-push."
