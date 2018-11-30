<?php

namespace app\home\controller;

use think\Controller;
use think\Request;
use think\Db;
use \Sms\Sendsms;
use think\Validate;
use app\home\model\Orders;
use app\home\model\User;
/**
 * @title 公共基类
 * @description 接口说明
 * @group 接口分组
 * @header name:token require:1 default: desc:秘钥(区别设置)
 */
class Common extends Controller
{
    protected $uid;
    /**
     * 构造方法，验证token
     *
     * @return \think\Response
     */
    public function __construct()
    {
        $req=request();
        $controller=strtolower($req->controller());
        $action=strtolower($req->action());
        $user=array("register","login","forgetpwd","sms");
        if($controller=="user" && in_array($action,$user)){
            return true;
        }
        //token验证
        // $token=$req->header("token")?$req->header("token"):"";
        // if(empty($token)){
        //     echo json(["msg"=>"token为空","code"=>101])->send();exit();

        // }
        // $res=Db::name("token")->where("token",$token)->find();
        // if($res){
        //     $this->uid=$token["token"];
        // }else{
        //     echo json(["msg"=>"设备更换","code"=>100])->send();exit();
        // }
    }

    // 封装成功的返回JSON格式数据方法
    public function succ($msg,$data=array())
    {
        $arr=array("msg"=>$msg,"code"=>1,"data"=>$data);
        return json($arr);
    }

    /**
     * 失败返回JSON格式数据的方法
     */
    public function err($msg,$data=array())
    {
        $arr=array("msg"=>$msg,"code"=>0,"data"=>$data);
        return json($arr);
    }

    /**
     * @title 短信接口
     * @description 接口说明
     * @author 开发者
     * @url /home/sms
     * @method POST
     * @module 前台模块

     * @param name:phone type:int require:1 default:1 other: desc:手机号
     * 
     *
     *
     */
    public function sms()
    {
        $post=request()->post();
        $phone=$post["phone"];
        $rule      = [
            'phone' => 'require|/^1[345678]\d{9}$/'
        ];
        $msg       = [
            'phone./^1[345678]\d{9}$/' => "手机号码格式错误"
        ];
        $validater = new Validate($rule, $msg);
        if (!$validater->check($post)) {
            return $this->err($validater->getError());
        }
        //生成短信验证码
        $code= make_num(6);
        $sms  =new  Sendsms();
        $res  = $sms->my_send($phone, "您本次的验证码为" . $code . ",请在150秒内完成操作!【FTC】");
        $res  = substr($res, 7, 1);

        // dump($res);die();
        if ($res == 0) {
                $mco=Db::name("msgcode")->where("tel",$phone)->find();
                if($mco){
                    Db::name("msgcode")->where("tel",$phone)->data(["code"=>$code,"expire_time"=>time()+150])->update();
                }else{
                    Db::name("msgcode")->create(["tel"=>$phone,"code"=>$code,"expire_time"=>time()+150]);
                }
            // /*********** 使用session存储验证码, 方便比对, md5加密   ***********/
            // $md5_code = md5($post['user_phone'] . '_' . md5($code));
            // session($post['user_phone'] . '_code', $md5_code);
            // /*********** 使用session存储验证码的发送时间  ***********/
            // session($post['user_phone'] . '_last_send_time', time());
            // $data['status'] = 1;
            // $data['info']   = "短信发送成功!";
            //     return $data;
            return $this->succ("短信发送成功");
        } else {
            // $data['status'] = 0;
            // $data['info']   = "短信发送失败!";
            // return $data;
            return $this->err("短信发送失败");
        }
    }
    
    /**
     * 测试方法
     */
    public function ceshi()
    {   
        $res=Db::name("orders")->where(["game_id"=>2,"status"=>1])->group("uid")->column("uid");
        dump($res);
        
        // echo time()+150;
        // $sql=Db::name("orders")->order("created_time desc")->buildSql();
        // $lucky=Db::table($sql . "a")->where(["a.game_id"=>2])->group("a.uid")->order("created_time desc")->column("uid");
        // array_shift($lucky);  
        // dump($lucky);

        // $luckyMoney=Db::name("lucky_jackpot")->order("created_time desc")->limit(1)->value("able_money");
        // $lucky_money= !empty($luckyMoney) ? $luckyMoney :0;
        // echo $lucky_money;

        // $path=Db::name("user_path")->where("uid",17)->value("path");
        // $arr=array_slice(explode(",",$path),-5,4);
        // dump($arr);
    }
    
    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
