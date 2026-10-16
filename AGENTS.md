# AGENTS.md — vibecoding on `jovian/beacon`

Zero-dependency environment detection. One call, one answer, no state.

## Run everything

```powershell
php tests/detect_test.php   # must print OK
php -l src/Beacon.php       # every PHP file, every time
```

## Rules

1. `declare(strict_types=1);` in every PHP file.
2. **Original work only.** Signals (env var names) are facts; every line of code is ours.
3. No global state. No static caches. A new detector = one tiny class + one `putenv` scenario in `tests/detect_test.php`.
4. Detection only — never apply settings, never touch the app. Tiers stay exactly: local, ci, development, preview, stage, production.
