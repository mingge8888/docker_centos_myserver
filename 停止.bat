@ECHO OFF&PUSHD %~DP0 &TITLE docker容器启动工具开,发作者:岑明 QQ2945157617
:main
MODE con: COLS=70 lines=40
cls
color 1f
@echo off
docker ps>nul 2>nul
if "%errorlevel%"=="0" (
color 1f
docker-compose kill
echo       ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
echo       ┃                                                      ┃
echo       ┃            恭喜！已成功停止容器                      ┃
echo       ┃                                                      ┃
echo       ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━┛
)  else (
color 1c 
echo       ┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓
echo       ┃                                                      ┃
echo       ┃             停止失败！docker服务未启动               ┃
echo       ┃                                                      ┃
echo       ┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛
)
 
 echo                    执行完成！该窗口2秒后自动关闭
choice /t 2 /d y /n >nul 
exit  
  