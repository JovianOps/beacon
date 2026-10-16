<?php

declare(strict_types=1);

namespace Jovian\Beacon\Platforms;

use Jovian\Beacon\Beacon;
use Jovian\Beacon\Platform;

/**
 * Lagoon. Signal: LAGOON_KUBERNETES.
 */
final class Lagoon implements Platform {

  public function id(): string {
    return 'lagoon';
  }

  public function active(): bool {
    return getenv('LAGOON_KUBERNETES') !== FALSE;
  }

  public function type(): ?string {
    $tier = getenv('LAGOON_ENVIRONMENT_TYPE');

    if ($tier === 'production') {
      return Beacon::PRODUCTION;
    }

    if ($tier !== 'development') {
      return Beacon::PREVIEW;
    }

    $branch = (string) getenv('LAGOON_GIT_BRANCH');
    $prod_branch = (string) getenv('ENVIRONMENT_PRODUCTION_BRANCH');

    if ($branch !== '' && $branch === $prod_branch) {
      return Beacon::PRODUCTION;
    }

    if ($branch === 'main' || $branch === 'master' || str_starts_with($branch, 'release/') || str_starts_with($branch, 'hotfix/')) {
      return Beacon::STAGE;
    }

    return $branch === 'develop' ? Beacon::DEVELOPMENT : Beacon::PREVIEW;
  }

}
