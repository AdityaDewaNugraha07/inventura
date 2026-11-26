<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuGroup extends Model
{
    protected $guarded = ['id'];

    public function menus()
    {
        return $this->hasMany(Menu::class)->whereNull('parent_id')->orderBy('order_no');
    }
}