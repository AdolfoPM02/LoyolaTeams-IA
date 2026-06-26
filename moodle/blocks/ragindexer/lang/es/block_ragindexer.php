<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname']                = 'Indexador RAG';
$string['ragindexer:addinstance']    = 'Añadir bloque Indexador RAG';
$string['ragindexer:indexcourse']    = 'Indexar materiales del curso';
$string['ragindexer:forcereindex']   = 'Forzar reindexación completa';

// Ajustes de administración.
$string['settings']                  = 'Configuración del Indexador RAG';
$string['apiurl']                    = 'URL del backend de indexación';
$string['apiurl_desc']               = 'URL base del backend FastAPI de indexación. Ejemplo: http://host.docker.internal:8001';
$string['apikey']                    = 'Token del indexador';
$string['apikey_desc']               = 'Token Bearer para autenticar las peticiones al backend de indexación (se guarda oculto).';
$string['timeout']                   = 'Tiempo de espera (segundos)';
$string['timeout_desc']              = 'Segundos máximos de espera para la respuesta del backend de indexación.';

// Interfaz del bloque.
$string['indexbutton']               = 'Actualizar índice del curso';
$string['forcereindex']              = 'Forzar reindexación completa';
$string['indexing']                  = 'Indexando materiales...';
$string['indexerror']                = 'Error al actualizar el índice. Inténtalo de nuevo.';
$string['indexpartialnotice']        = 'La indexación se completó parcialmente. Algunos recursos no pudieron procesarse.';
$string['errorsheading']             = 'Incidencias';

// Estados del resultado.
$string['statusok']                  = 'Curso indexado correctamente.';
$string['statuspartial']             = 'Curso indexado parcialmente.';
$string['statusfailed']              = 'La indexación ha fallado.';

// Contadores del resultado.
$string['resdocsnew']                = 'Documentos nuevos';
$string['resdocsmodified']           = 'Documentos modificados';
$string['resdocsdeleted']            = 'Documentos eliminados';
$string['reschunksadded']            = 'Chunks añadidos';
$string['reschunksstale']            = 'Chunks eliminados/obsoletos';
$string['resembeddings']             = 'Embeddings recalculados';
$string['resvisibility']             = 'Actualizaciones de visibilidad';

// Errores de transporte / internos (sin trazas, sin token).
$string['backendunavailable']        = 'El backend de indexación no está disponible ahora mismo. Inténtalo más tarde.';
$string['errorinvalidrequest']       = 'La petición de indexación fue rechazada. Contacta con el administrador.';
$string['errorunauthorized']         = 'El backend de indexación rechazó la autenticación. Contacta con el administrador.';
$string['errorinternal']             = 'El backend de indexación reportó un error interno. Inténtalo más tarde.';
$string['invalidapiresponse']        = 'Respuesta inválida del backend de indexación.';
$string['apiurlnotset']              = 'La URL del backend de indexación no está configurada. Contacta con el administrador.';
$string['tokennotset']               = 'El token del indexador no está configurado. Contacta con el administrador.';
