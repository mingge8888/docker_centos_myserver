 <img src="docker-logo.jpg" width="200" hegiht="200" align="center" />

##  介绍docker_centos_myserver
  本项目以`centos7`为宿主容器，集成了`sqlserver-5.6.0`、`swoole-4.0.3`、`redis-1.2.4`、`gd库`等最新扩展以及 `php-7.1.14`、`mysql-5.7`、`nginx-1.15.9`等等最新环境 ,也可以在此基础上增加其他组件,所有配置文件在项目中的`conf`目录内，请自行进行配置,centos部署环境就是这么简单

##  docker官方详细教程
 * ##  [查看详细教程](http://www.docker.org.cn/book/)

## 1、安装docker
*  ##  [点击下载](https://download.docker.com/win/stable/Docker%20for%20Windows%20Installer.exe)


### 2、设置代理
  *  安装后完成后等待右下角鲸鱼图标静止为启动完成，
  *  启动完成后在右下角鲸鱼图标点击右键,再点击`Strings`,在界面的左边栏目找到`Daemon`,然后在右边`Registry mirrosrs`添加下面地址
     ### `http://f1361db2.m.daocloud.io`

### 3、启动docker_centos_myserver容器文件
   * 建议使用`vscode编辑器`，再在vscode安装`docker`工具，这样   你会少打很多繁琐的命令，更容易上手docker
   * 命令行进入项目 cd /docker_centos_myserver , 输入 `docker-compose up`  或者`docker-compose up --build` 
   * 部署需要一定时间，因机器配置或网络决定 
   * 部署完成查看http演试: [http://127.0.0.1/](http://127.0.0.1/)

### 4、注意事项
   * ###  `本项目默认使用80、9501、3306端口来挂载演试项目，请保证80、9501、3306端口不要被占用，否则容器动会启动失败，也可在 docker-compose.yml自行更改端口`
   * ###  `切记: MYSQL是另开的容器，用php连接mysql时，连接地址127.0.0.1连不上的，应该填写容器名称,如: mysql`


### 项目作者
  * `岑明（号明哥，当代全栈高级工程师，而且长得很帅）`
  * `QQ:2945157617`
  * 2019/3/10 15:50

