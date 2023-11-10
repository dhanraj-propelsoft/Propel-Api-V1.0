<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Member extends Authenticatable
{
    use HasFactory,HasApiTokens;

    public function personDetails()
    {
        return $this->hasOne(PersonDetails::class,'uid','uid');
    }
    use HasFactory, HasApiTokens;
}
