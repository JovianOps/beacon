<?php

declare(strict_types=1);

namespace Jovian\Beacon\Platforms;

use Jovian\Beacon\Beacon;
use Jovian\Beacon\Platform;

/**
 * Platform.sh / Upsun. Signal: PLATFORM_APPLICATION.
 */
final class PlatformSh implements Platform {

  public function id(): string {
    return 'platformsh';
  }

  public function active(): bool {
    return getenv('PLATFORM_APPLICATION') !== FALSE;
  }

  public function type(): ?string {
    return match (getenv('PLATFORM_ENVIRONMENT_TYPE')) {
      'production' => Beacon::PRODUCTION,
      'staging' => Beacon::STAGE,
      'development' => Beacon::DEVELOPMENT,
      default => Beacon::PREVIEW,
    };
  }

}
