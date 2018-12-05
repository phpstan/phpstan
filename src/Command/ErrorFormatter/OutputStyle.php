<?php declare(strict_types=1);


namespace PHPStan\Command\ErrorFormatter;


use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;

interface OutputStyle extends OutputInterface, StyleInterface
{

}