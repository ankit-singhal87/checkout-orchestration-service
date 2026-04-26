# Phase 2 System Completion Focus

Phase 2 should optimize for interview-demo system completeness, not checkout feature breadth.

## Priority Order

1. **Async boundary:** publish existing `outbox_events` to Redis Streams with an at-least-once Laravel command before considering a Go worker. **Current status:** implemented as `checkout:outbox:publish`; demo helpers are `make demo-outbox-publish` and `make demo-redis-events`.
2. **Runtime story:** add an opt-in RoadRunner path only after dependency compatibility is verified; keep `php artisan serve` as the default local fallback. **Current status:** opt-in startup path exists; Laravel Octane/RoadRunner dependency installation still requires explicit approval.
3. **Observability path:** make request logs, trace/request IDs, and local collector docs easy to demo before adding provider-specific exporters.
4. **Demo runbook:** document a short path that starts local services, walks two tenants through checkout, shows an outbox event, and verifies tests. **Current status:** [demo-runbook.md](../demo-runbook.md).
5. **Cloud/deploy shape:** keep AWS/EKS/Terraform optional and documented until budget guardrails and destroy workflows are explicit.

## Low-ROI For Now

- More Laravel checkout endpoints such as vouchers, loyalty, collection points, and address book.
- Go service extraction before a clear async or latency boundary is active.
- Provider-specific observability dashboards before the OTLP/local story is demonstrable.
- Full SCAYLE-shaped API coverage.

## Completed Async Slice

The outbox publisher is implemented as a Laravel console command:

- Read unpublished `outbox_events` in ID order.
- Publish to Redis Stream `checkout:events`.
- Include `event_id`, `event_type`, `aggregate_type`, `aggregate_id`, `tenant_record_id`, `payload`, and `created_at`.
- Mark `published_at` only after a successful publish.
- Keep checkout confirmation independent of Redis availability.

This slice demonstrates transactional consistency, async boundaries, local infrastructure, and retry-friendly event design with less effort than adding more customer-facing checkout features.

## Next Slice Recommendation

Complete the RoadRunner runtime only after approving the required Composer dependency change:

- `laravel/octane`
- `spiral/roadrunner-cli`
- `spiral/roadrunner-http`

Keep `php artisan serve` as the default local runtime.
