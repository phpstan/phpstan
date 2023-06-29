<?php declare(strict_types=1);

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as KernelVendor;
use App\Http\Middleware\Auth;
use App\Http\Middleware\RequestLogger;
use App\Http\Middleware\Reset;

class Kernel extends KernelVendor
{
    /**
     * @var array
     */
    protected $middleware = [
    ];
}
