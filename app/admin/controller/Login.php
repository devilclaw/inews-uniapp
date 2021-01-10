<?php
declare (strict_types = 1);

namespace app\admin\controller;
use think\captcha\facade\Captcha;
use think\facade\Db;
use think\facade\Session;
use app\admin\model\Users;

class Login
{
    public function index()
    {
        
        if(request()->isPost())
        {
            $pwd = input('post.password');
            $uname = input('post.uname');
            $res = Users::UserInfo($uname);
            if($res)
            {
                if(password_verify($pwd,$res['pwd']))
                {
                    Session::set('uid',$res['uid']);
                    Session::set('uname',$res['uname']);
                    echo 'true';
                }else{
                    echo 'false';
                }
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

    public function checkCaptcha()
    {
        $res = captcha_check(input('post.captcha'));
        if(!$res)
        {
            return false;
            exit;
        }else{
            return true;
            exit;
        }
      

    }
}
