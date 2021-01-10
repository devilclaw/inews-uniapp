<?php

namespace app\admin\controller;

use app\BaseController;
use lib\Auth;

/**权限认证类**/

use think\facade\Session;

class Common extends BaseController
{
    public function initialize()
    {

        $sess_auth = session('uid');

        $uname = session('uname');


        if (!$sess_auth) {
            jumpTo('/login/index');
            exit;
        } else {
            $auth = new Auth();
            if (!$auth->check(request()->controller() . '/' . request()->action(), $sess_auth)) {
                //echo $sess_auth;
                historyTo('抱歉~你没有操作该栏目的权限，请联系管理员!');
                exit;
            }
        }
    }
}
