<?php

namespace RoyGoldman\ComposerReinstall\Command;

use Composer\Command\BaseCommand;
use Composer\Installer;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;

use DrupalComposer\PreservePaths\PluginWrapper;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use RoyGoldman\ComposerReinstall\PathPreserverWrapper;

/**
 * The "reinstall" command class.
 *
 * Downloads scaffold files and generates the autoload.php file.
 */
class ReinstallCommand extends BaseCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    parent::configure();
    $this
      ->setName('reinstall')
      ->setDescription('Reinstall a set of packages.')
      ->addArgument('packages', InputArgument::IS_ARRAY, 'Packages that should be reinstalled, if not provided all packages are.')
      ->addOption('apcu-autoloader', null, InputOption::VALUE_NONE, 'Use APCu to cache found/not-found classes.')
      ->addOption('classmap-authoritative', 'a', InputOption::VALUE_NONE, 'Autoload classes from the classmap only. Implicitly enables `--optimize-autoloader`.')
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Outputs the operations but will not execute anything (implicitly enables --verbose).')
      ->addOption('no-autoloader', null, InputOption::VALUE_NONE, 'Skips autoloader generation.')
      ->addOption('no-dev', null, InputOption::VALUE_NONE, 'Disables installation of require-dev packages.')
      ->addOption('no-progress', null, InputOption::VALUE_NONE, 'Do not output download progress.')
      ->addOption('no-scripts', null, InputOption::VALUE_NONE, 'Skips the execution of all scripts defined in composer.json file.')
      ->addOption('no-suggest', null, InputOption::VALUE_NONE, 'Do not show package suggestions.')
      ->addOption('prefer-source', null, InputOption::VALUE_NONE, 'Forces installation from package sources when possible, including VCS information.')
      ->addOption('prefer-dist', null, InputOption::VALUE_NONE, 'Forces installation from package dist even for dev versions.')
      ->addOption('optimize-autoloader', 'o', InputOption::VALUE_NONE, 'Optimize autoloader during autoloader dump.')
      ->addOption('verbose', 'v|vv|vvv', InputOption::VALUE_NONE, 'Shows more details including new commits pulled in when updating packages.')
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = $this->getIO();
    $package_list = $input->getArgument('packages');

    $composer = $this->getComposer(true, $input->getOption('no-plugins'));

    $composer->getDownloadManager()->setOutputProgress(!$input->getOption('no-progress'));

    $commandEvent = new CommandEvent(PluginEvents::COMMAND, 'reinstall', $input, $output);
    $composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent);

    $config = $composer->getConfig();

    $repo_manager = $composer->getRepositoryManager();

    foreach ($package_list as $package) {
      $package_instance = $repo_manager->findPackage($package, '*');
      if (empty($package_instance)) {
        $io->writeError('<warning>Warning unknown package `' . $package . '`.</warning>');
        return 1;
      }
      else {
        $packages[] = $package_instance;
      }
    }

    $install_manager = $composer->getInstallationManager();

    $paths = [];
    foreach ($packages as $package) {
      $paths[$package->getPrettyName()] = realpath($install_manager->getInstallPath($package));
    }

    $preserver = NULL;
    if (class_exists(PluginWrapper::class)) {
      // If `drupal-composer/preserve-paths` is installed, preserve paths.
      $preserver = new PathPreserverWrapper($composer, $io);
      $preserver->preserve($packages, $paths);
    }

    foreach ($paths as $package_name => $path) {
      $io->write('Preparing `' . $package_name . '` for reinstall.');
      // Remove packages source.
      static::rmdirr($path);
    }

    if ($preserver) {
      // If we preserved paths, restore files.
      $preserver->restore($packages, $paths);
    }

    // Reset composer to do a clean install.
    $this->resetComposer();
    $composer = $this->getComposer();

    $installer = Installer::create($io, $composer);

    list($preferSource, $preferDist) = $this->getPreferredInstallOptions($config, $input);

    $optimize = $input->getOption('optimize-autoloader') || $config->get('optimize-autoloader');
    $authoritative = $input->getOption('classmap-authoritative') || $config->get('classmap-authoritative');
    $apcu = $input->getOption('apcu-autoloader') || $config->get('apcu-autoloader');

    $installer
      ->setDryRun($input->getOption('dry-run'))
      ->setVerbose($input->getOption('verbose'))
      ->setPreferSource($preferSource)
      ->setPreferDist($preferDist)
      ->setDevMode(!$input->getOption('no-dev'))
      ->setDumpAutoloader(!$input->getOption('no-autoloader'))
      ->setRunScripts(!$input->getOption('no-scripts'))
      ->setSkipSuggest($input->getOption('no-suggest'))
      ->setOptimizeAutoloader($optimize)
      ->setClassMapAuthoritative($authoritative)
      ->setApcuAutoloader($apcu)
    ;

    if ($input->getOption('no-plugins')) {
      $installer->disablePlugins();
    }

    return $installer->run();

  }

  /**
   * Recursively remove a given directory.
   *
   * @param string $dir
   *   Directory to remove.
   * @param RoyGoldman\ComposerReinstall\PathPreserverWrapper $preserver
   *   Preserver instance if available.
   *
   * @return bool
   *   Returns TRUE on success or FALSE on failure.
   */
  public static function rmdirr($dir, PathPreserverWrapper $preserver = NULL) {
    if (!file_exists($dir)) {
      return TRUE;
    }
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
      if ($preserver && $preserver->isPreserved($dir)) {
        continue;
      }
      (is_dir("$dir/$file")) ? static::rmdirr("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
  }

}
