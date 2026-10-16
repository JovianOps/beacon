<?php

declare(strict_types=1);

namespace Jovian\Beacon\Stacks;

use Jovian\Beacon\Stack;

/**
 * Generic container. Probe order: explicit markers, then runtime files.
 */
final class Container implements Stack {

  public function id(): string {
    return 'container';
  }

  public function active(): bool {
    if (getenv('DOCKER') !== FALSE || getenv('container') !== FALSE) {
      return TRUE;
    }

    if (file_exists('/.dockerenv') || file_exists('/.dockerinit')) {
      return TRUE;
    }

    $cgroup = is_readable('/proc/1/cgroup') ? ((string) @file_get_contents('/proc/1/cgroup')) : '';

    return str_contains($cgroup, 'docker') || str_contains($cgroup, 'kubepods');
  }

}
