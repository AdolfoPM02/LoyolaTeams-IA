<?php
namespace block_ragassistant\service;

defined('MOODLE_INTERNAL') || die();

/**
 * Construye el objeto "context" del contrato canónico de FastAPI POST /ask.
 *
 * El contrato (QueryContextPayload) es ESTRICTO (extra="forbid"): solo admite
 * estos campos y rechaza cualquier otro. Por eso NO se incluyen identificadores
 * personales (user_id, email, fullname...), ni context_id, ni roles como lista,
 * ni top_k. La trazabilidad Moodle (userid, contextid) se calcula aparte, en la
 * función externa, solo para el log local.
 *
 * Campos:
 *   course_id    (string, obligatorio)
 *   role         (string, obligatorio: student|teacher|editingteacher|guest|admin)
 *   section_id   (string|null)
 *   activity_id  (string|null)
 *   resource_id  (string|null)
 *   language     (string|null)
 *   visibility   (string|null)
 */
class context_builder {

    /**
     * Construye el array de contexto compatible con FastAPI /ask.
     *
     * @param int      $courseid ID del curso (origen: Moodle, no el usuario).
     * @param int|null $cmid     ID del módulo de curso si la consulta procede de una actividad.
     * @return array
     */
    public function build(int $courseid, ?int $cmid = null): array {
        $coursecontext = \context_course::instance($courseid);

        $context = [
            'course_id'   => (string) $courseid,
            'role'        => $this->resolve_role($coursecontext),
            'section_id'  => null,
            'activity_id' => null,
            'resource_id' => null,
            'language'    => $this->normalize_language(current_language()),
            'visibility'  => 'visible',
        ];

        if ($cmid) {
            $context['activity_id'] = (string) $cmid;
            $sectionid = $this->resolve_section_id($courseid, $cmid);
            if ($sectionid !== null) {
                $context['section_id'] = (string) $sectionid;
            }
        }

        return $context;
    }

    /**
     * Deriva un único rol desde las capacidades/roles Moodle en el contexto del
     * curso. NUNCA proviene de input libre del usuario. FastAPI valida el rol
     * contra una lista cerrada: student|teacher|editingteacher|guest|admin.
     *
     * @param \context $coursecontext
     * @return string
     */
    private function resolve_role(\context $coursecontext): string {
        global $USER;

        if (is_siteadmin($USER->id)) {
            return 'admin';
        }

        if (has_capability('moodle/course:manageactivities', $coursecontext)) {
            // Profesor con permisos de edición.
            return 'editingteacher';
        }

        if (has_capability('mod/assign:grade', $coursecontext)
                || has_capability('gradereport/grader:view', $coursecontext)) {
            // Profesor no editor (calificación / revisión docente).
            return 'teacher';
        }

        if (isguestuser($USER) || !isloggedin()) {
            return 'guest';
        }

        return 'student';
    }

    /**
     * Normaliza el idioma Moodle a un código corto (es_wp -> es, en_us -> en).
     *
     * @param string $lang Resultado de current_language().
     * @return string
     */
    private function normalize_language(string $lang): string {
        $lang = strtolower(str_replace('-', '_', trim($lang)));
        $base = explode('_', $lang)[0];
        return $base !== '' ? $base : 'es';
    }

    /**
     * Obtiene el número de sección del módulo indicado, o null si no se puede.
     *
     * @param int $courseid
     * @param int $cmid
     * @return int|null
     */
    private function resolve_section_id(int $courseid, int $cmid): ?int {
        try {
            $modinfo = get_fast_modinfo($courseid);
            $cm = $modinfo->get_cm($cmid);
            return (int) $cm->sectionnum;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
