# Guardrails

- Keep diffs focused; avoid broad rewrites.
- Avoid unrelated formatting churn.
- Do not upgrade dependencies without a task-specific reason and approval where needed.
- Do not generate or commit secrets.
- Do not invent production-readiness claims.
- Do not use vendor-specific naming for generic platform behavior.
- Do not add large docs without updating the relevant index.
- Keep human and agent documentation lanes separate.
- Preserve useful existing docs; move or summarize only when clearly misplaced, stale, duplicate, or unsafe.
- Prefer Makefile targets over direct scripts when both exist.
