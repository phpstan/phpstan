<?php

declare(strict_types=1);

namespace App\Query;

use App\Bus\QueryHandlerInterface;

/**
 * @phpstan-type Return string
 */
final class ExampleQueryHandler implements QueryHandlerInterface
{
    /** @return Return */
    public function __invoke(ExampleQuery $exampleQuery)
    {
        return '1';
    }
}