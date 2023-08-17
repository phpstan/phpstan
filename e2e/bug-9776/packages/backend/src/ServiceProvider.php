<?php

namespace CristianHG2\Backend;

use Carbon\Carbon;
use Cmixin\BusinessDay;

class ServiceProvider
{
    public function boot(): void
    {
        BusinessDay::enable(Carbon::class, 'us-national');
    }
}
