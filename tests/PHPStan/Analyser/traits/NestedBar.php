<?php declare(strict_types = 1);

namespace AnalyseTraits;

class NestedBar
{
    use NestedFooTrait;

    public function doBar()
    {
    }
}
