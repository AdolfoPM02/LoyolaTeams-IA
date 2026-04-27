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


## 🧠 D. DESARROLLO DEL PLUGIN IA (Implementación RAG Automática)

Hoja de ruta para convertir el bloque openai_chat en un tutor virtual que lea automáticamente los apuntes del curso.
# Fase 1: El Extractor de Información (Backend)

    Archivo a modificar: /blocks/openai_chat/lib.php

    Objetivo: Crear una función (ej. obtener_texto_archivos_curso($courseid)) que utilice la API de Moodle (get_fast_modinfo) y la File API (get_file_storage()) para rastrear recursos del curso y extraer su contenido en texto plano.

# Fase 2: El Contexto del Curso (Frontend -> Backend)

    Archivos a modificar: /blocks/openai_chat/block_openai_chat.php y /blocks/openai_chat/amd/src/lib.js

    Objetivo: Capturar el $COURSE->id donde se encuentra el alumno y enviarlo mediante JavaScript (AJAX) cada vez que el usuario pulse el botón de enviar mensaje.

# Fase 3: La Intercepción del Mensaje (API)

    Archivo a modificar: /blocks/openai_chat/api/completion.php (o /classes/completion.php)

    Objetivo: Interceptar la petición antes de enviarla a OpenAI. Inyectar un System Prompt invisible que diga: "Eres un tutor de la Universidad Loyola. Responde a la pregunta usando SOLO esta información: [TEXTO_EXTRAIDO_FASE_1]".

# Fase 4: Lectura Avanzada de PDFs (Librería Externa)

Por defecto, PHP solo procesa archivos .txt. Para leer los PDFs de los profesores, hay que instalar un parser dentro del contenedor de Docker.

    Comando de instalación (Ejecutar dentro de la carpeta del plugin):

Bash

composer require smalot/pdfparser
