<?php

namespace app\home\controller;

use think\Controller;
use think\Request;
use app\home\model\User;
use think\Db;
use think\Validate;
use app\home\validate\VUser;


/**
 * @title 用户相关
 * @description 接口说明
 * @group 接口分组
 * @header name:token require:1 default: desc:秘钥(区别设置)
 */
class UserController extends Common
{
    /**
     * @title 前台注册接口  不需要token
     * @description 接口说明
     * @author 开发者
     * @url /home/register
     * @method POST
     * @module 前台模块

     * @param name:tel type:int require:1 default:1 other: desc:用户名
     * @param name:telcode type:int require:1 default:1 other: desc:短信验证码
     * @param name:password type:string require:1 default:1 other: desc:密码
     * @param name:repassword type:string require:1 default:1 other: desc:重复密码
     * @param name:paycode type:int require:1 default:1 other: desc:支付密码
     * @param name:repaycode type:int require:1 default:1 other: desc:重复支付密码
     * @param name:invite_code type:int require:1 default:1 other: desc:邀请码
     * 
     *
     *
     */
    public function register()
    {
        $req=request();
        if(!$req->isPost()){
            return $this->err("请求错误");
        }
        $post=$req->post();
        $validate= new VUser;
        if(!$validate->check($post)){
            return $this->err($validate->getError());
        }
        //验证手机验证码
        $telcode=$req->post("telcode");
        $data["tel"]=$req->post("tel");
        if(Db::name("user")->where("tel",$data["tel"])->find()){
            return $this->err("此手机号已被注册");
        }
        $check=$this->checkcode($data['tel'],$telcode);
        if($check){
            return $this->err($check['info']);
        }
        
        //验证邀请码
        $invite=trim($req->post("invite_code"));
        if($parent=Db::name("user")->where("invite_code",$invite)->find()){
            $data["parent_id"]=$parent["id"];
        }else{
            return $this->err("请填写正确的邀请码");
        }
        Db::startTrans();
        $data["name"]="a".$data["tel"];
        $data["password"]=md5(trim($req->post("password")));
        $data["paycode"]=md5($req->post("paycode"));
        $data["created_time"]=time();
        // 生成邀请码
        // $data["invite_code"]=make_num(6);
        // if(Db::name("user")->where("invite_code",$data["invite_code"])->find()){
        //         $data["invite_code"]=make_num(6);
        // }
        $data["invite_code"]=$this->setinvite();
        $res=User::create($data);
            // 获取邀请人的脉络
        $parent=Db::name("user_path")->where("uid",$res->parent_id)->find();
            //添加新账户的脉络
        $result=Db::name("user_path")->insert([
                "uid"   =>$res->id,
                "pid"   =>$res->parent_id,
                "path"  =>$parent["path"].$res->parent_id.","
            ]);
        if($res && $result){
            Db::commit();
            return $this->succ("注册成功");
        }else{
            Db::rollback();
            return $this->err("注册失败，请重新注册");
        }  

    }


    //前台登陆方法
    /**
     * @title 前台登陆   不需要token
     * @description 接口说明
     * @author 开发者
     * @url /home/login
     * @method POST
     * @module 前台模块

     * @param name:phone type:int require:1 default:1 other: desc:用户名
     * @param name:password type:string require:1 default:1 other: desc:密码
     * 
     *
     *
     */
    public function login(){
        $post=request()->post();
        $phone=$post["phone"];
        $pwd=$post["password"];
        $data=User::where("tel",$phone)->find();
        if($data){
            if(md5($pwd)==$data["password"]){
                $res=Db::name("token")->where("tel",$phone)->find();
                //重新生成token
                $token=md5($phone.$pwd.mt_rand());
                if($res){
                    Db::name("token")->where("tel",$phone)->data(["token"=>$token])->update();
                }else{
                    Db::name("token")->data(["tel"=>$phone,"token"=>$token])->insert();
                }
                return $this->succ("登陆成功");
            }else{
                return $this->err("抱歉！您输入的密码错误");
            }
        }else{
            return $this->err("抱歉！您输入的手机号错误");
        }

    }



