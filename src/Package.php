<?php

namespace Cpx;

use InvalidArgumentException;

class Package
{
    protected function __construct(
        public string $vendor,
        public string $name,
        public ?string $version = null,
    ) {}

    public static function parse(string $str): Package
    {
        if (empty($str)) {
            throw new InvalidArgumentException('A package name must be provided.');
        }

        if (!str_contains($str, '/')) {
            throw new InvalidArgumentException('A package name should be in the format "<vendor>/<package>');
        }

        $parts = explode(':', str_replace('@', ':', $str));
        [$vendor, $name] = explode('/', $parts[0]);
        $version = $parts[1] ?? null;

        if ($version === '') {
            $version = null;
        }

        return new Package($vendor, $name, $version);
    }

    public function folder(): string
    {
        return "{$this->vendor}/{$this->name}/{$this->versionName()}";
    }

    public function versionName(): string
    {
        return $this->version ?? 'latest';
    }

    public function fullPackageString(): string
    {
        return "{$this->vendor}/{$this->name}"
            . ($this->version ? ':' . $this->version : '');
    }

    public function delete(): void
    {
        $packageDirectory = cpx_dir("{$this->folder()}");
        exec("rm -rf {$packageDirectory}");
    }

    public function __toString(): string
    {
        return $this->fullPackageString();
    }
}
