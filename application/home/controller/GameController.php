<?php

namespace app\home\controller;

// use think\Controller;
use think\Request;
use app\home\model\User;
use app\home\model\Games;
use app\home\model\Orders;
use think\Db;
class GameController extends Common
{
    //用户刚进游戏返回接口
    public function index(){

    }
    /**
     * @title 购买钥匙接口
     * @description 接口说明
     * @author 开发者
     * @url /home/buykey
     * @method POST
     * @module 前台模块

     * 
     * @param name:num type:int require:1 default:1 other: desc:购买key数量
     * @param name:paycode type:int require:1 default:1 other: desc:支付密码
     // * @param name:gid type:int require:1 default:0 other: desc:游戏ID
     */
     public function buyKey(){
        // $uid=$this->uid;
        $uid=1;
        $post=request()->post();
        $keynum = trim($post["keynum"]);
        $paycode=trim($post["paycode"]);
        $set=Db::name("system_set")->where("id",1)->find();
        $money= $keynum * $set["key_price"];
        $user=User::get($uid);
        if($money > $user->balance){
            return $this->err("您的金额不足");
        }
        if(md5($paycode) != $user->paycode){
            return $this->err("支付密码错误");
        }
        //判断游戏是否开启
        $result=Games::where("status",1)->find();
        // $gid = !empty(trim($post["gid"])) ? trim($post["gid"]) : 0;
        // $result=Games::order("id","desc")->limit(1)->find();
        if(!$result){
            $game_id=$this->createGame($money,$uid);
        }else{
            $game_id=$this->updateGame($result->id,$money,$uid);
        }
        
       // 给推荐人分成
        $parents=$this->giveParents($money,$game_id,$uid,$set);
        
        //最后返回结果
        if($game_id && $parents){
            $user->balance=["dec",$money];
            $user->save();
            //钱包日志
            Db::name("wallet_log")->insert([
                "uid"   =>$uid,
                "gid"   =>$game_id,
                "money" =>$money,
                "type"  =>1,
                "created_time"=>date("Y-m-d H:i:s",time())
            ]);
            return $this->succ("KEY购买成功");
        }else{
            return $this->err("购买失败");
        }        

    }

//开始新一轮游戏的方法
    private function createGame($money,$uid){
        $time=date("Y-m-d H:i:s",time());
        $set=Db::name("system_set")->where("id",1)->find();
        $game=Games::create([
            "created_time"          =>$time,//游戏开始时间
            "updated_time"          =>$time,//最新投注时间
            "expire_time"           =>$time+86400,//24小时
            "key_num"                =>$money/$set["key_price"],//本局游戏KEY数量
            "foundation"            =>$money*$set["foundation"],//基金会分成
            "artisan"               =>$money*$set["artisan"],//技术分成
            "team"                  =>$money*$set["team"],//项目组分成
            "jackpot"               =>$money*$set["jackpot"],//奖池
            "lucky_jackpot"         =>$money*$set["lucky_jackpot"],//幸运奖池
            "last_key_id"           =>$uid,//最后投注人ID
        ]);
        
        //生成此用户新的订单表
        $result= Orders::create([
            "uid"           =>$uid,
            "game_id"       =>$game->id,
            "total"         =>$money/$set["key_price"],
            "give_money"    =>$money*$set["noout"],
            "out_money"     =>$money*$set["multiple"],
            "created_time"  =>$time,
            "updated_time"  =>$time
        ]);
        
        //投注金额60%分给自己
        User::where("id",$uid)->setInc("balance",$money*$set["noout"]);
        //添加提成日志
        $this->walletLog($uid,$game->id,0,$money,1)
        return $game->id;
    }


