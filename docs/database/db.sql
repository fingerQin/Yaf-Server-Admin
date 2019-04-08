/*
Navicat MySQL Data Transfer

Source Server         : 腾讯云自用MySQL
Source Server Version : 50636
Source Host           : 193.112.101.85:3306
Source Database       : yaf-server-admin

Target Server Type    : MYSQL
Target Server Version : 50636
File Encoding         : 65001

Date: 2019-01-07 15:24:58
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for finger_ad
-- ----------------------------
DROP TABLE IF EXISTS `finger_ad`;
CREATE TABLE `finger_ad` (
  `ad_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `ad_name` varchar(50) NOT NULL COMMENT '广告名称',
  `pos_id` int(11) unsigned NOT NULL COMMENT '广告位置。对应ms_ad_postion.pos_id',
  `ad_image_url` varchar(255) NOT NULL COMMENT '广告图片',
  `ad_url` varchar(255) NOT NULL COMMENT '广告图片URL跳转地址',
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '广告生效时间',
  `end_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '广告失效时间',
  `display` tinyint(1) NOT NULL DEFAULT '1' COMMENT '显示状态：1显示、0隐藏',
  `status` tinyint(1) NOT NULL COMMENT '状态：0无效、1正常、2删除',
  `listorder` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序。小到大排序。',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `c_by` int(11) unsigned NOT NULL COMMENT '创建人',
  `c_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `u_by` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `u_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`ad_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='广告表';

-- ----------------------------
-- Records of finger_ad
-- ----------------------------
INSERT INTO `finger_ad` VALUES ('6', '世界杯广告', '1', 'http://files.hunshijian.com/images/voucher/20180913/5b9a1b9e34193.png', 'http://www.baidu.com', '2018-08-07 11:11:00', '2018-08-31 11:11:00', '1', '1', '1', 'xxxxxxx', '1', '2018-08-07 11:55:45', '1', '2018-12-05 17:07:49');

-- ----------------------------
-- Table structure for finger_ad_position
-- ----------------------------
DROP TABLE IF EXISTS `finger_ad_position`;
CREATE TABLE `finger_ad_position` (
  `pos_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pos_name` varchar(50) NOT NULL COMMENT '广告位置名称',
  `pos_code` varchar(50) NOT NULL COMMENT '广告位置编码。通过编码来读取广告数据',
  `pos_ad_count` smallint(5) NOT NULL COMMENT '该广告位置显示可展示广告的数量',
  `status` tinyint(1) NOT NULL COMMENT '状态：0无效、1正常、2删除',
  `c_by` int(11) unsigned NOT NULL COMMENT '创建人',
  `c_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '创建时间',
  `u_by` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `u_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`pos_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='广告位置接表';

-- ----------------------------
-- Records of finger_ad_position
-- ----------------------------
INSERT INTO `finger_ad_position` VALUES ('1', 'APP 首页', 'app_home', '5', '1', '1', '2018-08-07 10:40:02', '1', '2018-08-07 10:40:02');
INSERT INTO `finger_ad_position` VALUES ('2', '222', '1223', '1', '2', '1', '2018-08-07 11:14:17', '1', '2018-08-07 11:14:59');

-- ----------------------------
-- Table structure for finger_admin_menu
-- ----------------------------
DROP TABLE IF EXISTS `finger_admin_menu`;
CREATE TABLE `finger_admin_menu` (
  `menuid` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '菜单ID',
  `menu_name` varchar(40) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `parentid` smallint(6) NOT NULL DEFAULT '0' COMMENT '父菜单ID',
  `c` varchar(50) NOT NULL DEFAULT '' COMMENT '控制器',
  `a` varchar(50) NOT NULL DEFAULT '' COMMENT '操作',
  `ext_param` varchar(255) NOT NULL DEFAULT '' COMMENT '附加参数',
  `listorder` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  `icon` varchar(255) NOT NULL DEFAULT '' COMMENT 'icon 图标',
  `is_display` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否显示：0-否|1-是',
  `helpstr` mediumtext COMMENT '帮助文档内容',
  `c_by` smallint(6) NOT NULL COMMENT '创建人ID',
  `c_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `u_by` smallint(6) NOT NULL DEFAULT '0' COMMENT '修改人ID',
  `u_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`menuid`),
  KEY `idx_pid` (`parentid`),
  KEY `idx_c_a` (`c`,`a`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8 COMMENT='后台菜单表';

-- ----------------------------
-- Records of finger_admin_menu
-- ----------------------------
INSERT INTO `finger_admin_menu` VALUES ('1', '常用功能', '0', '', '', '', '0', '', '1', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('2', '常用功能', '1', '', '', '', '0', '', '1', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('3', '修改密码', '2', 'Admin', 'editPwd', '', '0', 'glyphicon-lock', '1', '', '0', '2017-08-03 16:10:00', '0', '2017-11-23 17:42:07');
INSERT INTO `finger_admin_menu` VALUES ('4', '管理后台首页', '2', 'Index', 'Index', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('5', '管理后台Ajax获取菜单', '2', 'Index', 'left', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('6', '管理后台右侧默认页', '2', 'Index', 'right', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('7', '管理后台面包屑', '2', 'Index', 'arrow', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('8', '文件上传', '2', 'Index', 'upload', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('9', '系统管理', '0', '', '', '', '0', '', '1', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('10', '系统配置', '9', '', '', '', '0', '', '1', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('14', '配置管理', '10', 'Config', 'index', '', '0', 'glyphicon-cog', '1', 'ssadada', '0', '2017-08-03 16:10:00', '0', '2018-07-24 11:15:40');
INSERT INTO `finger_admin_menu` VALUES ('15', '添加配置', '10', 'Config', 'add', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('16', '编辑配置', '10', 'Config', 'edit', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('17', '删除配置', '10', 'Config', 'delete', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('18', '配置排序', '10', 'Config', 'sort', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('19', '配置缓存清除', '10', 'Config', 'ClearCache', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('20', '菜单列表', '10', 'Menu', 'index', '', '0', 'glyphicon-th-list', '1', '', '0', '2017-08-03 16:10:00', '0', '2018-07-25 11:44:57');
INSERT INTO `finger_admin_menu` VALUES ('21', '添加菜单', '10', 'Menu', 'add', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('22', '编辑菜单', '10', 'Menu', 'edit', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('23', '删除菜单', '10', 'Menu', 'delete', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('24', '菜单排序', '10', 'Menu', 'sort', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('25', '权限管理', '0', '', '', '', '0', '', '1', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('26', '管理员管理', '25', 'Admin', '', '', '0', '', '1', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('27', '管理员列表', '26', 'Admin', 'index', '', '0', 'glyphicon-user', '1', '', '0', '2017-08-03 16:10:00', '0', '2017-11-23 18:17:24');
INSERT INTO `finger_admin_menu` VALUES ('28', '添加管理员', '26', 'Admin', 'add', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2018-12-05 17:01:02');
INSERT INTO `finger_admin_menu` VALUES ('29', '更新管理员', '26', 'Admin', 'edit', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2018-12-05 17:01:04');
INSERT INTO `finger_admin_menu` VALUES ('30', '删除管理员', '26', 'Admin', 'delete', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2018-12-05 17:01:03');
INSERT INTO `finger_admin_menu` VALUES ('31', '角色管理', '25', 'Role', '', '', '0', '', '1', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('32', '角色列表', '31', 'Role', 'index', '', '0', 'glyphicon-th-list', '1', '', '0', '2017-08-03 16:10:00', '0', '2017-11-23 18:19:10');
INSERT INTO `finger_admin_menu` VALUES ('33', '添加角色', '31', 'Role', 'add', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('34', '更新角色', '31', 'Role', 'update', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('35', '删除角色', '31', 'Role', 'delete', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('36', '角色赋权', '31', 'Role', 'setPermission', '', '0', '', '0', '', '0', '2017-08-03 16:10:00', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('45', 'APP 版本&升级', '9', 'app', '', '', '0', '', '1', '', '0', '2017-08-17 09:26:26', '0', '2017-12-04 15:25:08');
INSERT INTO `finger_admin_menu` VALUES ('46', 'APP 版本&升级', '45', 'app', 'list', '', '0', '', '1', '<h3>\r\n	<strong>1）管理后台设置之后，可以在 API 的 system.init 接口体现出来。以及单独的 APP 升级接口。</strong>\r\n</h3>', '0', '2017-08-17 09:29:22', '0', '2019-01-07 10:58:30');
INSERT INTO `finger_admin_menu` VALUES ('47', 'APP 版本添加', '45', 'app', 'add', '', '0', '', '0', '', '0', '2017-08-17 09:29:52', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('48', 'APP 版本编辑', '45', 'app', 'edit', '', '0', '', '0', '', '0', '2017-08-17 09:30:47', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('49', 'APP 版本删除', '45', 'app', 'delete', '', '0', '', '0', '', '0', '2017-08-17 09:31:23', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('50', 'API 应用管理', '9', 'Api', '', '', '0', '', '1', '', '0', '2017-08-17 09:56:56', '0', '2017-12-04 15:24:57');
INSERT INTO `finger_admin_menu` VALUES ('51', 'API 应用列表', '50', 'Api', 'list', '', '0', '', '1', '', '0', '2017-08-17 09:58:09', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('52', 'APi 应用添加', '50', 'Api', 'add', '', '0', '', '0', '', '0', '2017-08-17 09:58:40', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('53', 'API 应用编辑', '50', 'Api', 'edit', '', '0', '', '0', '', '0', '2017-08-17 09:59:09', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('54', 'API 应用删除', '50', ' Api', 'delete', '', '0', '', '0', '', '0', '2017-08-17 09:59:44', '0', '2017-11-16 16:05:51');
INSERT INTO `finger_admin_menu` VALUES ('55', '内容管理', '0', '', '', '', '0', '', '1', '', '0', '2018-07-11 09:59:44', '0', '2018-07-11 09:59:44');
INSERT INTO `finger_admin_menu` VALUES ('56', '分类管理', '55', 'Category', 'index', '', '0', '', '1', '', '0', '2018-07-11 10:00:28', '0', '2018-07-11 10:00:28');
INSERT INTO `finger_admin_menu` VALUES ('57', '分类列表', '56', 'Category', 'index', '', '0', '', '1', '', '0', '2018-07-11 10:00:49', '0', '2018-07-11 10:00:49');
INSERT INTO `finger_admin_menu` VALUES ('58', '文章管理', '55', 'News', 'Index', '', '0', '', '1', '', '0', '2018-07-11 10:02:12', '0', '2018-07-11 10:02:12');
INSERT INTO `finger_admin_menu` VALUES ('59', '文章列表', '58', 'News', 'Index', '', '0', '', '1', '', '0', '2018-07-11 10:02:28', '0', '2018-07-11 10:02:28');
INSERT INTO `finger_admin_menu` VALUES ('60', '分类添加', '56', 'News', 'add', '', '0', '', '0', '', '0', '2018-07-11 10:02:45', '0', '2018-07-11 10:02:45');
INSERT INTO `finger_admin_menu` VALUES ('61', '分类编辑', '56', 'News', 'edit', '', '0', '', '0', '', '0', '2018-07-11 10:02:58', '0', '2018-07-11 10:02:58');
INSERT INTO `finger_admin_menu` VALUES ('62', '分类删除', '56', 'News', 'delete', '', '0', '', '0', '', '0', '2018-07-11 10:03:11', '0', '2018-07-11 10:03:11');
INSERT INTO `finger_admin_menu` VALUES ('63', '分类排序', '56', 'News', 'sort', '', '0', '', '0', '', '0', '2018-07-11 10:03:23', '0', '2018-07-11 10:03:23');
INSERT INTO `finger_admin_menu` VALUES ('64', '文章添加', '58', 'News', 'add', '', '0', '', '0', '', '0', '2018-07-11 10:03:35', '0', '2018-07-11 10:03:35');
INSERT INTO `finger_admin_menu` VALUES ('65', '文章编辑', '58', 'News', 'edit', '', '0', '', '0', '', '0', '2018-07-11 10:03:45', '0', '2018-07-11 10:03:45');
INSERT INTO `finger_admin_menu` VALUES ('66', '文章删除', '58', 'News', 'delete', '', '0', '', '0', '', '0', '2018-07-11 10:03:57', '0', '2018-07-11 10:03:57');
INSERT INTO `finger_admin_menu` VALUES ('67', '文件管理', '9', 'File', 'Index', '', '0', '', '1', '', '0', '2018-07-12 09:16:21', '0', '2018-07-12 09:17:57');
INSERT INTO `finger_admin_menu` VALUES ('68', '文件列表', '67', 'File', 'Index', '', '0', '', '1', '', '0', '2018-07-12 09:17:42', '0', '2018-07-12 09:17:42');
INSERT INTO `finger_admin_menu` VALUES ('69', '文件删除', '67', 'File', 'delete', '', '0', '', '0', '', '0', '2018-07-12 09:18:44', '0', '2018-07-12 09:18:52');
INSERT INTO `finger_admin_menu` VALUES ('70', '友情链接', '55', 'Link', 'Index', '', '0', '', '1', '<img src=\"http://files.hunshijian.com/images/news/20180724/5b569b30a1e11.jpg\" alt=\"\" />', '0', '2018-07-12 15:03:41', '0', '2018-07-24 11:21:22');
INSERT INTO `finger_admin_menu` VALUES ('71', '友情链接列表', '70', 'Link', 'Index', '', '0', '', '1', '<img src=\"http://files.hunshijian.com/images/news/20180724/5b569b30a1e11.jpg\" alt=\"\" />', '0', '2018-07-12 15:04:18', '0', '2018-07-24 11:21:22');
INSERT INTO `finger_admin_menu` VALUES ('72', '添加友情链接', '70', 'Link', 'add', '', '0', '', '0', '', '0', '2018-07-12 15:04:32', '0', '2018-07-12 15:04:32');
INSERT INTO `finger_admin_menu` VALUES ('73', '友情链接编辑', '70', 'Link', 'edit', '', '0', '', '0', '', '0', '2018-07-12 15:04:48', '0', '2018-07-12 15:04:52');
INSERT INTO `finger_admin_menu` VALUES ('74', '友情链接删除', '70', 'Link', 'delete', '', '0', '', '0', '', '0', '2018-07-12 15:05:13', '0', '2018-07-12 15:05:13');
INSERT INTO `finger_admin_menu` VALUES ('75', '友情链接排序', '70', 'Link', 'sort', '', '0', '', '0', '', '0', '2018-07-12 15:06:04', '0', '2018-07-12 15:06:04');
INSERT INTO `finger_admin_menu` VALUES ('76', '广告管理', '55', 'ad', 'index', '', '0', '', '1', null, '0', '2018-08-07 09:17:34', '0', '2018-08-07 09:17:34');
INSERT INTO `finger_admin_menu` VALUES ('77', '广告位', '76', 'ad', 'position', '', '0', '', '1', null, '0', '2018-08-07 09:22:09', '0', '2018-08-07 10:28:55');
INSERT INTO `finger_admin_menu` VALUES ('78', '添加广告位', '76', 'ad', 'addPosition', '', '0', '', '0', null, '0', '2018-08-07 09:22:38', '0', '2018-08-07 10:29:03');
INSERT INTO `finger_admin_menu` VALUES ('79', '编辑广告位', '76', 'ad', 'editPosition', '', '0', '', '0', null, '0', '2018-08-07 09:22:53', '0', '2018-08-07 10:29:26');
INSERT INTO `finger_admin_menu` VALUES ('80', '删除广告位', '76', 'ad', 'deletePosition', '', '0', '', '0', null, '0', '2018-08-07 09:23:38', '0', '2018-08-07 10:29:16');
INSERT INTO `finger_admin_menu` VALUES ('81', '广告列表', '76', 'ad', 'adlist', '', '0', '', '0', null, '0', '2018-08-07 09:23:59', '0', '2018-08-07 09:26:36');
INSERT INTO `finger_admin_menu` VALUES ('82', '添加广告', '76', 'ad', 'addAd', '', '0', '', '0', null, '0', '2018-08-07 09:24:22', '0', '2018-08-07 09:26:07');
INSERT INTO `finger_admin_menu` VALUES ('83', '编辑广告', '76', 'ad', 'editAdd', '', '0', '', '0', null, '0', '2018-08-07 09:24:48', '0', '2018-08-07 09:26:16');
INSERT INTO `finger_admin_menu` VALUES ('84', '广告排序', '76', 'ad', 'adSort', '', '0', '', '0', null, '0', '2018-08-07 10:12:48', '0', '2018-08-07 10:12:48');
INSERT INTO `finger_admin_menu` VALUES ('85', '禁用管理员', '26', 'Admin', 'forbid', '', '0', '', '0', null, '0', '2018-12-05 17:02:28', '0', '2018-12-05 17:02:28');

-- ----------------------------
-- Table structure for finger_admin_role
-- ----------------------------
DROP TABLE IF EXISTS `finger_admin_role`;
CREATE TABLE `finger_admin_role` (
  `roleid` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `role_name` varchar(20) NOT NULL COMMENT '角色名称',
  `listorder` smallint(6) NOT NULL DEFAULT '0' COMMENT '排序',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '角色说明',
  `is_default` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否默认：0-否|1-是(仅超级管理员)',
  `role_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '<字典>状态：0-失效|1-有效|2-删除',
  `c_by` smallint(6) NOT NULL COMMENT '创建人ID',
  `c_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `u_by` smallint(6) NOT NULL DEFAULT '0' COMMENT '修改人ID',
  `u_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`roleid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='后台角色表';

-- ----------------------------
-- Records of finger_admin_role
-- ----------------------------
INSERT INTO `finger_admin_role` VALUES ('1', '超级管理员', '0', '绝对的 Power', '1', '1', '1', '2018-07-09 15:16:29', '0', '2018-07-10 10:23:45');
INSERT INTO `finger_admin_role` VALUES ('2', '普通管理员', '0', '普通管理员', '0', '1', '0', '2019-01-07 14:25:47', '0', '2019-01-07 14:25:47');

-- ----------------------------
-- Table structure for finger_admin_role_priv
-- ----------------------------
DROP TABLE IF EXISTS `finger_admin_role_priv`;
CREATE TABLE `finger_admin_role_priv` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '后台角色权限ID',
  `roleid` smallint(6) NOT NULL DEFAULT '0' COMMENT '角色ID',
  `menuid` smallint(6) NOT NULL COMMENT '菜单ID',
  `c_by` smallint(6) NOT NULL COMMENT '创建人ID',
  `c_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `u_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_rid` (`roleid`)
) ENGINE=InnoDB AUTO_INCREMENT=341 DEFAULT CHARSET=utf8 COMMENT='后台角色权限表';

-- ----------------------------
-- Records of finger_admin_role_priv
-- ----------------------------
INSERT INTO `finger_admin_role_priv` VALUES ('302', '2', '1', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('303', '2', '2', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('304', '2', '3', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('305', '2', '4', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('306', '2', '5', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('307', '2', '6', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('308', '2', '7', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('309', '2', '8', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('310', '2', '25', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('311', '2', '26', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('312', '2', '27', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('313', '2', '28', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('314', '2', '29', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('315', '2', '30', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('316', '2', '85', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('317', '2', '31', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('318', '2', '32', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('319', '2', '33', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('320', '2', '35', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('321', '2', '55', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('322', '2', '56', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('323', '2', '57', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('324', '2', '60', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('325', '2', '61', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('326', '2', '63', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('327', '2', '76', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('328', '2', '77', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('329', '2', '78', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('330', '2', '79', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('331', '2', '80', '1', '2019-01-07 14:43:49', '2019-01-07 14:43:49');
INSERT INTO `finger_admin_role_priv` VALUES ('332', '2', '81', '1', '2019-01-07 14:43:50', '2019-01-07 14:43:50');
INSERT INTO `finger_admin_role_priv` VALUES ('333', '2', '82', '1', '2019-01-07 14:43:50', '2019-01-07 14:43:50');
INSERT INTO `finger_admin_role_priv` VALUES ('334', '2', '83', '1', '2019-01-07 14:43:50', '2019-01-07 14:43:50');
INSERT INTO `finger_admin_role_priv` VALUES ('335', '2', '84', '1', '2019-01-07 14:43:50', '2019-01-07 14:43:50');
INSERT INTO `finger_admin_role_priv` VALUES ('336', '2', '9', '1', '2019-01-07 14:43:50', '2019-01-07 14:43:50');
INSERT INTO `finger_admin_role_priv` VALUES ('337', '2', '45', '1', '2019-01-07 14:43:50', '2019-01-07 14:43:50');
INSERT INTO `finger_admin_role_priv` VALUES ('338', '2', '46', '1', '2019-01-07 14:43:50', '2019-01-07 14:43:50');
INSERT INTO `finger_admin_role_priv` VALUES ('339', '2', '47', '1', '2019-01-07 14:43:50', '2019-01-07 14:43:50');
INSERT INTO `finger_admin_role_priv` VALUES ('340', '2', '48', '1', '2019-01-07 14:43:50', '2019-01-07 14:43:50');

-- ----------------------------
-- Table structure for finger_admin_user
-- ----------------------------
DROP TABLE IF EXISTS `finger_admin_user`;
CREATE TABLE `finger_admin_user` (
  `adminid` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `real_name` varchar(20) NOT NULL COMMENT '真实姓名',
  `passwd` char(32) NOT NULL COMMENT '密码',
  `passwd_salt` char(6) NOT NULL COMMENT '密码盐',
  `mobile` varchar(15) NOT NULL DEFAULT '0' COMMENT '手机号码',
  `roleid` smallint(6) NOT NULL DEFAULT '0' COMMENT '角色ID',
  `user_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '<字典>状态：0-失效|1-有效|2-删除',
  `c_by` smallint(6) NOT NULL COMMENT '创建人ID',
  `c_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `u_by` smallint(6) NOT NULL DEFAULT '0' COMMENT '修改人ID',
  `u_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`adminid`),
  KEY `idx_m` (`mobile`),
  KEY `idx_rid` (`roleid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='后台管理员表';

-- ----------------------------
-- Records of finger_admin_user
-- ----------------------------
INSERT INTO `finger_admin_user` VALUES ('1', 'administrator', '74ab1dfb68973a7b50b21d04dd0ee63a', '4lddvv', '13812345678', '1', '1', '1', '2018-07-07 16:01:56', '1', '2019-01-07 11:54:07');

-- ----------------------------
-- Table structure for finger_api_auth
-- ----------------------------
DROP TABLE IF EXISTS `finger_api_auth`;
CREATE TABLE `finger_api_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'API 应用ID',
  `api_type` char(10) NOT NULL COMMENT '应用类型：app-APP调用接口|admin-管理后台调用接口|activity-活动接口调用',
  `api_name` char(20) NOT NULL COMMENT '应用名称',
  `api_key` char(20) NOT NULL COMMENT 'API 标识',
  `api_secret` char(32) NOT NULL COMMENT 'API 密钥',
  `api_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '<字典>应用状态：0-无效|1-正常|2-删除',
  `c_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `u_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_aen` (`api_key`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='API 应用权限表';

-- ----------------------------
-- Records of finger_api_auth
-- ----------------------------
INSERT INTO `finger_api_auth` VALUES ('1', 'app', 'APP 测试', 'appid_test', '98040e3735acce4080a5b021de4f030f', '1', '2018-06-23 08:39:28', '2018-12-05 16:40:56');
INSERT INTO `finger_api_auth` VALUES ('2', 'admin', '管理后台密钥（访问管理接口专用）', 'admin_api_call', '16b36c2c513de50126144bb8085260ba', '1', '2018-07-11 09:28:36', '2019-01-07 11:00:08');

-- ----------------------------
-- Table structure for finger_app_upgrade
-- ----------------------------
DROP TABLE IF EXISTS `finger_app_upgrade`;
CREATE TABLE `finger_app_upgrade` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `app_type` varchar(10) NOT NULL COMMENT 'APP_类型：1-IOS|2-Android',
  `app_title` varchar(20) NOT NULL COMMENT 'APP 升级标题。eg: 1.0.0 升级',
  `app_v` varchar(10) NOT NULL COMMENT 'APP 版本号。eg:1.0.0',
  `app_desc` varchar(255) NOT NULL DEFAULT '' COMMENT 'APP 版本升级介绍',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '下载地址，仅Android使用',
  `upgrade_way` tinyint(4) NOT NULL DEFAULT '0' COMMENT '<字典>升级_方式：0-不升级|1-建议升级|2-强制升级|3-应用关闭',
  `dialog_repeat` tinyint(4) NOT NULL DEFAULT '0' COMMENT '建议升级时，升级弹框是否每次都弹出：0-否|1-是',
  `channel` varchar(20) NOT NULL DEFAULT '' COMMENT 'Android 渠道编号。IOS 此值无效',
  `app_status` tinyint(4) NOT NULL COMMENT '状态：0-无效|1-正常|2-删除',
  `c_by` smallint(6) NOT NULL COMMENT '创建人ID',
  `c_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `u_by` smallint(6) NOT NULL DEFAULT '0' COMMENT '修改人ID',
  `u_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_av` (`app_v`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='APP客户端升级表';

-- ----------------------------
-- Records of finger_app_upgrade
-- ----------------------------

-- ----------------------------
-- Table structure for finger_category
-- ----------------------------
DROP TABLE IF EXISTS `finger_category`;
CREATE TABLE `finger_category` (
  `cat_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `cat_name` varchar(50) NOT NULL COMMENT '分类名称',
  `cat_type` smallint(3) NOT NULL COMMENT '分类类型。见category_type_list字典。',
  `parentid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '父分类ID',
  `lv` smallint(3) NOT NULL COMMENT '菜单层级',
  `cat_code` varchar(50) NOT NULL COMMENT '分类code编',
  `is_out_url` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否外部链接：1是、0否',
  `out_url` varchar(255) NOT NULL DEFAULT '' COMMENT '外部链接地址',
  `display` tinyint(1) NOT NULL DEFAULT '0' COMMENT '显示状态：1是、0否',
  `tpl_name` char(50) NOT NULL DEFAULT '' COMMENT '模板名称',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0无效、1正常、2删除',
  `listorder` smallint(5) NOT NULL DEFAULT '0' COMMENT '排序值。小到大排列。',
  `u_by` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `u_time` datetime DEFAULT NULL COMMENT '修改时间戳',
  `c_time` datetime DEFAULT NULL COMMENT '创建时间戳',
  `c_by` int(11) unsigned NOT NULL COMMENT '管理员账号ID',
  PRIMARY KEY (`cat_id`),
  KEY `cat_code` (`cat_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分类表';

-- ----------------------------
-- Records of finger_category
-- ----------------------------

-- ----------------------------
-- Table structure for finger_config
-- ----------------------------
DROP TABLE IF EXISTS `finger_config`;
CREATE TABLE `finger_config` (
  `configid` mediumint(9) NOT NULL AUTO_INCREMENT COMMENT '公共_配置ID',
  `title` varchar(20) NOT NULL COMMENT '标题',
  `cfg_key` varchar(30) NOT NULL COMMENT '配置key',
  `cfg_value` text NOT NULL COMMENT '配置值',
  `description` text NOT NULL COMMENT '配置描述',
  `cfg_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '<字典>状态：0-失效|1-有效|2-删除',
  `c_by` smallint(6) NOT NULL COMMENT '创建人ID',
  `c_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `u_by` smallint(6) NOT NULL DEFAULT '0' COMMENT '修改人ID',
  `u_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`configid`),
  KEY `idx_ck20` (`cfg_key`(20))
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='公共_配置表';

-- ----------------------------
-- Records of finger_config
-- ----------------------------
INSERT INTO `finger_config` VALUES ('1', '网站名称', 'site_name', 'Yaf-Server', '网站名称', '1', '1', '2018-07-09 09:50:31', '1', '2019-01-04 17:06:08');

-- ----------------------------
-- Table structure for finger_event
-- ----------------------------
DROP TABLE IF EXISTS `finger_event`;
CREATE TABLE `finger_event` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `code` char(10) NOT NULL COMMENT '事件编码',
  `userid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `error_code` int(11) NOT NULL DEFAULT '0' COMMENT '错误码',
  `error_msg` varchar(255) NOT NULL DEFAULT '' COMMENT '错误消息',
  `status` tinyint(1) NOT NULL COMMENT '处理状态：0-待处理,1-已经处理,2-处理失败',
  `data` varchar(500) NOT NULL COMMENT '事件内容',
  `u_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `c_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统事件消息表';

-- ----------------------------
-- Records of finger_event
-- ----------------------------

-- ----------------------------
-- Table structure for finger_files
-- ----------------------------
DROP TABLE IF EXISTS `finger_files`;
CREATE TABLE `finger_files` (
  `file_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `file_name` char(255) NOT NULL DEFAULT '' COMMENT '文件名称',
  `file_type` tinyint(1) NOT NULL COMMENT '文件类型：1-图片、2-其他文件',
  `file_size` int(11) unsigned NOT NULL COMMENT '文件大小。单位：(byte)',
  `file_md5` char(32) NOT NULL COMMENT '文件md5值',
  `user_type` tinyint(1) NOT NULL COMMENT '用户类型：1管理员、2普通用户',
  `user_id` int(11) unsigned NOT NULL COMMENT '用户ID',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0失效、1有效、2删除',
  `u_time` datetime DEFAULT NULL COMMENT '更新时间',
  `c_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='文件表';

-- ----------------------------
-- Records of finger_files
-- ----------------------------
INSERT INTO `finger_files` VALUES ('1', 'http://yaf-server-admin.oss-cn-shenzhen.aliyuncs.com/images/voucher/20190107/5c32f8ec154b2.png', '1', '457095', '4d39264a792fd2dc3a0a236a2c4b7d13', '1', '1', '1', '2019-01-07 14:59:56', '2019-01-07 14:59:56');

-- ----------------------------
-- Table structure for finger_link
-- ----------------------------
DROP TABLE IF EXISTS `finger_link`;
CREATE TABLE `finger_link` (
  `link_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `link_name` varchar(50) NOT NULL COMMENT '友情链接名称',
  `link_url` varchar(100) NOT NULL COMMENT '友情链接URL',
  `cat_id` int(11) unsigned NOT NULL COMMENT '友情链接分类ID。对应ms_category.cat_id',
  `image_url` varchar(100) NOT NULL DEFAULT '' COMMENT '友情链接图片',
  `display` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否显示。1显示、0隐藏',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态：0无效、1正常、2删除',
  `listorder` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序。小到大排序。',
  `hits` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'URL点击量',
  `c_by` int(11) unsigned NOT NULL COMMENT '创建人',
  `c_time` datetime DEFAULT NULL COMMENT '创建时间',
  `u_by` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `u_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`link_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='友情链接表';

-- ----------------------------
-- Records of finger_link
-- ----------------------------

-- ----------------------------
-- Table structure for finger_news
-- ----------------------------
DROP TABLE IF EXISTS `finger_news`;
CREATE TABLE `finger_news` (
  `news_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '文章ID、主键',
  `cat_code` varchar(50) NOT NULL COMMENT '分类编码。对应ms_category.cat_code',
  `title` char(50) NOT NULL COMMENT '文章标题',
  `intro` char(250) NOT NULL COMMENT '文章简介。也是SEO中的description',
  `keywords` char(50) NOT NULL DEFAULT '' COMMENT '文章关键词。也是SEO中的keywords',
  `image_url` char(100) NOT NULL DEFAULT '' COMMENT '文章列表图片',
  `source` char(20) NOT NULL DEFAULT '' COMMENT '文章来源',
  `display` tinyint(1) NOT NULL DEFAULT '0' COMMENT '文章是否显示。1显示、0隐藏',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '文章状态：0无效、1正常、2删除',
  `listorder` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '排序。小到大排序。',
  `hits` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '文章访问量',
  `c_by` int(11) unsigned NOT NULL COMMENT '创建人',
  `c_time` datetime DEFAULT NULL COMMENT '创建时间',
  `u_by` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改人',
  `u_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`news_id`),
  KEY `c_time` (`c_time`),
  KEY `c_by` (`c_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章表';

-- ----------------------------
-- Records of finger_news
-- ----------------------------

-- ----------------------------
-- Table structure for finger_news_data
-- ----------------------------
DROP TABLE IF EXISTS `finger_news_data`;
CREATE TABLE `finger_news_data` (
  `news_id` int(11) unsigned NOT NULL COMMENT '文章ID',
  `content` text COMMENT '文章内容',
  `u_time` datetime NOT NULL COMMENT '更新时间',
  `c_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`news_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='文章副表';

-- ----------------------------
-- Records of finger_news_data
-- ----------------------------

-- ----------------------------
-- Table structure for finger_push_device
-- ----------------------------
DROP TABLE IF EXISTS `finger_push_device`;
CREATE TABLE `finger_push_device` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `device_token` varchar(64) NOT NULL COMMENT '设备_token',
  `device_type` tinyint(4) NOT NULL COMMENT '设备_类型：1-IOS|2-Android',
  `last_device_token` varchar(64) NOT NULL DEFAULT '' COMMENT '上一次设备_token',
  `last_device_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上一次设备_类型(参考device_type字段)',
  `app_ver` varchar(20) NOT NULL DEFAULT '' COMMENT 'app_版本号',
  `last_login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后登录时间',
  `c_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `u_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`),
  KEY `idx_dt` (`device_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户设备唯一标识';

-- ----------------------------
-- Records of finger_push_device
-- ----------------------------

-- ----------------------------
-- Table structure for finger_sms_blacklist
-- ----------------------------
DROP TABLE IF EXISTS `finger_sms_blacklist`;
CREATE TABLE `finger_sms_blacklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(50) NOT NULL DEFAULT '' COMMENT '手机号',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1屏蔽状态 0已解除屏蔽',
  `c_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `u_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `body` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手机号黑名单表';

-- ----------------------------
-- Records of finger_sms_blacklist
-- ----------------------------

-- ----------------------------
-- Table structure for finger_sms_conf
-- ----------------------------
DROP TABLE IF EXISTS `finger_sms_conf`;
CREATE TABLE `finger_sms_conf` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '短信通道名称',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1短信 2语音',
  `uname` varchar(100) NOT NULL DEFAULT '' COMMENT '账户名',
  `upwd` varchar(100) NOT NULL DEFAULT '' COMMENT '密码',
  `level` tinyint(4) NOT NULL DEFAULT '1' COMMENT '优先级(升序)',
  `max_num` mediumint(9) NOT NULL DEFAULT '1' COMMENT '最高处理限额',
  `keywords` varchar(30) NOT NULL DEFAULT '' COMMENT '通道简写关键词',
  `other` varchar(1000) NOT NULL DEFAULT '' COMMENT '其他配置信息(一维数组的json格式)',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否启用 1启用 9关闭',
  `s_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '通道开始时间',
  `e_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '通道结束时间',
  `u_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `keywords` (`keywords`),
  KEY `status` (`status`,`e_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信通道配置表';

-- ----------------------------
-- Records of finger_sms_conf
-- ----------------------------

-- ----------------------------
-- Table structure for finger_sms_sendlog
-- ----------------------------
DROP TABLE IF EXISTS `finger_sms_sendlog`;
CREATE TABLE `finger_sms_sendlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mobile` varchar(15) NOT NULL DEFAULT '' COMMENT '手机号码',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '短信内容',
  `tpl_id` mediumint(9) NOT NULL DEFAULT '0' COMMENT '短信模板ID',
  `error_msg` varchar(255) NOT NULL DEFAULT '' COMMENT '回调内容',
  `sms_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：1-创建|2-发送成功|3-发送失败',
  `channel_id` mediumint(9) NOT NULL DEFAULT '0' COMMENT '短信通道标识',
  `s_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发送时间',
  `verify_code` varchar(10) NOT NULL DEFAULT '' COMMENT '验证码',
  `cksms` tinyint(4) NOT NULL DEFAULT '2' COMMENT '1已验证通过，2为未使用，3验证码已失效（验证大于等于3次）。',
  `sms_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1手机短信，2语音短信',
  `platform` tinyint(4) NOT NULL DEFAULT '0' COMMENT '平台类型设备(1-ios;2-android;3-wap;4-PC)',
  `ip` varchar(50) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `c_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `idx_t` (`mobile`),
  KEY `idx_smss` (`sms_status`),
  KEY `idx_ct` (`c_time`),
  KEY `idx_st` (`s_time`),
  KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统短信日志';


-- ----------------------------
-- Records of finger_sms_sendlog
-- ----------------------------

-- ----------------------------
-- Table structure for finger_sms_tpl
-- ----------------------------
DROP TABLE IF EXISTS `finger_sms_tpl`;
CREATE TABLE `finger_sms_tpl` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `send_key` varchar(30) NOT NULL DEFAULT '' COMMENT '短信模板KEY',
  `title` varchar(30) NOT NULL DEFAULT '' COMMENT '短信模板标题',
  `sms_body` varchar(100) NOT NULL DEFAULT '' COMMENT '短信模板内容',
  `trigger_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '触发类型：1-用户触发(验证码)、2-系统触发（推送通知）',
  `c_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `u_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `op_id` int(11) NOT NULL DEFAULT '0' COMMENT '最后操作者ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sk` (`send_key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='短信模板配置表';

-- ----------------------------
-- Records of finger_sms_tpl
-- ----------------------------
INSERT INTO `finger_sms_tpl` VALUES ('1', 'USER_REGISTER_CODE', '注册验证码', '验证码：%CODE%，您正在注册账号，请您10分钟内完成验证，客服不会向您索取验证码，切勿告知他人。【Yaf-Server-Admin】', '1', '2018-06-24 22:47:53', '2019-01-07 10:47:22', '1');
INSERT INTO `finger_sms_tpl` VALUES ('2', 'USER_LOGIN_CODE', '登录验证码', '验证码：%CODE%，您正在登录账号，请您10分钟内完成验证，客服不会向您索取验证码，切勿告知他人。【Yaf-Server-Admin】', '1', '2018-06-24 22:49:06', '2019-01-07 10:47:29', '1');
INSERT INTO `finger_sms_tpl` VALUES ('3', 'ADMIN_LOGIN_CODE', '管理后台登录验证码', '验证码：%CODE%，请您10分钟内完成验证。【混时间】', '1', '2018-07-30 15:20:11', '2018-09-29 11:34:23', '1');
INSERT INTO `finger_sms_tpl` VALUES ('4', 'USER_FIND_PWD', '找回密码验证码', '验证码：%CODE%，您正在找回密码，请您10分钟内完成验证，客服不会向您索取验证码，切勿告知他人。【Yaf-Server-Admin】', '1', '2018-08-15 09:59:42', '2019-01-07 10:47:32', '0');
INSERT INTO `finger_sms_tpl` VALUES ('5', 'EXCEPTION_LOGIN', '登录登录', '您的账号%MOBILE%刚刚在异地登录。请确认是否是本人操作。如果不是本人登录，请及时修改密码。【Yaf-Server-Admin】', '2', '2018-09-14 09:15:33', '2019-01-07 10:47:35', '0');

-- ----------------------------
-- Table structure for finger_user
-- ----------------------------
DROP TABLE IF EXISTS `finger_user`;
CREATE TABLE `finger_user` (
  `userid` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `mobile` char(11) NOT NULL DEFAULT '' COMMENT '手机号码',
  `salt` char(6) NOT NULL COMMENT '密码盐',
  `pwd` char(32) NOT NULL COMMENT '密码',
  `open_id` char(32) NOT NULL DEFAULT '' COMMENT '用户对外的标识',
  `nickname` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称(手机号码加*号)',
  `headimg` varchar(150) NOT NULL DEFAULT '' COMMENT '头像',
  `intro` varchar(200) NOT NULL DEFAULT '' COMMENT '简介',
  `platform` tinyint(4) NOT NULL DEFAULT '0' COMMENT '操作平台：0-无|1-IOS|2-Android|3-WAP|4-PC端',
  `app_market` varchar(15) NOT NULL DEFAULT '' COMMENT 'app应用市场',
  `last_login_ip` varchar(50) NOT NULL COMMENT '最后登录IP',
  `last_login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后登录时间',
  `cur_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态：0-无效|1-有效|2-锁定/禁用|3-冻结',
  `u_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  `c_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`userid`),
  UNIQUE KEY `uk_t` (`mobile`),
  UNIQUE KEY `uk_oid` (`open_id`),
  KEY `index_ct` (`c_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户表';

-- ----------------------------
-- Records of finger_user
-- ----------------------------
