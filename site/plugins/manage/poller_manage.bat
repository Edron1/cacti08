@echo off
%~d0
cd %~p0
pskill.exe php-manage.exe
pskill.exe perl-manage.exe
php-manage.exe %~p0\poller_manage.php
exit