 //投注后的方法,修改游戏表 判断游戏用户表是否有此用户来更新或新建
    private function updateGame($gid,$money,$uid){
        //更新游戏表
        $time=date("Y-m-d H:i:s",time());
        $set=Db::name("system_set")->where("id",1)->find();
        $game=Games::get($gid);
        $game->updated_time     =$time;
        $game->expire_time      =$game->expire_time-$time<1800 ? $game->expire_time+1800 :$game->expire_time;
        $game->key_num           =["inc",$money/$set["key_price"]];
        $game->foundation       =["inc",$money*$set["foundation"]];
        $game->artisan          =["inc",$money*$set["artisan"]];
        $game->team             =["inc",$money*$set["team"]];
        $game->jackpot          =["inc",$money*$set["jackpot"]];
        $game->lucky_jackpot    =["inc",$money*$set["lucky_jackpot"]];
        $game->last_key_id      =$uid;
        $game->save();


        //生成此用户新的订单表
        $result= Orders::create([
            "uid"           =>$uid,
            "game_id"       =>$gid,
            "total"         =>$money/$set["key_price"],
            "give_money"    =>0,
            "out_money"     =>$money*$set["multiple"],
            "created_time"  =>$time,
            "updated_time"  =>$time
        ]);
        //判断幸运奖池是否超过两万
        $lucky_jackpot=Games::where("id",$gid)->value("lucky_jackpot");
        // 获取幸运奖池分红配置
        if($lucky_jackpot >=20000){
            //找到所有未出局并且复投的订单用户
            $lucky=Db::name("orders")->where(["game_id"=>2,"status"=>1])->group("uid")->column("uid");
            //随机选取20位幸运者分红
            $lucker=array_rand($lucky,20);
            foreach ($lucker as $k=>$v){
                User::where("id",$v)->setInc("balance",$lucky_jackpot/20);
                $this->walletLog($v,$gid,$uid,$lucky_jackpot/20,3)
            }
            //判断是否有20位 没有的话分完剩余的给平台
            $length=count($lucker);
            if($length < 20){
                //剩余的给平台
                Games::where("id",$gid)->setInc("platform",$lucky_jackpot/20*(20-$length));
                //添加溢出日志
            }
                
            
            //幸运奖池减去20000
            Games::where("id",$gid)->setDec("lucky_jackpot",20000);
            //幸运奖开奖次数加1
            Games::where("id",$gid)->setInc("lucky_times",1);
        }
        
        // 投注金额60%分给前面静态没有出局者加权平均分红奖金
        // 获取订单表中此轮游戏未出局的所有订单
        $orders=Orders::where(["status"=>1,"game_id"=>$gid])->field(["id","uid","give_money","out_money","total"])->select();
        $num=Orders::where(["status"=>1,"game_id"=>$gid])->sum("total");
        $every=$money*$set["noout"]/$num;//充值金额的60%再除于未出局者总投入KEY数量 得到每个KEY该分多少钱
        //遍历给此轮游戏未出局者加权平分
        foreach ($orders as $key=>$value){
           $overflow=$value->give_money + $every*$value->total - $value->out_money;
           $pid=User::where("id",$value->uid)->value("parent_id");
           if($overflow>=0){
            Orders::where("id",$value->id)->update(["give_money"=>$value->out_money,"status"=>0]);//用户出局
            //多余的给游戏表溢出字段
            if($overflow>0){
                Games::where("id",$gid)->setInc("overflow",$overflow);
                //添加溢出日志
            }
            //每人分97%
                User::where("id",$value->uid)->setInc("balance",($value->out_money-$value->give_money)*$set["out_self"]);
             //分红日志   
                $this->walletLog($value->uid,$gid,$uid,($value->out_money-$value->give_money)*$set["out_self"],8);
            //上级分3%
                User::where("id",$pid)->setInc("balance",($value->out_money-$value->give_money)*$set["out_parent"]);
            //分红日志
                $this->walletLog($pid,$gid,$value->uid,($value->out_money-$value->give_money)*$set["out_parent"],9);
           }else{
               Orders::where("id",$value->id)->setInc("give_money",abs($overflow));
               //给用户和上级分成
               User::where("id",$value->uid)->setInc("balance",abs($overflow)*$set["out_self"]);
               //分红日志
               $this->walletLog($value->id,$gid,$uid,$abs($overflow)*$set["out_self"],8);
               User::where("id",$pid)->setInc("balance",abs($overflow)*$set["out_parent"]);
               // 分红日志
                $this->walletLog($pid,$gid,$value->id,abs($overflow)*$set["out_parent"],9);
           }
        }

         
         return $gid;
    }


//每一次投注，给一二三级推荐人奖金 没有
    private function giveParents($money,$gid,$uid,$set){
        $pid=User::where("id",$uid)->value("parent_id");
        if(!$pid){
            Games::where("id",$gid)->setInc("platform",$money*($set["level1"]+$set["level2"]+$set["level3"]));
        }else{
            //给一级推荐人
            User::where("id",$pid)->setInc("balance",$money*$set["level1"]);
            $this->walletLog($pid,$gid,$uid,$money*$set["level1"],5);
            //二级推荐人
            $sid=User::where("id",$pid)->value("parent_id");
            if(!$sid){
                Games::where("id",$gid)->setInc("platform",$money*($set["level2"]+$set["level3"]));
            }else{
                User::where("id",$sid)->setInc("balance",$money*$set["level2"]);
                $this->walletLog($sid,$gid,$uid,$money*$set["level2"],6);
                //三级推荐人
                $tid=User::where("id",$sid)->value("parent_id");
                if(!$tid){
                    Games::where("id",$gid)->setInc("platform",$money*$set["level3"]);
                }else{
                    User::where("id",$tid)->setInc("balance",$money*$set["level3"]);
                    $this->walletLog($sid,$gid,$uid,$money*$set["level3"],7);
                }
            }
        }
        return true;
    }
    

