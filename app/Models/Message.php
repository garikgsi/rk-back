<?php

namespace App\Models;

use App\Interfaces\TableInterface;
use App\Traits\TableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model implements TableInterface
{
    use HasFactory, SoftDeletes, TableTrait ;

}
