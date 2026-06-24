<?php
defined('MOODLE_INTERNAL') || die();

class block_ragindexer extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_ragindexer');
    }

    public function applicable_formats() {
        return [
            'course-view' => true,
            'mod'         => false,
            'site'        => false,
            'my'          => false,
        ];
    }

    public function has_config() {
        return true;
    }

    public function get_content() {
        global $COURSE, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $context = context_course::instance($COURSE->id);

        // Solo profesorado/gestores con la capacidad de indexar ven el bloque.
        if (!has_capability('block/ragindexer:indexcourse', $context)) {
            $this->content = new stdClass();
            $this->content->text = '';
            return $this->content;
        }

        $PAGE->requires->js_call_amd('block_ragindexer/indexer', 'init', [[
            'courseid' => (int) $COURSE->id,
        ]]);

        $templatedata = [
            'courseid' => $COURSE->id,
        ];

        $this->content = new stdClass();
        $this->content->text = $PAGE->get_renderer('core')->render_from_template(
            'block_ragindexer/block',
            $templatedata
        );

        return $this->content;
    }
}
