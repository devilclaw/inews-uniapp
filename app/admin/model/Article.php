<?php

namespace app\admin\model;
use think\Model;


class Article extends Model
{
    protected $name = 'article';

    public function searchIdAttr($query, $value, $data)
    {
        $query->where('id','like', $value . '%');
    }

    public function searchTitleAttr($query, $value, $data)
    {
        $query->where('title','like', $value . '%');
    }
    
    public function getArticle($data)
    {  
        $draw=$data['draw'];	
        $order_column=$data['order']['0']['column'];
        $order_dir=$data['order']['0']['dir'];
        if(isset($order_column)){
            $i = intval($order_column);
                switch($i){
                /**插件默认从0列开始排序 导致第一列禁用排序失效**/
                case 0;$order_column="id";break;
                default;$orderSql = '';
            }
        }
            
        /**分页参数**/
        $start=isset($data['length']) ? $data['start'] :null;/**从多少开始**/
        $length= $data['length'] != -1 ? $data['length']:null;/**数据长度**/
        
          
         $total= Article::select();
         $recordsTotal = count($total);
        
        /**搜索条件**/	
        $search=$data['search']['value'];/**获取前台传过来的过滤条件**/
        if(strlen($search)>0){/**如果是搜索查询**/
            $recordsFilteredResult =  Article::withSearch(['title'], [
                'title'			=>	$search  
            ])
            ->select()->toArray();
            $recordsFiltered = count($recordsFilteredResult);

        }else{
            $recordsFilteredResult = Article::field('id, name, title,is_menu, pid ')
            ->limit($start,$length)->order($order_column,$order_dir)->select()->toArray();
            $recordsFiltered = $recordsTotal;
        }
        $res = [
            'recordsTotal'=>$recordsTotal,
            'recordsFiltered'=>$recordsFiltered ,
            'draw'=>$draw,
            'tableData'=>$recordsFilteredResult
        ];
        return $res;
     
    }
}