@echo off
cd /d "c:/xampp/htdocs/comercioPlusOficial"
echo Actualizando desde GitHub...
git stash push -m "auto-backup-local-$(date /t)"
git pull origin master
git stash pop
echo ✅ Actualizacion completada!
pause

