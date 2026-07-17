@echo off
setlocal EnableDelayedExpansion
title Push to GitHub - surreytuning
cd /d "%~dp0"

echo.
echo ============================================
echo    SURREYTUNING - SAVE AND PUSH TO GITHUB
echo ============================================
echo.

where git >nul 2>nul
if errorlevel 1 (
    echo [ERROR] Git is not installed or not in PATH.
    goto end
)

rem --- One-time identity setup if git doesn't know who you are ---
set GITNAME=
for /f "delims=" %%i in ('git config user.name 2^>nul') do set GITNAME=%%i
if not defined GITNAME (
    echo Git needs your name and email once, so commits are labelled.
    set /p GITNAME="Your name: "
    set /p GITEMAIL="Your email: "
    git config --global user.name "!GITNAME!"
    git config --global user.email "!GITEMAIL!"
    echo.
)

echo Checking for changed files...
echo.
set HASCHANGES=
for /f "delims=" %%i in ('git status --porcelain 2^>nul') do set HASCHANGES=1

if defined HASCHANGES (
    echo These files have changed since the last push:
    echo --------------------------------------------
    git status --short
    echo --------------------------------------------
    echo.
    set MSG=
    set /p MSG="Describe the change, or just press Enter: "
    if "!MSG!"=="" set MSG=Site update %date% %time:~0,5%
    git add -A
    git commit -m "!MSG!"
    if errorlevel 1 (
        echo.
        echo [ERROR] Commit failed - see the message above.
        echo Nothing was pushed. Ask Claude: "my commit failed" and paste the text above.
        goto end
    )
) else (
    echo No new file changes found - will push any waiting commits.
)

echo.
echo Pushing to GitHub...
git push
if errorlevel 1 (
    echo.
    echo First push attempt failed - syncing with GitHub and retrying...
    git pull --rebase --autostash
    if errorlevel 1 (
        git rebase --abort >nul 2>nul
        echo.
        echo [ERROR] Could not automatically sync with GitHub.
        echo Most likely the code on GitHub conflicts with your local changes.
        echo Nothing has been lost. Tell Claude: "my push failed" and it can take a look.
        goto end
    )
    git push
    if errorlevel 1 (
        echo.
        echo [ERROR] Push is still failing.
        echo.
        echo If you saw "Authentication failed" or "Permission denied" above,
        echo your GitHub login on this PC has expired. To fix it:
        echo    1. Open Start menu, search "Credential Manager", open it
        echo    2. Click "Windows Credentials"
        echo    3. Remove any entries starting with git:https://github.com
        echo    4. Run this script again - a GitHub login window will appear
        goto end
    )
)

echo.
echo ============================================
echo    SUCCESS - everything is on GitHub.
echo    You can now update the production site.
echo ============================================

:end
echo.
pause
