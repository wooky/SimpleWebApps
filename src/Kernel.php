<?php

declare(strict_types=1);

namespace SimpleWebApps;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
  use MicroKernelTrait;
}
