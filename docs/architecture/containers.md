# C4: Containers

```mermaid
flowchart LR
  Browser[Browser] --> CloudFront[CloudFrontHTTP3Optional]
  CloudFront --> Laravel[LaravelRoadRunnerCheckout]
  Browser --> Laravel
  Laravel --> MySQL[(MySQLMultipleSchemas)]
  Laravel --> Redis[(RedisCacheLocksStreams)]
  Laravel --> OpenSearch[(OpenSearchReadModels)]
  Laravel --> OTel[OpenTelemetryCollector]
  Laravel --> Worker[OrderProcessorWorker]
  Worker --> Redis
  Worker --> MySQL
  Worker --> OpenSearch
  OTel --> Grafana[GrafanaLocalOrCloud]
```

## Local Mode

- Laravel/RoadRunner
- MySQL
- Redis
- OpenSearch
- OpenTelemetry Collector
- Prometheus
- Loki
- Grafana
- Jaeger or Tempo

## Deploy Mode

- Amazon EKS
- CloudFront with HTTP/3 viewer connections
- AWS WAF
- ALB Ingress
- RDS MySQL
- ElastiCache Redis
- SQS/SNS
- Optional AWS OpenSearch
- Grafana Cloud by default
