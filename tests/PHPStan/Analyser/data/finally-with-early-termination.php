<?php

namespace FinallyNamespace;

try {
    $integerOrString = 1;
    $fooOrBarException = null;
    return 1;
} catch (FooException $e) {
    $integerOrString = 1;
    $fooOrBarException = $e;
    throw $e;
} catch (BarException $e) {
    $integerOrString = 'foo';
    $fooOrBarException = $e;
    return $e;
} finally {
    die;
}
