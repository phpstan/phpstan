<?php

declare(strict_types=1);

namespace App;

use Doctrine\ORM\EntityManager;

class Test
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
