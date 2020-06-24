<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class EntityBeforeUpdateEvent extends Event
{
    public const NAME = 'entity.beforeUpdate';
}
