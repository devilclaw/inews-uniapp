<?php
declare (strict_types = 1);

namespace app\admin\controller;
use think\facade\Session;
use lib\Rule;/**无限极分类扩展类库**/
use think\facade\Db;
 

class Index extends Common
{
    public function index()
    {
        $uid = session('uid');
        $uname = session('uname');
 
        //根据uid 获取权限菜单
        //连表查询获取菜单数据
        $res = Db::name('users')->alias('u')
        ->where('u.uid',$uid)
        ->field('u.uid,u.uname,ar.rules')
        ->leftJoin('users_role ur','ur.uid = u.uid')
        ->leftJoin('auth_role ar','ar.id = ur.role_id ')
        ->select()->toArray();
   //只获取rules权限列表
        $rules = array_column($res,'rules');
        
        $rules = explode(",",implode(",",$rules));
         
        //根据rules列选定角色菜单
        $res = Db::name('auth_rule')->field('id,name,title,pid')->order('id','asc')->where('is_menu',1)->where('id','in',$rules)->select();
        //dump($res);

        $rlist = Rule::Rulelayer($res);
        // dump($rlist); 
        $data = ['uid'=>$uid,
                'uname'=>$uname,
                'rlist' => $rlist
            ];   
                 
       return view('index', $data);
    }


    public function welcome()
    {
        return view('welcome');
    }
    
    public function logout()
    {
        Session::clear();
        return redirect('/login/index');
    }


    public function clearcache()
    {
        //得到admin应用的runtime目录 D:\phpstudy_pro\WWW\inews-uniapp.io\think\runtime\admin\ 
        $RUNTIME_PATH = app()->getRuntimePath();
         
        if(delete_dir_file($RUNTIME_PATH) )
        {
            
            $res = ['status'=>1,'msg'=>'清除成功'];
            return json($res);
            exit;
        }else{
                $res = ['status'=>0,'msg'=>'清除失败'];
            return json($res);
            exit;
        }   
        
    }
}
