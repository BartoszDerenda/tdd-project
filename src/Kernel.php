<?php

/**
 * Project for ZTP2.
 *
 * License? What license?
 */

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

/**
 * Class kernel.
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;
}
