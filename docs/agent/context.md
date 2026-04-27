# Agent Context

- Independent generic headless-commerce checkout orchestration POC.
- Primary app: Laravel checkout app in `apps/checkout`.
- Baseline local runtime: Nginx/PHP-FPM over HTTP.
- Optional runtime: RoadRunner/Octane for performance/runtime experiments.
- Edge parity profile: Caddy HTTPS with HTTP/1.1, HTTP/2, and HTTP/3/QUIC smoke checks.
- Local/CI database: MySQL container.
- Production database target: RDS MySQL.
- Local/CI/parity/AWS are separate modes; do not blur their guarantees.
- HTTP/3 coverage is edge smoke only, not a full application protocol matrix.
- AWS-oriented deployment docs/assets exist, but deployment is optional and manually approved.
