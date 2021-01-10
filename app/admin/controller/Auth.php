<?php

declare(strict_types=1);

namespace app\admin\controller;

use app\admin\model\Users;
use think\Request;
use think\facade\Db;
use lib\Rule;
use app\admin\model\AuthRuleModel;

class Auth extends Common
{
	/**
	 * 显示资源列表
	 *
	 * @return \think\Response
	 */
	public function test1()
	{

		$user = Users::find(1);
		// 获取用户的所有角色
		$roles = $user->roles;
		dd($roles);
		foreach ($roles as $role) {
			// 输出用户的角色名
			echo $role->title;
			// 获取中间表模型
			dump($role->pivot);
		}
	}
	public function index()
	{

		//http://datatables.club/manual/server-side
		if (request()->isPost()) {
			$data = request()->param();
			/**获取post参数**/
			$draw = $data['draw'];
			/**获取Datatables发送的参数 必要 这个值会直接返回给前端 DataTables使用它来确保DataTables按顺序绘制从服务器端处理请求返回的Ajax（Ajax请求是异步的，因此可以不按顺序返回**/

			$order_column = $data['order']['0']['column'];
			/**从哪一列开始排序，默认从0开始**/
			$order_dir = $data['order']['0']['dir'];
			/**排序ase desc 升序或者降序**/
			/*******拼接mysql*****/
			$orderSql = "";
			if (isset($order_column)) {
				$i = intval($order_column);
				switch ($i) {
						/**插件默认从0列开始排序 导致第一列禁用排序失效**/

					case 0;
						$orderSql = " order by u.uid " . $order_dir;
						break;
						// case 1;$orderSql = " order by u.uname ".$order_dir;break;
					case 3;
						$orderSql = " order by u.create_time " . $order_dir;
						break;

					default;
						$orderSql = '';
				}
			}


			/**分页参数**/
			$start = $data['start'];
			/**从多少开始**/
			$length = $data['length'];
			/**数据长度**/

			$limitSql = '';

			$limitFlag = isset($start) && $length != -1; //注意-1表示应返回所有记录（尽管这抵消了服务器端处理的任何好处！）

			if ($limitFlag) {
				$limitSql = " LIMIT " . intval($start) . ", " . intval($length);
			}


			/**搜索条件**/
			$search = $data['search']['value'];
			/**获取前台传过来的过滤条件**/
			$sumSqlWhere = " where (u.uid LIKE '%" . $search . "%'  or u.uname LIKE '%" . $search . "%')";


			/**定义查询数据总记录数sql**/
			$sumSql = "SELECT count(uid) as sum FROM 13_users as u";
			/*条件过滤后记录数 必要*/
			$recordsFiltered = 0;
			/*表的总记录数 必要*/
			$recordsTotal = 0;
			$resTotal = Db::query($sumSql);
			/**查询数据表记录总条数**/

			foreach ($resTotal as $key => $val) {
				$recordsTotal =  $val['sum'];
				/**获取记录总数赋值给变量**/
			}

			/**定义过滤条件查询过滤后的记录数sql**/

			/***根据搜索条件拼接mysql语句***/
			if (strlen($search) > 0) {
				/**如果是搜索查询**/
				$recordsFilteredResult = Db::query($sumSql . $sumSqlWhere);
				foreach ($recordsFilteredResult as $k => $vals) {
					$recordsFiltered =  $vals['sum'];
					/**获取记录总数赋值给变量**/
				}
			} else {
				$recordsFiltered = $recordsTotal;
			}

			$totalResultSql = "select u.uid,u.uname,u.create_time,u.status,ar.title from 13_users as u left join 13_users_role as ur on ur.uid = u.uid left join 13_auth_role as ar on ar.id = ur.role_id";

			$dataResult = Db::query($totalResultSql . $sumSqlWhere . $orderSql . $limitSql);
			//  dd($dataResult);
			foreach ($dataResult as $k => $v) {

				$dataResult[$k]['status'] = $v['status'] == 1 ? '启用' : '禁用';
				$dataResult[$k]['create_time'] = date('Y-m--d H:i:s', $v['create_time']);
			}


			/**Output 包含的是必要的**/
			echo json_encode(array(
				"draw" => intval($draw),
				"recordsTotal" => intval($recordsTotal),
				"recordsFiltered" => intval($recordsFiltered),
				"tableData" => $dataResult
			), 320);
		} else {
			return view('index');
		}
	}

