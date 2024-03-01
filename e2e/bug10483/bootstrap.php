<?php

// wrongly defined type for constant to trigger the bug
if(!defined("FILTER_VALIDATE_INT"))define("FILTER_VALIDATE_INT",false);