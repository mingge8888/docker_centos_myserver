@ECHO OFF&PUSHD %~DP0 &TITLE docker容器启动工具开发 作者:岑明 QQ2945157617
color 1f
set INTERVAL=10
:Again
docker ps>nul
if "%errorlevel%"=="1" (
color 1c
echo *******************************启动失败，服务还没启动,正在重试......*******************************
echo 正在重试启动容器...... （任意键执行重试）
timeout %INTERVAL%>nul
 
goto Again
)  else (
color 1f
echo *******************************docker服务已启动，正在运行容器*******************************
docker-compose kill
docker-compose up
 
) 
 cmd /K

 