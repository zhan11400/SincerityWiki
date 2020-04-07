# SincerityWiki
基于TP6开发的一款针对IT团队开发的简单好用的文档管理系统
可以用来储存日常接口文档，数据库字典，手册说明等文档。内置项目管理，用户管理，权限管理等功能，能够满足大部分中小团队的文档管理需求。
## 安装与部署
1，从GitHub下载源码后，执行composer下载vendor
```
 composer install
```
2，修改站点根目录的.env配置文件，若配置出错则以下步骤都失败

3,执行创建数据库操作
```
 php think CreateDatabase
```
4，数据表迁移
```
php think magrate:run
```
5，数据填充
```
php think seed:run
```
6，操作完成后，后台登录账号密码为admin 123456

## 使用的技术
- thinkphp  6.0.2
- mysql 5.6
- editor.md
- bootstrap 3.2
- jquery 库
- layer 弹出层框架
- webuploader 文件上传框架
- Nprogress 库
- jstree 
- font awesome 字体库
- cropper 图片剪裁库

## 功能
1. 项目管理，可以对项目进行编辑更改，成员添加等。
2. 文档管理，添加和删除文档，文档历史恢复等。
3. 用户管理，添加和禁用用户，个人资料更改等。
4. 用户权限管理 ， 实现用户角色的变更。
5. 项目加密，可以设置项目公开状态为私密、半公开、全公开。
6. 站点配置，二次开发时可以添加自定义配置项。
7. 项目导出
