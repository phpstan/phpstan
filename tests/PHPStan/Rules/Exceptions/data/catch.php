<?php

namespace TestCatch;

class FooCatch
{

}

class MyCatchException extends \Exception
{

}

try {

} catch (\TestCatch\FooCatch $e) { // not an exception

}

try {

} catch (\TestCatch\MyCatchException $e) {

}

try {

} catch (\FooCatchException $e) { // nonexistent exception class

}

try {

} catch (\TypeError $e) {

}
