<?php

// wrongly defined type for constant to trigger the bug
if(!defined("FILTER_SANITIZE_MAGIC_QUOTES"))define("FILTER_SANITIZE_MAGIC_QUOTES",false);
