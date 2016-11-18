<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

class qtype_wq_renderer extends qtype_renderer {

    protected $base;

    public function __construct(qtype_renderer $base = null, moodle_page $page, $target) {
        parent::__construct($page, $target);
        $this->base = $base;
    }

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        $result = $this->base->formulation_and_controls($qa, $options);
        $this->add_javascript();
        $result .= html_writer::start_tag('div', array('class' => 'ablock'));
        $result .= $this->lang();
        $result .= $this->auxiliar_cas();
        $result .= $this->question($qa);
        $result .= $this->question_instance($qa);
        $result .= html_writer::end_tag('div');
        return $result;
    }

    public function specific_feedback(question_attempt $qa) {
        return $this->base->specific_feedback($qa);
    }

    public function correct_response(question_attempt $qa) {
        return $this->base->correct_response($qa);
    }

    protected function add_javascript() {
        // Add javascript to launch editor and quizzes.
        $this->page->requires->js('/question/type/wq/quizzes/service.php?name=quizzes.js&service=resource', false);
    }
    protected function question(question_attempt $qa) {
        // Add question definition.
        $question = $qa->get_question();
        $wirisquestion = $question->wirisquestion;
        $studentquestion = $wirisquestion->getStudentQuestion();
        $wirisquestionattributes = array(
            'type' => 'hidden',
            'value' => $studentquestion->serialize(),
            'class' => 'wirisquestion',
        );
        return html_writer::empty_tag('input', $wirisquestionattributes);
    }
    protected function question_instance(question_attempt $qa) {
        // Add question instance.
        $xml = $qa->get_last_qt_var('_sqi');
        if (empty($xml)) {
            $question = $qa->get_question();
            $sqi = $question->wirisquestioninstance->getStudentQuestionInstance();
            $xml = $sqi->serialize();
        } else {
            $builder = com_wiris_quizzes_api_QuizzesBuilder::getInstance();
            $sqi = $builder->readQuestionInstance($xml);
            $question = $qa->get_question();
            $question->wirisquestioninstance->updateFromStudentQuestionInstance($sqi);
            $xml = $question->wirisquestioninstance->serialize();
        }

        $sqiname = $qa->get_qt_field_name('_sqi');
        $wirisquestioninstanceattributes = array(
            'type' => 'hidden',
            'value' => $xml,
            'class' => 'wirisquestioninstance',
            'name' => $sqiname,
            'id' => $sqiname,
        );
        return html_writer::empty_tag('input', $wirisquestioninstanceattributes);
    }
    protected function auxiliar_cas() {
        return html_writer::empty_tag('input', array('class' => 'wirisauxiliarcasapplet', 'type' => 'hidden'));
    }
    protected function lang() {
        return html_writer::empty_tag('input', array('class' => 'wirislang', 'type' => 'hidden', 'value' => current_language()));
    }

    /**
     * Display any extra question-type specific content that should be visible
     * when grading, if appropriate.
     *
     * @param question_attempt $qa a question attempt.
     * @param question_display_options $options controls what should and should not be displayed.
     * @return string HTML fragment.
     */
    public function manual_comment(question_attempt $qa, question_display_options $options) {
        return $this->base->manual_comment($qa, $options);
    }

    public function feedback_class($fraction) {
        return $this->base->feedback_class($fraction);
    }
}