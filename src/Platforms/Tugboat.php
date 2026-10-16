<?php

declare(strict_types=1);

namespace Jovian\Beacon\Platforms;

use Jovian\Beacon\Beacon;
use Jovian\Beacon\Platform;

/**
 * Tugboat previews. Signal: TUGBOAT_PREVIEW_ID.
 */
final class Tugboat implements Platform {

  public function id(): string {
    return 'tugboat';
  }

  public function active(): bool {
    return getenv('TUGBOAT_PREVIEW_ID') !== FALSE;
  }

  public function type(): ?string {
    return Beacon::PREVIEW;
  }

}
