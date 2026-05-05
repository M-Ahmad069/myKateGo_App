<?php

namespace App\Enums;

enum Goal: string
{
    case LoseWeight = 'lose_weight';
    case BuildMuscle = 'build_muscle';
    case GetFit = 'get_fit';
    case Maintain = 'maintain';
}
