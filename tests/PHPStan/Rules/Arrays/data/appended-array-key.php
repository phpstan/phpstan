<?php

namespace AppendedArrayKey;

class Foo
{

    /** @var array<int, mixed> */
    private $intArray;

    /** @var array<string, mixed> */
    private $stringArray;

    /** @var array<int|string, mixed> */
    private $bothArray;

    /**
     * @param int|string $intOrString
     */
    public function doFoo(
        int $int,
        string $string,
        $intOrString,
        ?string $stringOrNull
    ) {
        $this->intArray[new \DateTimeImmutable()] = 1;
        $this->intArray[$intOrString] = 1;
        $this->intArray[$int] = 1;
        $this->intArray[$string] = 1;
        $this->stringArray[$int] = 1;
        $this->stringArray[$string] = 1;
        $this->stringArray[$intOrString] = 1;
        $this->bothArray[$int] = 1;
        $this->bothArray[$intOrString] = 1;
        $this->bothArray[$string] = 1;
        $this->stringArray[$stringOrNull] = 1; // will be cast to string
        $this->stringArray['0'] = 1;
    }

    public function checkRewrittenArray()
    {
        $this->stringArray = [];
        $integerKey = (int)'1';

        $this->stringArray[$integerKey] = false;
    }
}
