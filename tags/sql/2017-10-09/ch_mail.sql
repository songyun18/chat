/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 100125
 Source Host           : localhost
 Source Database       : chat

 Target Server Type    : MySQL
 Target Server Version : 100125
 File Encoding         : utf-8

 Date: 10/10/2017 16:08:31 PM
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `ch_mail`
-- ----------------------------
DROP TABLE IF EXISTS `ch_mail`;
CREATE TABLE `ch_mail` (
  `mail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT '接收人id',
  `send_id` int(11) DEFAULT NULL COMMENT '发送人id(如果没有可以为0)',
  `content` varchar(255) DEFAULT NULL COMMENT '内容',
  `type` tinyint(1) DEFAULT NULL COMMENT '0普通 1好友请求 2其他',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态（0未阅读 1同意 2拒绝）',
  `add_time` int(10) DEFAULT NULL COMMENT '添加时间',
  `update_time` int(10) DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`mail_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
