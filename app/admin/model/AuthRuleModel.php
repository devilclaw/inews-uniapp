<?php

namespace app\admin\model;

use think\Model;


class AuthRuleModel extends Model
{
    protected $name = 'auth_rule';
    public function AuthRule($data)
    {
        $draw = $data['draw'];

        //order参数
        $order_column = $data['order']['0']['column'];
        $order_dir = $data['order']['0']['dir'];
        if (isset($order_column)) {
            $i = intval($order_column);
            switch ($i) {
                    /**插件默认从0列开始排序 导致第一列禁用排序失效**/
                case 0;
                    $order_column = "id";
                    break;
                default;
                    $orderSql = '';
            }
        }
        /**分页参数**/
        $start = isset($data['length']) ? $data['start'] : null;
        /**从多少开始**/
        $length = $data['length'] != -1 ? $data['length'] : null;
        /**数据长度**/


        //获取数据总长度
        $total = AuthRuleModel::select();
        $recordsTotal = count($total);

        /**搜索条件**/
        $search = $data['search']['value'];
        /**获取前台传过来的过滤条件**/

        if (strlen($search) > 0) {
            /**如果是搜索查询**/

            $recordsFilteredResult = AuthRuleModel::where('title|id|name', 'like', '%' . $search . '%')
                ->select()->toArray();
            //    dd(AuthRuleModel::getLastSql()) ;
            $recordsFiltered = count($recordsFilteredResult);
        } else {
            $recordsFilteredResult = AuthRuleModel::field('id, name, title,is_menu, pid ')
                ->limit($start, $length)->order($order_column, $order_dir)->select()->toArray();
            $recordsFiltered = $recordsTotal;
        }
        $res = [
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'draw' => $draw,
            'tableData' => $recordsFilteredResult
        ];
        return $res;
    }
}
