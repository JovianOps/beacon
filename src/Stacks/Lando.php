<?php

declare(strict_types=1);

namespace Jovian\Beacon\Stacks;

use Jovian\Beacon\Stack;

/**
 * Lando container. Signal: LANDO_INFO.
 */
final class Lando implements Stack {

  public function id(): string {
    return 'lando';
  }

  public function active(): bool {
    return getenv('LANDO_INFO') !== FALSE;
  }

}
