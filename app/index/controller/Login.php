<?php
namespace app\index\controller;
use think\facade\Db;/**加载数据库**/
use app\BaseController;
use think\facade\View;
use think\facade\Session;
use think\captcha\facade\Captcha;
use think\Request;

class Login extends BaseController
{
  
    public function index()
    {
        
        if (request()->isPost()){
            
            extract(request()->param());
            $res=Db::name('iuser')->where('email',$email)->find();/**查询数据**/
            if(password_verify($pwd,$res['pwd'])){
                Session::set('uid',$res['uid']);
                Session::set('uname',$res['uname']);
                Session::set('gender',$res['gender']);
                
                echo 'true';
            }else{
                echo 'false';
            }	
		 
		}else{
			
			return view('index');
		} 
    }

    public function verify()
    {
		
        return Captcha::create('verify');    
    }

    public function checkcapcha(){
        $res = captcha_check(input('post.captcha'));
        if($res)
		{
			return true;
			exit;
		}else{
			return false;
			exit;
		}
		 
	}
}
