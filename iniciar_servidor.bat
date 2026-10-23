@echo off
title Listo - Servidor Local
color 0b
echo ===================================================
echo             LISTO - INICIALIZADOR LOCAL
echo ===================================================
echo.
echo Iniciando servidor PHP embutido em http://localhost:8000 ...
echo Pressione CTRL+C nesta janela para parar o servidor.
echo.

start http://localhost:8000

"bin\php\php.exe" -S localhost:8000 -t .
pause
