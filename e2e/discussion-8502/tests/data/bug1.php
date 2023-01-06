<?php

namespace Bug1;

use function PHPStan\Testing\assertType;

/** @var \Psr\Container\ContainerInterface $logger */

assertType('string', $logger->get('bar'));