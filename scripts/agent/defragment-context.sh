#!/usr/bin/env sh
set -eu

handoff_file="${CONTEXT_HANDOFF_FILE:-docs/agent/context-handoff.md}"
section_title="## Active Threads"

usage() {
  cat <<'USAGE'
Usage:
  HANDOFF_LINES="- Keep this one-line learning" make defragment-context
  HANDOFF_FILE=/tmp/handoff-bullets.txt make defragment-context
  make show-context

Prepares an orchestrator-owned context defragmentation handoff by replacing
the volatile Active Threads section in docs/agent/context-handoff.md with only
the selected handoff lines.

This command is phase 1 of defragmentation. Phase 2 must be done by the
orchestrator: stop the current worker/session and start a fresh one from
docs/agent/README.md plus docs/agent/context-handoff.md.
USAGE
}

if [ "${SHOW_CONTEXT:-0}" = "1" ]; then
  cat "$handoff_file"
  exit 0
fi

if [ ! -f "$handoff_file" ]; then
  echo "Context handoff file not found: $handoff_file" >&2
  exit 1
fi

if [ "${1:-}" = "--help" ]; then
  usage
  exit 0
fi

lines_file="$(mktemp)"
updated_file="$(mktemp)"
trap 'rm -f "$lines_file" "$updated_file"' EXIT

if [ "${HANDOFF_FILE:-}" != "" ]; then
  if [ ! -f "$HANDOFF_FILE" ]; then
    echo "HANDOFF_FILE not found: $HANDOFF_FILE" >&2
    exit 1
  fi
  cat "$HANDOFF_FILE" > "$lines_file"
elif [ "${HANDOFF_LINES:-}" != "" ]; then
  printf '%s\n' "$HANDOFF_LINES" > "$lines_file"
else
  usage >&2
  exit 2
fi

normalized_file="$(mktemp)"
trap 'rm -f "$lines_file" "$updated_file" "$normalized_file"' EXIT

while IFS= read -r line; do
  case "$line" in
    "")
      ;;
    "- "*)
      printf '%s\n' "$line" >> "$normalized_file"
      ;;
    *)
      printf -- '- %s\n' "$line" >> "$normalized_file"
      ;;
  esac
done < "$lines_file"

if [ ! -s "$normalized_file" ]; then
  echo "No handoff lines provided after trimming blank lines." >&2
  exit 2
fi

awk -v section="$section_title" -v bullets="$normalized_file" '
  $0 == section {
    print $0
    print ""
    while ((getline line < bullets) > 0) {
      print line
    }
    close(bullets)
    in_section = 1
    replaced = 1
    next
  }

  in_section && /^## / {
    in_section = 0
    print ""
    print $0
    next
  }

  in_section {
    next
  }

  {
    print $0
  }

  END {
    if (!replaced) {
      print ""
      print section
      print ""
      while ((getline line < bullets) > 0) {
        print line
      }
      close(bullets)
    }
  }
' "$handoff_file" > "$updated_file"

mv "$updated_file" "$handoff_file"

echo "Context handoff defragmented: $handoff_file"
echo "Persisted lines:"
cat "$normalized_file"
echo ""
echo "Orchestrator action required: close this context-heavy agent/session and continue in a fresh one from docs/agent/README.md plus $handoff_file."
