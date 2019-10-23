<?php

namespace App\Support;

use Exception;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class Packagist
{
    public static function getPackage(string $packageName)
    {
        $url = "https://packagist.org/packages/{$packageName}.json";

        $package = json_decode(file_get_contents($url));

        if ($package === null || (isset($package->message) && $package->message === 'Package not found')) {
            throw new InvalidArgumentException("The package \"{$packageName}\" was not found.", 404);
        }

        if (!isset($package->package)) {
            throw new Exception('An unknown error occurred retrieving the package.');
        }

        return $package->package;
    }

    public static function searchPackages(string $searchTerm)
    {
        $searchTerm = urlencode($searchTerm);

        $url = "https://packagist.org/search.json?q={$searchTerm}";

        $packages = json_decode(file_get_contents($url));

        if (! isset($packages->results)) {
            return [];
        }

        return $packages->results;
    }

    public static function getPackagesByVendor(string $vendor): array
    {
        $vendor = urlencode($vendor);

        $url = "https://packagist.org/packages/list.json?vendor={$vendor}";

        $packages = json_decode(file_get_contents($url));

        if (! isset($packages->packageNames)) {
            return [];
        }

        return $packages->packageNames;
    }
}
