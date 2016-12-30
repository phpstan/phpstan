<?php

implode(); // should report 1-2 parameters
implode('-', []); // OK
implode([]); // also OK , variant with $pieces only
implode('-', [], 'foo'); // should report 3 parameters given, 1-2 required
