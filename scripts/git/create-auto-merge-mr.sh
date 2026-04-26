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
auto_merge_attempts="${AUTO_MERGE_ATTEMPTS:-10}"
auto_merge_sleep_seconds="${AUTO_MERGE_SLEEP_SECONDS:-3}"

if [ -z "$source_branch" ]; then
  echo "Could not determine source branch. Set SOURCE_BRANCH explicitly." >&2
  exit 1
fi

if ! command -v glab >/dev/null 2>&1; then
  echo "glab is required to create GitLab merge requests." >&2
  exit 1
fi

if ! command -v jq >/dev/null 2>&1; then
  echo "jq is required to read merge request metadata from the GitLab API." >&2
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

mr_json="$(glab api "projects/:fullpath/merge_requests?state=opened&source_branch=$source_branch&target_branch=$target_branch")"
mr_iid="$(printf '%s\n' "$mr_json" | jq -r '.[0].iid // empty')"

if [ -n "$mr_iid" ]; then
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

  mr_json="$(glab api "projects/:fullpath/merge_requests?state=opened&source_branch=$source_branch&target_branch=$target_branch")"
  mr_iid="$(printf '%s\n' "$mr_json" | jq -r '.[0].iid // empty')"
fi

if [ -z "$mr_iid" ]; then
  echo "Could not find an open merge request for $source_branch -> $target_branch." >&2
  exit 1
fi

attempt=1
while [ "$attempt" -le "$auto_merge_attempts" ]; do
  glab api -X PUT "projects/:fullpath/merge_requests/$mr_iid/merge" \
    -F auto_merge=true \
    -F squash=true \
    -f squash_commit_message="$squash_message" \
    -F should_remove_source_branch=true \
    -f sha="$head_sha" \
    --silent || true

  mr_state="$(glab api "projects/:fullpath/merge_requests/$mr_iid")"
  merge_when_pipeline_succeeds="$(printf '%s\n' "$mr_state" | jq -r '.merge_when_pipeline_succeeds')"
  state="$(printf '%s\n' "$mr_state" | jq -r '.state')"

  if [ "$merge_when_pipeline_succeeds" = "true" ] || [ "$state" = "merged" ]; then
    break
  fi

  if [ "$attempt" -eq "$auto_merge_attempts" ]; then
    echo "Could not enable auto-merge for merge request !$mr_iid." >&2
    exit 1
  fi

  echo "Auto-merge is not ready yet for !$mr_iid; retrying in ${auto_merge_sleep_seconds}s."
  sleep "$auto_merge_sleep_seconds"
  attempt=$((attempt + 1))
done

glab mr view "$mr_iid"
