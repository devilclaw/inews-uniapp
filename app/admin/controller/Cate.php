<?php
namespace app\admin\controller;
use think\facade\Db;/**加载数据库**/
use app\BaseController;
 
use app\admin\model\CateModel;
class Cate extends Common
{

    public function index()
    {
        if (request()->isPost()){
            $data=request()->param();/**获取post参数**/
            $res = (new CateModel)->Cate($data);
             
            foreach($res['tableData'] as $k=>$v){	
                $res['tableData'][$k]['status']=$v['status']==1?'启用':'禁用';
            }
            echo json_encode($res,320);
        }else{
            return view('index');
        }
    }

    public function update()
    {
        if (request()->isPost()){
            
			extract(request()->param());
			$data=[
				'catname'=>$catname,
				'status'=>$status,
				'create_time'=>time()
			];
			$res = Db::name('cate')->where(['catid'=>$catid])->update($data);
			if($res){
                echo "true";
                exit;
            }else{
                echo "false";
                exit;
            }
  

		}else{
			//未更新 接受get参数id
			$catid=input("get.catid");
			 
			/**关联查询获取数据**/
			$info=Db::name('cate')->where(['catid'=>$catid])->find();
			return view('update',['info'=>$info]);
		}
    }

    public function add()
	{
		if (request()->isPost()) {
			$data=[
				'catname'=>input('post.catname'),
				'status'=>input('post.status'),
                'create_time'=>time()
            ];
            $catid = Db::name('cate')->insertGetId($data);
				if($catid){
					echo "true";
					exit;
				}else{
					echo "false";
					exit;
				}
		}else{
			 
			return view('add');
		}
    }
    
    public function del()
    {
        $catid=input('post.catid');	 
        $sql="delete from 13_cate where catid='".$catid."'";
        $res=Db::execute($sql);
        if($res!==false){
            echo "true";
            exit;
        }else{
            echo "false";
            exit;
        }
		 
    }
}
   

