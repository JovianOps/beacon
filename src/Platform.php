<?php

declare(strict_types=1);

namespace Jovian\Beacon;

/**
 * A hosting or CI platform that can own the environment type.
 */
interface Platform {

  /**
   * Machine ID, unique across registered platforms.
   */
  public function id(): string;

  /**
   * Whether this platform detects the current environment.
   */
  public function active(): bool;

  /**
   * The environment tier, or NULL when the platform cannot name one.
   */
  public function type(): ?string;

}
