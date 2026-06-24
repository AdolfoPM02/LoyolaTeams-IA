<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname']                = 'Indexador RAG';
$string['ragindexer:addinstance']    = 'Añadir bloque Indexador RAG';
$string['ragindexer:indexcourse']    = 'Indexar materiales del curso';
$string['ragindexer:forcereindex']   = 'Forzar reindexación completa';

// Ajustes de administración.
$string['settings']                  = 'Configuración del Indexador RAG';
$string['apiurl']                    = 'URL del backend de indexación';
$string['apiurl_desc']               = 'URL base del backend de indexación. Ejemplo: http://rag-api:8000';
$string['apikey']                    = 'Token de autenticación';
$string['apikey_desc']               = 'Token Bearer para autenticar las peticiones al backend de indexación.';
$string['timeout']                   = 'Tiempo de espera (segundos)';
$string['timeout_desc']              = 'Segundos máximos de espera para la respuesta del backend de indexación.';

// Interfaz del bloque.
$string['backendpending']            = 'La indexación del curso depende de un backend que puede no estar disponible todavía.';
$string['indexbutton']               = 'Actualizar índice del curso';
$string['indexing']                  = 'Indexando materiales...';
$string['indexok']                   = 'Índice actualizado correctamente.';
$string['indexerror']                = 'Error al actualizar el índice. El backend de indexación puede no estar disponible todavía.';

// Errores internos.
$string['invalidapiresponse']        = 'Respuesta inválida del backend de indexación.';
$string['apiurlnotset']              = 'La URL del backend de indexación no está configurada. Contacta con el administrador.';
