<?php
namespace app\admin\controller;

use app\admin\controller\Common;
use think\Db;
use think\facade\Request;
class Feedback extends Common
{
	//反馈列表
	public function index()
	{
		$search = Request::param('search');
		$style  = Request::param('style',-1);
		
		$sql    = Db::table('ri_feedback')->alias('b')->join(['ybk_user'=>'u'],'b.user_id = u.id')->field('b.*,u.mobile_phone')->order('created_at','desc');
		if(!empty($search)){
			$sql->where('b.user_id|u.mobile_phone',$search);
		}
		if($style >= 0){
			$sql->where('b.style',$style);
		}
		$list = $sql->paginate(15);
		$this->assign('search',$search);
		$this->assign('style',$style);
		$this->assign('list',$list);
		return view('Feedback/index');
	}
	//修改状态
	public function changeState()
	{
		$id  = Request::param('id');
		$res = Db::name('feedback')->where(['id'=>$id])->find();
		if(!$res){
			return json(['status' => 1, 'message' =>'信息不存在']);
		}
		$res = Db::name('feedback')->where(['id'=>$id])->update(['status'=>1]);
		if($res){
			return json(['status' => 0, 'message' =>'修改成功']);
		}else{
			return json(['status' => 1, 'message' =>'修改失败']);
		}

	}

}