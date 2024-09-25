<?php

namespace Cpx\Commands;

use Cpx\Console;
use Cpx\Exceptions\ConsoleException;

class FormatCommand extends Command
{
    public function __invoke()
    {
        $directory = $this->console->arguments[0] ?? '.';

        if (file_exists('vendor/bin/pint')) {
            $command = "vendor/bin/pint {$directory}";

            if ($this->console->hasOption('dry-run')) {
                $command .= ' --test';
            }

            return Console::parse($command)->exec();
        }

        if (file_exists('vendor/bin/php-cs-fixer')) {
            $command = "vendor/bin/php-cs-fixer fix {$directory}";

            if ($this->console->hasOption('dry-run')) {
                $command .= ' --dry-run';
            }

            $command .= ' --allow-risky=yes';

            return Console::parse($command)->exec();
        }

        if (file_exists('vendor/bin/phpcbf')) {
            $command = "vendor/bin/phpcbf {$directory}";

            return Console::parse($command)->exec();
        }

        throw new ConsoleException('No code formatters found in the project.');
    }
}
