<?php

array_udiff([], [], new \stdClass());
array_udiff([], [], [], 'foo');
array_udiff_assoc([], [], [], 'foo');
array_udiff_uassoc([], [], [], 'foo', 'foo');
array_udiff([], 'foo');
array_udiff_assoc([], 'foo');
array_udiff_uassoc([], 'foo');
array_udiff([], [], []);
array_udiff_assoc([], [], []);
array_udiff_uassoc([], [], [], []);
