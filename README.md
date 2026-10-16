# Beacon

Zero-dependency PHP environment detection: which platform, which stack, which tier. No statics, no cache, no context machinery — one `detect()` call, one immutable answer.

```php
$result = (new \Jovian\Beacon\Beacon())->detect();

if ($result->type === \Jovian\Beacon\Beacon::PRODUCTION) {
  // Production settings.
}
// $result->platform: acquia|lagoon|pantheon|platformsh|skpr|tugboat|
//   github_actions|gitlab_ci|circleci|null
// $result->stack: ddev|lando|container|native (+ your customs)
```

A pre-set `ENVIRONMENT_TYPE` always wins (manual override). Otherwise the owning platform names the tier; a platform that cannot falls back to `development` — never local, never production on a guess. Two platforms claiming one environment throws instead of guessing.

## Why another detector

`drevops/environment-detector` is the reference: ring model, 9 platforms, 4 stacks, Drupal contextualization, static caches, snapshot-heavy tests. Correct and heavy. Beacon keeps the rings and the signal table, and drops the rest — all original code:

| environment-detector | Beacon |
| :--- | :--- |
| Static registry + caches + `reset()` | Instance per call, fresh reads, nothing stale |
| Platform/stack/context rings + contextualizers | Platform/stack rings only — detection, not application |
| Drupal context baked in | Framework-free; your app owns its settings |
| PHPUnit + fixtures per detector | One plain-PHP script, `putenv` scenarios, runs anywhere |

## Verification status (honest)

- ✅ `php tests/detect_test.php` → `OK` — every platform tier, the Lagoon branch ladder, stack specificity, override, fallbacks, custom additions, and the two-owner throw. Runs on any PHP, no extensions, no network.
- ✅ All PHP lint-clean.
