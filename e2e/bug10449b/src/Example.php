<?php

declare(strict_types=1);

namespace App;

use App\Bus\QueryBusInterface;

final class Example
{
    public function __construct(private QueryBusInterface $queryBus)
    {
    }

    public function __invoke(mixed $x): string
    {
        if ($x instanceof Query\ExampleQuery)
        {
            $value = $this->queryBus->handle($x);
            $this->needsString($value);
            return $value;
        }

        return 'hello';
    }

    private function needsString(string $s):void {}
}