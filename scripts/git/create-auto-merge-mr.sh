#!/usr/bin/env sh
set -eu

target_branch="${TARGET_BRANCH:-main}"
source_branch="${SOURCE_BRANCH:-$(git branch --show-current)}"
head_sha="$(git rev-parse HEAD)"
title="${MR_TITLE:-$(git log -1 --pretty=%s)}"
squash_message="${SQUASH_MESSAGE:-$title}"
description="${MR_DESCRIPTION:-Summary: Agent-created merge request for $source_branch.

Validation: Run local validation before creating the merge request.

Risk: Review the branch diff and pipeline result before relying on auto-merge.}"

if [ -z "$source_branch" ]; then
  echo "Could not determine source branch. Set SOURCE_BRANCH explicitly." >&2
  exit 1
fi

if ! command -v glab >/dev/null 2>&1; then
  echo "glab is required to create GitLab merge requests." >&2
  exit 1
fi

if [ "${ALLOW_DIRTY:-0}" != "1" ]; then
  if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "Working tree has uncommitted changes. Commit or stash them before creating an MR." >&2
    exit 1
  fi
fi

echo "Pushing $source_branch to GitLab origin."
git push origin "$source_branch"

mr_list="$(glab mr list --source-branch "$source_branch" --target-branch "$target_branch")"

if printf '%s\n' "$mr_list" | grep -q '^No open merge requests'; then
  mr_exists=0
elif [ -n "$mr_list" ]; then
  mr_exists=1
else
  mr_exists=0
fi

if [ "$mr_exists" -eq 1 ]; then
  echo "Merge request already exists for $source_branch -> $target_branch."
else
  glab mr create \
    --source-branch "$source_branch" \
    --target-branch "$target_branch" \
    --title "$title" \
    --description "$description" \
    --squash-before-merge \
    --remove-source-branch \
    --yes
fi

glab mr merge "$source_branch" \
  --auto-merge \
  --squash \
  --squash-message "$squash_message" \
  --remove-source-branch \
  --sha "$head_sha" \
  --yes

glab mr view "$source_branch"
