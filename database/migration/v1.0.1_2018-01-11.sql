/*
 Navicat MySQL Data Transfer

 Source Server         : wechat
 Source Server Type    : MySQL
 Source Server Version : 50715
 Source Host           : rm-uf684r31g08zh100bo.mysql.rds.aliyuncs.com
 Source Database       : wechat_test

 Target Server Type    : MySQL
 Target Server Version : 50715
 File Encoding         : utf-8

 Date: 01/11/2018 17:17:30 PM
*/
CREATE TABLE `wx_payment_distribute` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `bonus_id` int(20) DEFAULT '0' COMMENT '红包id',
  `time` char(10) DEFAULT '' COMMENT '时间日期年月日',
  `uid` char(30) DEFAULT '' COMMENT '发包人open_id',
  `bonus_money` decimal(10,2) DEFAULT '0.00' COMMENT '发红包金额',
  `payable_money` decimal(10,2) DEFAULT '0.00' COMMENT '被领取金额',
  `to_uid` char(30) DEFAULT '' COMMENT '受益人open_id',
  `commission` decimal(12,4) DEFAULT '0.0000' COMMENT '提成金额',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='推广提成记录表 PanHao';

CREATE TABLE `wx_user_level` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uid` char(30) DEFAULT '' COMMENT '用户openid',
  `level` varchar(20) DEFAULT NULL COMMENT '等级',
  `number` int(10) DEFAULT NULL COMMENT '数量',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户等级表 panhao';

CREATE TABLE `wx_user_relation` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `uid` varchar(100) DEFAULT '' COMMENT '用户openid',
  `pid` varchar(100) DEFAULT '' COMMENT '父openid',
  `depth` tinyint(5) DEFAULT '1' COMMENT '分销层级',
  `path` varchar(500) DEFAULT '' COMMENT '层级路径 如1||4||5 中间用||分隔',
  `created_at` datetime DEFAULT NULL COMMENT '绑定时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='推广用户关系表 PanHao';

alter  table  wx_payment_bonus  add  COLUMN  form_id  VARCHAR(255)  COMMENT    '表单提交时,发送模板的formid'  DEFAULT  '';  
alter  table  wx_payment_bonus  add  COLUMN  prepay_id  VARCHAR(255)  COMMENT    '微信支付时,发送模板用'  DEFAULT  '';  
alter  table  wx_payment_bonus_detail  add  COLUMN  payable_money1  DECIMAL(10,2)  COMMENT    '应领取金额'  DEFAULT  0.00;
alter  table  wx_payment_bonus_detail    modify  column  receive_service_money  DECIMAL(10,4);
alter  table  wx_payment_wallet    modify  column  balance  DECIMAL(10,4);
alter  table  we_payment_bonus_detail  add  COLUMN  is_optimum  tinyint(1)  COMMENT    '是否最佳'  DEFAULT  0;
alter  table  wx_payment_wallet  add  COLUMN  frozen_money  DECIMAL(10,4)  COMMENT    '冻结资金'  DEFAULT  0.00;
alter  table  wx_payment_order  add  COLUMN  wx_money  DECIMAL(10,2)  COMMENT    '微信付款金额'  DEFAULT  0.00;
alter  table  wx_payment_order  add  COLUMN  is_close  TINYINT(1)  COMMENT    '是否关闭'  DEFAULT  0;
ALTER  TABLE  `wx_user`  ADD  COLUMN  `invite_logo`  varchar(255)  DEFAULT  ''  COMMENT  '邀请码图片路径'  AFTER  `status`;
alter  table  wx_payment_bill_log  add  COLUMN  from_uid  VARCHAR(50)  COMMENT    '资金来源于(微信openid)'  DEFAULT  '';


