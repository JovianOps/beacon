<?php

declare(strict_types=1);

namespace Jovian\Beacon\Stacks;

use Jovian\Beacon\Stack;

/**
 * DDEV container. Signal: IS_DDEV_PROJECT.
 */
final class Ddev implements Stack {

  public function id(): string {
    return 'ddev';
  }

  public function active(): bool {
    return getenv('IS_DDEV_PROJECT') !== FALSE;
  }

}
