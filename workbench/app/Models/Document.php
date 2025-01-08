<?php

namespace Workbench\App\Models;

use GearboxSolutions\HasOneFile\Traits\HasOneFile;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasOneFile;

    protected $guarded = [];
}
