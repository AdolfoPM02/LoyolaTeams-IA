# 🚀 LoyolaTeams-IA: Manual de Supervivencia Moodle 5.x

Este archivo contiene los comandos exactos para gestionar tu entorno de desarrollo local sin errores de rutas o base de datos.

---

## 📂 Rutas del Proyecto
- Código Moodle: /home/andres/Escritorio/PROYECTO_IA/LoyolaTeams-IA/moodle  
- Docker Tools: /home/andres/Escritorio/PROYECTO_IA/LoyolaTeams-IA/moodle-docker  

---

## ☀️ A. AL EMPEZAR A TRABAJAR (Levantar el entorno)

IMPORTANTE: Ejecuta esto cada vez que abras una terminal nueva para que Docker sepa dónde están tus archivos.

1. Configurar variables y entrar a la carpeta:
export MOODLE_DOCKER_WWWROOT=/home/andres/Escritorio/PROYECTO_IA/LoyolaTeams-IA/moodle  
export MOODLE_DOCKER_DB=mariadb  
cd /home/andres/Escritorio/PROYECTO_IA/LoyolaTeams-IA/moodle-docker  

2. Encender los motores:
bin/moodle-docker-compose start  

3. Acceso Web:
- URL: http://localhost:8000  
- User: admin  
- Pass: test  

---

## 🌙 B. AL TERMINAR DE TRABAJAR (Dormir el contenedor)

Haz esto antes de apagar el PC o cerrar VS Code para liberar memoria RAM.

Detener servicios (sin borrar datos):
cd /home/andres/Escritorio/PROYECTO_IA/LoyolaTeams-IA/moodle-docker  
bin/moodle-docker-compose stop  

---

## 🛠️ C. COMANDOS DE MANTENIMIENTO (Uso frecuente)

1. Ver cambios en el código (Purgar Caché)

Si tocas el código de tu chat de IA y no ves cambios en el navegador:
cd /home/andres/Escritorio/PROYECTO_IA/LoyolaTeams-IA/moodle-docker  
bin/moodle-docker-compose exec webserver php admin/cli/purge_caches.php  

2. Comprobar si todo está encendido

Si la web no carga, mira si los contenedores están en "Up":
bin/moodle-docker-compose ps  

3. Reinicio total (Si algo explota)
bin/moodle-docker-compose restart  