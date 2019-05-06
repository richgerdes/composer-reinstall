<?php

namespace RoyGoldman\ComposerReinstall;

use DrupalComposer\PreservePaths\PluginWrapper;
use DrupalComposer\PreservePaths\PathPreserver;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Filesystem;

/**
 * Wrap DrupalComposer\PreservePaths\PluginWrapper to reuse functionality.
 */
class PathPreserverWrapper extends PluginWrapper
{

    /**
     * @var \Composer\IO\IOInterface
     */
    protected $io;

    /**
     * @var \Composer\Composer
     */
    protected $composer;

    /**
     * @var \Composer\Util\Filesystem
     */
    protected $filesystem;

    /**
     * @var \DrupalComposer\PreservePaths\PathPreserver[string]
     */
    protected $preservers;

    /**
     * {@inheritdoc}
     */
    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->io = $io;
        $this->composer = $composer;
        $this->filesystem = new Filesystem();
    }

    /**
     * Pre Package event behaviour for backing up preserved paths.
     *
     * @param string[] $packages
     * @param string[] $paths
     */
    public function preserve(array $packages, array $paths)
    {
        $preserver = new PathPreserver(
            $paths,
            $this->getPreservePaths(),
            $this->composer->getConfig()->get('cache-dir'),
            $this->filesystem,
            $this->io
        );

        // Store preserver for reuse in post package.
        $this->preservers[$this->getUniqueNameFromPackages($packages)] = $preserver;

        $preserver->preserve();
    }

    /**
     * Pre Package event behaviour for backing up preserved paths.
     *
     * @param string[] $packages
     * @param string[] $paths
     */
    public function restore(array $packages, array $paths)
    {
        $key = $this->getUniqueNameFromPackages($packages);
        if ($this->preservers[$key]) {
            $this->preservers[$key]->rollback();
            unset($this->preservers[$key]);
        }
    }
    
    /**
     * Check if a given path is preserved.
     *
     * @param string $path
     * @return bool
     */
    public function isPreserved($path)
    {
      $preserved_paths = $this->getPreservePaths();
      return in_array($path, $preserve_paths);
    }
}
