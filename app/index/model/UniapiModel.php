<?php

namespace app\index\model;


use think\Model;

class  UniapiModel extends Model
{


    protected $name = 'article';
    public function DetailNews($data)
    {
        $newsid = $data['newsid'];

        $res = UniapiModel::where('id', $newsid)
            ->alias('a')
            ->leftjoin('13_users u', 'u.uid=a.uid')
            ->field('a.content,a.comment_count,u.uname,u.create_time')
            ->select();
        // dd(UniapiModel::getLastSql());
        return $res;
    }
}
