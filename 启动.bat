@ECHO OFF&PUSHD %~DP0 &TITLE docker�����������߿��� ����:��� QQ2945157617
color 1f
set INTERVAL=10
:Again
docker ps>nul
if "%errorlevel%"=="1" (
color 1c
echo *******************************����ʧ�ܣ�����û����,��������......*******************************
echo ����������������...... �������ִ�����ԣ�
timeout %INTERVAL%>nul
 
goto Again
)  else (
color 1f
echo *******************************docker������������������������*******************************
docker-compose kill
docker-compose up
 
) 
 cmd /K

 