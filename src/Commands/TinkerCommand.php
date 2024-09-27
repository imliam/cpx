<?php

namespace Cpx\Commands;

use Cpx\Console;
use Cpx\Package;
use Cpx\Commands\Command;

class TinkerCommand extends Command
{
    public function __invoke()
    {
        $psyshConfig = realpath(__DIR__ . '/../../files/psysh-config.php');

        return runCommand(Package::parse('psy/psysh'), Console::parse("psysh --config {$psyshConfig}"));
    }
}
