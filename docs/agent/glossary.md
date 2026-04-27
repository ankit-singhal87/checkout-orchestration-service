# Glossary

- Parity profile: local mode that exercises production-like edge/runtime behavior without claiming production equivalence.
- Edge profile: Caddy-fronted local HTTPS profile for forwarded headers, security headers, and protocol smoke checks.
- Outbox: transactional table of side effects written with domain changes and published asynchronously.
- Idempotency key: caller-provided key used to make repeated checkout/order requests safe.
- Tenant: isolated commerce context resolved by trusted host/configuration, not by untrusted path alone.
- RDS: AWS managed relational database service; production target is MySQL-compatible RDS.
- PHP-FPM: default PHP process manager behind Nginx for the Laravel app.
- RoadRunner/Octane: optional long-running Laravel runtime profile for performance/runtime experiments.
- Caddy: local edge proxy used for HTTPS/H1/H2/H3 smoke checks.
- HTTP/3/QUIC: edge protocol coverage tested only as local smoke validation.
