##项目初始化
第一步:  
git clone git@47.97.19.81:wechat-game-api 
第二步:  
php composer.phar install  
第三步:  
访问 http://localhost/ 看是否有信息  
第四步:  
新建自己拼音分支，然后push到远程
  
##常用命令  
api文档生成   
首先安装 nodejs npm  
npm install apidoc -g  
apidoc -i application/backend/controller/ -o public/docs/backend   
apidoc -i application/api/controller/ -o public/docs/api 
composer dumpautoload -o  
php think build --config build.php 

##目录结构模块说明
application  
api 移动端和前端的接口  
backend 后台的接口 菜单管理 后台用户管理 角色管理    
user 用户相关  
cms 内容管理 活动管理 广告管理
payment 支付钱包       
message 消息管理 短信 推送  
cron 定时脚本  
system 系统配置 参数设置 规则管理 评分卡 评级 第三方配置  
common 公共基础类 基础函数等 基础配置文件  
  
database 放置全量sql文件  
database/migration 放置数据增量sql文件 
tests 单元测试  
extend/helper 工具类  
extend/service 第三方调用二次封装  
extend/thirdpart 第三方接口类（非composer 第三方） 

##开发注意事项
如果第三方有依赖包，通过composer引入  
如果没有放到extend 目录下

api 和 backend   
    主要功能入口控制器 引用 其他模块的logic（业务实现） 不能直接引用model  
其他模块  
    主要功能model（数据层）logic（业务层） service (服务层)
业务模块 除common模块以为 其他模块的 控制器继承 BaseController 模型继承 BaseModel 逻辑继承 BaseLogic   
route 代表整个应用的路由  
目前主要分api和backend 二块分组  
里面可以再细化 分需要登录的和不需要登录的 
根目录下config放公用的配置文件，模块下的配置文件config指针对各自模块的 
框架开发规则 参考 README.md 文件

##表结构命名  
前缀以项目 miyin_  
模块前缀 user_  
字段小写 多个单词用_进行分割

比如 miyin_backend_user 后台用户  
·
普通表里面包含常用的几个字段  
created_at 创建时间  
updated_at 修改时间  
deleted_at 逻辑删除  

##版本控制
dev 开发分支 合并所有人的分支 用于开发和测试  
master 线上分支 每次更新打tag版本号 如 v1.0.0  
自己分支 如 cuidongming   
初始流程  
 1.克隆项目 2.切换到dev分支 3.创建自己的本地分支   4.推送自己的分支到远程  
开发流程  
 1.自己分支开发后先commit 2.拉远程dev分支内容并合并到本地   3.推送自己的分支到远程  
测试流程   
1.切换本地dev分支 2.拉取dev分支 3.合并远程开发人员分支   4.推送dev到远程分支  
生产流程   
1.把测试验收过的dev分支合并到master 2.master拉到服务器并打好版本号

##接口文档规范
参考backend/controller/Demo.php与back/logic/DemoLogic.php


##相关文档
Thinkphp5手册地址https://www.kancloud.cn/manual/thinkphp5/118003