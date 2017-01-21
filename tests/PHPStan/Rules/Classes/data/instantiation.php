<?php

namespace TestInstantiation;

new FooInstantiation;
new FooInstantiation();
new FooInstantiation(1); // additional parameter
new FooBarInstantiation(); // nonexistent
new BarInstantiation(); // missing parameter
new LoremInstantiation(); // abstract
new IpsumInstantiation(); // interface

$test = 'Test';
new $test(); // skip

new ClassWithVariadicConstructor(1, 2, 3);
new \DatePeriod();
new \DatePeriod(new \DateTime(), new \DateInterval('P1D'), new \DateTime(), \DatePeriod::EXCLUDE_START_DATE);
