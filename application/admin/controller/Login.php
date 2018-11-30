<?php


namespace app\Admin\controller;

use think\Controller;
use think\Db;
use think\facade\Session;

class Login extends Controller
{
    public function index()
    {
        # 如果有session就跳到主页
        if(Session::has('user'))
            $this->redirect('/admin/Index/index');

        # 1. 获取用户名密码(username, password)
        $user = input('post.');

        if(!$user)
        # 返回登陆页面
            return view('Login/login');
        if(!captcha_check($user['captcha'])){
            return ['status' => 1, 'massage' => '验证码不正确'];
        }

        $user['user_pwd'] = md5($user['user_pwd']);
        $res = Db::name('admin')->where(['user_name'=>$user['user_name'],'password'=>$user['user_pwd']])->find();
        if($res){
            Session::set('user.id' , $res['id']);
            return json(['status' => 0, 'massage' => '登录成功']); 
        }else{
            return json(['status' => 1, 'massage' => '用户名和密码错误']); 
        }
    }

 
    public function login_out()
    {
        Session::delete('user');

        $this -> redirect('admin/login/index');
    }


}