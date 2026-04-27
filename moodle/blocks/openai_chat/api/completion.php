<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * API endpoint for retrieving GPT completion
 *
 * @package    block_openai_chat
 * @copyright  2023 Bryce Yoder <me@bryceyoder.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \block_openai_chat\completion;

require_once('../../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/blocks/openai_chat/lib.php');

global $DB, $PAGE;

if (get_config('block_openai_chat', 'restrictusage') !== "0") {
    require_login();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: $CFG->wwwroot");
    die();
}

$body = json_decode(file_get_contents('php://input'), true);
$message = clean_param($body['message'], PARAM_NOTAGS);
$history = clean_param_array($body['history'], PARAM_NOTAGS, true);
$block_id = clean_param($body['blockId'], PARAM_INT, true);

$instance_record = $DB->get_record('block_instances', ['blockname' => 'openai_chat', 'id' => $block_id], '*');
$instance = block_instance('openai_chat', $instance_record);
if (!$instance) {
    throw new moodle_exception('invalidblockinstance', 'error', $block_id);
}

$context = context::instance_by_id($instance_record->parentcontextid); #Cogemos el contexto del bloque padre 
$PAGE->set_context($context);

#NUEVO CODIGO RAG PARA ENVIAR APUNTES DEL CURSO COMO CONTEXTO ADICIONAL
$apuntes_curso = "";
$courseid = 0;

#Si el bloque está dentro del curso 
if ($context->contextlevel == CONTEXT_COURSE) {
    $courseid = $context->instanceid;
} 
#Si el bloque está dentro de un archivo 
elseif ($context->contextlevel == CONTEXT_MODULE) {
    $cm = get_coursemodule_from_id('', $context->instanceid);
    if ($cm) {
        $courseid = $cm->course;
    }
}

#Si encontramos el curso , extraemos los apuntes 
if ($courseid > 0) {
    $apuntes_curso = obtener_texto_archivos_curso($courseid);
}

// Set block instance settings
$blocksettings = [
    'sourceoftruth' => '',
    'prompt' => '',
    'username' => '',
    'assistantname' => ''
];
foreach ($blocksettings as $settingname => $value) {
    if ($instance->config && property_exists($instance->config, $settingname) && $instance->config->$settingname) {
        $blocksettings[$settingname] = $instance->config->$settingname;
    }
}

$mensaje_para_la_ia = $message; // Hacemos una copia para no romper tu base de datos

if(!empty($apuntes_curso)){
    $mensaje_para_la_ia = "Actúa como un profesor de la Universidad Loyola. Te voy a dar un contexto y luego una pregunta del alumno.\n";
    $mensaje_para_la_ia .= "REGLA OBLIGATORIA: Responde a la pregunta utilizando ÚNICA Y EXCLUSIVAMENTE el contexto. No uses conocimientos externos. Si la respuesta no está, di literalmente: 'Lo siento, esa información no está en los apuntes del profesor'.\n\n";
    $mensaje_para_la_ia .= "=== CONTEXTO DE LA ASIGNATURA ===\n" . $apuntes_curso . "\n=== FIN DEL CONTEXTO ===\n\n";
    $mensaje_para_la_ia .= "PREGUNTA DEL ALUMNO: " . $message;
}


$completion = new completion($mensaje_para_la_ia, $history, $blocksettings); #Le pasamos el mensaje con el contexto inyectado.
$response = $completion->create_completion($PAGE->context); #Le pasamos el contexto del bloque para que pueda usarlo en la función de log_message() y así guardar el contexto junto al mensaje y la respuesta en la base de datos.

#La IA devuelve la respuesta en formato markdown , esto lo que hace es formatear para que moodle lo puede inyectar en codigo html sin que se rompa el formato markdown y se vea bien en el chat. Además, al formatear con FORMAT_MARKDOWN, también se asegura de que cualquier enlace o formato especial incluido en la respuesta de la IA se muestre correctamente en el chat de Moodle.
$response["message"] = format_text($response["message"], FORMAT_MARKDOWN, ['context' => $context]);

#Guarda en la base de datos el mensaje del usuario, la respuesta de la IA y el contexto del bloque (que puede ser el curso o el módulo) para futuras consultas o para que el profesor pueda revisar los chats de los alumnos. Esto solo se hace si la opción de logging está activada en la configuración del bloque.
log_message($message, $response['message'], $context);

$response = json_encode($response); 
echo $response;