	public function add()
	{
		if (request()->isPost()) {
			$role_id = input('post.role');
			$data = [
				'uname' => input('post.uname'),
				'pwd' => password_hash(input('post.pwd'), PASSWORD_BCRYPT),
				'status' => intval(input('post.status')),
				'login_ip' => request()->ip(),
				'create_time' => time(),
			];


			$uid = Db::name('users')->insertGetId($data);
			if ($uid) {
				$res = [
					'uid' => $uid,
					'role_id' => $role_id
				];
				//往用户-角色关联表插入数据
				$id = Db::name('users_role')->insertGetId($res);
				if ($id) {
					echo 'true';
					exit;
				}
			} else {
				echo 'false';
				exit;
			}
		} else {
			//角色选择下拉展示
			$role = Db::name('auth_role')->field('id,title')->order('id', 'asc')->where('status', 1)->select();
			return view('add', ['role' => $role]);
		}
	}


	public function update()
	{

		//当前是提交操作
		if (request()->isPost()) {

			extract(request()->param());
			$data = [
				'status' => input('post.status'),
				'login_ip' => request()->ip(),
				'create_time' => time()
			];
			//只是更新user表中的数据
			$res = Db::name('users')->where(['uid' => $uid])->update($data);
			if ($res !== false) {
				//更新用户的角色
				$re = Db::name('users_role')->where(['uid' => $uid])->update(['role_id' => $role_id]);
				if ($re !== false) {
					echo 'true';
				} else {
					echo 'false';
				}
			} else {
				echo 'false';
			}
		} else {
			// dd(request()->param());
			//当用户信息未被修改
			// 获取所有角色
			$role = Db::name('auth_role')->field('id,title')->order('id', 'asc')->where('status', 1)->select();
			//获取uid获取用户全部信息,连表查询用户-中间表-角色表
			$uid = input('get.uid');
			$info = Db::name('users')->alias('u')
				->field('u.uname,u.uid,u.status,ur.role_id')
				->join('users_role ur', 'u.uid = ur.uid')
				->join('auth_role ar', 'ar.id = ur.role_id')
				->where('u.uid', $uid)
				->find();

			// dump($info);
			// dd($role);
			return view('update', ['info' => $info, 'role' => $role]);
		}
	}

	public function del()
	{
		if (request()->isPost()) {
			$uid = intval(input('post.uid'));

			$sql = "delete u, ur from 13_users u inner join 13_users_role ur on u.uid = ur.uid where u.uid=" . $uid;
			$res = Db::execute($sql);
			if (false !== $res) {
				echo 'true';
				exit;
			} else {
				echo 'false';
				exit;
			}
		}
	}

	public function resetpwd()
	{
		if (request()->isPost()) {
			extract(request()->param());
			$pwd = password_hash($pwd, PASSWORD_BCRYPT);
			$res =  Db::name('users')->where('uid', $uid)->update(['pwd' => $pwd]);
			if ($res) {
				echo 'true';
				exit;
			} else {
				echo 'false';
				exit;
			}
		} else {
			$uid = input('get.uid');
			return view('resetpwd', ['uid' => $uid]);
		}
	}


	public function rule()
	{
		if (request()->isPost()) {
			$data = request()->param();
			/**获取post参数**/
			$res = (new AuthRuleModel)->AuthRule($data);
			// dd($res);
			$dataResult = Rule::RuleList($res['tableData']);

			foreach ($dataResult as $k => $v) {
				$dataResult[$k]['is_menu'] = $v['is_menu'] == 1 ? '启用' : '禁用';
			}
			$res['tableData'] = empty($dataResult) ? $res['tableData'] : $dataResult;
			echo json_encode($res, 320);
		} else {
			return view('rule');
		}
	}

	public function addrule()
	{
		if (request()->isPost()) {
			$data = [
				'name' => input('post.name'),
				'title' => input('post.title'),
				'is_menu' => input('post.is_menu'),
				'pid' => input('post.pid')
			];

			$id = Db::name('auth_rule')->insertGetId($data);
			if ($id) {
				echo "true";
				exit;
			} else {
				echo "false";
				exit;
			}
		} else {
			$pid = input('pid') ? input('pid') : '0';
			/**获取pid**/
			return view('addrule', ['pid' => $pid]);
		}
	}



	/**编辑**/
	public function updaterule()
	{
		if (request()->isPost()) {
			$id = input('post.id');
			$data = [
				'name' => input('post.name'),
				'title' => input('post.title'),
				'is_menu' => input('post.is_menu')
			];

			$res = Db::name('auth_rule')->where(['id' => $id])->update($data);
			//有数据更新返回1， 无数据更新返回0
			if ($res) {
				echo "true";
				exit;
			} else {
				echo "false";
				exit;
			}
		} else {
			$id = input('id');
			$info = Db::name('auth_rule')->where(['id' => $id])->find();
			return view('updaterule', ['info' => $info]);
		}
	}
	/**************规则编辑end*****************/


