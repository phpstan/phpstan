<?php

declare(strict_types=1);

namespace App\Query;

use App\Bus\QueryInterface;

/**
 * @phpstan-import-type Return from ExampleQueryHandler
 * @implements QueryInterface<Return>
 */
final class ExampleQuery implements QueryInterface
{
}