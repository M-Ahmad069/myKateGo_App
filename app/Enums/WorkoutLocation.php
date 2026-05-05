<?php

namespace App\Enums;

enum WorkoutLocation: string
{
    case Home = 'home';
    case Gym = 'gym';
    case Either = 'either';
}