    /**
     * @title 忘记密码和修改密码接口    不需要token
     * @description 接口说明
     * @author 开发者
     * @url /home/forgetpwd
     * @method POST
     * @module 前台模块

     * @param name:phone type:int require:1 default:1 other: desc:手机号
     * @param name:code type:int require:1 default:1 other: desc:手机验证码
     * @param name:password type:string require:1 default:1 other: desc:密码
     * @param name:repassword type:string require:1 default:1 other: desc:重复密码
     * 
     *
     *
     */
    public function forgetpwd(){
        $post=request()->post();
        
        $rule      = [
            'phone' => 'require|/^1[345678]\d{9}$/',
            'code'  => 'require|regex:/^\d{6}$/',
            "password"=>'require|regex:/^\w{6,18}$/',
            'repassword'=>'require|confirm:password'
        ];
        $msg       = [
            'phone.require'    =>"请填写手机号码",
            'phone./^1[345678]\d{9}$/' => "手机号码格式错误",
            'code.require'     =>'验证码不能为空',
            'code.regex'       =>'验证码必须是六位数字',
            'password.regex'       =>'密码必须为6-18位的字母数字下划线组成',
            'password.require' =>'密码不能为空',
            'repassword.require'=>"重复密码不能为空",
            'repassword.confirm'=>"重复密码与密码不一致"
        ];
        $validater = new Validate($rule, $msg);
        if (!$validater->check($post)) {
            $data['info']   = $validater->getError();
            return $this->err($data['info']);
        }
        $phone=trim($post["phone"]);
        $code=trim($post["code"]);
        $pass=trim($post['password']);
        //查看此账号是否存在
        if(!User::where("tel",$phone)->find()){
            return $this->err("此手机号码并未注册，请注册!");
        }
        //验证手机短信
        $result=$this->checkcode($phone,$code);
        if($result){
            return $this->err($result['info']);
        }
        $res=User::where("tel",$phone)->data(["password"=>md5($pass)])->update();
        if($res){
            return $this->succ("密码修改成功");
        }else{
            return $this->err('密码修改失败');
        }
    }


    
    /**
     * @title 修改支付密码
     * @description 接口说明
     * @author 开发者
     * @url /home/updatepaycode
     * @method POST
     * @module 前台模块

     * @param name:phone type:int require:1 default:1 other: desc:电话
     * @param name:code type:int require:1 default:1 other: desc:手机验证码
     * @param name:paycode type:int require:1 default:1 other: desc:支付密码
     * @param name:repaycode type:int require:1 default:1 other: desc:重复支付密码
     */
    public function updatePaycode(){
        // $uid=$this->uid;
        $uid=13;
        $post=request()->post();
        $rule      = [
            'phone' => 'require|/^1[345678]\d{9}$/',
            'code'  => 'require|regex:/^\d{6}$/',
            "paycode"=>'require|regex:/^\d{6}$/',
            'repaycode'=>'require|confirm:paycode'
        ];
        $msg       = [
            'phone.require'    =>"请填写手机号码",
            'phone./^1[345678]\d{9}$/' => "手机号码格式错误",
            'code.require'     =>'验证码不能为空',
            'code.regex'       =>'验证码必须是六位数字',
            'paycode.regex'       =>'支付密码必须为6位数字组成',
            'paycode.require' =>'支付密码不能为空',
            'repaycode.require'=>"重复支付密码不能为空",
            'repaycode.confirm'=>"重复支付密码与支付密码不一致"
        ];
        $validater = new Validate($rule, $msg);
        if (!$validater->check($post)) {
            $vali['info']   = $validater->getError();
            return $this->err($vali['info']);
        }

        $phone=trim($post["phone"]);
        $code=trim($post["code"]);
        $paycode=trim($post['paycode']);
        $user=User::where("tel",$phone)->find();
        if($user["id"] != $uid){
            return $this->err("此电话号码不是此账号绑定的电话号码！");
        }
        $result=$this->checkcode($phone,$code);
        if($result){
            return $this->err($result['info']);
        }
        $data=User::get($uid);
        $data->paycode=md5($paycode);
        $res=$data->save();
        if($res){
            return $this->succ("支付密码修改成功");
        }else{
            return $this->err("修改失败，请重新修改！");
        }
    }
    

    /**
     * @title 相互转豆接口
     * @description 接口说明
     * @author 开发者
     * @url /home/transfer
     * @method POST
     * @module 前台模块

     * @param name:name type:int require:1 default:1 other: desc:对方账号
     * @param name:money type:int require:1 default:1 other: desc:金豆数量
     * @param name:paycode type:int require:1 default:1 other: desc:支付密码
     */
    public function transfer(){
        $post=request()->post();
        $uid=$this->uid;
        $money=trim($post["money"]);
        $name=trim($post["name"]);
        // $uid=13;
        $user_id=User::where("name",$name)->value("id");
        if($uid==$user_id){
            return $this->err("您不能给自己转账");
        }
        $data=User::where("id",$uid)->field(["balance","paycode"])->find();
        if($money > $data["balance"]){
            return $this->err("您的余额不足");
        }
        if(md5($post["paycode"]) != $data["paycode"]){
            return $this->err("支付密码错误");
        }
        $oid=User::where("name",$post["name"])->value("id");
        if(!$oid){
            return $this->err("此账户并不存在");
        }
        //获取对方的上三代推荐人用户ID
        $path=Db::name("user_path")->where("uid",$oid)->value("path");
        $arr=array_slice(explode(",",$path),-5,4);
        if(!in_array($uid,$arr)){
            return $this->err("您只能转账给自己的下三代推荐人");
        }
        Db::startTrans();
        // 减少自己余额 增加对方余额
        $res1=User::where("id",$uid)->setDec("balance",$money);
        
        $res2=User::where("id",$oid)->setInc("balance",$money);
        //添加转账日志
        $res3=Db::name("transfer_log")->insert([
            "uid"=>$uid,
            "oid"=>$oid,
            "money"=>$money,
            "created_time"=>date("Y-m-d H:i:s",time())
        ]);
        if($res1 && $res2 && $res3){
            Db::commit();
            return $this->succ("转账成功");
        }else{
            Db::rollback();
            return $this->err("转账失败，请重试！");
        }

    }


    public function checkcode($phone,$code)
    {
        $res=Db::name("msgcode")->where("tel",$phone)->find();
        if(empty($res)){
            $data["info"]="并未向您的手机发送短信，请点击按钮发送！";
            $data["status"]=0;
            return $data;
        }
        if($code !=$res["code"]){
            $data["info"]="验证码错误";
            $data["ststus"]=0;
            return $data;
        }
        if(time() > $res["expire_time"]){
            $data["info"]="验证码过期,请重新点击按钮发送验证码";
            $data["ststus"]=0;
            return $data;
        }
        
    }

    //生成唯一邀请码方法
    public function setinvite(){
        $str=make_num(6);
        if(Db::name("msgcode")->where("code",$str)->find()){
            $this->setinvite();
        }else{
            return $str;
        }
    }
   
}
