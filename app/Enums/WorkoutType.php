<?php

namespace App\Enums;

enum WorkoutType: string
{
    case Strength = 'strength';
    case Cardio = 'cardio';
    case Hiit = 'hiit';
    case Rest = 'rest';
    case Flexibility = 'flexibility';
}
