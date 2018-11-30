<?php

namespace app\home\controller;

use think\Controller;
use think\Request;
use app\home\model\User;
use app\home\model\Games;
use app\home\model\GameUsers;
use think\Db;
class Game extends Common
{
	//购买KEY接口
    //传入两个参数 keynum  paycode
    public function buykey(){
    	// $uid=$this->uid;
    	$uid=13;
        $post=request()->post();
        $keynum = trim($post["keynum"]);
        $paycode=trim($post["paycode"]);
        $key_price=Db::name("system_set")->where("id",1)->value("key_price");
        $money= $keynum * $key_price;
        $user=User::get($uid);
        if($money > $user->balance){
        	return $this->err("您的金额不足");
        }
        if(md5($paycode) != $user->paycode){
        	return $this->err("支付密码错误");
        }
        $res=Games::where("status",1)->find();
        if($res){
        	$result=$this->updateGame($res->id,$money,$uid);
        }else{
        	$result=$this->createGame($money,$uid);
        }
        if($result){
        	$user->balance=["dec",$money];
        	$user->save();
        	return $this->succ("KEY购买成功");
        }else{
        	return $this->err("购买失败");
        }        

    }

    //开始一轮游戏的方法
    private function($money,$uid){
    	$time=time();
    	$set=Db::name("system_set")->where("id",1)->find();
    	$game=Games::create([
    		"created_time"			=>$time,//游戏开始时间
    		"updated_time"			=>$time,//最新投注时间
    		"expire_time"			=>$time+86400,//24小时
    		"keynum"				=>$money/$set["key_price"],//本局游戏KEY数量
    		"foundation"			=>$money*$set["foundation"],//基金会分成
    		"artisan"				=>$money*$set["artisan"],//技术分成
    		"team"					=>$money*$set["team"],//项目组分成
    		"jackpot"				=>$money*$set["jackpot"],//奖池
    		"lucky_jackpot"			=>$money*$set["lucky_jackpot"],//幸运奖池
    		"last_key_id"			=>$uid,//最后投注人ID
       	]);
    	//添加用户到本局游戏用户表
    	GamUsers::create([
    		"uid"				=>$uid,
    		"game_id"			=>$game->id,
    		"total"				=>$money,
    		"available"			=>$money,
    		"out_money"			=>$money*$set["multiple"],
    		"created_time"		=>$time,
    		"updated_time"		=>$time,

    	]);

    	//给自己分60%
    	User::where("id",$uid)->setInc("balance",$money*$noout);

    	return true;
    }


    //投注后的方法,修改游戏表 判断游戏用户表是否有此用户来更新或新建
    private function($gia,$money,$uid){
    	//更新游戏表
    	$time=time();
    	$set=Db::name("system_set")->where("id",1)->find();
    	$game=Games::get($gid);
    	$game->updated_time		=$time;
    	$game->expire_time 		=$game->expire_time-$time<1800 ? $game->expire_time+1800 :$game->expire_time;
    	$game->keynum			=$money*$set["key_price"];
    	$game->foundation		=["inc",$money*$set["foundation"]];
    	$game->artisan			=["inc",$money*$set["artisan"]];
    	$game->team				=["inc",$money*$set["team"]];
    	$game->jackpot 			=["inc",$money*$set["jackpot"]];
    	$game->lucky_jackpot	=["inc",$money*$set["lucky_jackpot"]];
    	$game->last_key_id		=$uid;
    	$game->save();

    	//判断此轮游戏此用户是否投注 
    	$guser=GameUsers::where("uid",$uid)->where("game_id",$gid)->find();
    	if($res){
    		// 已投注则为复投
    		$guser->total 		=["inc",$money];
    		$guser->availbale 	=["inc",$money];
    		$guser->out_money 	=["inc",$money*$set["multiple"]];
    		$guser->updated_time=$time;
    		$guser->is_out 		=0;
    		$guser->betting_times		=['inc',1];
    		$guser->save();
	    	}else{
	    		// 未投注则为初投
    		GamUsers::create([
    		"uid"				=>$uid,
    		"game_id"			=>$game->id,
    		"total"				=>$money,
    		"available"			=>$money,
    		"out_money"			=>$money*$set["multiple"],
    		"created_time"		=>$time,
    		"updated_time"		=>$time,

    		]);
    	}

    	//投注金额60%分给前面静态没有出局者加权评分奖金
    	//然后判断前面用户是否出局


    }

    /**
     * 幸运奖池金额超过2万时触发此方法 给未出局且复投者幸运奖
     */
    public function lucky_jackpot($id)
    {
        //获取此局游戏
        $game = Games::get($id);
        $lucky_money=$game["lucky_jackpot"];
        //把幸运奖金分成20份 得到每一份多少钱
        $every=$lucky_money*0.05;
        $users=GameUsers::where("game_id",$id)->where("is_out",0)->where("betting_times",">",1)->select();
        //获取此轮游戏未出局且复投的随机20个用户,遍历给他们每人幸运奖池金额的5%
        $lucker=array_round($users,20);

        Db::startTrans();
        try {
            foreach($lucker as $k=>$v){
                User::where("id",$v->id)->setInc("balance",$every);
            }
            $game->lucky_jackpot = ["dec",$lucky_money];
            $game->save();
            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
}
