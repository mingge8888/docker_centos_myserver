@ECHO OFF&PUSHD %~DP0 &TITLE docker������������,��������:��� QQ2945157617
MODE con: COLS=70 lines=40
cls
color 1f 
echo   ��������������������������������
echo   ��                                                           ��
echo   �� ��ʾ: �������ʹ���ֶ�CMD���docker-compose up --build  ��
echo   ��                                                           ��
echo   ��������������������������������
:start
docker ps>nul 2>nul
 if "%errorlevel%"=="0"  (
color 1f
echo       ����������������������������������������������������������
echo       ��                                                      ��
echo       ��              docker����������,������������...        ��
echo       ��                                                      ��
echo       ����������������������������������������������������������
docker-compose kill  
if exist mysql/data/mysql (
docker-compose up -d 
echo                    ִ����ɣ��ô���2����Զ��ر�
choice /t 2 /d y /n >nul 
exit 
)else (
docker-compose up
cmd /k
)
)  else (
color 1c
echo       ����������������������������������������������������������������������
echo       ��                                                      ��
echo       ��             docker����δ����,��������...             ��
echo       ��                                                      ��
echo       ���������������������������������������������������������������������� 
choice /t 4 /d y /n >nul
goto start
 )

 