# Composer Reinstall

This package provides a basic reinstall command for composer.

## About

When using the [Composer Package Manager](https://getcomposer.org/), you may
find the need to reinstall a given package, either after making changes, or
because the package's installation was otherwise damaged. Composer [doesn't
provide](https://github.com/composer/composer/issues/3112) this command itself,
as such, there isn't a way to handle this process. The package provides a
reinstall command for composer.

### How it works

The reinstall command leverages composers installation mechinism, in order to
ensure that the package gets installed correctly. In order to force composer to
reinstall a package, you need to remove the package from the system, and then
run `composer install`, to download any missing dependencies. With the new
command, the package(s) are removed from the filesystem and then the installer
is used to redownload the packages.

## Installation

Either add this package to the local or global composer install. To add the
package to your project run the following command.

```
composer require roygoldman/composer-reinstall
```

## Usage

Once the package is installed, you should run the following command to reinstall
a given package or set of packages.

```
composer reinstall vendor/package [vendor/package2 ...]
```

### Options

The following options are available for the `reinstall` command. These options
effect how composer handles package installation.

```
      --apcu-autoloader          Use APCu to cache found/not-found classes.
  -a, --classmap-authoritative   Autoload classes from the classmap only. Implicitly enables `--optimize-autoloader`.
      --dry-run                  Outputs the operations but will not execute anything (implicitly enables --verbose).
      --no-autoloader            Skips autoloader generation.
      --no-dev                   Disables installation of require-dev packages.
      --no-progress              Do not output download progress.
      --no-scripts               Skips the execution of all scripts defined in composer.json file.
      --no-suggest               Do not show package suggestions.
      --prefer-source            Forces installation from package sources when possible, including VCS information.
      --prefer-dist              Forces installation from package dist even for dev versions.
  -o, --optimize-autoloader      Optimize autoloader during autoloader dump.
  -h, --help                     Display this help message
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi                     Force ANSI output
      --no-ansi                  Disable ANSI output
  -n, --no-interaction           Do not ask any interactive question
      --profile                  Display timing and memory usage information
      --no-plugins               Whether to disable plugins.
  -d, --working-dir=WORKING-DIR  If specified, use the given directory as working directory.
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```
