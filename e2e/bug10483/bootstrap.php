<?php

// constant that's used in the Filter extension that was introduced in a later version of PHP.
// on earlier php version introduce the same constant via a bootstrap file but with a wrong type
if(!defined("FILTER_SANITIZE_ADD_SLASHES"))define("FILTER_SANITIZE_ADD_SLASHES",false);
