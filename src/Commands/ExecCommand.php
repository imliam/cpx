<?php

namespace Cpx\Commands;

use Cpx\ClassAliasAutoloader;

class ExecCommand extends Command
{
    public string $path;

    protected bool $shouldFindAutoloader = true;
    protected bool $shouldLoadLaravelBootstrap = true;
    protected bool $shouldAliasClasses = true;
    protected bool $shouldBeVerbose = false;

    public static ClassAliasAutoloader $classAliasAutoloader;

    public function __invoke()
    {
        if (empty($this->console->arguments[0])) {
            $this->error('Please supply the path to a file to execute.');
            return;
        }

        $this->path = realpath($this->console->arguments[0]);

        if (!file_exists($this->path)) {
            $this->error("File does not exist at '{$this->path}'");
            return;
        }

        $this->shouldFindAutoloader = $this->console->getOption('find-autoloader') ?? true;
        $this->shouldLoadLaravelBootstrap = $this->console->getOption('load-laravel-bootstrap') ?? true;
        $this->shouldAliasClasses = $this->console->getOption('alias-classes') ?? true;
        $this->shouldBeVerbose = $this->console->getOption('verbose') ?? false;

        if ($this->shouldFindAutoloader) {
            $this->findAutoloader();
        }

        $this->run();
    }

    public function run(): void
    {
        require $this->path;
    }

    protected function findAutoloader(): void
    {
        $autoloadRootDirectory = dirname($this->path);

        $autoloadFileSuffix = '/vendor/autoload.php';
        $autoloadFile = $autoloadRootDirectory . $autoloadFileSuffix;

        while (!file_exists($autoloadFile)) {
            $autoloadRootDirectory = realpath(dirname($autoloadRootDirectory));
            $autoloadFile = $autoloadRootDirectory . $autoloadFileSuffix;

            if ($autoloadRootDirectory === '/') {
                break;
            }
        }

        if (file_exists($autoloadFile)) {
            ob_start();

            require_once $autoloadFile;

            if ($this->shouldLoadLaravelBootstrap && file_exists($autoloadRootDirectory . '/bootstrap/app.php')) {
                if (!defined('LARAVEL_START')) {
                    define('LARAVEL_START', microtime(true));
                }

                require_once $autoloadRootDirectory . '/bootstrap/app.php';
            }

            ob_clean();

            if ($this->shouldAliasClasses) {
                $this->getClassAliasAutoloader()->addAliases($autoloadRootDirectory);
                spl_autoload_register($this->getClassAliasAutoloader()->aliasClass(...));
            }
        }
    }

    public function getClassAliasAutoloader(): ClassAliasAutoloader
    {
        return static::$classAliasAutoloader ??= new ClassAliasAutoloader($this->shouldBeVerbose);
    }
}
