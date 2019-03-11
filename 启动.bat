@ECHO OFF&PUSHD %~DP0 &TITLE docker容器启动工具,开发作者:岑明 QQ2945157617
:main
MODE con: COLS=70 lines=40
cls
color 1f
:start
docker ps>nul 2>nul
 if "%errorlevel%"=="0"  (
color 1f
echo       ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
echo       ┃                                                      ┃
echo       ┃              docker服务已启动,正在启动容器...        ┃
echo       ┃                                                      ┃
echo       ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━┛
docker-compose kill  
docker-compose up  -d
 )  else (
color 1c
echo       ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
echo       ┃                                                      ┃
echo       ┃             docker服务未启动,正在重试...             ┃
echo       ┃                                                      ┃
echo       ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛ 
choice /t 4 /d y /n >nul
goto start
 )
 echo                    执行完成！该窗口2秒后自动关闭
choice /t 2 /d y /n >nul 
exit  
 