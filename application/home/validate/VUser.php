<?php

namespace app\home\validate;

use think\Validate;

class VUser extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
        // "name"=>"require|unique:user",
        "password"=>"require|regex:/^\w{6,18}$/",
        "repassword"=>"require|confirm:password",
        "tel"=>"require|regex:/^1[3-8]{1}[0-9]{9}$/|unique:user",
        "paycode"=>"require|regex:/^\d{6}$/",
        "repaycode"=>"require|confirm:paycode",
        "invite_code"=>"require"

    ];
    
    /**
     * 定义错误信息
     * 格式：'字段名.规则名'	=>	'错误信息'
     *
     * @var array
     */	
    protected $message = [
        // "name.require"          =>"用户名不能为空",
        // "name.unique"           =>"用户名已存在",
        "password.require"      =>"密码不能为空",
        "password.regex"        =>"密码必须是6-18位的数字字母下划线",
        "repassword.require"    =>"重复密码不能为空",
        "repassword.confirm"    =>"重复密码与密码不一致",
        "tel.require"           =>"电话不能为空",
        "tel.regex"             =>"电话格式不对",
        "tel.unique"            =>"此电话已被注册",
        "paycode.require"       =>"请填写支付密码",
        'paycode.regex'         =>"支付密码格式不对，应该是6位纯数字",
        "repaycode.require"     =>"重复支付密码不能为空",
        "repaycode.confirm"     =>"重复支付密码与支付密码不一致",
        "invite_code.require"   =>"邀请码不能为空",
    ];
}
