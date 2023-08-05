<?php declare(strict_types = 1);

namespace Bug9714;

use SimpleXmlElement;

class HelloWorld
{
    public function sayHello(): void
    {
        $xml = new SimpleXmlElement('asdf');
        $dataTags = $xml->xpath('//data');
    }
}