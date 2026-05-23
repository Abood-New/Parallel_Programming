# Measurement and Identification of Bottlenecks  
## (Analysis Bottleneck & Benchmarking)

The system was progressively benchmarked under concurrent transactional workloads to identify scalability bottlenecks and evaluate the effectiveness of each optimization strategy.

The primary tested workflow included:

1. Product browsing  
2. Adding items to cart  
3. Checkout and stock update operations  

Stress testing was performed using the k6 load-testing framework under realistic e-commerce scenarios with concurrent users performing transactional operations simultaneously.

---

# Benchmark Results Summary

| Phase | Identified Bottleneck | Optimization Applied | Avg Response Time | Throughput | Failure Rate | Observation |
|---|---|---|---|---|---|---|
| Initial Unsafe Checkout | Race condition / inventory inconsistency | No locking | ~3s | Very low | High inconsistency risk | Multiple users could purchase the same stock simultaneously |
| Pessimistic Locking | Database lock contention | `lockForUpdate()` | ~3s | Sequential processing | 0% | Correctness achieved but requests waited for locks |
| Atomic Database Update | Lock contention overhead | Conditional atomic SQL update | ~2.05s | ~92 req/sec | 0% | Reduced contention and improved transactional concurrency |
| Worker Saturation | Limited concurrent request processing | Increase Octane workers (3 → 8) | ~520ms | ~250 req/sec | 0% | Major performance gain proved worker concurrency was dominant bottleneck |
| Distributed Load Balancing | Application-layer saturation | NGINX + 3 backend servers | ~364ms | ~301 req/sec | ~15% | Throughput improved significantly but exposed database bottleneck |
| Database Connection Pressure | Shared DB resource contention | Increase MySQL `max_connections` (151 → 500) | ~349ms | ~307 req/sec | ~16% | Minor improvement indicated hardware/DB processing became dominant limitation |
| Stable Concurrent Validation | Concurrent transactional consistency | Optimized single-server architecture | ~156ms | ~186 req/sec | 0% | Successfully handled 100 simultaneous users without crashes or data corruption |

---

# Bottleneck Evolution Analysis

The bottleneck progressively migrated through different architectural layers during optimization:

| Stage | Dominant Bottleneck |
|---|---|
| Initial implementation | Race condition and inventory inconsistency |
| After pessimistic locking | Database lock contention |
| After atomic updates | Worker concurrency saturation |
| After increasing workers | Database throughput pressure |
| After load balancing | Shared database and hardware saturation |

This demonstrated that scalability improvements at one layer exposed bottlenecks in deeper system layers, which is expected behavior in distributed systems.

---

# Key Benchmark Comparison

## Before Optimization

| Metric | Result |
|---|---|
| Architecture | Single server, 3 workers |
| Avg Latency | ~2.05s |
| Throughput | ~92 req/sec |
| Concurrent Safety | Unsafe |
| Data Consistency | At risk |

---

## After Optimization

| Metric | Result |
|---|---|
| Architecture | 8 workers + optimized concurrency control |
| Avg Latency | ~156ms |
| Throughput | ~186 req/sec |
| Concurrent Safety | Safe |
| Data Consistency | Maintained |
| Failed Requests | 0% |

---

# Load Balancing Benchmark

A distributed architecture was later implemented using NGINX load balancing across three Laravel Octane servers.

| Metric | Single Server | Load Balanced |
|---|---|
| Avg Latency | ~520ms | ~364ms |
| Throughput | ~250 req/sec | ~301 req/sec |
| Failure Rate | 0% | ~15% |

The benchmark demonstrated that horizontal scaling improved throughput and reduced latency, but increased simultaneous pressure on the shared MySQL database, exposing database and hardware bottlenecks.

---

# Stress Test Validation

A realistic concurrent-user benchmark was executed using 100 simultaneous virtual users (`per-vu-iterations`).

Results:

| Metric | Result |
|---|---|
| Concurrent Users | 100 |
| Avg Response Time | ~156ms |
| p95 Latency | ~279ms |
| Failed Requests | 0% |
| Data Corruption | None |
| Server Crash | None |

This proved the system could safely handle at least 100 concurrent users while maintaining transactional correctness and stable performance.

---

# Final Analysis

The benchmarking process successfully identified multiple bottlenecks across the system architecture, including:

- race conditions
- database locking contention
- worker saturation
- shared database pressure
- hardware resource saturation

Incremental optimizations significantly improved performance, reduced latency, and maintained transactional consistency under concurrent load.

The final architecture demonstrated stable concurrent processing, scalable worker utilization, and successful handling of realistic transactional workloads without crashes or data loss.
