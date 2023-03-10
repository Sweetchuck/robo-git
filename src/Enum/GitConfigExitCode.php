<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\Git\Enum;

enum GitConfigExitCode: int
{
    case Ok = 0;

    case NameInvalid = 1;

    case NameMissing = 2;

    case ConfigFileInvalid = 3;

    case ConfigFileIoError = 4;

    case NameNotExists = 5;

    case InvalidRegexp = 6;
}
