<?php

namespace CristianHG2\Backend\Services;

use Carbon\Carbon;

class MyClass
{
    protected function method(Carbon $carbon): Carbon
    {
        if ($carbon->isHoliday()) {
            return $carbon->nextBusinessDay();
        }

        return $carbon;
    }
}
