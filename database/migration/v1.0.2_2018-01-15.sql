/*
 Navicat MySQL Data Transfer

 Source Server         : wechat_test
 Source Server Type    : MySQL
 Source Server Version : 50715
 Source Host           : rm-uf684r31g08zh100bo.mysql.rds.aliyuncs.com
 Source Database       : wechat_test

 Target Server Type    : MySQL
 Target Server Version : 50715
 File Encoding         : utf-8

 Date: 01/15/2018 14:26:15 PM
*/

-- ----------------------------
--  Table structure for `wx_user_power`
-- ----------------------------
CREATE TABLE `wx_user_power` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `uid` char(30) DEFAULT '' COMMENT '用户openid',
  `power` int(10) DEFAULT '10' COMMENT '用户体力值',
  `login_time` char(50) DEFAULT '' COMMENT '最近登录时间',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户体力变化表 panhao';

-- ----------------------------
--  Table structure for `wx_system_power`
-- ----------------------------
CREATE TABLE `wx_system_power` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) DEFAULT '0' COMMENT '1赠送 2消耗',
  `title` varchar(10) DEFAULT '' COMMENT '英文简写',
  `name` varchar(255) DEFAULT '' COMMENT '体力名称介绍',
  `num` tinyint(10) DEFAULT '0' COMMENT '体力值额',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='系统体力配置表 panhao';

CREATE TABLE `wx_system_config` (
  `id` int(20) NOT NULL AUTO_INCREMENT,
  `name` char(255) DEFAULT '' COMMENT '常量名',
  `num` int(20) DEFAULT '0' COMMENT '数值',
  `remark` varchar(255) DEFAULT '' COMMENT '常量备注',
  `prefix` char(10) DEFAULT '' COMMENT '前缀',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='常量配置表 panhao'