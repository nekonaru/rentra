<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('pemesanan:sapu')->everyMinute()->withoutOverlapping();
