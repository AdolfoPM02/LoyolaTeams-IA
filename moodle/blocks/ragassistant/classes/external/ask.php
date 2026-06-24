<?php
namespace block_ragassistant\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use block_ragassistant\service\context_builder;
use block_ragassistant\service\rag_client;

class ask extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT,  'Course ID'),
            'question' => new external_value(PARAM_TEXT, 'User question'),
            'cmid'     => new external_value(PARAM_INT,  'Course module ID', VALUE_DEFAULT, 0),
        ]);
    }

    public static function execute(int $courseid, string $question, int $cmid = 0): array {
        global $DB;

        // 1. Validar parámetros
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'question' => $question,
            'cmid'     => $cmid,
        ]);

        // 2. Validar contexto y permisos
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('block/ragassistant:ask', $context);

        // 3. Construir contexto Moodle
        $builder      = new context_builder();
        $moodlecontext = $builder->build(
            $params['courseid'],
            $params['cmid'] ?: null
        );

        // 4. Montar payload completo
        $payload = array_merge($moodlecontext, [
            'question' => trim($params['question']),
            'top_k'    => (int) (get_config('block_ragassistant', 'topk') ?: 5),
        ]);

        // 5. Llamar a FastAPI
        $client   = new rag_client();
        $response = $client->ask($payload);

        // 6. Guardar log mínimo (sin guardar la pregunta completa por privacidad)
        $log = new \stdClass();
        $log->courseid      = $params['courseid'];
        $log->userid        = $moodlecontext['user_id'];
        $log->contextid     = $moodlecontext['context_id'];
        $log->cmid          = $params['cmid'] ?: null;
        $log->question_hash = hash('sha256', $params['question']);
        $log->status        = $response['status'] ?? 'unknown';
        $log->sources_count = count($response['sources'] ?? []);
        $log->confidence    = isset($response['confidence']) ? (float) $response['confidence'] : null;
        $log->latency_ms    = isset($response['latency_ms']) ? (int) $response['latency_ms'] : null;
        $log->timecreated   = time();

        $DB->insert_record('block_ragassistant_log', $log);

        // 7. Devolver respuesta normalizada
        return [
            'answer'      => $response['answer']     ?? '',
            'status'      => $response['status']     ?? 'error',
            'confidence'  => isset($response['confidence'])  ? (float) $response['confidence']  : 0.0,
            'latency_ms'  => isset($response['latency_ms'])  ? (int)   $response['latency_ms']  : 0,
            'sources'     => $response['sources']    ?? [],
            'warnings'    => $response['warnings']   ?? [],
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'answer'     => new external_value(PARAM_RAW,   'Answer text'),
            'status'     => new external_value(PARAM_TEXT,  'Status: ok | insufficient_context | error'),
            'confidence' => new external_value(PARAM_FLOAT, 'Confidence score', VALUE_OPTIONAL),
            'latency_ms' => new external_value(PARAM_INT,   'Response latency in ms', VALUE_OPTIONAL),
            'sources'    => new external_multiple_structure(
                new external_single_structure([
                    'title'     => new external_value(PARAM_TEXT, 'Source title',  VALUE_OPTIONAL),
                    'course_id' => new external_value(PARAM_INT,  'Course ID',     VALUE_OPTIONAL),
                    'cm_id'     => new external_value(PARAM_INT,  'CM ID',         VALUE_OPTIONAL),
                    'page'      => new external_value(PARAM_INT,  'Page number',   VALUE_OPTIONAL),
                    'score'     => new external_value(PARAM_FLOAT,'Relevance score',VALUE_OPTIONAL),
                    'url'       => new external_value(PARAM_URL,  'Source URL',    VALUE_OPTIONAL),
                ]),
                'Source documents', VALUE_DEFAULT, []
            ),
            'warnings'   => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'Warning message'),
                'Warnings', VALUE_DEFAULT, []
            ),
        ]);
    }
}
