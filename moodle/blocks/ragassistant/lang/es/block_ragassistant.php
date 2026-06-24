<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname']            = 'Asistente RAG';
$string['ragassistant:addinstance'] = 'Añadir bloque Asistente RAG';
$string['ragassistant:ask']      = 'Consultar al asistente RAG';
$string['ragassistant:indexcourse'] = 'Indexar materiales del curso';
$string['ragassistant:forcereindex'] = 'Forzar reindexación completa';

// Ajustes de administración
$string['settings']              = 'Configuración del Asistente RAG';
$string['apiurl']                = 'URL de la API FastAPI';
$string['apiurl_desc']           = 'URL base del servidor FastAPI RAG. Ejemplo: http://rag-api:8000';
$string['apikey']                = 'Token de autenticación';
$string['apikey_desc']           = 'Token Bearer para autenticar las peticiones a FastAPI.';
$string['timeout']               = 'Tiempo de espera (segundos)';
$string['timeout_desc']          = 'Segundos máximos de espera para la respuesta de FastAPI.';
$string['topk']                  = 'Número de fragmentos (top_k)';
$string['topk_desc']             = 'Número de fragmentos de documentos que se recuperan para cada consulta.';
$string['showsources']           = 'Mostrar fuentes';
$string['showsources_desc']      = 'Muestra las fuentes documentales junto a la respuesta.';
$string['showconfidence']        = 'Mostrar confianza';
$string['showconfidence_desc']   = 'Muestra el nivel de confianza de la respuesta.';
$string['allowindexing']         = 'Permitir indexación por profesores';
$string['allowindexing_desc']    = 'Permite a los profesores lanzar la indexación del curso desde el bloque.';
$string['debugmode']             = 'Modo depuración';
$string['debugmode_desc']        = 'Activa logs detallados de las llamadas a FastAPI.';

// Interfaz del bloque
$string['placeholder']           = 'Pregunta sobre los materiales de este curso...';
$string['askbutton']             = 'Preguntar';
$string['indexbutton']           = 'Actualizar índice del curso';
$string['thinking']              = 'Consultando los materiales del curso...';
$string['sources']               = 'Fuentes';
$string['page']                  = 'página';
$string['errorapi']              = 'Error al conectar con el asistente. Inténtalo de nuevo.';
$string['nocontext']             = 'No he encontrado información suficiente en los materiales del curso para responder con seguridad.';
$string['indexing']              = 'Indexando materiales...';
$string['indexok']               = 'Índice actualizado correctamente.';
$string['indexerror']            = 'Error al actualizar el índice.';

// Errores internos
$string['invalidapiresponse']    = 'Respuesta inválida de la API RAG.';
$string['apikeynotset']          = 'El token de la API no está configurado. Contacta con el administrador.';
$string['apiurlnotset']          = 'La URL de la API no está configurada. Contacta con el administrador.';
