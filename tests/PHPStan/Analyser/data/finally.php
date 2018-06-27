<?php

namespace FinallyNamespace;

class FooException extends \Exception
{

}

class BarException extends \Exception
{

}

function () {
    try {
        $integerOrString = 1;
        $fooOrBarException = null;
    } catch (FooException $e) {
        $integerOrString = 1;
        $fooOrBarException = $e;
    } catch (BarException $e) {
        $integerOrString = 'foo';
        $fooOrBarException = $e;
    } finally {
        die;
    }
};
