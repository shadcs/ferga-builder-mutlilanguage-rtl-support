<?php

namespace Ferga\FilamentBuilder\Tests\Fixtures\Models;

use Ferga\FilamentBuilder\Concerns\HasBuilderContent;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasBuilderContent;

    protected $guarded = [];

    protected $casts = ['content' => 'array'];
}
