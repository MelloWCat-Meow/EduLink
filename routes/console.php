<?php

use Illuminate\Support\Facades\Schedule;

// Cancel orders whose tutor-reply or payment deadline has passed and free their slots.
Schedule::command('edulink:lepas-penahanan')->everyMinute()->withoutOverlapping();
