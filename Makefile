SHELL := /bin/sh

.PHONY: help
help:
	@printf '%s\n' 'Common commands:'
	@printf '%s\n' '  make check-tools              Verify required host tools'
	@printf '%s\n' '  make up                       Start default local services'
	@printf '%s\n' '  make up-app                   Start Nginx/PHP-FPM checkout app over HTTP'
	@printf '%s\n' '  make up-roadrunner            Start optional RoadRunner performance profile'
	@printf '%s\n' '  make up-outbox-worker         Start checkout app plus outbox worker'
	@printf '%s\n' '  make up-order-processor       Start order processor worker'
	@printf '%s\n' '  make up-parity                Start Caddy HTTPS/H1/H2/H3 edge path'
	@printf '%s\n' '  make up-search                Start search profile services'
	@printf '%s\n' '  make up-observability         Start observability profile services'
	@printf '%s\n' '  make up-identity              Start identity profile services'
	@printf '%s\n' '  make down                     Stop local services'
	@printf '%s\n' '  make bootstrap-checkout       Bootstrap Laravel checkout app if missing'
	@printf '%s\n' '  make install-host-tools       Install missing essential host tools'
	@printf '%s\n' '  make test-checkout            Run checkout app tests'
	@printf '%s\n' '  make test-checkout-runtime    Smoke test RoadRunner HTTP runtime'
	@printf '%s\n' '  make test-checkout-parity     Smoke test Caddy HTTPS/H1/H2/H3 edge path'
	@printf '%s\n' '  make test-worker-runtime-smoke Smoke test outbox and order processor workers'
	@printf '%s\n' '  make test-order-processor-runtime Smoke test order processor command registration'
	@printf '%s\n' '  make validate                 Run scaffold and Phase 1 validation'
	@printf '%s\n' '  make pre-push                 Run the repository pre-push checks'
	@printf '%s\n' '  make pre-push-full            Run pre-push checks including checkout tests'
	@printf '%s\n' '  make demo-outbox-publish      Publish demo outbox rows to Redis Streams'
	@printf '%s\n' '  make demo-redis-events        Show recent Redis Stream checkout events'
	@printf '%s\n' '  make show-context             Show compact agent context handoff'
	@printf '%s\n' '  make defragment-context       Persist selected handoff lines and drop redundant context'
	@printf '%s\n' '  make create-auto-merge-mr     Create GitLab MR with squash and auto-merge'
	@printf '%s\n' '  make install-hooks            Install local Git hooks'

.PHONY: check-tools
check-tools:
	sh scripts/dev/check-tools.sh

.PHONY: up
up:
	sh scripts/dev/up.sh

.PHONY: up-app
up-app:
	COMPOSE_PROFILES=app sh scripts/dev/up.sh

.PHONY: up-roadrunner
up-roadrunner:
	COMPOSE_PROFILES=performance docker compose up -d checkout-roadrunner

.PHONY: up-outbox-worker
up-outbox-worker:
	COMPOSE_PROFILES=app,worker sh scripts/dev/up.sh

.PHONY: up-order-processor
up-order-processor:
	COMPOSE_PROFILES=app,worker sh scripts/dev/up.sh

.PHONY: up-parity
up-parity:
	docker compose -f docker-compose.yml -f docker-compose.caddy.yml up -d

.PHONY: up-search
up-search:
	COMPOSE_PROFILES=search sh scripts/dev/up.sh

.PHONY: up-observability
up-observability:
	COMPOSE_PROFILES=observability sh scripts/dev/up.sh

.PHONY: up-identity
up-identity:
	COMPOSE_PROFILES=identity sh scripts/dev/up.sh

.PHONY: down
down:
	sh scripts/dev/down.sh

.PHONY: bootstrap-checkout
bootstrap-checkout:
	sh scripts/dev/bootstrap-checkout-app.sh

.PHONY: install-host-tools
install-host-tools:
	sh scripts/dev/install-host-tools.sh

.PHONY: test-checkout
test-checkout:
	sh scripts/test/checkout-app.sh

.PHONY: test-checkout-runtime
test-checkout-runtime:
	sh scripts/test/checkout-runtime.sh

.PHONY: test-checkout-parity
test-checkout-parity:
	sh scripts/test/checkout-parity.sh

.PHONY: test-worker-runtime-smoke
test-worker-runtime-smoke:
	sh scripts/test/worker-runtime.sh
	sh scripts/test/order-processor-runtime.sh

.PHONY: test-order-processor-runtime
test-order-processor-runtime:
	sh scripts/test/order-processor-runtime.sh

.PHONY: test-markdown-links
test-markdown-links:
	sh scripts/test/markdown-links.sh

.PHONY: test-behavior-specs
test-behavior-specs:
	sh scripts/test/behavior-specs.sh

.PHONY: test-route-stubs
test-route-stubs:
	sh scripts/test/route-stubs.sh

.PHONY: test-seed-fixtures
test-seed-fixtures:
	sh scripts/test/seed-fixtures.sh

.PHONY: validate-scaffold
validate-scaffold:
	sh scripts/ci/validate-scaffold.sh

.PHONY: validate-phase1
validate-phase1:
	sh scripts/ci/validate-phase1.sh

.PHONY: validate
validate: validate-scaffold validate-phase1

.PHONY: migration-immutability
migration-immutability:
	sh scripts/git/check-migration-immutability.sh "$${MIGRATION_IMMUTABILITY_BASE_REF:-origin/main}"

.PHONY: pre-push
pre-push:
	sh scripts/git/pre-push.sh

.PHONY: pre-push-full
pre-push-full:
	RUN_CHECKOUT_TESTS_ON_PRE_PUSH=1 sh scripts/git/pre-push.sh

.PHONY: demo-outbox-publish
demo-outbox-publish:
	docker compose exec -T checkout php artisan checkout:outbox:publish --limit="$${LIMIT:-10}"

.PHONY: demo-redis-events
demo-redis-events:
	docker compose exec -T redis redis-cli XRANGE "$${REDIS_STREAM:-checkout:events}" - + COUNT "$${COUNT:-5}"

.PHONY: show-context
show-context:
	SHOW_CONTEXT=1 sh scripts/agent/defragment-context.sh

.PHONY: defragment-context
defragment-context:
	sh scripts/agent/defragment-context.sh

.PHONY: create-auto-merge-mr
create-auto-merge-mr:
	sh scripts/git/create-auto-merge-mr.sh

.PHONY: install-hooks
install-hooks:
	sh scripts/git/install-hooks.sh
