<?php

namespace Fibtegis\FilamentInfiniteScroll\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_published' => 'boolean',
    ];
}
