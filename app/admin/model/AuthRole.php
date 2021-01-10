<?php

namespace app\admin\model;
use think\Model;


class AuthRole extends Model
{
    protected $name = 'auth_role';
    
    public function AuthRole()
    {  
        return $this->hasMany(Users::class,'uid');
    }
}