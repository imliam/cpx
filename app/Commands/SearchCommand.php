<?php

namespace App\Commands;

use App\Support\Packagist;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class SearchCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'search {package}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Search for the most popular packages by a given name';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $packages = Packagist::searchPackages($this->argument('package'));

        if (empty($packages)) {
            $this->info('No packages with the name "' . $this->argument('package') . '" were found');
        }

        $this->title('Available packages:');

        foreach ($packages as $package) {
            $this->info($package->name);
        }
    }
}
