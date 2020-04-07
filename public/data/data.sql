/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80012
 Source Host           : localhost:3306
 Source Schema         : smart_wiki

 Target Server Type    : MySQL
 Target Server Version : 80012
 File Encoding         : 65001

 Date: 03/04/2020 18:10:43
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for wk_config
-- ----------------------------
DROP TABLE IF EXISTS `wk_config`;
CREATE TABLE `wk_config`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '名称',
  `key` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '键',
  `value` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '值',
  `config_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'user' COMMENT '变量类型：system 系统内置/user 用户定义',
  `remark` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` datetime(0) NOT NULL COMMENT '创建时间',
  `modify_time` datetime(0) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `id`(`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '开发设置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wk_config
-- ----------------------------
INSERT INTO `wk_config` VALUES (1, '站点名称', 'SITE_NAME', 'SincerityWiki', 'system', '站点名称', '2020-04-03 18:10:13', '2020-04-03 18:10:13');
INSERT INTO `wk_config` VALUES (2, '邮件有效期', 'MAIL_TOKEN_TIME', '3600', 'system', '找回密码邮件有效期,单位为秒', '2020-04-03 18:10:13', '2020-04-03 18:10:13');
INSERT INTO `wk_config` VALUES (3, '启用匿名访问', 'ENABLE_ANONYMOUS', '1', 'system', '是否启用匿名访问：0 否/1 是', '2020-04-03 18:10:13', '2020-04-03 18:10:13');
INSERT INTO `wk_config` VALUES (4, '启用登录验证码', 'ENABLED_CAPTCHA', '1', 'system', '是否启用登录验证码：0 否/1 是', '2020-04-03 18:10:13', '2020-04-03 18:10:13');
INSERT INTO `wk_config` VALUES (5, '是否启用注册', 'ENABLED_REGISTER', '1', 'system', '是否启用注册：0 否/1 是', '2020-04-03 18:10:13', '2020-04-03 18:10:13');
INSERT INTO `wk_config` VALUES (6, '注册默认的用户角色', 'DEFAULT_GROUP_LEVEL', '2', 'system', '注册默认的用户角色：0 超级管理员/1 普通用户/ 2 访客', '2020-04-03 18:10:13', '2020-04-03 18:10:13');

-- ----------------------------
-- Table structure for wk_document
-- ----------------------------
DROP TABLE IF EXISTS `wk_document`;
CREATE TABLE `wk_document`  (
  `doc_id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '文档名称',
  `parent_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '父ID',
  `project_id` int(20) NOT NULL DEFAULT 0 COMMENT '所属项目',
  `doc_sort` int(10) NOT NULL DEFAULT 0 COMMENT '排序',
  `doc_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '文档内容',
  `create_at` int(11) NOT NULL DEFAULT 0 COMMENT '创建人',
  `modify_at` int(11) NOT NULL DEFAULT 0 COMMENT '修改人',
  `version` datetime(0) NOT NULL COMMENT '当前时间',
  `create_time` datetime(0) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`doc_id`) USING BTREE,
  UNIQUE INDEX `doc_id`(`doc_id`) USING BTREE,
  INDEX `project_id`(`project_id`) USING BTREE,
  INDEX `doc_sort`(`doc_sort`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文档表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wk_document_history
-- ----------------------------
DROP TABLE IF EXISTS `wk_document_history`;
CREATE TABLE `wk_document_history`  (
  `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `doc_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '文档ID',
  `parent_id` bigint(20) NOT NULL DEFAULT 0 COMMENT '父ID',
  `doc_name` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '文档名称',
  `doc_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '文档内容',
  `create_at` int(11) NOT NULL DEFAULT 0 COMMENT '历史记录创建人',
  `modify_time` datetime(0) NOT NULL COMMENT '修改时间',
  `modify_at` int(11) NOT NULL DEFAULT 0 COMMENT '修改人',
  `version` datetime(0) NOT NULL COMMENT '当前时间',
  `create_time` datetime(0) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`history_id`) USING BTREE,
  UNIQUE INDEX `history_id`(`history_id`) USING BTREE,
  INDEX `doc_id`(`doc_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文档编辑历史记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wk_logs
-- ----------------------------
DROP TABLE IF EXISTS `wk_logs`;
CREATE TABLE `wk_logs`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_data` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作前的原数据',
  `present_data` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '操作后的数据',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '日志内容',
  `create_at` int(11) NOT NULL DEFAULT 0 COMMENT '创建人',
  `create_time` datetime(0) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '文档编辑历史记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wk_member
-- ----------------------------
DROP TABLE IF EXISTS `wk_member`;
CREATE TABLE `wk_member`  (
  `member_id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '账号',
  `member_passwd` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '密码',
  `nickname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '昵称',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '描述',
  `group_level` int(1) NOT NULL DEFAULT 2 COMMENT '用户基本：0 超级管理员，1 普通用户，2 访客',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户邮箱',
  `phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户手机号',
  `headimgurl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户头像',
  `remember_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户session',
  `state` int(1) NOT NULL DEFAULT 0 COMMENT '用户状态：0 正常，1 禁用',
  `create_at` int(10) NOT NULL DEFAULT 0 COMMENT '创建人',
  `modify_time` datetime(0) NOT NULL COMMENT '修改时间',
  `last_login_time` datetime(0) NOT NULL COMMENT '最后登陆时间',
  `last_login_ip` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '最后登录IP',
  `user_agent` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '最后登录浏览器信息',
  `create_time` datetime(0) NOT NULL COMMENT '创建时间',
  `version` datetime(0) NOT NULL COMMENT '当前时间',
  PRIMARY KEY (`member_id`) USING BTREE,
  UNIQUE INDEX `account`(`account`) USING BTREE,
  UNIQUE INDEX `email`(`email`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户信息表' ROW_FORMAT = Dynamic;

INSERT INTO `smart_wiki`.`wk_member`(`member_id`, `account`, `member_passwd`, `nickname`, `description`, `group_level`, `email`, `phone`, `headimgurl`, `remember_token`, `state`, `create_at`, `modify_time`, `last_login_time`, `last_login_ip`, `user_agent`, `create_time`, `version`) VALUES (1, 'admin', '$2y$10$taeaFypS6eWOvd9iKE4VIO9eW1SO81/f4XjNhKp/yPWIiOsfTnsuK', 'admin', '0', 0, '', '', '/static/images/middle.gif', '', 0, 0, '2020-04-03 18:45:10', '2020-04-03 18:45:19', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36', '2020-04-03 18:45:10', '2020-04-03 18:45:10');

-- ----------------------------
-- Table structure for wk_migrations
-- ----------------------------
DROP TABLE IF EXISTS `wk_migrations`;
CREATE TABLE `wk_migrations`  (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `start_time` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `end_time` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`version`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of wk_migrations
-- ----------------------------
INSERT INTO `wk_migrations` VALUES (20200403083342, 'Config', '2020-04-03 18:08:47', '2020-04-03 18:08:47', 0);
INSERT INTO `wk_migrations` VALUES (20200403085842, 'Document', '2020-04-03 18:08:47', '2020-04-03 18:08:47', 0);
INSERT INTO `wk_migrations` VALUES (20200403090936, 'DocumentHistory', '2020-04-03 18:08:47', '2020-04-03 18:08:47', 0);
INSERT INTO `wk_migrations` VALUES (20200403091654, 'Logs', '2020-04-03 18:08:47', '2020-04-03 18:08:47', 0);
INSERT INTO `wk_migrations` VALUES (20200403092124, 'Member', '2020-04-03 18:08:47', '2020-04-03 18:08:47', 0);
INSERT INTO `wk_migrations` VALUES (20200403092935, 'Passwords', '2020-04-03 18:08:47', '2020-04-03 18:08:47', 0);
INSERT INTO `wk_migrations` VALUES (20200403093541, 'Project', '2020-04-03 18:08:47', '2020-04-03 18:08:47', 0);
INSERT INTO `wk_migrations` VALUES (20200403094103, 'Relationship', '2020-04-03 18:08:47', '2020-04-03 18:08:47', 0);

-- ----------------------------
-- Table structure for wk_passwords
-- ----------------------------
DROP TABLE IF EXISTS `wk_passwords`;
CREATE TABLE `wk_passwords`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '唯一认证码',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户邮箱',
  `is_valid` int(1) NOT NULL DEFAULT 0 COMMENT '0有效，1无效',
  `user_address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户IP地址',
  `send_time` datetime(0) NOT NULL COMMENT '邮件发送时间',
  `create_time` datetime(0) NOT NULL COMMENT '创建时间',
  `valid_time` datetime(0) NOT NULL COMMENT '校验时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `token`(`token`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '找回密码' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wk_project
-- ----------------------------
DROP TABLE IF EXISTS `wk_project`;
CREATE TABLE `wk_project`  (
  `project_id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '项目名称',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '项目描述',
  `doc_tree` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '当前项目的文档树',
  `project_open_state` int(1) NOT NULL COMMENT '项目公开状态：0 私密，1 完全公开，2 加密公开',
  `project_password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '项目密码',
  `doc_count` int(10) NOT NULL COMMENT '文档数量',
  `create_time` datetime(0) NOT NULL COMMENT '创建时间',
  `create_at` int(11) NOT NULL COMMENT '创建人',
  `modify_time` datetime(0) NOT NULL COMMENT '修改时间',
  `modify_at` int(11) NOT NULL COMMENT '修改人',
  `version` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0.1' COMMENT '版本号',
  PRIMARY KEY (`project_id`) USING BTREE,
  UNIQUE INDEX `project_id`(`project_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '项目表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for wk_relationship
-- ----------------------------
DROP TABLE IF EXISTS `wk_relationship`;
CREATE TABLE `wk_relationship`  (
  `rel_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(10) NOT NULL DEFAULT 0 COMMENT '会员id',
  `project_id` int(10) NOT NULL COMMENT '项目id',
  `role_type` int(11) NOT NULL DEFAULT 0 COMMENT '项目角色：0 参与者，1 所有者',
  PRIMARY KEY (`rel_id`) USING BTREE,
  INDEX `project_id`(`project_id`) USING BTREE,
  INDEX `member_id`(`member_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '项目成员表表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
