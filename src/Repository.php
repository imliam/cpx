<?php

namespace Cpx;

use Cpx\Enums\RepositoryType;

class Repository
{
    protected function __construct(
        public RepositoryType $type,
        public ?string $url,
    ) {}

    public static function parse(string $str): ?static
    {

        // Check for local path in the package name
        $paths = ['file:', 'path:'];
        foreach ($paths as $path) {
            if (strpos($str, $path) === 0) {

                $value = explode($path, $str)[1];
                $value = str_replace('///', '/', $value);
                $value = str_replace('//', '/', $value);

                return new Repository(RepositoryType::Path, $value);
            }
        }

        // Git
        $gits = ['git+https://', 'git+http://', 'ssh://', 'git+ssh://'];
        foreach ($gits as $git) {
            if (strpos($str, $git) === 0) {

                $value = str_replace('git+', '', $str);

                return new Repository(RepositoryType::Git, $value);
            }
        }

        $composers = ['https://', 'http://'];
        foreach ($composers as $composer) {
            if (strpos($str, $composer) === 0) {
                return new Repository(RepositoryType::Composer, $str);
            }
        }

        return null;
    }

    public function make(): ?array
    {
        switch ($this->type) {
            case RepositoryType::Composer:
                return [
                    'type' => 'composer',
                    'url' => $this->url,
                ];
            case RepositoryType::Git:
                return [
                    'type' => 'git',
                    'url' => $this->url,
                ];
            case RepositoryType::Path:
                return [
                    'type' => 'path',
                    'url' => $this->url,
                ];
        }

        return null;
    }

    public static function apply(?Repository $repo, string $installDir): bool
    {
        $original = file_get_contents("{$installDir}/composer.json");
        $json = json_decode($original, true);

        if (!isset($json['repositories']) || count($json['repositories'] ?? []) == 0) {
            if ($repo === null) {
                unset($json['repositories']);
            } else {
                $json['repositories'] = [$repo->make()];
            }
        } elseif (count($json['repositories']) > 0) {
            $json['repositories'] = [$repo->make()];
        }

        $final = json_encode($json, JSON_PRETTY_PRINT);
        file_put_contents("{$installDir}/composer.json", $final);
        return $final !== $original;
    }
}
