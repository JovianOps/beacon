<?php

declare(strict_types=1);

namespace Jovian\Beacon;

/**
 * The outcome of one detection run. Immutable, no global state.
 */
final class Result {

  /**
   * @param string $type
   *   One of Beacon::LOCAL/CI/DEVELOPMENT/PREVIEW/STAGE/PRODUCTION.
   * @param string|null $platform
   *   ID of the owning platform, or NULL when none matched.
   * @param string $stack
   *   ID of the active stack; always set.
   */
  public function __construct(
    public readonly string $type,
    public readonly ?string $platform,
    public readonly string $stack,
  ) {}

}
