<?php
namespace block_ragassistant\service;

defined('MOODLE_INTERNAL') || die();

class context_builder {

    /**
     * Construye el array de contexto Moodle que se enviará a FastAPI.
     *
     * @param int      $courseid  ID del curso actual
     * @param int|null $cmid      ID del módulo de curso (si aplica)
     * @return array
     */
    public function build(int $courseid, ?int $cmid = null): array {
        global $USER;

        $context   = \context_course::instance($courseid);
        $roles     = get_user_roles($context, $USER->id);
        $rolenames = [];

        foreach ($roles as $role) {
            $rolenames[] = $role->shortname;
        }

        // Si no tiene rol asignado explícitamente, comprobamos si es admin
        if (empty($rolenames) && is_siteadmin($USER->id)) {
            $rolenames[] = 'admin';
        }

        $payload = [
            'course_id'  => $courseid,
            'user_id'    => (int) $USER->id,
            'context_id' => (int) $context->id,
            'roles'      => $rolenames,
            'lang'       => current_language(),
        ];

        if ($cmid) {
            $payload['cm_id'] = (int) $cmid;
        }

        return $payload;
    }
}
