@ECHO OFF&PUSHD %~DP0 &TITLE docker容器启动工具开发 作者:岑明 QQ2945157617
color 1f
@echo off
docker ps>nul
if "%errorlevel%"=="0" (
color 1f
docker-compose kill
echo *******************************已停止容器*******************************
)  else (
color 1c
echo  *******************************docker服务还没启动************************ 
) 
echo 退出……按任意键
pause> nul
exit