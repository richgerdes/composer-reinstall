<?php

namespace RoyGoldman\ComposerReinstall;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;

/**
 * Composer plugin to provide a `reinstall` command.
 */
class Plugin implements PluginInterface, Capable {

  /**
   * {@inheritdoc}
   */
  public function getCapabilities() {
    return [
      'Composer\Plugin\Capability\CommandProvider' => 'RoyGoldman\ComposerReinstall\CommandProvider',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io) {
    // Do nothing.
  }

}
