<?php

namespace app\index\controller;

use think\facade\Db;

/**加载数据库**/

use app\BaseController;
use think\facade\Session;
use lib\Rule;

/**无限极分类扩展类库**/

class Detail extends BaseController
{
    public function index()
    {


        //获取博客文章id

        $id = input('id');

        //获取新闻相关信息
        $news = Db::name('article')
            ->alias('a')
            ->field('u.uname,a.content,a.title,a.create_time,a.cover,c.catid,c.catname')
            ->where('a.id', $id)
            ->group('a.id')
            ->leftJoin('cate c', 'c.catid = a.catid')
            ->leftJoin('users u', 'u.uid = a.uid ')
            ->find();




        $news['create_time'] = GetTime($news['create_time']);
        $news['cover'] = json_decode($news['cover']);



        //获取评论者个人信息与评论内容
        $rule = Db::name('comm')
            ->alias('c')
            ->where(['i.status' => 1, 'c.status' => 1, 'c.news_id' => $id])
            ->order('c.create_time', 'desc')
            ->field('c.id,c.con,c.create_time,c.pid,i.uname,i.gender')
            ->leftJoin('13_iuser i', 'i.uid = c.uid')
            ->select()->toArray();

        $count = count($rule);

        foreach ($rule as $k => $v) {
            $rule[$k]['create_time'] =  GetTime($v['create_time']);
        }

        $comm = Rule::Rulelayer($rule, $pid = 0);

        $data = [
            'uid' => session('uid'),
            'uname' => session('uname'),
            'gender' => session('gender'),
            'id' => $id,
            'comm' => $comm,
            'catname' => $news['catname'],
            'catid' => $news['catid'],
            'list' => $news,
            'count' => $count
        ];

        return view('index', $data);
    }
    /**
     * 增加评论 
     * 
     */

    public function add()
    {

        if (request()->isPost()) {
            $data = request()->param();

            $news_id = input('news_id');
            $data['create_time'] = time();

            $data['uid'] = session::get('uid');
            $in = Db::name('comm')->insertGetId($data);
            //评论增加成功 文章表评论数自增1
            if ($in) {
                Db::name('article')
                    ->where('id', $news_id)
                    ->inc('comment_count')
                    ->update();
                return json(["status" => 1, "com_id" => $in, "pid" => $data['pid']]);
            }
        }
    }

    public function del()
    {
        if (request()->isPost()) {

            $com_id = input('post.com_id');
            $news_id = input('post.news_id');
            $res = Db::name('comm')->delete($com_id);
            if ($res) {
                Db::name('article')
                    ->where('id', $news_id)
                    ->dec('comment_count')
                    ->update();
                return json(['status' => 1, 'msg' => '该评论已删除']);
            } else {
                return json(['status' => 0, 'msg' => '评论删除失败']);
            }
        }
    }
}
