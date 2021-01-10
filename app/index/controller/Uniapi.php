<?php

namespace app\index\controller;


use think\facade\Db;
use think\db\BaseQuery;
use app\BaseController;
use lib\Rule;



use app\index\model\UniapiModel;




class uniapi extends BaseController

{
    //分类列表
    public function cate()
    {
        $res = Db::name('cate')->field('catname,catid')->select();
        return json(array('data' => $res));
    }
    // public function list()
    // {
    //     $minId = input('minId');
    //     $pageSize = input('pageSize');
    //     $res = Db::name('article')
    //     ->alias('a')
    //     ->leftJoin('cate c','a.catid = c.catid')
    //     ->field('u.uname,a.id,a.title,a.create_time,a.comment_count,a.cover ')
    //     ->leftJoin('users u','u.uid = a.uid')
    //     ->limit($minId,$pageSize)
    //     ->where(['c.catid'=>1,'a.status'=>1])
    //     ->group('a.id')
    //     ->select()->toArray();
    // 	foreach($res as $k=>$v){ 
    // 		$res[$k]['create_time']=Rule::GetDateuk($v['create_time']);
    // 		$res[$k]['cover']=json_decode($v['cover']);		
    //     }
    //     return json($res);
    // }
    //文章封面图 标题 作者 发布时间 评论数
    public function news()
    {

        $catid = input('catId');
        $pageSize = input('pageSize');
        $offset = input('offset');

        $res = Db::name('article')
            ->alias('a')
            ->leftJoin('cate c', 'a.catid = c.catid')
            ->field('u.uname,a.id,a.title,a.create_time,a.comment_count,a.cover,c.catid,c.catname')
            ->leftJoin('users u', 'u.uid = a.uid')
            ->limit($offset, $pageSize)
            ->where(['c.catid' => $catid, 'a.status' => 1])
            ->group('a.id')
            ->select()->toArray();
        foreach ($res as $k => $v) {
            $res[$k]['create_time'] = GetTime($v['create_time']);
            $res[$k]['cover'] = json_decode($v['cover']);
        }
        return json($res);
    }


    //自己的  Model方法中含有 
    // public function detail()
    // {

    //     $data = request()->param();
    //     $res = (new UniapiModel)->DetailNews($data);
    //     // dd(json_encode($res));
    //     return json($res);
    // }


    public function detail()
    {

        $newsid = input('newsid');
        // dump($newsid);
        $content = Db::name('article')->field('content')->where('id', $newsid)->find();

        $comm = Db::name('comm')->field('uid,con,pid,id,create_time')->where('news_id', $newsid)->select()->toArray();
        // dd($comm);

        //这个操作避免了两表联合查询
        //循环出$comm利用comm中的uid值,来查询iuser表 
        foreach ($comm as $k => $v) {
            $comm[$k]['create_time'] = GetTime($v['create_time']);
            // dd($comm);
            //   $comm[1]['uid']
            $uname = Db::name('iuser')->field('uname')->where('uid', $v['uid'])->find();
            // dd($comm);
            //最后 为 $comm加一个键值对  uname
            $comm[$k]['uname'] = $uname['uname'];
        }

        $comm =  Rule::Rulelayer($comm);

        $res = [
            'comm' => $comm,
            'content' => $content
        ];
        // dd($res);
        return json($res);
    }





    //用户登录

    public function login()
    {

        $pwd = input('post.password');
        $uname = input('post.username');
        $res = Db::name('iuser')->where('uname', $uname)->find();
        /**查询数据**/
        if (password_verify($pwd, $res['pwd'])) {
            $ava  = json_decode($res['avator']);
            $result = [
                'uname' => $res['uname'],
                'ava' => $ava, 'uid' => $res['uid'],
                'code' => 1,
                'msg' => '登录成功'
            ];
            return json($result);
            exit;
        } else {
            $result = [
                'code' => 0,
                'msg' => '密码或账号错误,请检查~'
            ];
            return json($result);
            exit;
        }
    }

    //评论新增
    public function comm($pid = 0, $status = 1)
    {
        $con = input('con');
        $uid = input('uid');
        $newsid = input('newsid');

        // $pid = input('pid');
        $createtime = time();
        $insertData = ['con' => $con, 'uid' => $uid, 'news_id' => $newsid, 'pid' => $pid, 'create_time' => $createtime, 'status' => $status];

        $res = Db::name('comm')->insertGetId($insertData);
        // return json($res);
        if (is_numeric($res)) {
            return 'comm success';
        } else {
            return 'false';
        }
    }
}
