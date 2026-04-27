#!/usr/bin/env sh
set -eu

tmp_links="$(mktemp)"
tmp_code_links="$(mktemp)"
tmp_missing_links="$(mktemp)"
trap 'rm -f "$tmp_links" "$tmp_code_links" "$tmp_missing_links"' EXIT

find . \
  -path './.git' -prune -o \
  -path './.agents' -prune -o \
  -path './node_modules' -prune -o \
  -path './apps/checkout/vendor' -prune -o \
  -path './apps/checkout/node_modules' -prune -o \
  -path './apps/checkout/storage/framework/views' -prune -o \
  -name '*.md' -print | sort >"$tmp_links"

missing=0

while IFS= read -r file; do
  if grep -nE '`[^`]*!?\[[^]]+\]\([^)]+\)[^`]*`' "$file" >>"$tmp_code_links"; then
    missing=1
  fi
done <"$tmp_links"

if [ -s "$tmp_code_links" ]; then
  echo "Markdown links must not be wrapped in inline code ticks:" >&2
  cat "$tmp_code_links" >&2
fi

awk '
  {
    rest = $0
    while (match(rest, /!?\[[^]]+\]\([^)]+\)/)) {
      token = substr(rest, RSTART, RLENGTH)
      target = token
      sub(/^!?\[[^]]+\]\(/, "", target)
      sub(/\)$/, "", target)
      split(target, parts, /[[:space:]]+/)
      print FILENAME "\t" FNR "\t" parts[1]
      rest = substr(rest, RSTART + RLENGTH)
    }
  }
' $(cat "$tmp_links") | while IFS='	' read -r file line target; do
  case "$target" in
    ''|\#*|http://*|https://*|mailto:*|tel:*|//*)
      continue
      ;;
  esac

  clean_target="${target%%#*}"
  clean_target="${clean_target%%\?*}"

  case "$clean_target" in
    '')
      continue
      ;;
    /*)
      check_path=".${clean_target}"
      ;;
    *)
      check_path="$(dirname "$file")/${clean_target}"
      ;;
  esac

  if [ ! -e "$check_path" ]; then
    echo "$file:$line: missing Markdown link target: $target" >>"$tmp_missing_links"
  fi
done

if [ -s "$tmp_missing_links" ]; then
  cat "$tmp_missing_links" >&2
  missing=1
fi

if [ "$missing" -ne 0 ]; then
  exit 1
fi

count="$(wc -l <"$tmp_links" | tr -d ' ')"
echo "Markdown link validation passed for $count files."
