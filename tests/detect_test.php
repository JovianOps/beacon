<?php

declare(strict_types=1);

/**
 * @file
 * Prove detection across platforms, stacks, overrides and failures.
 *
 * Pure environment manipulation; no network, no Docker. Each case scrubs the
 * signals it sets. Run with: php tests/detect_test.php
 */

spl_autoload_register(static function (string $class): void {
  if (str_starts_with($class, 'Jovian\\Beacon\\')) {
    require __DIR__ . '/../src/' . str_replace('\\', '/', substr($class, strlen('Jovian\\Beacon\\'))) . '.php';
  }
});

use Jovian\Beacon\Beacon;
use Jovian\Beacon\Platform;
use Jovian\Beacon\Result;
use Jovian\Beacon\Stack;

$failures = [];

$check = static function (string $description, bool $condition) use (&$failures): void {
  if (!$condition) {
    $failures[] = $description;
  }
  putenv('ENVIRONMENT_TYPE');
};

$signals = ['AH_SITE_ENVIRONMENT', 'LAGOON_KUBERNETES', 'LAGOON_ENVIRONMENT_TYPE', 'LAGOON_GIT_BRANCH', 'ENVIRONMENT_PRODUCTION_BRANCH', 'PANTHEON_ENVIRONMENT', 'PLATFORM_APPLICATION', 'PLATFORM_ENVIRONMENT_TYPE', 'SKPR_ENV', 'TUGBOAT_PREVIEW_ID', 'GITHUB_WORKFLOW', 'GITLAB_CI', 'CIRCLECI', 'CI', 'IS_DDEV_PROJECT', 'LANDO_INFO', 'DOCKER', 'ENVIRONMENT_TYPE'];

$scrub = static function () use ($signals): void {
  foreach ($signals as $name) {
    putenv($name);
  }
};

$detect = static function (array $env, array $platforms = [], array $stacks = [], string $fallback = Beacon::DEVELOPMENT): Result {
  foreach ($env as $name => $value) {
    putenv($name . '=' . $value);
  }

  return (new Beacon($platforms, $stacks, $fallback))->detect();
};

// One platform per tier.
$cases = [
  [['AH_SITE_ENVIRONMENT' => 'prod'], Beacon::PRODUCTION, 'acquia'],
  [['AH_SITE_ENVIRONMENT' => 'test'], Beacon::STAGE, 'acquia'],
  [['AH_SITE_ENVIRONMENT' => 'dev'], Beacon::DEVELOPMENT, 'acquia'],
  [['AH_SITE_ENVIRONMENT' => 'ode123'], Beacon::PREVIEW, 'acquia'],
  [['LAGOON_KUBERNETES' => 'true', 'LAGOON_ENVIRONMENT_TYPE' => 'production'], Beacon::PRODUCTION, 'lagoon'],
  [['LAGOON_KUBERNETES' => 'true', 'LAGOON_ENVIRONMENT_TYPE' => 'development', 'LAGOON_GIT_BRANCH' => 'main'], Beacon::STAGE, 'lagoon'],
  [['LAGOON_KUBERNETES' => 'true', 'LAGOON_ENVIRONMENT_TYPE' => 'development', 'LAGOON_GIT_BRANCH' => 'release/1'], Beacon::STAGE, 'lagoon'],
  [['LAGOON_KUBERNETES' => 'true', 'LAGOON_ENVIRONMENT_TYPE' => 'development', 'LAGOON_GIT_BRANCH' => 'develop'], Beacon::DEVELOPMENT, 'lagoon'],
  [['LAGOON_KUBERNETES' => 'true', 'LAGOON_ENVIRONMENT_TYPE' => 'development', 'LAGOON_GIT_BRANCH' => 'feature/x'], Beacon::PREVIEW, 'lagoon'],
  [['LAGOON_KUBERNETES' => 'true', 'LAGOON_ENVIRONMENT_TYPE' => 'development', 'LAGOON_GIT_BRANCH' => 'main', 'ENVIRONMENT_PRODUCTION_BRANCH' => 'main'], Beacon::PRODUCTION, 'lagoon'],
  [['PANTHEON_ENVIRONMENT' => 'live'], Beacon::PRODUCTION, 'pantheon'],
  [['PANTHEON_ENVIRONMENT' => 'test'], Beacon::STAGE, 'pantheon'],
  [['PLATFORM_APPLICATION' => 'app', 'PLATFORM_ENVIRONMENT_TYPE' => 'production'], Beacon::PRODUCTION, 'platformsh'],
  [['SKPR_ENV' => 'stg'], Beacon::STAGE, 'skpr'],
  [['TUGBOAT_PREVIEW_ID' => 'abc'], Beacon::PREVIEW, 'tugboat'],
  [['GITHUB_WORKFLOW' => 'ci'], Beacon::CI, 'github_actions'],
  [['GITLAB_CI' => 'true'], Beacon::CI, 'gitlab_ci'],
  [['CIRCLECI' => 'true'], Beacon::CI, 'circleci'],
];

