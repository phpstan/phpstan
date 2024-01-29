<?php

declare(strict_types=1);

namespace App;

use App\Bus\QueryBusInterface;

final class Example
{
    public function __construct(private QueryBusInterface $queryBus)
    {
    }

    public function __invoke(): string
    {
        $value = $this->queryBus->handle(new Query\ExampleQuery());
        \PHPStan\dumpType($value);
        return $value;
    }
}