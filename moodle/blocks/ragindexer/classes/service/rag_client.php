<?php
namespace block_ragindexer\service;

defined('MOODLE_INTERNAL') || die();

/**
 * Cliente HTTP hacia el backend de indexación.
 *
 * No implementa lógica RAG: solo transporta la petición de indexación al
 * backend FastAPI. El endpoint /index-course todavía no existe en el repo
 * tfm (queda como deuda de una fase posterior); mientras tanto el método
 * devolverá un error controlado o el aviso de backend no disponible.
 */
class rag_client {

    /**
     * Solicita la indexación del curso al backend.
     *
     * @param array $payload
     * @return array
     * @throws \moodle_exception
     */
    public function index_course(array $payload): array {
        $apiurl = rtrim((string) get_config('block_ragindexer', 'apiurl'), '/');
        $token  = (string) get_config('block_ragindexer', 'apikey');
        $timeout = (int) (get_config('block_ragindexer', 'timeout') ?: 30);

        if (empty($apiurl)) {
            throw new \moodle_exception('apiurlnotset', 'block_ragindexer');
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
            $apiurl . '/index-course',
            json_encode($payload),
            ['CURLOPT_HTTPHEADER' => $headers]
        );

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            throw new \moodle_exception('invalidapiresponse', 'block_ragindexer');
        }

        return $decoded;
    }
}
