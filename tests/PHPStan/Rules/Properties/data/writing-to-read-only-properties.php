<?php

namespace WritingToReadOnlyProperties;

/**
 * @property-read int $readOnlyProperty
 * @property int $usualProperty
 * @property-write int $writeOnlyProperty
 */
class Foo
{

    public function doFoo()
    {
        $this->readOnlyProperty = 1;
        $this->readOnlyProperty += 1;

        $this->usualProperty = 1;
        $this->usualProperty .= 1;

        $this->writeOnlyProperty = 1;
        $this->writeOnlyProperty .= 1;

        $self = new self();
        $self->readOnlyProperty = 1;
        $self->readOnlyProperty += 1;

        $self->usualProperty = 1;
        $self->usualProperty .= 1;

        $self->writeOnlyProperty = 1;
        $self->writeOnlyProperty .= 1;
    }
}
