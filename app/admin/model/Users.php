<?php

namespace app\admin\model;
use think\Model;
use app\admin\model\AuthRole;
use app\admin\model\UsersRole;

class Users extends Model
{
    protected $name = 'users';
    protected $pk = 'uid';
    
    //在login控制器使用
    public static function UserInfo($uname)
    {  
        if(!empty($uname))
        {
            $user = static::where('uname', $uname)->field('pwd,uid,uname')->findOrEmpty();
            if (!$user->isEmpty()) {
               return $user;
            }
        } 
    }


    public function roles()
    {
        return $this->belongsToMany(AuthRole::class,UsersRole::class,'uid','role_id');
    }
}