<?php

namespace App\Models;
/**
 * only for testing usage
 */

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TableTrait;
use App\Interfaces\TableInterface;

class TestModel extends Model  implements TableInterface
{
    use HasFactory, TableTrait;
}
