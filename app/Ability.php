<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ability extends Model
{
    protected $fillable = [
        'name'
    ];
    
    public $timestamps = false;
    
    public function users()
    {
        return $this->belongsToMany('App\User');
    }
}
