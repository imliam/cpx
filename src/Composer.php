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

    /**
     * Get a list of bin scripts from a package's composer.json file
     *
     * @return string[]
     */
    public static function detectBinFromComposer(string $directory): array
    {
        $composerFile = "{$directory}/composer.json";

        if (file_exists($composerFile)) {
            $composerData = json_decode(file_get_contents($composerFile), true);

            if (isset($composerData['bin'])) {
                return (array) $composerData['bin'];
            }
        }

        return [];
    }

    public static function getCurrentVersion(string $directory): string
    {
        $composerLock = "{$directory}/composer.lock";

        if (file_exists($composerLock)) {
            $lockData = json_decode(file_get_contents($composerLock), true);

            return $lockData['packages'][0]['version'] ?? 'unknown';
        }

        return 'unknown';
    }
}
