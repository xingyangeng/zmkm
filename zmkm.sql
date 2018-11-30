/*
Navicat MySQL Data Transfer

Source Server         : localhost_3306
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : zmkm

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-11-30 18:25:48
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for think_games
-- ----------------------------
DROP TABLE IF EXISTS `think_games`;
CREATE TABLE `think_games` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_time` int(10) DEFAULT NULL COMMENT '开始时间',
  `updated_time` int(10) DEFAULT NULL COMMENT '修改时间',
  `expire_time` int(10) DEFAULT NULL COMMENT '过期时间',
  `platform` decimal(10,2) DEFAULT '0.00' COMMENT '给一二三级推荐人，若无就给平台',
  `overflow` decimal(10,2) DEFAULT '0.00' COMMENT '如果60%分成金额大于用户的出局金额，多余的部分放这里',
  `key_num` int(10) DEFAULT NULL COMMENT '钥匙数量',
  `foundation` decimal(10,2) DEFAULT '0.00' COMMENT '基金会分成',
  `artisan` decimal(10,2) DEFAULT '0.00' COMMENT '技术人员所得分成',
  `team` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '技术团队所得分成',
  `jackpot` decimal(10,2) DEFAULT '0.00' COMMENT '奖池金豆数量',
  `last_key_id` int(11) DEFAULT NULL COMMENT '最后一个买钥匙的人的ID',
  `lucky_times` tinyint(4) DEFAULT '0' COMMENT '幸运分红次数',
  `lucky_jackpot` decimal(7,2) DEFAULT '0.00' COMMENT '幸运奖池',
  `status` enum('0','1') DEFAULT '1' COMMENT '游戏是否结束1开始0结束',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of think_games
-- ----------------------------

-- ----------------------------
-- Table structure for think_game_set
-- ----------------------------
DROP TABLE IF EXISTS `think_game_set`;
CREATE TABLE `think_game_set` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '开奖后分成配置表',
  `winner` decimal(4,2) DEFAULT NULL COMMENT '中奖者分红比例',
  `other` decimal(5,3) DEFAULT NULL COMMENT '其余96人分红比例',
  `other_num` tinyint(4) DEFAULT NULL COMMENT '最后投入分成人数量，不包括中奖者',
  `level1` decimal(4,2) DEFAULT '0.00' COMMENT '一代推广者所得比例',
  `level2` decimal(4,2) DEFAULT '0.00' COMMENT '2代推广者所得比例',
  `level3` decimal(4,2) DEFAULT '0.00' COMMENT '3代推广者所得比例',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of think_game_set
-- ----------------------------
INSERT INTO `think_game_set` VALUES ('1', '0.37', '0.005', '96', '0.04', '0.03', '0.02');

-- ----------------------------
-- Table structure for think_lucker_log
-- ----------------------------
DROP TABLE IF EXISTS `think_lucker_log`;
CREATE TABLE `think_lucker_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '幸运奖池开奖日志',
  `gid` int(11) DEFAULT NULL COMMENT '游戏轮数ID',
  `uid` int(11) DEFAULT NULL COMMENT '此轮游戏幸运奖获得者ID',
  `money` decimal(6,2) DEFAULT '0.00' COMMENT '获奖奖金',
  `created_time` int(10) DEFAULT NULL COMMENT '获奖时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of think_lucker_log
-- ----------------------------

-- ----------------------------
-- Table structure for think_money_log
-- ----------------------------
DROP TABLE IF EXISTS `think_money_log`;
CREATE TABLE `think_money_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gid` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `oid` int(11) DEFAULT NULL,
  `created_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of think_money_log
-- ----------------------------

-- ----------------------------
-- Table structure for think_msgcode
-- ----------------------------
DROP TABLE IF EXISTS `think_msgcode`;
CREATE TABLE `think_msgcode` (
  `tel` char(11) NOT NULL COMMENT '用户电话',
  `code` varchar(6) NOT NULL COMMENT '验证码',
  `expire_time` int(10) NOT NULL COMMENT '过期时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of think_msgcode
-- ----------------------------
INSERT INTO `think_msgcode` VALUES ('16638034358', '232323', '1543234258');
INSERT INTO `think_msgcode` VALUES ('18217375350', '503692', '1543211817');
INSERT INTO `think_msgcode` VALUES ('13838710613', '232325', '1543455786');
INSERT INTO `think_msgcode` VALUES ('13523451441', '123456', '1543456088');
INSERT INTO `think_msgcode` VALUES ('13523451442', '111111', '1543461726');
INSERT INTO `think_msgcode` VALUES ('13523451443', '111222', '1543462555');
INSERT INTO `think_msgcode` VALUES ('13523451444', '121212', '1543473974');

-- ----------------------------
-- Table structure for think_orders
-- ----------------------------
DROP TABLE IF EXISTS `think_orders`;
CREATE TABLE `think_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '玩家ID',
  `game_id` int(11) NOT NULL COMMENT '游戏ID',
  `total` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '本局游戏购买的KEY数量',
  `give_money` decimal(11,2) DEFAULT '0.00' COMMENT '后面买KEY者的分成',
  `out_money` decimal(11,2) NOT NULL DEFAULT '0.00' COMMENT '出局金额',
  `created_time` datetime NOT NULL COMMENT '第一次购买时间',
  `updated_time` datetime NOT NULL COMMENT '追加KEY时间',
  `status` enum('1','0') NOT NULL DEFAULT '1' COMMENT '此次购买KEY是否出局 0出局',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of think_orders
-- ----------------------------

-- ----------------------------
-- Table structure for think_recharge_log
-- ----------------------------
DROP TABLE IF EXISTS `think_recharge_log`;
CREATE TABLE `think_recharge_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '支付或提现记录表',
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `payway` varchar(20) DEFAULT NULL COMMENT '充值方式',
  `total` decimal(7,2) DEFAULT NULL COMMENT '总价格，也就是买金豆的数量',
  `created_time` int(10) DEFAULT NULL COMMENT '充值时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of think_recharge_log
-- ----------------------------

-- ----------------------------
-- Table structure for think_system_set
-- ----------------------------
DROP TABLE IF EXISTS `think_system_set`;
CREATE TABLE `think_system_set` (
  `id` int(11) NOT NULL,
  `foundation` decimal(4,2) NOT NULL,
  `artisan` decimal(4,2) NOT NULL,
  `team` decimal(4,2) NOT NULL COMMENT '平台分成',
  `noout` decimal(4,2) NOT NULL COMMENT '未出局者分成',
  `jackpot` decimal(4,2) NOT NULL COMMENT '奖池分成',
  `lucky_jackpot` decimal(4,2) NOT NULL COMMENT '幸运奖池分成',
  `level1` decimal(4,2) NOT NULL COMMENT '一代推广者分成',
  `level2` decimal(4,2) NOT NULL COMMENT '二代推广者分成',
  `level3` decimal(4,2) NOT NULL COMMENT '三代推广者分成',
  `key_price` decimal(4,2) NOT NULL DEFAULT '0.00' COMMENT '钥匙价格',
  `multiple` decimal(4,2) NOT NULL COMMENT '出局倍数',
  `out_parent` decimal(4,2) NOT NULL COMMENT '出局后推荐人所得比例',
  `out_self` decimal(4,2) NOT NULL COMMENT '出局后自己所得',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of think_system_set
-- ----------------------------
INSERT INTO `think_system_set` VALUES ('1', '0.03', '0.03', '0.02', '0.60', '0.15', '0.02', '0.08', '0.04', '0.03', '30.00', '1.25', '0.03', '0.97');

-- ----------------------------
-- Table structure for think_token
-- ----------------------------
DROP TABLE IF EXISTS `think_token`;
CREATE TABLE `think_token` (
  `tel` char(11) NOT NULL,
  `token` varchar(32) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of think_token
-- ----------------------------
INSERT INTO `think_token` VALUES ('18217375350', '8a768b9f0776f4c254e78fe3949fbb90');

-- ----------------------------
-- Table structure for think_transfer_log
-- ----------------------------
DROP TABLE IF EXISTS `think_transfer_log`;
CREATE TABLE `think_transfer_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '自己ID',
  `oid` int(11) DEFAULT NULL COMMENT '对方ID',
  `money` decimal(10,2) DEFAULT NULL,
  `created_time` datetime DEFAULT NULL COMMENT '转账时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of think_transfer_log
-- ----------------------------

-- ----------------------------
-- Table structure for think_user
-- ----------------------------
DROP TABLE IF EXISTS `think_user`;
CREATE TABLE `think_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL COMMENT '账户名',
  `password` char(32) NOT NULL COMMENT '密码',
  `paycode` char(32) NOT NULL COMMENT '支付密码',
  `invite_code` int(6) NOT NULL COMMENT '邀请码',
  `tel` char(11) NOT NULL,
  `balance` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '账户金额',
  `parent_id` int(11) NOT NULL COMMENT '邀请人ID',
  `status` enum('0','1') NOT NULL DEFAULT '0' COMMENT '0正常1异常',
  `head_img` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
  `created_time` char(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni` (`invite_code`,`tel`,`name`) USING BTREE COMMENT '6位纯数字邀请码',
  KEY `pro` (`parent_id`,`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of think_user
-- ----------------------------
INSERT INTO `think_user` VALUES ('1', 'a18217375350', 'e10adc3949ba59abbe56e057f20f883e', '7e4fe470b987efbd0597cbd50a27d03d', '666666', '18217375350', '2225.33', '0', '0', '', '1543026000');
INSERT INTO `think_user` VALUES ('13', 'a16638034358', 'e10adc3949ba59abbe56e057f20f883e', '7e4fe470b987efbd0597cbd50a27d03d', '775659', '16638034358', '22876.09', '1', '0', '', '1543200338');
INSERT INTO `think_user` VALUES ('14', 'a13838710613', 'e10adc3949ba59abbe56e057f20f883e', '7e4fe470b987efbd0597cbd50a27d03d', '267294', '13838710613', '51259.57', '13', '0', '', '1543455756');
INSERT INTO `think_user` VALUES ('15', 'a13523451441', 'e10adc3949ba59abbe56e057f20f883e', '7e4fe470b987efbd0597cbd50a27d03d', '654150', '13523451441', '65557.84', '14', '0', '', '1543455931');
INSERT INTO `think_user` VALUES ('16', 'a13523451442', 'e10adc3949ba59abbe56e057f20f883e', '7e4fe470b987efbd0597cbd50a27d03d', '108102', '13523451442', '6423.48', '15', '0', '', '1543461662');
INSERT INTO `think_user` VALUES ('17', 'a13523451443', 'e10adc3949ba59abbe56e057f20f883e', '7e4fe470b987efbd0597cbd50a27d03d', '887390', '13523451443', '1476.80', '16', '0', '', '1543462007');
INSERT INTO `think_user` VALUES ('18', 'a13523451444', 'e10adc3949ba59abbe56e057f20f883e', '7e4fe470b987efbd0597cbd50a27d03d', '900326', '13523451444', '1292.48', '16', '0', '', '1543473165');

-- ----------------------------
-- Table structure for think_user_path
-- ----------------------------
DROP TABLE IF EXISTS `think_user_path`;
CREATE TABLE `think_user_path` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '推荐脉络表',
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `pid` int(11) DEFAULT NULL COMMENT '推广者ID',
  `path` varchar(255) DEFAULT NULL COMMENT '从最初推广者到自己的路径',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of think_user_path
-- ----------------------------
INSERT INTO `think_user_path` VALUES ('1', '1', '0', '0,');
INSERT INTO `think_user_path` VALUES ('2', '13', '1', '0,1,');
INSERT INTO `think_user_path` VALUES ('3', '14', '13', '0,1,13,');
INSERT INTO `think_user_path` VALUES ('4', '15', '14', '0,1,13,14,');
INSERT INTO `think_user_path` VALUES ('5', '16', '15', '0,1,13,14,15,');
INSERT INTO `think_user_path` VALUES ('6', '17', '16', '0,1,13,14,15,16,');
INSERT INTO `think_user_path` VALUES ('7', '18', '16', '0,1,13,14,15,16,');

-- ----------------------------
-- Table structure for think_wallet_log
-- ----------------------------
DROP TABLE IF EXISTS `think_wallet_log`;
CREATE TABLE `think_wallet_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '钱包进出记录',
  `uid` int(11) NOT NULL COMMENT '用户ID',
  `oid` int(11) DEFAULT NULL COMMENT '消费oid为0 其余为给与提成的那个人ID',
  `gid` int(11) NOT NULL COMMENT '游戏轮次 ',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00',
  `type` tinyint(4) NOT NULL COMMENT '1为消费2为60%分成3为幸运奖池分红4为奖池爆以后分红5为1级提成6二级提成7三级提成8出局自己分成9出局上级分成',
  `created_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of think_wallet_log
-- ----------------------------
