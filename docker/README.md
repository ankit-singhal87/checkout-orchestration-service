# Docker Images

Dockerfiles and image build context helpers live here.

## Phase 1 Scope

- Keep service images local-first and reproducible.
- Add Laravel/RoadRunner image definitions when the Laravel app skeleton exists.
- Add Go worker images only after a worker boundary is approved.

Docker Compose remains the default local runtime entry point.

## Checkout Image

`docker/checkout.Dockerfile` provides PHP CLI, Composer, and required PHP extensions for bootstrapping and testing the Laravel checkout app without installing PHP or Composer on the host.