<?php
namespace block_ragassistant\service;

defined('MOODLE_INTERNAL') || die();

class rag_client {

    /**
     * Envía una pregunta a FastAPI y devuelve la respuesta normalizada.
     *
     * @param array $payload Contexto + pregunta
     * @return array
     * @throws \moodle_exception
     */
    public function ask(array $payload): array {
        $apiurl = rtrim(get_config('block_ragassistant', 'apiurl'), '/');
        $token  = get_config('block_ragassistant', 'apikey');
        $timeout = (int) (get_config('block_ragassistant', 'timeout') ?: 60);
        $debug  = (bool) get_config('block_ragassistant', 'debugmode');

        if (empty($apiurl)) {
            throw new \moodle_exception('apiurlnotset', 'block_ragassistant');
        }

        $curl = new \curl();
        $curl->setopt([
            'CURLOPT_TIMEOUT'        => $timeout,
            'CURLOPT_CONNECTTIMEOUT' => 10,
            'CURLOPT_RETURNTRANSFER' => true,
        ]);

        $headers = ['Content-Type: application/json'];
        if (!empty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $response = $curl->post(
            $apiurl . '/ask',
            json_encode($payload),
            ['CURLOPT_HTTPHEADER' => $headers]
        );

        if ($debug) {
            debugging('RAG ask payload: ' . json_encode($payload), DEBUG_DEVELOPER);
            debugging('RAG ask response: ' . $response, DEBUG_DEVELOPER);
        }

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            throw new \moodle_exception('invalidapiresponse', 'block_ragassistant');
        }

        return $decoded;
    }

    /**
     * Solicita la indexación del curso a FastAPI.
     *
     * @param array $payload
     * @return array
     * @throws \moodle_exception
     */
    public function index_course(array $payload): array {
        $apiurl = rtrim(get_config('block_ragassistant', 'apiurl'), '/');
        $token  = get_config('block_ragassistant', 'apikey');

        if (empty($apiurl)) {
            throw new \moodle_exception('apiurlnotset', 'block_ragassistant');
        }

        $curl = new \curl();
        $curl->setopt([
            'CURLOPT_TIMEOUT'        => 30,
            'CURLOPT_CONNECTTIMEOUT' => 10,
            'CURLOPT_RETURNTRANSFER' => true,
        ]);

        $headers = ['Content-Type: application/json'];
        if (!empty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $response = $curl->post(
            $apiurl . '/index-course',
            json_encode($payload),
            ['CURLOPT_HTTPHEADER' => $headers]
        );

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            throw new \moodle_exception('invalidapiresponse', 'block_ragassistant');
        }

        return $decoded;
    }
}
