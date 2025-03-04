<?php

namespace Cpx\Enums;

enum RepositoryType: string
{
    case Composer = 'composer';
    case Git = 'git';
    case Path = 'path';
}
