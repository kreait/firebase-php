<?php

declare(strict_types=1);

namespace Kreait\Firebase\Exception\Database;

use Kreait\Firebase\Exception\DatabaseException;
use Kreait\Firebase\Exception\RuntimeException;

final class PreconditionFailed extends RuntimeException implements DatabaseException
{
}
