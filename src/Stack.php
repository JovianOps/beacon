<?php

declare(strict_types=1);

namespace Jovian\Beacon;

/**
 * The substrate the code runs on. Exactly one stack always matches.
 */
interface Stack {

  /**
   * Machine ID, unique across registered stacks.
   */
  public function id(): string;

  /**
   * Whether this stack detects the current environment.
   */
  public function active(): bool;

}
