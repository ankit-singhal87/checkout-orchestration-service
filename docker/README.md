# Docker Images

Dockerfiles and image build context helpers live here.

## Phase 1 Scope

- Keep service images local-first and reproducible.
- Keep the default Laravel image compatible with PHP-FPM for local development and PHP CLI for tests.
- Keep RoadRunner/Octane as an optional performance profile rather than the default local runtime.
- Add Go worker images only after a worker boundary is approved.

Docker Compose remains the default local runtime entry point.

## Checkout Image

[docker/checkout.Dockerfile](checkout.Dockerfile) provides PHP-FPM, PHP CLI, Composer, and required PHP extensions for running and testing the Laravel checkout app without installing PHP or Composer on the host.
