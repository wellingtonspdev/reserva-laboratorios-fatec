@echo off
setlocal

set "PROJECT_DIR=%~dp0"
set "HOST=127.0.0.1"
set "PORT=8000"
set "MYSQL_SERVICE=MySQL80"
set "LOCAL_PHP=%PROJECT_DIR%local\tools\php\php.exe"
set "XAMPP_PHP=C:\xampp\php\php.exe"

echo.
echo ClassroomBookings - inicializacao rapida
echo Projeto: %PROJECT_DIR%
echo URL: http://%HOST%:%PORT%/
echo.

sc query "%MYSQL_SERVICE%" >nul 2>&1
if not errorlevel 1 (
    echo Verificando servico %MYSQL_SERVICE%...
    sc query "%MYSQL_SERVICE%" | findstr /I "RUNNING" >nul 2>&1
    if errorlevel 1 (
        echo Iniciando %MYSQL_SERVICE%...
        net start "%MYSQL_SERVICE%"
    ) else (
        echo %MYSQL_SERVICE% ja esta em execucao.
    )
) else (
    echo Aviso: servico %MYSQL_SERVICE% nao encontrado. Verifique o MySQL manualmente se o app falhar.
)

if exist "%LOCAL_PHP%" (
    set "PHP_BIN=%LOCAL_PHP%"
) else if exist "%XAMPP_PHP%" (
    set "PHP_BIN=%XAMPP_PHP%"
) else (
    where php >nul 2>&1
    if not errorlevel 1 (
        set "PHP_BIN=php"
    ) else (
        echo.
        echo ERRO: PHP nao encontrado.
        echo Instale o XAMPP ou adicione o PHP ao PATH.
        pause
        exit /b 1
    )
)

echo.
echo Usando PHP: %PHP_BIN%
echo Subindo servidor em http://%HOST%:%PORT%/
echo Pressione Ctrl+C para parar.
echo.

cd /d "%PROJECT_DIR%"
"%PHP_BIN%" -S %HOST%:%PORT%

endlocal
