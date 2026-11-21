<?php

use Illuminate\Support\Facades\Schedule;

// Cancel orders whose tutor-reply or payment deadline has passed and free their slots.
Schedule::command('edulink:lepas-penahanan')->everyMinute()->withoutOverlapping();

// Day-before reminders for tomorrow's sessions, to parents and tutors. Safe to run more than once.
Schedule::command('edulink:pengingat-sesi')->dailyAt('07:00')->withoutOverlapping();

// Reschedule requests nobody answered before the old session time: the session stays put.
Schedule::command('edulink:jadwal-ulang-kedaluwarsa')->everyMinute()->withoutOverlapping();
