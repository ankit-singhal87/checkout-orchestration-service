# Validation Matrix

| Change type | Minimal validation | Stronger validation | Docker? | Cloud? | Cost |
| --- | --- | --- | --- | --- | --- |
| Docs-only change | `rg` for stale links/terms; markdown link check if available | `make validate` | no by default | no | cheap |
| PHP app change | focused Pest/unit command or `sh scripts/test/checkout-app.sh` | `make pre-push-full` | sometimes | no | moderate to expensive |
| Docker/runtime change | relevant `make up-*` smoke path | `make pre-push-full` plus runtime smoke target | yes | no | moderate to expensive |
| CI change | `make validate` and script syntax checks | CI pipeline/MR validation | no locally | no | cheap to moderate |
| Caddy/parity change | `make up-parity` then `make test-checkout-parity` | add default app/runtime checks | yes | no | moderate |
| Worker/outbox change | focused worker command or `make test-order-processor-runtime` | `make test-worker-runtime-smoke` | yes | no | moderate |
| ADR/human-doc change | link/term search and markdown link check | `make validate` | no by default | no | cheap |

Do not run expensive Docker/parity validation for docs-only edits unless those docs change Docker/parity instructions.