	/**删除规则**/
	public function delrule()
	{
		$id = input('post.id');

		$res = Db::name('auth_rule')->where('pid', $id)->select()->toArray();
		/**获取子栏目数据如果存在子栏目数据则不能删除**/

		if (!empty($res)) {
			echo '1';
			exit;
		} else {
			$sql = "delete from 13_auth_rule where id='" . $id . "'";
			$re = Db::execute($sql);
			if ($re !== false) {
				echo "true";
				exit;
			} else {
				echo "false";
				exit;
			}
		}
	}

	/**************规则管理end*****************/




	public function group()
	{

		//http://datatables.club/manual/server-side
		if (request()->isPost()) {
			$data = request()->param();
			/**获取post参数**/
			$draw = $data['draw'];
			/**获取Datatables发送的参数 必要 这个值会直接返回给前端 DataTables使用它来确保DataTables按顺序绘制从服务器端处理请求返回的Ajax（Ajax请求是异步的，因此可以不按顺序返回**/

			$order_column = $data['order']['0']['column'];
			/**从哪一列开始排序，默认从0开始**/
			$order_dir = $data['order']['0']['dir'];
			/**排序ase desc 升序或者降序**/
			/*******拼接mysql*****/
			$orderSql = "";
			if (isset($order_column)) {
				$i = intval($order_column);
				switch ($i) {
						/**插件默认从0列开始排序 导致第一列禁用排序失效**/

					case 0;
						$orderSql = " order by id " . $order_dir;
						break;


					default;
						$orderSql = '';
				}
			}


			/**分页参数**/
			$start = $data['start'];
			/**从多少开始**/
			$length = $data['length'];
			/**数据长度**/

			$limitSql = '';

			$limitFlag = isset($start) && $length != -1; //注意-1表示应返回所有记录（尽管这抵消了服务器端处理的任何好处！）

			if ($limitFlag) {
				$limitSql = " LIMIT " . intval($start) . ", " . intval($length);
			}


			/**搜索条件**/
			$search = $data['search']['value'];
			/**获取前台传过来的过滤条件**/
			$sumSqlWhere = " where (id LIKE '%" . $search . "%'  or title LIKE '%" . $search . "%')";


			/**定义查询数据总记录数sql**/
			$sumSql = "SELECT count(id) as sum FROM 13_auth_role as ar";
			/*条件过滤后记录数 必要*/
			$recordsFiltered = 0;
			/*表的总记录数 必要*/
			$recordsTotal = 0;
			$resTotal = Db::query($sumSql);
			/**查询数据表记录总条数**/

			foreach ($resTotal as $key => $val) {
				$recordsTotal =  $val['sum'];
				/**获取记录总数赋值给变量**/
			}

			/**定义过滤条件查询过滤后的记录数sql**/

			/***根据搜索条件拼接mysql语句***/
			if (strlen($search) > 0) {
				/**如果是搜索查询**/
				$recordsFilteredResult = Db::query($sumSql . $sumSqlWhere);
				foreach ($recordsFilteredResult as $k => $vals) {
					$recordsFiltered =  $vals['sum'];
					/**获取记录总数赋值给变量**/
				}
			} else {
				$recordsFiltered = $recordsTotal;
			}

			$totalResultSql = "select id, title,status from 13_auth_role ";

			$dataResult = Db::query($totalResultSql . $sumSqlWhere . $orderSql . $limitSql);
			//  dd($dataResult);
			foreach ($dataResult as $k => $v) {

				$dataResult[$k]['status'] = $v['status'] == 1 ? '启用' : '禁用';
			}


			/**Output 包含的是必要的**/
			echo json_encode(array(
				"draw" => intval($draw),
				"recordsTotal" => intval($recordsTotal),
				"recordsFiltered" => intval($recordsFiltered),
				"tableData" => $dataResult
			), 320);
		} else {
			return view('group');
		}
	}

	public function updategroup()
	{
		if (request()->isPost()) {
			$id = input('post.id');
			/**获取规则id数组**/
			$rules = input('rules/a');
			$rules = implode(',', $rules);
			$data = [
				'title' => input('post.title'),
				'status' => input('post.status'),
				'rules' => $rules
			];
			$res = Db::name('auth_role')->where(['id' => $id])->update($data);

			if ($res) {
				echo "true";
				exit;
			} else {
				echo "false";
				exit;
			}
		} else {
			$id = input('id');
			//
			$info = Db::name('auth_role')->where(['id' => $id])->find();
			$res = Db::name('auth_rule')->order('id', 'asc')->select();
			//获取全部的路由名称
			$rule = Rule::Rulelayer($res);
			// dump($rule);
			//取出规则的id 字符串 将字符串转换为数组 判断是否选中
			$rules = explode(',', $info['rules']);
			$data = [
				'info' => $info,
				'rule' => $rule,
				'rules' => $rules
			];

			return view('updategroup', $data);
		}
	}
}
