<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('bookings:sweep')->everyMinute()->withoutOverlapping();
