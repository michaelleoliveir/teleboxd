<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tmdb:sync')->dailyAt('02:00')->withoutOverlapping();