<?php

namespace App\Enums;

enum MetodeBayar: string
{
    case Tunai = 'tunai';
    case Qris = 'qris';
}
