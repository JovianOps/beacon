<?php

declare(strict_types=1);

namespace Jovian\Beacon\Platforms;

use Jovian\Beacon\Beacon;
use Jovian\Beacon\Platform;

/**
 * Acquia Cloud. Signal: AH_SITE_ENVIRONMENT.
 */
final class Acquia implements Platform {

  public function id(): string {
    return 'acquia';
  }

  public function active(): bool {
    return getenv('AH_SITE_ENVIRONMENT') !== FALSE;
  }

  public function type(): ?string {
    return match (getenv('AH_SITE_ENVIRONMENT')) {
      'prod' => Beacon::PRODUCTION,
      'test', 'stage' => Beacon::STAGE,
      'dev' => Beacon::DEVELOPMENT,
      default => Beacon::PREVIEW,
    };
  }

}
