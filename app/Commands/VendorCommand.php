<?php

namespace App\Commands;

use App\Support\Packagist;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class VendorCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'vendor {vendor}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Search for packages by a given vendor';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $packages = Packagist::getPackagesByVendor($this->argument('vendor'));

        if (empty($packages)) {
            $this->info('No packages from "' . $this->argument('vendor') . '" were found');
        }

        $this->title('Available packages:');

        foreach ($packages as $package) {
            $this->info($package);
        }
    }
}
