# Commands

| Command | Purpose | Cost | Docker | Cloud |
| --- | --- | --- | --- | --- |
| `make help` | List repository command entrypoints. | cheap | no | no |
| `make check-tools` | Verify required host tools. | cheap | no | no |
| `make up` | Start default local services. | moderate | yes | no |
| `make up-app` | Start Nginx/PHP-FPM checkout app over HTTP. | moderate | yes | no |
| `make up-parity` | Start Caddy HTTPS/H1/H2/H3 edge profile. | moderate | yes | no |
| `make up-roadrunner` | Start optional RoadRunner/Octane runtime profile. | moderate | yes | no |
| `make validate` | Run scaffold and Phase 1 validation. | cheap to moderate | no by default | no |
| `make pre-push-full` | Run pre-push checks with checkout tests enabled. | expensive | sometimes | no |
| `make codex-workflows-status` | Show installed `codex-workflows` version and managed file count. | cheap | no | no |
| `make codex-workflows-update-dry-run` | Preview upstream `codex-workflows` managed-file updates. | cheap | no | no |
| `make codex-workflows-update` | Update managed `.agents/skills` and `.codex/agents` files from the pinned npm package. | cheap | no | no |
| `make test-markdown-style` | Run markdownlint over repository Markdown. | cheap | no | no |
| `make test-openapi` | Lint the checkout OpenAPI contract. | cheap | no | no |
| `make test-root-tools` | Run root npm-managed documentation, OpenAPI, and Codex workflow checks. | cheap | no | no |
| `make tools-outdated` | Show available updates for root npm-managed tools. | cheap | no | yes |
| `make tools-audit` | Audit root npm-managed tools for known vulnerabilities. | cheap | no | yes |
| `npm run tools:check` | Run root npm-managed documentation, OpenAPI, and Codex workflow checks. | cheap | no | no |
| `npm run tools:outdated` | Show available updates for root npm-managed tools. | cheap | no | yes |
| `npm run tools:audit` | Audit root npm-managed tools for known vulnerabilities. | cheap | no | yes |
| `sh scripts/test/checkout-app.sh` | Run checkout app Pest tests, using local PHP or checkout container fallback. | moderate | sometimes | no |
| `cd apps/checkout && php artisan test --parallel --recreate-databases` | Run checkout tests directly when local PHP dependencies are available. | moderate | no | no |
| `cd apps/checkout && composer validate` | Validate Composer metadata. | cheap | no | no |

Prefer the cheapest relevant command first. Do not run Docker/parity checks for docs-only changes unless the docs changed those flows.

## Codex Workflows

This repository pins `codex-workflows` in the root `package.json`; keep
Laravel PHP dependencies in [../../apps/checkout/composer.json](../../apps/checkout/composer.json).
Use npm only for repository-level Codex workflow tooling here.

Managed upstream files live under `.agents/skills` and `.codex/agents`, with
hashes tracked in `.codex-workflows-manifest.json`. Prefer
`make codex-workflows-update-dry-run` before applying updates so locally
modified managed files are visible before the manifest changes.

## Root NPM Policy

Use the root `package.json` for repository-level tooling only:

- Codex workflow tooling.
- Markdown and documentation checks.
- OpenAPI, schema, or generated-doc validation.
- Other repeatable checks shared by multiple apps or services.

Keep app-specific JavaScript dependencies in `apps/checkout/package.json`.
Keep PHP and Laravel dependencies in `apps/checkout/composer.json`.

Prefer pinned dev dependencies plus `npm run`, `npm exec`, or Make wrappers for
repeatable workflows. Run `npm install` from the repository root to install the
shared tools. Avoid floating `npx <tool>@latest` in documented commands
because it is not reproducible. One-off `npx` use is acceptable for local
investigation, but promote it to a pinned root dev dependency before making it
part of validation, CI, or agent runbooks.

## Dependency Updates

Update root npm tooling deliberately:

- Run `make tools-outdated` to inspect available updates.
- Update one tool family per branch, for example Codex workflows, Markdown
  tooling, or OpenAPI tooling.
- Prefer exact versions in `devDependencies`; let `package-lock.json` record the
  transitive dependency graph.
- Run `npm install` after editing root npm dependencies.
- Run `make tools-audit` after dependency changes.
- Do not run `npm audit fix --force` without review; it may downgrade or make
  breaking dependency changes.
- Keep runtime, Laravel frontend, and PHP dependencies out of the root package.