 //游戏结束相关操作
    //需要获奖者ID 和游戏ID 
    public function gameOver(){
        $post=request()->post();
        $id=trim($post["last_id"]);
        $gid=trim($post["gid"]);
        $game=Games::get($gid);
        $jackpot=$game->jackpot;
        $lucky_jackpot=$game->lucky_jackpot;
        // 获取中奖配置数据
        $set=Db::name("game_set")->where("id",1)->find();
        //给中奖者分红
        User::where("id",$id)->setInc("balance",$jackpot*$set["winner"]);
        $this->walletLog($id,$gid,0,$jackpot*$set["winner"],0);
        //给中奖者一二三代推广人分成  没有则分给平台
        $parents=$this->getParentId($id);
        switch(cuunt($parents)){
            case 3:
            for($i=0;$i<count($parents);$i++){
                User::where("id",$parents[$i])->setInc("balance",$set["level".($i-1)]*$jackpot);
                 // $this->walletLog($parents[$i],$gid,$,$jackpot*$set["winner"],0);
            }
            break;
            case 2:
                for($i=0;$i<count($parents);$i++){
                    User::where("id",$parents[$i])->setInc("balance",$set["level".($i-1)]*$jackpot);
                }
                Games::where("id",$gid)->setInc("platform",$jackpot*$set["level3"]);
            break;
            case 1:
                User::where("id",$parents[0])->setInc("balance",$set["level1"]*$jackpot);
                Games::where("id",$gid)->setInc("platform",$jackpot*($set["level2"]+$set["level3"]));
            break;
            case 0:
                Games::where("id",$gid)->setInc("platform",$jackpot*($set["level1"]+$set["level2"]+$set["level3"]));
            break;
        }
        //给最后投入的96个人每人分红0.5%
            // 获取最后投入的96个人
         $sql=Db::name("orders")->order("created_time desc")->buildSql();
        $lucky=Db::table($sql . "a")->where(["a.game_id"=>$gid])->group("a.uid")->order("created_time desc")->limit($set["other_num"]+1)->column("uid");
        array_shift($lucky);
        $time=date("Y-m-d H:i:s");
        // 判断是否有96个人得到分红 没有就分给平台
        
            foreach ($lucky as $k=>$v){
                User::where("id",$v)->setInc("balance",$jackpot*$set["other"]);
                //日志
                Db::name("gameover_log")->insert([
                    "uid"       =>$v,
                    "gid"       =>$gid,
                    "money"     =>$jackpot*$set["other"],
                    "created_time"=>$time
                ]);
            }
        if(count($lucky) < $set["other_num"]){
            $len = $set["other_num"]-count($lucky);
            
            //其余的分给平台
            Games::where("id",$gid)->setInc("platform",$len*$set["other"]);
        }

        return $this->succ("游戏结束！");
    }

//获取此用户的前三级推广者
    private function getParentId($id){
        $path=Db::name("uid",$uid)->value("path");
        $arr=explode(",",$path);
        array_pop($arr);
        $arr=array_reverse($arr);
        $res=array_slice($arr,0,3);
        return $res;
    }
//添加提成的方法
    private function walletLog($uid,$gid,$oid,$money,$type){
        $time=date("Y-m-d H:i:s",time());
        Db::name("wallet_log")->insert([
            "uid"       =>$uid,
            "gid"       =>$gid,
            "oid"       =>$oid,
            "money"     =>$money,
            "type"      =>$type,
            "created_time"=>$time()
        ]);
    }
}
