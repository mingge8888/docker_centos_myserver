@ECHO OFF&PUSHD %~DP0 &TITLE docker�����������߿��� ����:��� QQ2945157617
color 1f
@echo off
docker ps>nul 2>nul
if "%errorlevel%"=="0" (
color 1f
docker-compose kill
echo *******************************��ֹͣ����*******************************
)  else (
color 1c
echo  *******************************docker����û����************************ 
) 
echo �˳������������
pause> nul
exit