<?php
namespace block_ragindexer\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use block_ragindexer\service\rag_client;

class index_course extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'courseid'     => new external_value(PARAM_INT,  'Course ID'),
            'forcereindex' => new external_value(PARAM_BOOL, 'Force full reindex', VALUE_DEFAULT, false),
        ]);
    }

    public static function execute(int $courseid, bool $forcereindex = false): array {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid'     => $courseid,
            'forcereindex' => $forcereindex,
        ]);

        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('block/ragindexer:indexcourse', $context);

        if ($params['forcereindex']) {
            require_capability('block/ragindexer:forcereindex', $context);
        }

        $payload = [
            'course_id'    => $params['courseid'],
            'requested_by' => (int) $USER->id,
            'mode'         => $params['forcereindex'] ? 'full' : 'incremental',
            'force_reindex'=> $params['forcereindex'],
        ];

        $client   = new rag_client();
        $response = $client->index_course($payload);

        return [
            'job_id'  => $response['job_id'] ?? '',
            'status'  => $response['status'] ?? 'error',
            'message' => $response['message'] ?? '',
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'job_id'  => new external_value(PARAM_TEXT, 'Indexing job ID'),
            'status'  => new external_value(PARAM_TEXT, 'Status: queued | running | error'),
            'message' => new external_value(PARAM_TEXT, 'Human-readable status message', VALUE_OPTIONAL),
        ]);
    }
}
