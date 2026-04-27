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
| `sh scripts/test/checkout-app.sh` | Run checkout app Pest tests, using local PHP or checkout container fallback. | moderate | sometimes | no |
| `cd apps/checkout && php artisan test --parallel --recreate-databases` | Run checkout tests directly when local PHP dependencies are available. | moderate | no | no |
| `cd apps/checkout && composer validate` | Validate Composer metadata. | cheap | no | no |

Prefer the cheapest relevant command first. Do not run Docker/parity checks for docs-only changes unless the docs changed those flows.
