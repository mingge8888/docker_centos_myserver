 <img src="docker-logo.jpg" width="200" hegiht="200" align="center" />

##  介绍docker_centos_myserver
  本项目以`centos7`为宿主容器，集成了`php-7.1.14`、`mysql-5.7`、`nginx-1.15.9`、`gitea`、`portainer`等等最新环境以及`sqlserver-5.6.0`、`swoole-4.0.3`、`redis-1.2.4`、`gd库`等最新扩展,也可以在此基础上增加其他组件,
      
  根目录下`conf`为PHP、nginx、php-fpm的挂载配置，可自行进行配置。
  
  
  根目录内 `启动.bat`、`停止.bat` 可以启动及停止容器(只限于window用户)。
   

##  docker基础教程
 * ##  [查看docker教程](http://www.docker.org.cn/book/)

## 1、安装docker
*  ##  [window版点击下载](https://download.docker.com/win/stable/Docker%20for%20Windows%20Installer.exe)

*   ##  [Mac版安装点击下载](https://download.docker.com/mac/stable/Docker.dmg)

*  ##  [cenot安装链接](https://docs.docker.com/install/linux/docker-ce/centos/)

*   ##  [ubuntu安装链接](https://docs.docker.com/install/linux/docker-ce/ubuntu/)


*    ### linux、Mac下docker-compose的安装
     ###  [docker-compose安装](https://docs.docker.com/compose/install/)

### 2、设置代理（重要）
   
  *  windows、Mac环境下，安装后完成后等待右下角鲸鱼图标静止为启动完成，
  *  windows、Mac环境下，启动完成后在右下角鲸鱼图标点击右键,再点击`Strings`,在界面的左边栏目找到`Daemon`,然后在右边`Registry mirrosrs`添加下面地址。
      ```html
      http://f1361db2.m.daocloud.io
      ```
      或
      ```html    
      http://hub-mirror.c.163.com
      ```
 *  linux下创建或修改配置 vi /etc/docker/daemon.json
     ```json
      {
      "registry-mirrors": [ "http://f1361db2.m.daocloud.io"]
      }
      ```
   
  * 这步必不可少，否则下载镜像时连接不上 
     

### 3、让docker有操作本地硬盘权限（window环境下重要）
  *   再次右下图标右键点击`Strings`,在界面的左边栏目找到`Shared Drives`,然后在右边,`docker_centos_myserver`在哪个磁盘就打勾， 。
     

### 4、启动docker_centos_myserver容器文件
   * 命令行进入项目 cd /docker_centos_myserver , 输入 `docker-compose up`。
   * window下最简单方式是双击项目根目录双击 `启动.bat`、`停止.bat` 文件，开机自动启动容器，只需要把 `启动.bat`快捷方式放入启动项即可。
   * 第一次起动容器部署需要一定时间，因机器配置或网络决定 
   * 部署完成查看http演试: [http://127.0.0.1/](http://127.0.0.1/)
   

### 5、容器管理
  ####   每次要输入繁琐的命令行,对于容器管理非常的不便，特别是新手，对于可视化管理容器提供下面两个方案：
 * 1、使用`vscode编辑器`，在vscode安装`docker`扩展工具
 * 2、使用项目自带的`portainer容器管理容器`,项目构建完成后打开[http://127.0.0.1:9000/](http://127.0.0.1:9000/) 即可以访问。 
            
    关于portainer设置教程，请点击 
     [https://cloud.tencent.com/developer/article/1351922](https://cloud.tencent.com/developer/article/1351922) 
  
### 6、注意事项
   * ####  `本项目默认占用80、3306、9000端口，`
     ####   `80是php端口、3306是mysql端口、9000是web容器管理端口，请保证以上端口不要被占用，否则容器动会启动失败，可自行在 docker-compose.yml 更改端口`。

   * ####  `切记: MYSQL是另开的容器，用php连接mysql时，连接地址127.0.0.1连不上的，应该填写容器名称,如: mysql`。

  * ####  `本项目本身具备跨平台通用性，不存在兼容性问题`。

### 项目作者
  * `岑明（号明哥，当代全栈高级工程师，而且长得很帅）`
  * `QQ:2945157617`
  * 2019/3/12 15:50

