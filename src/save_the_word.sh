#rm -r /tmp/symfony
rm -r app/cache
rm -r app/logs

#mkdir /tmp/symfony
mkdir app/cache
mkdir app/logs

#chmod 777 -R /tmp
chmod 777 -R app/cache
chmod 777 -R app/logs

printf "Caché Limpiada Correctamente\n"