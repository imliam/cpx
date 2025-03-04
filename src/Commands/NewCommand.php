<?php

declare(strict_types=1);

namespace Cpx\Commands;

use Cpx\Package;

class NewCommand extends Command
{
    public function __invoke()
    {
        $console = $this->console;

        if (count($console->arguments) < 1) {
            throw new \Exception("No package name provided.");
        }
        if (count($console->arguments) < 2) {
            throw new \Exception("No directory provided.");
        }

        $str = $console->arguments[0];
        if (!str_contains($str, '/')) {
            throw new \Exception("Invalid package name: {$str}");
        }

        $package = Package::parse($str, $console->options);

        $installDir = $package->installOrUpdatePackage();

        $config = [
            "type" => "path",
            "url" => "{$installDir}/vendor/{$package->vendor}/{$package->name}",
            "options" => [
                "symlink" => false
            ]
        ];

        $projectDir = getcwd() . '/' . $console->arguments[1];

        $command = "composer create-project $package->vendor/$package->name --stability=dev --repository='" . json_encode($config) . "' $projectDir";
        foreach ($console->options as $key => $value) {
            if ($key === 'repo') {
                continue;
            }
            $command .= " --$key=$value";
        }

        $descriptorspec = [STDIN, STDOUT, STDOUT];
        $proc = proc_open($command, $descriptorspec, $pipes);
        proc_close($proc);
    }
}
