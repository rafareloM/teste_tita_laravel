<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class section extends Model
{

    protected $connection = 'mysql';

    protected $table = 'sections';

    protected $fillable = ['name', 'value'];
}
