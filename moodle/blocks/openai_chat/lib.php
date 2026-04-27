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
 * General plugin functions
 *
 * @package    block_openai_chat
 * @copyright  2023 Bryce Yoder <me@bryceyoder.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * If setting is enabled, log the user's message and the AI response
 * @param string usermessage: The text sent from the user
 * @param string airesponse: The text returned by the AI 
 */
function log_message($usermessage, $airesponse, $context) {
    global $USER, $DB;

    if (!get_config('block_openai_chat', 'logging')) {
        return;
    }

    $DB->insert_record('block_openai_chat_log', (object) [
        'userid' => $USER->id,
        'usermessage' => $usermessage,
        'airesponse' => $airesponse,
        'contextid' => $context->id,
        'timecreated' => time()
    ]);
}

function block_openai_chat_extend_navigation_course($nav, $course, $context) { # Añade un botón al menú de navegación del curso para acceder a los registros de chat
    if ($nav->get('coursereports')) {
        $nav->get('coursereports')->add(
            get_string('openai_chat_logs', 'block_openai_chat'),
            new moodle_url('/blocks/openai_chat/report.php', ['courseid' => $course->id]),
            navigation_node::TYPE_SETTING,
            null
        );
    }
}

function obtener_texto_archivos_curso($courseid){
    global $DB;

    $texto_completo = '';

    $fs = get_file_storage(); #Activa el sistema de archivos de moodle
    $modinfo = get_fast_modinfo($courseid);
    
    foreach($modinfo->cms as $cm){
        if ($cm->modname === 'resource'){ #Solo procesa archivos subidos como recursos
            $context = context_module::instance($cm->id);
            $files = $fs->get_area_files($context->id, 'mod_resource', 'content', 0, 'sortorder DESC, id ASC', false);

            foreach($files as $file){
                if($file->get_mimetype() === 'text/plain'){
                    $texto_completo .= "\n--- Apuntes del documento: " . $file->get_filename() . " ---\n";
                    $texto_completo .= $file->get_content();
                    $texto_completo .= "\n-------------------------------------------------\n";
                }
            }
        }
    }
    return $texto_completo;
}