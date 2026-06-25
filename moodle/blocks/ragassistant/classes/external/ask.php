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
        global $DB, $USER;

        // 1. Validar parámetros.
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'question' => $question,
            'cmid'     => $cmid,
        ]);

        // 2. Validar contexto y permisos.
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('block/ragassistant:ask', $context);

        $question = trim($params['question']);

        // 3. Construir el "context" canónico (sin datos personales).
        $builder = new context_builder();
        $moodlecontext = $builder->build(
            $params['courseid'],
            $params['cmid'] ?: null
        );

        // 4. Payload canónico exacto del contrato: { question, context }.
        $payload = [
            'question' => $question,
            'context'  => $moodlecontext,
        ];

        // 5. Llamar a FastAPI /ask (el cliente normaliza errores a status).
        $client   = new rag_client();
        $response = $client->ask($payload);

        $status    = $response['status'] ?? 'error';
        $metadata  = is_array($response['metadata'] ?? null) ? $response['metadata'] : [];
        $sources   = is_array($response['sources'] ?? null) ? $response['sources'] : [];
        $warnings  = is_array($response['warnings'] ?? null) ? $response['warnings'] : [];
        $bestscore = isset($metadata['best_score']) && $metadata['best_score'] !== null
            ? (float) $metadata['best_score'] : null;
        $latency   = isset($response['latency_ms']) ? (int) $response['latency_ms'] : null;

        // 6. Log mínimo privacy-first: nunca la pregunta ni la respuesta completas,
        //    nunca el token. userid/contextid se calculan aquí, no se envían a FastAPI.
        $log = new \stdClass();
        $log->courseid      = $params['courseid'];
        $log->userid        = (int) $USER->id;
        $log->contextid     = (int) $context->id;
        $log->cmid          = $params['cmid'] ?: null;
        $log->question_hash = hash('sha256', $question);
        $log->status        = substr((string) $status, 0, 32);
        $log->sources_count = count($sources);
        $log->confidence    = $bestscore;
        $log->latency_ms    = $latency;
        $log->timecreated   = time();
        $DB->insert_record('block_ragassistant_log', $log);

        // 7. Normalizar fuentes al formato del contrato canónico (sin texto completo).
        $cleansources = [];
        foreach ($sources as $src) {
            if (!is_array($src)) {
                continue;
            }
            $cleansources[] = [
                'chunk_id'       => isset($src['chunk_id']) ? (string) $src['chunk_id'] : '',
                'source_uri'     => isset($src['source_uri']) ? (string) $src['source_uri'] : '',
                'document_title' => isset($src['document_title']) ? (string) $src['document_title'] : '',
                'section'        => isset($src['section']) ? (string) $src['section'] : '',
                'page'           => isset($src['page']) && $src['page'] !== null ? (int) $src['page'] : null,
            ];
        }

        $cleanwarnings = [];
        foreach ($warnings as $w) {
            $cleanwarnings[] = (string) $w;
        }

        return [
            'status'     => (string) $status,
            'answer'     => (string) ($response['answer'] ?? ''),
            'abstained'  => !empty($metadata['abstained']),
            'best_score' => $bestscore !== null ? $bestscore : 0.0,
            'latency_ms' => $latency !== null ? $latency : 0,
            'request_id' => (string) ($response['request_id'] ?? ''),
            'sources'    => $cleansources,
            'warnings'   => $cleanwarnings,
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status'     => new external_value(PARAM_ALPHAEXT,
                'answered | abstained | error | invalid_request | degraded'),
            'answer'     => new external_value(PARAM_RAW,   'Answer text'),
            'abstained'  => new external_value(PARAM_BOOL,  'Whether the backend abstained', VALUE_OPTIONAL),
            'best_score' => new external_value(PARAM_FLOAT, 'Best retrieval score', VALUE_OPTIONAL),
            'latency_ms' => new external_value(PARAM_INT,   'Response latency in ms', VALUE_OPTIONAL),
            'request_id' => new external_value(PARAM_TEXT,  'Backend request id', VALUE_OPTIONAL),
            'sources'    => new external_multiple_structure(
                new external_single_structure([
                    'chunk_id'       => new external_value(PARAM_TEXT, 'Chunk id',       VALUE_OPTIONAL),
                    'source_uri'     => new external_value(PARAM_RAW,  'Source URI',     VALUE_OPTIONAL),
                    'document_title' => new external_value(PARAM_TEXT, 'Document title', VALUE_OPTIONAL),
                    'section'        => new external_value(PARAM_TEXT, 'Section label',  VALUE_OPTIONAL),
                    'page'           => new external_value(PARAM_INT,  'Page number',    VALUE_OPTIONAL),
                ]),
                'Cited sources', VALUE_DEFAULT, []
            ),
            'warnings'   => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'Warning message'),
                'Warnings', VALUE_DEFAULT, []
            ),
        ]);
    }
}
