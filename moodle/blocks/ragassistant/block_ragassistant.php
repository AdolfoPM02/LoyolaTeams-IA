<?php
defined('MOODLE_INTERNAL') || die();

class block_ragassistant extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_ragassistant');
    }

    public function applicable_formats() {
        return [
            'course-view' => true,
            'mod'         => true,
            'site'        => false,
            'my'          => false,
        ];
    }

    public function has_config() {
        return true;
    }

    public function get_content() {
        global $COURSE, $USER, $PAGE;

        if ($this->content !== null) {
            return $this->content;
        }

        $context = context_course::instance($COURSE->id);

        if (!has_capability('block/ragassistant:ask', $context)) {
            $this->content = new stdClass();
            $this->content->text = '';
            return $this->content;
        }

        $canindex = has_capability('block/ragassistant:indexcourse', $context);

        $PAGE->requires->js_call_amd('block_ragassistant/chat', 'init', [[
            'courseid' => $COURSE->id,
            'userid'   => $USER->id,
            'canindex' => $canindex,
        ]]);

        $templatedata = [
            'courseid' => $COURSE->id,
            'canindex' => $canindex,
        ];

        $this->content = new stdClass();
        $this->content->text = $PAGE->get_renderer('core')->render_from_template(
            'block_ragassistant/block',
            $templatedata
        );

        return $this->content;
    }
}
