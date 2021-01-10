<?php
namespace app\index\controller;
use think\facade\Db;/**加载数据库**/
use app\BaseController;
use think\facade\Session;
use lib\Rule;
 
class Index extends BaseController
{
    public function index()
    { 

        //获取所有分类
        $cate = Db::name('cate')->column('catname,catid') ;
        //获取最新分类下的新闻
        $totalResultSql = "select a.id,u.uname,a.content,a.title,a.comment_count,a.create_time,a.cover,c.catid,c.catname from 13_article as a left join 13_cate as c on c.catid = a.catid left join 13_users as u on u.uid = a.uid where a.status=1 and a.del=0  ";
		
		$group=" group by a.id";
		 
		
		$dataResult = Db::query($totalResultSql.$group);
		
		foreach($dataResult as $k=>$v){ 
			$dataResult[$k]['create_time']=GetTime($v['create_time']);
			$dataResult[$k]['cover']=json_decode($v['cover']);	
        }
        
         
        
        $data=[
            'uid'=>session('uid'),
            'uname'=>session('uname'),
            'news'=>$dataResult,
            'cate'=>$cate
        ];
        
        return view('index',$data);
    }

    public function logout(){
         
        Session::clear(); 
        $res = ['status'=>1,'msg'=>'退出成功'];
        return json($res);
        exit;
		 
	}

    
}