foreach ($cases as [$env, $type, $platform]) {
  $scrub();
  $result = $detect($env);
  $check(sprintf('%s detects %s.', $platform, $type), $result->type === $type && $result->platform === $platform);
}

// No platform: CI signal means ci, otherwise local.
$scrub();
$check('Generic CI signal means ci.', $detect(['CI' => 'true'])->type === Beacon::CI);
$scrub();
$local = $detect([]);
$check('Empty environment means local on native.', $local->type === Beacon::LOCAL && $local->platform === NULL && $local->stack === 'native');

// Stacks: specific beats generic, container beats native.
$scrub();
$check('DDEV wins over container.', $detect(['IS_DDEV_PROJECT' => '1', 'DOCKER' => '1'])->stack === 'ddev');
$scrub();
$check('Lando wins over container.', $detect(['LANDO_INFO' => '{}', 'DOCKER' => '1'])->stack === 'lando');
$scrub();
$check('Container marker resolves container.', $detect(['DOCKER' => '1'])->stack === 'container');

// Manual override always wins and skips publishing.
$scrub();
putenv('ENVIRONMENT_TYPE=production');
$overridden = (new Beacon())->detect();
$check('ENVIRONMENT_TYPE override wins.', $overridden->type === Beacon::PRODUCTION && $overridden->platform === NULL);
putenv('ENVIRONMENT_TYPE');

// A platform that cannot name a tier falls back safely.
$typeless = new class() implements Platform {
  public function id(): string {
    return 'mystery';
  }

  public function active(): bool {
    return getenv('MYSTERY') !== FALSE;
  }

  public function type(): ?string {
    return NULL;
  }
};

$scrub();
$check('Typeless platform falls back to development.', $detect(['MYSTERY' => '1'], [$typeless])->type === Beacon::DEVELOPMENT);
$scrub();
$check('Custom fallback applies.', $detect(['MYSTERY' => '1'], [$typeless], [], Beacon::STAGE)->type === Beacon::STAGE);

// Custom stacks slot before the generic fallbacks.
$custom_stack = new class() implements Stack {
  public function id(): string {
    return 'custom';
  }

  public function active(): bool {
    return getenv('CUSTOM_STACK') !== FALSE;
  }
};

$scrub();
$check('Custom stack wins over container.', $detect(['CUSTOM_STACK' => '1', 'DOCKER' => '1'], [], [$custom_stack])->stack === 'custom');

// Two owners is a misconfiguration, not a guess.
$scrub();
putenv('GITHUB_WORKFLOW=ci');
putenv('CIRCLECI=true');

try {
  (new Beacon())->detect();
  $check('Two active platforms throw.', FALSE);
}
catch (\RuntimeException) {
  $check('Two active platforms throw.', TRUE);
}

$scrub();

if ($failures !== []) {
  echo "FAIL\n";

  foreach ($failures as $failure) {
    echo ' - ' . $failure . PHP_EOL;
  }

  exit(1);
}

echo "OK: detection holds across platforms, stacks and failures.\n";
