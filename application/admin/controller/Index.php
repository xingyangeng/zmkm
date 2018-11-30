<?php


namespace app\Admin\controller;

use app\admin\controller\Common;
use think\Db;
use think\facade\Request;
class Index extends Common
{
   // 后台首页
    public function index() 
    {
        return view('Index/index');
    }

    // 首页默认
    public function indexMain()
    {
        echo 123;
    }
    //系统参数
    public function configShow()
    {
    	$data = Db::name('config')->find();
    	$this->assign('config',$data);
    	return view('Index/config');
    }
    //系统参数编辑
    public function configEdit()
    {
    	$data = Request::post();
    	$res = Db::name('config')->where('id',1)->update($data);
    	if($res){
    		return json(['status' => 0, 'message' => '修改成功']);
    	}else{
    		return json(['status' => 1, 'message' => '修改失败']);
    	}
    }
    //认购信息
    public function goods()
    {
    	$data = Db::name('goods')->where('id',1)->find();
    	$this->assign('goods',$data);
    	return view('Index/goods');
    }

    //认购信息修改
    public function goodsEdit()
    {
    	$data = Request::post();
    	$res  = Db::name('goods')->where('id',1)->update($data);
    	if($res){
    		return json(['status' => 0, 'message' => '修改成功']);
    	}else{
    		return json(['status' => 1, 'message' => '修改失败']);
    	}
    }
}