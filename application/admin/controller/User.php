<?php
namespace app\admin\controller;

use app\admin\controller\Common;
use think\Db;
use think\facade\Request;
class User extends Common
{
	private $field = 'id,username,mobile_phone,wallet_address,user_money,sc_money,eth_money,level,invite_id,total,created_at';
	//用户列表
	public function index()
	{
		$search = Request::param('search');
		
		$sql = Db::table('ybk_user')->field($this->field)->where('is_delete',0);
		//筛选条件
		if($search){
			$sql->where('mobile_phone',$search)->whereOr('id',$search);
		}

		$data = $sql->paginate(15)->each(function($item,$key){
			$item['hg_money'] = Db::name('release_log')->where(['user_id'=>$item['id'],'to_account'=>1])->sum('sy_num');
			return $item;
		});

		$this->assign('search',$search);
		$this->assign('user_info',$data);
		return view('User/userList');
	}
	//用户信息
	public function userInfo() {
		$id = Request::param('id');
		$user = Db::table('ybk_user')->field($this->field.',password,transpassword')->where('id',$id)->find();
		$this->assign('user', $user);
		return view('User/userInfo');
		
	}
	//用户编辑
	public function editUser() {
		$data = Request::post();

		if(!preg_match("/^1[34578]{1}\d{9}$/",$data['mobile_phone'])){
			return json(['status' => 1, 'message' => '请填写正确的手机号']);
		}
		//手机号判断
		$phone = Db::table('ybk_user')->where('id',$data['id'])->value('mobile_phone');
		if($phone == $data['mobile_phone']){
			unset($data['mobile_phone']);
		}else{
			$res   = Db::table('ybk_user')->where('mobile_phone',$data['mobile_phone'])->find();
			if($res){
				return json(['status' => 1, 'message' => '手机号已存在']);
			}
		}
		//密码判断
		if (empty($data['password'])) {
			unset($data['password']);
		}else{
			$data['password'] = md5($data['password']);
		}

		if (empty($data['transpassword'])) {
			unset($data['transpassword']);
		}else{
			$data['transpassword'] = md5($data['transpassword']);
		}
		//账户判断
		if(empty($data['user_money']) || $data['user_money'] < 0){
			unset($data['user_money']);
		}
		if(empty($data['sc_money']) || $data['sc_money'] < 0){
			unset($data['sc_money']);
		}
		if(empty($data['eth_money']) || $data['eth_money'] < 0){
			unset($data['eth_money']);
		}
		$result = Db::table('ybk_user')->where('id',$data['id'])->update($data);
		if ($result) {
			return json(['status' => 0, 'message' =>'修改成功!']);
		} else {
			return json(['status' => 1, 'message' =>'修改失败!']);
		}
	}
	//删除用户
	public function delete_user()
	{
		$id  = Request::param('id');
		$res = Db::table('ybk_user')->where(['id'=>$id,'is_delete'=>0])->find();
		if(!$res){
			return json(['status' => 1, 'message' =>'用户不存在']);
		}
		$res = Db::table('ybk_user')->where('id',$id)->update(['is_delete'=>1]);
		if($res){
			return json(['status' => 0, 'message' =>'删除成功!']);
		}else{
			return json(['status' => 1, 'message' =>'删除成功!']);
		}

	}


}