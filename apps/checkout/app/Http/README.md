# HTTP Layer

HTTP code owns controllers, form requests, middleware, presenters, API resources, and exception rendering.

Rules:

- Controllers delegate to application services.
- Requests validate transport input only.
- Presenters build Blade view models or API responses.
- API errors use RFC 9457 Problem Details.
- Tenant resolution happens in middleware or an application boundary, not in Blade templates.
