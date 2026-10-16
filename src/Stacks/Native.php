<?php

declare(strict_types=1);

namespace Jovian\Beacon\Stacks;

use Jovian\Beacon\Stack;

/**
 * Bare metal. Always matches, so it stays last and the stack resolves.
 */
final class Native implements Stack {

  public function id(): string {
    return 'native';
  }

  public function active(): bool {
    return TRUE;
  }

}
