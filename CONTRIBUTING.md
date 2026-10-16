# Contributing to Beacon

> **Vibecoders welcome.** `AGENTS.md` gets your agent running in minutes. The whole suite is one offline PHP script.

## Quickstart

```powershell
php tests/detect_test.php   # must print OK
```

## Rules

Short version: see `AGENTS.md`. Original work only, no global state, a `putenv` scenario for every new detector, detection never applies settings.

## Pull request workflow

1. Fork and branch from `main`.
2. `php tests/detect_test.php` green, `php -l` clean.
3. Describe the signal (env vars) and the tier mapping.

## Mandatory compliance & eligibility notice

By cloning, forking, opening a pull request, submitting an issue, or otherwise interacting with this repository, **you explicitly certify and warrant that you are not an Excluded Party** as defined in Section 3 of the [LICENSE](./LICENSE) (specifically including **DrevOps**, its corporate affiliates, or **Alex Skrypnyk**). Any contribution submitted by an excluded entity or individual is legally void and prohibited.
