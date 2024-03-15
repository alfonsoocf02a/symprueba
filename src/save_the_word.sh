printf "SALVANDO EL MUNDO, UN MOMENTO POR FAVOR.\n"
printf "${GRAY}+++++++++++++++++++++++++${NC}\n"

printf "1. Borramos la carpeta de cache...\n"
printf "${GRAY}+++++++++++++++++++++++++${NC}\n"

#rm -r /tmp/symfony
rm -r app/cache

printf "2. Borramos la carpeta de logs...\n"
printf "${GRAY}+++++++++++++++++++++++++${NC}\n"

rm -r app/logs

printf "3. Creamos la carpeta cache...\n"
printf "${GRAY}+++++++++++++++++++++++++${NC}\n"

#mkdir /tmp/symfony
mkdir app/cache

printf "4. Creamos la carpeta logs...\n"
printf "${GRAY}+++++++++++++++++++++++++${NC}\n"

mkdir app/logs

printf "5. Dando permisos 777 a la carpeta cache...\n"
printf "${GRAY}+++++++++++++++++++++++++${NC}\n"

#chmod 777 -R /tmp
chmod 777 -R app/cache

printf "6. Dando permisos 777 a la carpeta logs...\n"
printf "${GRAY}+++++++++++++++++++++++++${NC}\n"

chmod 777 -R app/logs