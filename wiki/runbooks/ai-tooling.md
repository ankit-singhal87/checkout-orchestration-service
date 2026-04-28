# AI Tooling

## Codex

Codex is the primary implementation environment. Agents should work through normal Git branches and GitLab merge requests.
Agent commits should be small, coherent, and named with clean imperative messages, not generic tool-generated text.

The local agent Git token is only for local Git over HTTPS.

Use it like a password when Git prompts for credentials:

- Username: your GitLab username.
- Password: the local agent Git token.

Do not store this token in `.env`, docs, scripts, CI variables, shell history, or repository files.

## Codex Review Uses

Use Codex for:

- architecture review
- second opinions
- test-case generation
- code review prompts
- documentation review

Do not use separate provider API keys unless a separate API budget is intentional.

Keep provider credentials outside the repository and outside committed shell history.

## Repository Hosting

- GitLab is primary. GitHub is a mirror.
- Agents may push branches to GitLab when asked.
- Agent-created GitLab merge requests are enabled for this project when an API-capable local `glab` session is authenticated.
- Agents may create GitLab merge requests targeting `main` when the user asks.
- Merge requests should squash branch commits before merge and use a clean squash commit message.
- Merge remains manual.
- See [repository-mirroring.md](repository-mirroring.md).
