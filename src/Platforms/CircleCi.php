<?php

declare(strict_types=1);

namespace Jovian\Beacon\Platforms;

use Jovian\Beacon\Beacon;
use Jovian\Beacon\Platform;

/**
 * CircleCI. Signal: CIRCLECI.
 */
final class CircleCi implements Platform {

  public function id(): string {
    return 'circleci';
  }

  public function active(): bool {
    return getenv('CIRCLECI') !== FALSE;
  }

  public function type(): ?string {
    return Beacon::CI;
  }

}
