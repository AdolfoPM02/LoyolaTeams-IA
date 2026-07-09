<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname']            = 'Asistente RAG';
$string['ragassistant:addinstance'] = 'Añadir bloque Asistente RAG';
$string['ragassistant:ask']      = 'Consultar al asistente RAG';

// Ajustes de administración
$string['settings']              = 'Configuración del Asistente RAG';
$string['apiurl']                = 'URL de la API FastAPI';
$string['apiurl_desc']           = 'URL base del servidor FastAPI RAG. Ejemplo: http://rag-api:8000';
$string['apikey']                = 'Token de autenticación';
$string['apikey_desc']           = 'Token Bearer para autenticar las peticiones a FastAPI.';
$string['timeout']               = 'Tiempo de espera (segundos)';
$string['timeout_desc']          = 'Segundos máximos de espera para la respuesta de FastAPI.';
$string['showsources']           = 'Mostrar fuentes';
$string['showsources_desc']      = 'Muestra las fuentes documentales junto a la respuesta.';
$string['debugmode']             = 'Modo depuración';
$string['debugmode_desc']        = 'Activa logs detallados de las llamadas a FastAPI.';

// Interfaz del bloque
$string['placeholder']           = 'Pregunta sobre los materiales de este curso...';
$string['askbutton']             = 'Preguntar';
$string['thinking']              = 'Consultando los materiales del curso...';
$string['sources']               = 'Fuentes';
$string['page']                  = 'página';
$string['errorapi']              = 'Error al conectar con el asistente. Inténtalo de nuevo.';
$string['errordegraded']         = 'El asistente no está disponible temporalmente. Inténtalo más tarde.';
$string['errorinvalid']          = 'No se ha podido procesar la consulta. Reformula tu pregunta.';
$string['nocontext']             = 'No he encontrado evidencia suficiente en los materiales disponibles del curso para responder con fiabilidad.';

// Errores internos
$string['invalidapiresponse']    = 'Respuesta inválida de la API RAG.';
$string['apikeynotset']          = 'El token de la API no está configurado. Contacta con el administrador.';
$string['apiurlnotset']          = 'La URL de la API no está configurada. Contacta con el administrador.';
