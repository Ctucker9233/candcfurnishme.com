<?php

use App\Kernel;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return fn(array $context) => new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
