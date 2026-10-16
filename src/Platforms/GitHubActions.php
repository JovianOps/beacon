<?php

declare(strict_types=1);

namespace Jovian\Beacon\Platforms;

use Jovian\Beacon\Beacon;
use Jovian\Beacon\Platform;

/**
 * GitHub Actions. Signal: GITHUB_WORKFLOW.
 */
final class GitHubActions implements Platform {

  public function id(): string {
    return 'github_actions';
  }

  public function active(): bool {
    return getenv('GITHUB_WORKFLOW') !== FALSE;
  }

  public function type(): ?string {
    return Beacon::CI;
  }

}
