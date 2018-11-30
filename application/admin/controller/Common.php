<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\facade\Session;

class Common extends Controller
{
	public function initialize()
	{
		$admin = Session::get('user');
		if(empty($admin)){
			$this->redirect('/admin/login/index');
		}
	}
		
}