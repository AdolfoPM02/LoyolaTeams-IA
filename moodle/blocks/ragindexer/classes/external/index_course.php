<?php
namespace block_ragindexer\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;
use block_ragindexer\service\rag_client;

class index_course extends external_api {

    /** Máximo de mensajes de error/aviso que se devuelven a la UI. */
    private const MAX_MESSAGES = 10;
    /** Longitud máxima por mensaje (evita volcar trazas largas). */
    private const MAX_MESSAGE_LEN = 200;

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT,  'Course ID'),
            'force'    => new external_value(PARAM_BOOL, 'Force full reindex', VALUE_DEFAULT, false),
        ]);
    }

    public static function execute(int $courseid, bool $force = false): array {
        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid,
            'force'    => $force,
        ]);

        // Contexto y permisos: course_id sale del contexto Moodle, no del input libre.
        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('block/ragindexer:indexcourse', $context);
        if ($params['force']) {
            require_capability('block/ragindexer:forcereindex', $context);
        }

        // course_id canónico como string; sync_visibility siempre true.
        $client = new rag_client();
        $result = $client->index_course((string) $params['courseid'], (bool) $params['force'], true);

        return [
            'status'                  => (string) ($result['status'] ?? 'failed'),
            'course_id'               => (string) ($result['course_id'] ?? $params['courseid']),
            'documents_new'           => (int) ($result['documents_new'] ?? 0),
            'documents_unchanged'     => (int) ($result['documents_unchanged'] ?? 0),
            'documents_modified'      => (int) ($result['documents_modified'] ?? 0),
            'documents_deleted'       => (int) ($result['documents_deleted'] ?? 0),
            'visibility_updates'      => (int) ($result['visibility_updates'] ?? 0),
            'chunks_added'            => (int) ($result['chunks_added'] ?? 0),
            'chunks_deleted_or_stale' => (int) ($result['chunks_deleted_or_stale'] ?? 0),
            'embedding_recomputed'    => (int) ($result['embedding_recomputed'] ?? 0),
            'errors'                  => self::summarize($result['errors'] ?? []),
            'warnings'                => self::summarize($result['warnings'] ?? []),
            'errorkey'                => isset($result['_errorkey']) ? (string) $result['_errorkey'] : '',
        ];
    }

    /**
     * Resumen seguro de errores/avisos: recorta número y longitud, sin volcar
     * trazas largas. (No expone rutas privadas del backend.)
     *
     * @param mixed $messages
     * @return string[]
     */
    private static function summarize($messages): array {
        if (!is_array($messages)) {
            return [];
        }
        $out = [];
        foreach (array_slice($messages, 0, self::MAX_MESSAGES) as $m) {
            $text = is_string($m) ? $m : json_encode($m);
            $out[] = \core_text::substr($text, 0, self::MAX_MESSAGE_LEN);
        }
        return $out;
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'status'                  => new external_value(PARAM_ALPHA, 'ok | partial | failed | unavailable'),
            'course_id'               => new external_value(PARAM_TEXT,  'Course ID'),
            'documents_new'           => new external_value(PARAM_INT,   'New documents'),
            'documents_unchanged'     => new external_value(PARAM_INT,   'Unchanged documents'),
            'documents_modified'      => new external_value(PARAM_INT,   'Modified documents'),
            'documents_deleted'       => new external_value(PARAM_INT,   'Deleted documents'),
            'visibility_updates'      => new external_value(PARAM_INT,   'Visibility updates'),
            'chunks_added'            => new external_value(PARAM_INT,   'Chunks added'),
            'chunks_deleted_or_stale' => new external_value(PARAM_INT,   'Chunks deleted or stale'),
            'embedding_recomputed'    => new external_value(PARAM_INT,   'Embeddings recomputed'),
            'errors'                  => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'Error summary'), 'Errors', VALUE_DEFAULT, []),
            'warnings'                => new external_multiple_structure(
                new external_value(PARAM_TEXT, 'Warning summary'), 'Warnings', VALUE_DEFAULT, []),
            'errorkey'                => new external_value(PARAM_ALPHANUMEXT,
                'Transport error lang key, if any', VALUE_OPTIONAL),
        ]);
    }
}
