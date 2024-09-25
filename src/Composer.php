<?php

namespace Cpx;

use Exception;

class Composer
{
    public static function runCommand(string $command, string $directory = null): array
    {
        $output = [];
        $workingDirectory = $directory ? "--working-dir={$directory}" : '';

        exec("composer {$command} --no-interaction --quiet {$workingDirectory}", $output, $resultCode);

        if ($resultCode !== 0) {
            throw new Exception("Composer command failed: {$command}");
        }

        return $output;
    }
}
