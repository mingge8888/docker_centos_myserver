@ECHO OFF&PUSHD %~DP0 &TITLE docker�����������߿�,������:��� QQ2945157617
:main
MODE con: COLS=70 lines=40
cls
color 1f
@echo off
docker ps>nul 2>nul
if "%errorlevel%"=="0" (
color 1f
docker-compose kill
echo       ����������������������������������������������������������
echo       ��                                                      ��
echo       ��            ��ϲ���ѳɹ�ֹͣ����                      ��
echo       ��                                                      ��
echo       ����������������������������������������������������������
)  else (
color 1c 
echo       ����������������������������������������������������������������������
echo       ��                                                      ��
echo       ��             ֹͣʧ�ܣ�docker����δ����               ��
echo       ��                                                      ��
echo       ����������������������������������������������������������������������
)
 
 echo                    ִ����ɣ��ô���2����Զ��ر�
choice /t 2 /d y /n >nul 
exit  
  