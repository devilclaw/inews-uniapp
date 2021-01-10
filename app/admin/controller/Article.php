<?php

namespace app\admin\controller;

use think\facade\Db;

/**加载数据库**/

use app\BaseController;
use think\facade\View;
use think\captcha\facade\Captcha;
use think\facade\Session;

use lib\Rule;

/**无限极分类扩展类库**/

class Article extends Common
{

	public function index()
	{

		if (request()->isPost()) {
			$data = request()->param();
			/**获取post参数**/
			$draw = $data['draw'];
			/**获取Datatables发送的参数 必要 这个值会直接返回给前台**/

			$order_column = $data['order']['0']['column'];
			/**从哪一列开始排序，默认从0开始**/
			$order_dir = $data['order']['0']['dir'];
			/**排序ase desc 升序或者降序**/
			/*******拼接mysql*****/
			$orderSql = "";
			if (isset($order_column)) {
				$i = intval($order_column);
				switch ($i) {
						/**插件默认从0开始排序 导致第一行禁用排序失效**/
						/***改进 从第二行开始排序***/
					case 1;
						$orderSql = " order by a.id " . $order_dir;
						break;
					case 2;
						$orderSql = " order by c.catname " . $order_dir;
						break;
					case 4;
						$orderSql = " order by u.uname " . $order_dir;
						break;
					case 5;
						$orderSql = " order by a.create_time " . $order_dir;
						break;
					case 6;
						$orderSql = " order by a.status " . $order_dir;
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
			$limitFlag = isset($start) && $length != -1;
			if ($limitFlag) {
				$limitSql = " LIMIT " . intval($start) . ", " . intval($length);
			}

			/**定义查询数据总记录数sql**/
			$sumSql = "SELECT count(id) as sum FROM 13_article as a where a.del=0";
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


			/**搜索条件**/
			$search = $data['search']['value'];
			/**获取前台传过来的过滤条件**/
			/**定义过滤条件查询过滤后的记录数sql**/
			$sumSqlWhere = " and (a.id LIKE '%" . $search . "%'  or a.title LIKE '%" . $search . "%')";


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

			$totalResultSql = "select a.id,u.uname,u.uid,a.title,a.create_time, a.del,a.status,a.catid,a.cover,a.uid,c.catid,c.catname from 13_article as a left join 13_cate as c on c.catid = a.catid left join 13_users as u on u.uid = a.uid where a.del=0";

			$group = " group by a.id";


			$dataResult = Db::query($totalResultSql . $sumSqlWhere . $group . $orderSql . $limitSql);

			foreach ($dataResult as $k => $v) {

				$dataResult[$k]['status'] = $v['status'] == 1 ? '启用' : '禁用';
				$dataResult[$k]['create_time'] = date('Y-m--d H:i:s', $v['create_time']);
				$dataResult[$k]['cover'] = json_decode($v['cover']);
			}


			/**Output 包含的是必要的**/
			echo json_encode(array(
				"draw" => intval($draw),
				"recordsTotal" => intval($recordsTotal),
				"recordsFiltered" => intval($recordsFiltered),
				"data" => $dataResult
			), JSON_UNESCAPED_UNICODE);
		} else {
			return view('index');
		}
	}

	public function update()
	{
		if (request()->isPost()) {
			$id = input('post.id');
			$cover = json_encode(input('post.cover/a'));

			/**图片以字段方式存储到数据库**/
			$rest = Db::name('article')->field('cover')->where(['id' => $id])->find();
			$pics = json_decode($rest['cover']);
			if (empty($cover) || $cover == 'null' || $cover == '') {
				/**判断提交过来的图片字段是否为空 如果为空则表示没有上传新的文件 不处理cover字段**/
				$data = [
					'catid' => input('post.catid'),
					'title' => input('post.title'),
					'content' => input('content'),
					'status' => input('post.status'),

					'create_time' => time(),

				];
			} else {
				/**如果上传了新文件则要覆盖掉原来的文件**/
				$data = [
					'catid' => input('post.catid'),
					'title' => input('post.title'),
					'content' => input('content'),
					'cover' => $cover,
					/**图片以字段方式存储到数据库**/
					'status' => input('post.status'),

					'create_time' => time(),

				];

				/**删除原来保存的图片**/
				if (!empty($pics)) {
					foreach ($pics as $pic) {
						$file = $_SERVER['DOCUMENT_ROOT'] . '/storage/' . $pic;

						if (file_exists($file)) {
							/*php方法 */
							unlink($file);
						}
					}
				}
			}
			$res = Db::name('article')->where(['id' => $id])->update($data);
			if ($res) {
				echo "true";
				exit;
			} else {
				echo "false";
				exit;
			}
		} else {
			$id = input('id');

			//获取所有新闻分类内容
			$cates = Db::name('cate')->field('catid,catname')->select();


			$list = Db::name('article')->alias('a')->leftJoin('13_cate c', 'a.catid = c.catid')->field('a.id,a.title,a.content, a.status,a.catid,a.cover,c.catname')->where(['a.id' => $id])->group('a.id')->find();

			$list['cover'] = json_decode($list['cover']);

			return view('update', ['cates' => $cates, 'info' => $list]);
		}
	}

	public function recycleBin()
	{

		if (request()->isPost()) {
			$data = request()->param();
			/**获取post参数**/
			$draw = $data['draw'];
			/**获取Datatables发送的参数 必要 这个值会直接返回给前台**/

			$order_column = $data['order']['0']['column'];
			/**从哪一列开始排序，默认从0开始**/
			$order_dir = $data['order']['0']['dir'];
			/**排序ase desc 升序或者降序**/
			/*******拼接mysql*****/
			$orderSql = "";
			if (isset($order_column)) {
				$i = intval($order_column);
				switch ($i) {
						/**插件默认从0开始排序 导致第一行禁用排序失效**/
						/***改进 从第二行开始排序***/
					case 1;
						$orderSql = " order by a.id " . $order_dir;
						break;
					case 2;
						$orderSql = " order by c.catname " . $order_dir;
						break;
					case 4;
						$orderSql = " order by u.uname " . $order_dir;
						break;
					case 5;
						$orderSql = " order by a.create_time " . $order_dir;
						break;
					case 6;
						$orderSql = " order by a.status " . $order_dir;
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
			$limitFlag = isset($start) && $length != -1;
			if ($limitFlag) {
				$limitSql = " LIMIT " . intval($start) . ", " . intval($length);
			}

			/**定义查询数据总记录数sql**/
			$sumSql = "SELECT count(id) as sum FROM 13_article as a where a.del=0";
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


			/**搜索条件**/
			$search = $data['search']['value'];
			/**获取前台传过来的过滤条件**/
			/**定义过滤条件查询过滤后的记录数sql**/
			$sumSqlWhere = " and (a.id LIKE '%" . $search . "%'  or a.title LIKE '%" . $search . "%')";


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

			$totalResultSql = "select a.id,u.uname,u.uid,a.title,a.create_time,a.del,a.status,a.catid,a.cover,a.uid,c.catid,c.catname from 13_article as a left join 13_cate as c on c.catid = a.catid left join 13_users as u on u.uid = a.uid where a.del=1";

			$group = " group by a.id";


			$dataResult = Db::query($totalResultSql . $sumSqlWhere . $group . $orderSql . $limitSql);

			foreach ($dataResult as $k => $v) {

				$dataResult[$k]['status'] = $v['status'] == 1 ? '启用' : '禁用';
				$dataResult[$k]['create_time'] = date('Y-m--d H:i:s', $v['create_time']);
				$dataResult[$k]['cover'] = json_decode($v['cover']);
			}


			/**Output 包含的是必要的**/
			echo json_encode(array(
				"draw" => intval($draw),
				"recordsTotal" => intval($recordsTotal),
				"recordsFiltered" => intval($recordsFiltered),
				"data" => $dataResult
			), JSON_UNESCAPED_UNICODE);
		} else {
			return view('recycleBin');
		}
	}



	public function upload()
	{
		$files = request()->file();
		try {
			validate(['image' => 'filesize:10240|fileExt:jpg,gif,png,jepg'])
				->check($files);
			$savename = [];
			foreach ($files as $file) {
				//config/filesystem.php 默认local  存储在runtime/storage下
				//我们需要改变磁盘使用::disk()此处存放在 public/storage/files/日期/下
				$savename[] = \think\facade\Filesystem::disk('public')->putFile('files', $file);
			}
			/**转义**/
			echo json_encode(str_replace('\\', '/', $savename));
			exit;
		} catch (\think\exception\ValidateException $e) {
			echo $e->getMessage();
		}
	}

	public function uploadtest()
	{
		$files = request()->file();


		foreach ($files as $file) {
			//config/filesystem.php 默认local  存储在runtime/storage下
			//我们需要改变磁盘使用::disk()此处存放在 public/storage/files/日期/下
			$savename[] = \think\facade\Filesystem::disk('public')->putFile('files', $file);
		}
		/**转义**/
		echo json_encode(str_replace('\\', '/', $savename));
		exit;
	}




	public function add()
	{
		if (request()->isPost()) {
			// dd(request()->param());
			$cover = json_encode(input('post.cover/a'));
			/**图片以字段方式存储到数据库**/
			$data = [
				'catid' => input('post.catid'),
				'title' => input('post.title'),
				'content' => input('content'),
				'cover' => $cover,
				/**图片以字段方式存储到数据库**/
				'status' => input('post.status'),

				'create_time' => time(),
				'uid' => session('uid')
			];

			$id = Db::name('article')->insertGetId($data);
			if ($id) {
				echo "true";
				exit;
			} else {
				echo "false";
				exit;
			}
		} else {
			$res = Db::name('cate')->field('catid,catname')->select();
			return view('add', ['cate' => $res]);
		}
	}



	public function del()
	{
		$id = input('id');
		//删除文章时，本地服务器上的封面图也要unlink
		$res = Db::name('article')->where('id', $id)->field('cover')->find();
		$covers = json_decode($res['cover']);
		//print_r($covers);
		if (!empty($covers)) {
			foreach ($covers as $cover) {
				$file = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $cover;
				if (file_exists($file)) {
					unlink('uploads/' . $cover);
				}
			}
		}

		$sql = "delete from 13_article where id='" . $id . "'";
		$rest = Db::execute($sql);
		if ($rest !== false) {
			echo "true";
		} else {
			echo "false";
		}
	}


	/**标题重复检测**/
	public function checktitle()
	{
		$title = input('post.title');
		$res = Db::name('article')->where('title', $title)->column('title');
		/**查询标题是否存在**/
		if ($res) {
			echo "false";
			exit;
		} else {
			echo "true";
			exit;
		}
	}





	/**移到回收站/还原**/
	public function recycle()
	{
		$id = input('post.id');
		//前端传回的type 的值为 1 or  0 
		// dump(333);
		$type = input('post.type');

		$res = Db::name('article')->where('id', $id)->update(['del' => $type]);
		if ($res) {
			echo "true";
			exit;
		} else {
			echo "false";
			exit;
		}
	}


	/**批量移到回收站**/
	public function bulkRecycle()
	{
		$id = input('post.ids/a');
		if (empty($id)) {
			echo historyTo('请选择需要移走的文章!');
			exit;
		}
		//数组转字符串
		$ids = implode(',', $id);

		$sql = "update 13_article set del='1' where id in($ids)";
		$res = Db::execute($sql);
		if ($res) {
			echo jumpTo('/article/index');
			exit;
		} else {
			echo jumpTo('/article/index');
			exit;
		}
	}

	/**批量还原**/
	public function revertAll()
	{
		$id = input('post.ids/a');
		if (empty($id)) {
			echo historyTo('请选择需要移除的文章!');
			exit;
		}
		$ids = implode(',', $id);

		//print_r($ids);
		//exit;
		$sql = "update 13_article set del='0' where id in($ids)";
		$res = Db::execute($sql);
		if ($res) {
			echo jumpTo('/article/recycleBin');
			exit;
		} else {

			echo historyTo('操作失败!');
			exit;
		}
	}
}
