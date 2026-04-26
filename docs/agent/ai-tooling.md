# AI Tooling

## Cursor

Cursor is the primary implementation environment. Agents should work through normal Git branches and GitLab merge requests.
Agent commits should be small, coherent, and named with clean imperative messages, not generic tool-generated text.

The `cursor-dev-agent-git` GitLab token is only for local Git over HTTPS.

Use it like a password when Git prompts for credentials:

- Username: your GitLab username.
- Password: the `cursor-dev-agent-git` token.

Do not store this token in `.env`, docs, scripts, CI variables, shell history, or repository files.

## ChatGPT/Codex

Use the ChatGPT/Codex plan as a separate assistant for:

- architecture review
- second opinions
- test-case generation
- code review prompts
- documentation review

Do not assume a ChatGPT/Codex subscription pays for Cursor model usage.

## Provider API Keys In Cursor

Cursor can use provider API keys, but OpenAI API usage is billed separately from ChatGPT/Codex subscriptions.

Only add a provider API key to Cursor if a separate API budget is intentional.

Configuration path:

`Cursor Settings > Models > Provider > Add key > Verify > Save`

## Repository Hosting

- GitLab is primary. GitHub is a mirror.
- Agents may push branches to GitLab when asked.
- Agent-created GitLab merge requests are enabled for this project when an API-capable local `glab` session is authenticated.
- Agents may create GitLab merge requests targeting `main` when the user asks.
- Merge requests should squash branch commits before merge and use a clean squash commit message.
- Merge remains manual.
- See [docs/agent/mirroring.md](mirroring.md).
