<?php

namespace Cpx;

class Utils
{
    public static function arrayMapAssoc(callable $f, array $a): array
    {
        return array_merge(...array_map($f, array_keys($a), $a));
    }
}
