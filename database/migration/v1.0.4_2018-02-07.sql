ALTER TABLE `wx_game_coin_log` CHANGE COLUMN `type` `type` tinyint(2) DEFAULT 0 COMMENT '类型（0发起挑战，10挑战奖励，11平局,  1押注，2押注奖励，3应战，4应战奖励，5欢乐大转盘，6欢乐大转盘奖励，7充值, 8签到 9退款）';

CREATE TABLE `wx_payment_withdraw_review` (
  `id` int(30) NOT NULL AUTO_INCREMENT,
  `uid` varchar(100) DEFAULT '' COMMENT '用户openid',
  `money` decimal(8,2) DEFAULT '0.00' COMMENT '提现金额',
  `status` tinyint(3) DEFAULT '1' COMMENT '申请状态   0失败 1审核中 2审核成功',
  `created_at` datetime DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='大额提现审核表 panhao'