<?php

declare(strict_types=1);

namespace App\Bus;

interface QueryBusInterface
{
    /**
     * @template T
     *
     * @param QueryInterface<T> $query
     *
     * @return T
     */
    public function handle(QueryInterface $query);
}