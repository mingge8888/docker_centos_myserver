 <img src="docker-logo.jpg" width="200" hegiht="200" align="center" />

##  介绍docker_centos_myserver
  本项目以`centos7`为宿主容器，集成了`php-7.1.14`、`mysql-5.7`、`nginx-1.15.9`、`gitea`、`portainer`等等最新环境以及`sqlserver-5.6.0`、`swoole-4.0.3`、`redis-1.2.4`、`gd库`等最新扩展,也可以在此基础上增加其他组件,所有配置文件在项目中的`conf`目录内，请自行进行配置, centos部署环境就是这么简单。
  * 根目录内 `启动.bat`、`停止.bat` 可以启动及停止容器。
   

##  docker官方详细教程
 * ##  [查看官方教程](http://www.docker.org.cn/book/)

## 1、安装docker
*  ##  [点击下载](https://download.docker.com/win/stable/Docker%20for%20Windows%20Installer.exe)


### 2、设置代理（重要）
  *  安装后完成后等待右下角鲸鱼图标静止为启动完成，
  *  启动完成后在右下角鲸鱼图标点击右键,再点击`Strings`,在界面的左边栏目找到`Daemon`,然后在右边`Registry mirrosrs`添加下面地址。
 *   ```html
      http://f1361db2.m.daocloud.io
     ```
     

### 3、让docker有操作本地硬盘权限（重要）
  *   再次右下图标右键点击`Strings`,在界面的左边栏目找到`Shared Drives`,然后在右边,`docker_centos_myserver`在哪个磁盘就打勾，第2、3步必不可少。
     

### 4、启动docker_centos_myserver容器文件
   * 命令行进入项目 cd /docker_centos_myserver , 输入 `docker-compose up`。
   * 最简单方式是双击项目根目录双击 `启动.bat`、`停止.bat` 文件，开机自动启动容器，只需要把 `启动.bat`快捷方式放入启动项即可。
   * 第一次起动容器部署需要一定时间，因机器配置或网络决定 
   * 部署完成查看http演试: [http://127.0.0.1/](http://127.0.0.1/)
   

### 5、容器管理
 * ### 每次要输入繁琐的命令行,对于容器管理非常不便，特别是新手。对于管理管理提供两个方案
 * 1：使用`vscode编辑器`，在vscode安装`docker`扩展工具
 * 2：使用项目自带的`portainer容器管理面板`， [http://127.0.0.1:9000/](http://127.0.0.1:9000/) 即可以访问， 
     portainer设置教程，请点击 
     [https://cloud.tencent.com/developer/article/1351922](https://cloud.tencent.com/developer/article/1351922) 

### 5、注意事项
   * ###  `本安装教程只适用于window用户,linux或mac用户此教程不适用,请自己百度相关文档,搜索docker安装以及docker-compose安装即可。项目本身跨平台通用，不存在兼容性问题`。
    
   * ###  `本项目默认使用80、9501、3306、9000端口来挂载演试项目，请保证80、9501、3306、9000端口不要被占用，否则容器动会启动失败，也可在 docker-compose.yml自行更改端口`。

   * ###  `切记: MYSQL是另开的容器，用php连接mysql时，连接地址127.0.0.1连不上的，应该填写容器名称,如: mysql`。


### 项目作者
  * `岑明（号明哥，当代全栈高级工程师，而且长得很帅）`
  * `QQ:2945157617`
  * 2019/3/11 15:50

