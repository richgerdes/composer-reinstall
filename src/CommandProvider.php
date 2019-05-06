<?php

namespace RoyGoldman\ComposerReinstall;

use Composer\Plugin\Capability\CommandProvider as CapabilityCommandProvider;

use RoyGoldman\ComposerReinstall\Command\ReinstallCommand;

/**
 * List of all commands provided by this package.
 */
class CommandProvider implements CapabilityCommandProvider {

  /**
   * {@inheritdoc}
   */
  public function getCommands() {
    return [
      new ReinstallCommand(),
    ];
  }

}
