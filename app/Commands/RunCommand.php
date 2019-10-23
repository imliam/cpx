<?php

namespace App\Commands;

use App\Support\Packagist;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use stdClass;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class RunCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'run {package} {commandName?} {parameters?*}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Command description';

    /** @var string */
    protected $vendor;

    /** @var string */
    protected $packageName;

    /** @var string|null */
    protected $version = null;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setPackageDetails();

        try {
            $package = Packagist::getPackage("{$this->vendor}/{$this->packageName}");
            $version = $this->getPackageVersion($package, $this->version);
            $this->installPackage();
            $this->executeCommand();
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return;
        }
    }

    private function setPackageDetails(): void
    {
        $this->vendor = explode('/', $this->argument('package'), 2)[0] ?? null;
        $this->packageName = explode('/', explode('@', $this->argument('package'), 2)[0], 2)[1];
        $this->version = explode('@', $this->argument('package'), 2)[1] ?? null;
    }

    private function getPackageVersion(stdClass $package, string $version = null)
    {
        if ($version === null) {
            return null;
        }

        foreach ($package->versions as $versionDetails) {
            $matchingVersions = [
                $version,
                'v' . $version,
            ];

            if (in_array($versionDetails->version, $matchingVersions)) {
                return $versionDetails;
            }
        }

        throw new InvalidArgumentException('Version not found.');
    }

    private function getInstallationPath(string $path = ''): string
    {
        return base_path('installations' . DIRECTORY_SEPARATOR . $this->vendor)
            . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }

    private function installPackage()
    {
        if (file_exists($this->getInstallationPath($this->packageName))) {
            $this->info("{$this->vendor}/{$this->packageName} has already been installed.");

            return;
        }

        $this->info("Installing {$this->vendor}/{$this->packageName}...");

        File::makeDirectory($this->getInstallationPath());

        $process = new Process(
            ['composer', 'create-project', '--prefer-dist', '--quiet', '--no-interaction', $this->argument('package'), $this->packageName],
            $this->getInstallationPath()
        );

        $this->executeProcess($process);
    }

    private function executeCommand()
    {
        $composerPath = $this->getInstallationPath($this->packageName . DIRECTORY_SEPARATOR . 'composer.json');
        $composerFile = json_decode(File::get($composerPath));
        $commandName = $this->argument('commandName') ?? '';

        if (empty($commandName)) {
            if (isset($composerFile->scripts) && count((array) $composerFile->scripts) === 1) {
                return $this->runScript(reset($composerFile->scripts));
            }

            if (isset($composerFile->bin) && count($composerFile->bin) === 1) {
                return $this->runBin($composerFile->bin[0]);
            }
        }

        if (isset($composerFile->scripts->{$commandName})) {
            return $this->runScript($commandName);
        }

        if (isset($composerFile->bin)) {
            foreach ($composerFile->bin as $binCommand) {
                $fileName = explode('/', $binCommand);

                if (end($fileName) === $commandName || $binCommand === $commandName) {
                    return $this->runBin($binCommand);
                }
            }
        }

        if (empty($commandName)) {
            throw new InvalidArgumentException("No implicit command for {$this->vendor}/{$this->packageName}");
        }

        throw new InvalidArgumentException("Command \"{$commandName}\" not found for {$this->vendor}/{$this->packageName}");
    }

    private function runScript(string $commandName)
    {
        $this->info('Found script command...');

        $command = $this->argument('parameters');
        array_unshift($command, 'composer', '--working-dir', getcwd());

        $process = new Process($command);

        $this->executeProcess($process);
    }

    private function runBin(string $binCommand)
    {
        $scriptPath = $this->getInstallationPath($this->packageName . DIRECTORY_SEPARATOR . $binCommand);

        $command = $this->argument('parameters');
        array_unshift($command, $scriptPath);

        $process = new Process($command);

        $this->executeProcess($process);
    }

    private function executeProcess(Process $process)
    {
        try {
            $process->start();

            foreach ($process as $type => $data) {
                $this->info($data);
            }
        } catch (ProcessFailedException $e) {
            $this->error($e->getMessage());
        }
    }
}
