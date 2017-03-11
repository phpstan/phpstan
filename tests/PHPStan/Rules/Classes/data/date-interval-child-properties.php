<?php

namespace AccessPropertiesDateIntervalChild;

class DateIntervalChild extends \DateInterval
{
    public function doFoo()
    {
        echo $this->invert;
        echo $this->d;
        echo $this->m;
        echo $this->y;
        echo $this->nonexistent;
    }
}
