<?php

namespace Cpx;

use Cpx\Utils;
use Cpx\Commands\Command;
use InvalidArgumentException;

class Package
{
    protected function __construct(
        public string $vendor,
        public string $name,
        public ?string $version = null,
        public ?Repository $repository = null,
    ) {}

    public static function parse(string $str, $options = null): Package
    {
        if (empty($str)) {
            throw new InvalidArgumentException('A package name must be provided.');
        }

        if (!str_contains($str, '/')) {
            throw new InvalidArgumentException('A package name should be in the format "<vendor>/<package>');
        }

        $parts = explode(':', str_replace('@', ':', $str));
        [$vendor, $name] = explode('/', $parts[0]);
        $version = $parts[1] ?? null;

        if ($version === '') {
            $version = null;
        }

        if (is_array($options) && isset($options['repo'])) {
            $repository = Repository::parse($options['repo']);
            return new Package($vendor, $name, $version, $repository);
        }

        return new Package($vendor, $name, $version);
    }

    public function folder(): string
    {
        return "{$this->vendor}/{$this->name}/{$this->versionName()}";
    }

    public function versionName(): string
    {
        return $this->version ?? 'latest';
    }

    public function fullPackageString(): string
    {
        return "{$this->vendor}/{$this->name}"
            . ($this->version ? ':' . $this->version : '');
    }

    public function delete(): void
    {
        $packageDirectory = cpx_path("{$this->folder()}");
        exec("rm -rf {$packageDirectory}");
    }

    public function runCommand(Console $console, bool $autoUpdate = true): void
    {
        $installDir = $this->installOrUpdatePackage($autoUpdate);

        $binScripts = Composer::detectBinFromComposer("{$installDir}/vendor/{$this->vendor}/{$this->name}");

        if (empty($binScripts)) {
            printColor("Error: No bin command found in {$this}.", "\033[1;31m");
            exit(1);
        }

        $binScripts = Utils::arrayMapAssoc(fn ($key, $value) => [basename($value) => $value], $binScripts);

        if (count($binScripts) > 1) {
            $possibleCommands = array_values(array_unique(array_filter([
                $console->command,
                $console->arguments[0] ?? null,
                str_contains($console->command, '/') ? Package::parse($console->command)->name : null,
            ])));

            foreach ($possibleCommands as $possibleCommand) {
                if (in_array($possibleCommand, $binScripts)) {
                    if ($console->arguments[0] ?? null === $possibleCommand) {
                        unset($console->arguments[0]);
                        $console->arguments = array_values($console->arguments);
                    }
                    $command = $possibleCommand;
                    break;
                } elseif (array_key_exists($possibleCommand, $binScripts)) {
                    if ($console->arguments[0] ?? null === $possibleCommand) {
                        unset($console->arguments[0]);
                        $console->arguments = array_values($console->arguments);
                    }
                    $command = $binScripts[$possibleCommand];
                    break;
                }
            }

            if (!isset($command)) {
                echo Command::BACKGROUND_RED . "   More than 1 bin command found for {$this}: " . join(', ', array_keys($binScripts)) . '   ' . Command::COLOR_RESET . PHP_EOL;
                exit();
            }
        } else {
            $command = $binScripts[array_key_first($binScripts)];
        }

        $binPath = "$installDir/vendor/{$this->vendor}/{$this->name}/$command";

        if (file_exists($binPath)) {
            Metadata::open()->updateLastCheckTime($this)->save();

            // Prepare the command to run
            $cmd = "{$binPath} {$console->getCommandInput()}";

            // Use proc_open for better control of the process and to maintain colors and interactivity
            $descriptors = [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR,
            ];

            printColor("Running {$command} from {$this}");

            $process = proc_open($cmd, $descriptors, $pipes);

            if (is_resource($process)) {
                proc_close($process);
            }
        } else {
            echo "Error: Command $command not found in {$this}.\n";
        }
    }

    public function installOrUpdatePackage(bool $updateCheck = true): string
    {
        $installDir = cpx_path($this->folder());

        if (!is_dir($installDir)) {
            mkdir($installDir, 0755, true);
        }

        if (!is_dir("$installDir/vendor")) {
            printColor("Installing {$this}...");
            file_put_contents("{$installDir}/composer.json", json_encode([
                'name' => "cpx-{$this->vendor}/cpx-{$this->name}",
                'version' => '1.0.0',
                'config' => [
                    'allow-plugins' => true,
                ],
            ], JSON_PRETTY_PRINT));

            Repository::apply($this->repository, $installDir);

            // Composer::runCommand("init --name=cpx-{$package->name} --version=1.0.0 --no-interaction", $installDir);

            if ($this->version === null) {
                Composer::runCommand("require {$this->vendor}/{$this->name} --no-interaction --no-progress", $installDir);
            } else {
                Composer::runCommand("require {$this->vendor}/{$this->name}:{$this->version} --no-interaction --no-progress", $installDir);
            }

            Metadata::open()->updateLastCheckTime($this, 'updated')->save();

            return $installDir;
        }

        $didChangeRepo = Repository::apply($this->repository, $installDir);

        if ($updateCheck && ($didChangeRepo || $this->shouldCheckForUpdates($this))) {
            printColor("Checking for updates for {$this}...");
            $previousVersion = Composer::getCurrentVersion($installDir);
            Repository::apply($this->repository, $installDir);
            Composer::runCommand("update", $installDir);
            $newVersion = Composer::getCurrentVersion($installDir);

            if ($previousVersion !== $newVersion) {
                printColor("{$this} was upgraded from $previousVersion to $newVersion.");
            } else {
                printColor("{$this} is already up-to-date.");
            }

            Metadata::open()->updateLastCheckTime($this, 'updated')->save();
            return $installDir;
        }

        printColor("{$this} is already installed and doesn't need updating.");
        return $installDir;
    }

    function shouldCheckForUpdates(): bool
    {
        $metadata = Metadata::open();
        $packageKey = $this->fullPackageString();

        if (!$metadata->hasPackage($this)) {
            return true;
        }

        $lastCheck = strtotime($metadata->packages[$packageKey]->lastUpdatedAt);

        return (time() - $lastCheck) > 3600; // 1 hour
    }

    public function __toString(): string
    {
        return $this->fullPackageString();
    }
}
