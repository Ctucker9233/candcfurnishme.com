<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Zenstruck\ScheduleBundle\Schedule;
use Zenstruck\ScheduleBundle\Schedule\ScheduleBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;
    public function buildSchedule(Schedule $schedule): void
    {
        $schedule->timezone('UTC');
    }   
}
