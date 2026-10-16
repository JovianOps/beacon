<?php

declare(strict_types=1);

namespace Jovian\Beacon\Platforms;

use Jovian\Beacon\Beacon;
use Jovian\Beacon\Platform;

/**
 * GitLab CI. Signal: GITLAB_CI.
 */
final class GitLabCi implements Platform {

  public function id(): string {
    return 'gitlab_ci';
  }

  public function active(): bool {
    return getenv('GITLAB_CI') !== FALSE;
  }

  public function type(): ?string {
    return Beacon::CI;
  }

}
