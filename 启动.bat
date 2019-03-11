node-v10.15.3-linux-x64/bin
@ECHO OFF
@ECHO OFF&PUSHD %~DP0 &TITLE docker容器启动工具开发 作者:岑明 QQ2945157617
:main
MODE con: COLS=70 lines=59
cls
color 1f
set INTERVAL=10
:Again
docker ps>nul 2>nul
if "%errorlevel%"=="1" (
color 1c
echo       ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
echo       ┃                                                      ┃
echo       ┃             docker服务未启动,正在重试...             ┃
echo       ┃                                                      ┃
echo       ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛
 
timeout %INTERVAL%>nul
 
goto Again
)  else (
color 1f
echo       ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
echo       ┃                                                      ┃
echo       ┃              docker服务已启动,正在启动容器...        ┃
echo       ┃                                                      ┃
echo       ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━┛
 
docker-compose kill
docker-compose up
 )
cmd /K

 