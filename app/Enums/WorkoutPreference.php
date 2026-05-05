<?php

namespace App\Enums;

enum WorkoutPreference: string
{
    case Home = 'home';
    case Gym = 'gym';
    case Both = 'both';
    case None = 'none';
}
