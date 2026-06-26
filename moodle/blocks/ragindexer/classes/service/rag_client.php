<?php
namespace block_ragindexer\service;

defined('MOODLE_INTERNAL') || die();

/**
 * Cliente HTTP hacia el backend FastAPI de indexación (POST /index-course).
 *
 *  - Envía el contrato canónico { course_id, force, sync_visibility }.
 *  - Autenticación Bearer opcional (token de indexación configurado).
 *  - Normaliza TODA condición (HTTP 4xx/5xx, timeout, error de conexión, JSON
 *    inválido) a una estructura segura con `status`, sin filtrar trazas
 *    internas ni el token (que viaja en cabeceras y NUNCA se registra).
 *
 * No implementa lógica RAG: solo transporta la petición de indexación.
 */
class rag_client {

    /**
     * Solicita la indexación de un curso al backend.
     *
     * Siempre devuelve un array con, al menos, la clave `status`. No lanza por
     * errores de red/HTTP: los traduce a status 'unavailable' | 'failed'.
     * Solo lanza moodle_exception si falta la URL configurada.
     *
     * @param string $courseid       Course ID (string, contrato canónico).
     * @param bool   $force          Reindexación forzada.
     * @param bool   $syncvisibility Sincronizar visibilidad (por defecto true).
     * @return array
     * @throws \moodle_exception  Solo si la URL no está configurada.
     */
    public function index_course(string $courseid, bool $force = false, bool $syncvisibility = true): array {
        $apiurl  = rtrim((string) get_config('block_ragindexer', 'apiurl'), '/');
        $token   = (string) get_config('block_ragindexer', 'apikey');
        $timeout = (int) (get_config('block_ragindexer', 'timeout') ?: 30);

        if (empty($apiurl)) {
            throw new \moodle_exception('apiurlnotset', 'block_ragindexer');
        }

        $payload = [
            'course_id'       => $courseid,
            'force'           => $force,
            'sync_visibility' => $syncvisibility,
        ];

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
            $apiurl . '/index-course',
            json_encode($payload),
            ['CURLOPT_HTTPHEADER' => $headers]
        );

        $info     = $curl->get_info();
        $httpcode = isset($info['http_code']) ? (int) $info['http_code'] : 0;
        $errno    = $curl->get_errno();

        // Nota: no se registra el payload ni el token (privacy-first). Se evita
        // también cualquier debugging() incondicional que pudiera corromper la
        // respuesta JSON en contexto AJAX.

        // Error de red / timeout: no hay respuesta HTTP.
        if ($errno || $httpcode === 0) {
            return $this->error_result($courseid, 'unavailable', 'backendunavailable');
        }

        $decoded = json_decode($raw, true);

        // 2xx: se espera un IndexingResult válido del contrato.
        if ($httpcode >= 200 && $httpcode < 300) {
            if (!is_array($decoded) || !isset($decoded['status'])) {
                return $this->error_result($courseid, 'failed', 'invalidapiresponse');
            }
            $decoded['course_id'] = (string) ($decoded['course_id'] ?? $courseid);
            return $decoded;
        }

        // Errores HTTP mapeados.
        switch ($httpcode) {
            case 400:
            case 422:
                return $this->error_result($courseid, 'failed', 'errorinvalidrequest');
            case 401:
            case 403:
                return $this->error_result($courseid, 'failed', 'errorunauthorized');
            case 503:
                return $this->error_result($courseid, 'unavailable', 'backendunavailable');
            default:
                return $this->error_result($courseid, 'failed', 'errorinternal');
        }
    }

    /**
     * Estructura de error normalizada (forma IndexingResult con contadores a 0).
     * `errorkey` es una clave lang; el front la traduce, no expone trazas.
     *
     * @param string $courseid
     * @param string $status    unavailable | failed
     * @param string $errorkey  Clave lang del motivo.
     * @return array
     */
    private function error_result(string $courseid, string $status, string $errorkey): array {
        return [
            'course_id'               => $courseid,
            'status'                  => $status,
            'documents_new'           => 0,
            'documents_unchanged'     => 0,
            'documents_modified'      => 0,
            'documents_deleted'       => 0,
            'visibility_updates'      => 0,
            'chunks_added'            => 0,
            'chunks_deleted_or_stale' => 0,
            'embedding_recomputed'    => 0,
            'errors'                  => [],
            'warnings'                => [],
            // Clave lang interna del motivo de transporte (no es texto crudo del backend).
            '_errorkey'               => $errorkey,
        ];
    }
}
