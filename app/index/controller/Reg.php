<?php
 

namespace app\index\controller;
use think\facade\Db;/**加载数据库**/
use app\BaseController;
use think\facade\View;
use think\facade\Session;
use think\captcha\facade\Captcha;
use think\Request;

class Reg
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        
        if(request()->isPost())
        {
            extract(request()->param());
            
            $data = [
                'uname'=>$uname,
                'tel'=>$tel,
                'email'=>$email,
                'pwd'=>password_hash($pwd,PASSWORD_BCRYPT),/**加密**/
                'gender'=>$gender,        
				'status'=>1,
				'create_time'=>time()
			];
            $res=Db::name('i_user')->where('uname',$uname)->find();/**查询数据**/
            if ( empty($res)) {
                $uid = Db::name('i_user')->insertGetId($data);
                if ($uid) {
                    echo 'true';
                    exit;
                }else{
                    echo "false";
                    exit;
                }
                 
                
            }else{
                echo "false";
                exit;
            }
        }else{
            return view('index');
        }
        
    }

     /**邮箱检测**/
	public function checkemail(){
		$email=input('post.email');
		$res=Db::name('i_user')->where('email',$email)->column('email');  
		if($res){
			echo "false";
			exit;
		}else{
			echo "true";
			exit;
		}
		
    }
    
     /**用户名检测**/
	public function checkuname(){
		$uname=input('post.uname');
		$res=Db::name('i_user')->where('uname',$uname)->column('uname');  
		if($res){
			echo "false";
			exit;
		}else{
			echo "true";
			exit;
		}
		
	}
}
