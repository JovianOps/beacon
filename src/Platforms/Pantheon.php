<?php

declare(strict_types=1);

namespace Jovian\Beacon\Platforms;

use Jovian\Beacon\Beacon;
use Jovian\Beacon\Platform;

/**
 * Pantheon. Signal: PANTHEON_ENVIRONMENT.
 */
final class Pantheon implements Platform {

  public function id(): string {
    return 'pantheon';
  }

  public function active(): bool {
    return getenv('PANTHEON_ENVIRONMENT') !== FALSE;
  }

  public function type(): ?string {
    return match (getenv('PANTHEON_ENVIRONMENT')) {
      'live' => Beacon::PRODUCTION,
      'test' => Beacon::STAGE,
      'dev' => Beacon::DEVELOPMENT,
      default => Beacon::PREVIEW,
    };
  }

}
