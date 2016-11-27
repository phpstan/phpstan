<?php

namespace StrictComparison;

1 === 1;
1 === '1'; // wrong
1 !== '1'; // wrong
doFoo() === doBar();
1 === null;
(new Bar()) === 1; // wrong
