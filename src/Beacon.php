<?php

declare(strict_types=1);

namespace Jovian\Beacon;

/**
 * Detect the environment from signals. No state, no cache, no statics.
 *
 * Every detect() call reads the environment fresh, so tests and long-running
 * processes never see a stale answer. The one deliberate state change in the
 * whole library: a detected type is published to ENVIRONMENT_TYPE, and a
 * pre-set ENVIRONMENT_TYPE always wins (manual override for debugging).
 */
final class Beacon {

  public const LOCAL = 'local';
  public const CI = 'ci';
  public const DEVELOPMENT = 'development';
  public const PREVIEW = 'preview';
  public const STAGE = 'stage';
  public const PRODUCTION = 'production';

  /**
   * Platform classes, checked in order. At most one may match.
   *
   * @var list<class-string<Platform>>
   */
  private const PLATFORMS = [
    Platforms\Acquia::class,
    Platforms\Lagoon::class,
    Platforms\Pantheon::class,
    Platforms\PlatformSh::class,
    Platforms\Skpr::class,
    Platforms\Tugboat::class,
    Platforms\GitHubActions::class,
    Platforms\GitLabCi::class,
    Platforms\CircleCi::class,
  ];

  /**
   * Stack classes, most specific first. Exactly one always matches.
   *
   * @var list<class-string<Stack>>
   */
  private const STACKS = [
    Stacks\Ddev::class,
    Stacks\Lando::class,
    Stacks\Container::class,
    Stacks\Native::class,
  ];

  /**
   * @param list<Platform|class-string<Platform>> $platforms
   *   Extra platforms, checked after the built-ins.
   * @param list<Stack|class-string<Stack>> $stacks
   *   Extra stacks, checked before the generic fallbacks.
   * @param string $fallback
   *   Tier used when the owning platform cannot name one. Defaults to
   *   development: on misconfiguration, neither local nor production settings
   *   should apply.
   */
  public function __construct(
    private readonly array $platforms = [],
    private readonly array $stacks = [],
    private readonly string $fallback = self::DEVELOPMENT,
  ) {}

  /**
   * Run detection now.
   */
  public function detect(): Result {
    $override = getenv('ENVIRONMENT_TYPE');

    if (is_string($override) && $override !== '') {
      return new Result($override, NULL, $this->stackId());
    }

    $platform = $this->platformId();
    $type = $platform === NULL
      ? (getenv('CI') !== FALSE ? self::CI : self::LOCAL)
      : ($this->platformType($platform) ?? $this->fallback);

    putenv('ENVIRONMENT_TYPE=' . $type);

    return new Result($type, $platform, $this->stackId());
  }

  /**
   * ID of the single owning platform, or NULL. Two owners is a
   * misconfiguration and throws instead of guessing.
   */
  private function platformId(): ?string {
    $found = NULL;

    foreach ([...self::PLATFORMS, ...$this->platforms] as $class) {
      $platform = is_string($class) ? new $class() : $class;

      if (!$platform instanceof Platform) {
        throw new \InvalidArgumentException('Platforms must implement ' . Platform::class);
      }

      if ($platform->active()) {
        if ($found !== NULL) {
          throw new \RuntimeException(sprintf('Two platforms claim this environment: %s and %s.', $found, $platform->id()));
        }

        $found = $platform->id();
      }
    }

    return $found;
  }

  /**
   * Tier named by the owning platform.
   */
  private function platformType(string $id): ?string {
    foreach ([...self::PLATFORMS, ...$this->platforms] as $class) {
      $platform = is_string($class) ? new $class() : $class;

      if ($platform->id() === $id) {
        return $platform->type();
      }
    }

    return NULL;
  }

  /**
   * ID of the active stack. Native always matches, so this never fails.
   */
  private function stackId(): string {
    $specific = [...self::STACKS];
    array_splice($specific, -2, 0, $this->stacks);

    foreach ($specific as $class) {
      $stack = is_string($class) ? new $class() : $class;

      if (!$stack instanceof Stack) {
        throw new \InvalidArgumentException('Stacks must implement ' . Stack::class);
      }

      if ($stack->active()) {
        return $stack->id();
      }
    }

    return 'native';
  }

}
