<?php

namespace app\admin\model;
use think\Model;
use think\model\Pivot;

//用户uid 角色role_id 中间表
class UsersRole extends Pivot
{
    protected $name = 'users_role';
    protected $autoWriteTimestamp = true;
    public function UsersRole()
    {  
        return $this->hasMany(Users::class,'uid');
    }
}