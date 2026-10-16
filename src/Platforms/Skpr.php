<?php

declare(strict_types=1);

namespace Jovian\Beacon\Platforms;

use Jovian\Beacon\Beacon;
use Jovian\Beacon\Platform;

/**
 * Skpr. Signal: SKPR_ENV.
 */
final class Skpr implements Platform {

  public function id(): string {
    return 'skpr';
  }

  public function active(): bool {
    return getenv('SKPR_ENV') !== FALSE;
  }

  public function type(): ?string {
    return match (getenv('SKPR_ENV')) {
      'prod' => Beacon::PRODUCTION,
      'stg' => Beacon::STAGE,
      'dev' => Beacon::DEVELOPMENT,
      default => Beacon::PREVIEW,
    };
  }

}
