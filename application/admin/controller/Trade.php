<?php
namespace app\admin\controller;

use app\admin\controller\Common;
use think\Db;
use think\facade\Request;
class Trade extends Common
{
	//认购记录
	public function buyLog()
	{
		$search = Request::param('search');
		$sql    = Db::table('ri_buy_log')->alias('b')->join(['ybk_user'=>'u'],'b.user_id = u.id')->field('b.*,u.mobile_phone')->order('created_at','desc');
		if(!empty($search)){
			$sql->where('b.user_id|u.mobile_phone',$search);
		}
		$list = $sql->paginate(15)->each(function($item,$key){
			$item['exchange'] = floor(1 / $item['exchange'] * 1000000)/1000000;
			$item['created_at'] = date('Y-m-d H:i:s',$item['created_at']);
			return $item;
		});
		$this->assign('search',$search);
		$this->assign('list',$list);
		return view('Trade/buyLog');
	}

	//释放记录
	public function releaseLog()
	{
		$search = Request::param('search');
		$account= Request::param('account',0);
		$style  = Request::param('style',-1);
		$sql    = Db::table('ri_release_log')->alias('r')->join(['ybk_user'=>'u'],'r.user_id = u.id')->field('r.*,u.mobile_phone')->order('created_at','desc');
		if(!empty($search)){
			$sql->where('r.user_id|u.mobile_phone',$search);
		}
		if($account > 0){
			$sql->where('to_account',$account);
		}
		if($style >= 0){
			$sql->where('style',$style);
		}
		$list = $sql->paginate(15)->each(function($item,$key){
			$item['exchange'] = floor(1 / $item['exchange'] * 1000000)/1000000;
			return $item;
		});
		$this->assign('search',$search);
		$this->assign('account',$account);
		$this->assign('style',$style);
		$this->assign('list',$list);
		return view('Trade/releaseLog');

	}
	//回购记录
	public function hgLog()
	{
		$search = Request::param('search');
		$sql    = Db::table('ri_hg_log')->alias('b')->join(['ybk_user'=>'u'],'b.user_id = u.id')->field('b.*,u.mobile_phone')->order('created_at','desc');
		if(!empty($search)){
			$sql->where('b.user_id|u.mobile_phone',$search);
		}
		$list = $sql->paginate(15)->each(function($item,$key){
			$item['exchange'] = floor(1 / $item['exchange'] * 1000000)/1000000;
			return $item;
		});
		$this->assign('search',$search);
		$this->assign('list',$list);
		return view('Trade/hgLog');
	}


}