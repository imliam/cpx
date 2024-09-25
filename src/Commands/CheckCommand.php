<?php

namespace Cpx\Commands;

use Cpx\Console;
use Cpx\Exceptions\ConsoleException;

class CheckCommand extends Command
{
    public function __invoke()
    {
        if (file_exists('vendor/bin/phpstan')) {
            $command = 'vendor/bin/phpstan analyse';

            return Console::parse($command)->exec();
        }

        if (file_exists('vendor/bin/psalm')) {
            $command = 'vendor/bin/psalm';

            return Console::parse($command)->exec();
        }

        if (file_exists('vendor/bin/phan')) {
            $command = 'vendor/bin/phan';

            return Console::parse($command)->exec();
        }

        throw new ConsoleException('No static analyzers found in the project.');
    }
}
