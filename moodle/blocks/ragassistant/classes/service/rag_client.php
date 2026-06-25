<?php
namespace block_ragassistant\service;

defined('MOODLE_INTERNAL') || die();

/**
 * Cliente HTTP hacia el backend FastAPI del RAG (contrato canónico).
 *
 *  - POST {apiurl}/ask con el contrato { question, context }.
 *  - Autenticación Bearer opcional (si hay token configurado).
 *  - Normaliza TODA condición (HTTP 4xx/5xx, timeout, error de conexión, JSON
 *    inválido) a una respuesta con `status` del contrato, sin filtrar trazas
 *    internas al usuario y sin registrar nunca el token.
 *
 * No implementa lógica RAG: solo transporta la petición a FastAPI.
 */
class rag_client {

    /**
     * Envía una pregunta a FastAPI /ask y devuelve la respuesta normalizada.
     *
     * Siempre devuelve un array con, al menos, la clave `status`. No lanza por
     * errores de red/HTTP: los traduce a status 'error' | 'degraded' |
     * 'invalid_request'. Solo lanza moodle_exception si falta configuración.
     *
     * @param array $payload  ['question' => string, 'context' => array]
     * @return array
     * @throws \moodle_exception  Solo si la URL no está configurada.
     */
    public function ask(array $payload): array {
        $apiurl  = rtrim((string) get_config('block_ragassistant', 'apiurl'), '/');
        $token   = (string) get_config('block_ragassistant', 'apikey');
        $timeout = (int) (get_config('block_ragassistant', 'timeout') ?: 60);
        $debug   = (bool) get_config('block_ragassistant', 'debugmode');

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

        $raw = $curl->post(
            $apiurl . '/ask',
            json_encode($payload),
            ['CURLOPT_HTTPHEADER' => $headers]
        );

        $info     = $curl->get_info();
        $httpcode = isset($info['http_code']) ? (int) $info['http_code'] : 0;
        $errno    = $curl->get_errno();

        if ($debug) {
            // El payload no contiene datos personales ni token. El token viaja
            // en cabeceras y NUNCA se registra.
            debugging('RAG /ask payload: ' . json_encode($payload), DEBUG_DEVELOPER);
            debugging('RAG /ask http_code: ' . $httpcode . ' errno: ' . $errno, DEBUG_DEVELOPER);
        }

        // Error de red / timeout: no hay respuesta HTTP.
        if ($errno || $httpcode === 0) {
            return $this->error_response('degraded');
        }

        $decoded = json_decode($raw, true);

        // 2xx: se espera una AskResponse válida del contrato.
        if ($httpcode >= 200 && $httpcode < 300) {
            if (!is_array($decoded) || !isset($decoded['status'])) {
                return $this->error_response('error');
            }
            return $decoded;
        }

        // Errores HTTP mapeados a status del contrato.
        switch ($httpcode) {
            case 400:
            case 422:
                return $this->error_response('invalid_request');
            case 401:
            case 403:
                return $this->error_response('error');
            case 503:
                return $this->error_response('degraded');
            default:
                return $this->error_response('error');
        }
    }

    /**
     * Respuesta de error normalizada compatible con el front. `answer` vacío;
     * el JS muestra un mensaje seguro según `status`.
     *
     * @param string $status  error | degraded | invalid_request
     * @return array
     */
    private function error_response(string $status): array {
        return [
            'status'   => $status,
            'answer'   => '',
            'sources'  => [],
            'metadata' => [
                'abstained'  => false,
                'best_score' => null,
            ],
            'warnings'   => [],
            'latency_ms' => 0,
            'request_id' => '',
        ];
    }
}
