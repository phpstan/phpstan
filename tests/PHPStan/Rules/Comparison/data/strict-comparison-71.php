<?php

namespace StrictComparison71;

class Foo
{

    public function returnsNullableString(): ?bool
    {
        return false;
    }

    public function doCheckNullableString(): int
    {
        $result = $this->returnsNullableString();
        if ($result === true) {
            return 1;
        } elseif ($result === false) {
            return 2;
        } elseif ($result === null) {
            return 3;
        }
        return 4;
    }

    public function doCheckNullableAndAddString(?int $memoryLimit): void
    {
        if ($memoryLimit === null) {
            $memoryLimit = 'abc';
        }

        if ($memoryLimit === 'abc') {
            // doSomething
        }
    }
}
