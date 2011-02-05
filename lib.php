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

/**
 * CkEditor text editor integration.
 *
 * @package    editor
 * @subpackage ckeditor
 * @copyright  2011 didier Belot (electrolinux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class ckeditor_texteditor extends texteditor {
    /** @var string active version - directory name */
    public $version = '3.5';

    public function supported_by_browser() {
        if (check_browser_version('MSIE', 6)) {
            return true;
        }
        if (check_browser_version('Gecko', 20030516)) {
            return true;
        }
        if (check_browser_version('Safari', 412)) {
            return true;
        }
        if (check_browser_version('Chrome', 6)) {
            return true;
        }
        if (check_browser_version('Opera', 9)) {
            return true;
        }

        return false;
    }

    public function get_supported_formats() {
        return array(FORMAT_HTML => FORMAT_HTML);
    }

    public function get_preferred_format() {
        return FORMAT_HTML;
    }

    public function supports_repositories() {
        return true;
        //return false;
    }

    public function head_setup() {
    }

    public function use_editor($elementid, array $options=null, $fpoptions=null) {
        global $PAGE;
        $PAGE->requires->js('/lib/editor/ckeditor/ckeditor/'.$this->version.'/ckeditor.js');
        //$PAGE->requires->js_init_call('M.editor_ckeditor.init_editor', array($elementid, array(), true);
        $PAGE->requires->js_init_call('M.editor_ckeditor.init_editor', array($elementid, $this->get_init_params($elementid, $options)), true);
        if ($fpoptions) {
            $PAGE->requires->js_init_call('M.editor_ckeditor.init_filepicker', array($elementid, $fpoptions), true);
	}
    }

    protected function get_init_params($elementid, array $options=null) {
        global $CFG, $PAGE, $OUTPUT;

        //TODO: we need to implement user preferences that affect the editor setup too

	/*
	$directionality = get_string('thisdirection', 'langconfig');
        $strtime        = get_string('strftimetime');
        $strdate        = get_string('strftimedaydate');
	*/
        $lang           = current_language();
        //$contentcss     = $PAGE->theme->editor_css_url()->out(false);

        $context = empty($options['context']) ? get_context_instance(CONTEXT_SYSTEM) : $options['context'];

        $params = array(
                    'elements' => $elementid,
                    'relative_urls' => false,
                    'document_base_url' => $CFG->httpswwwroot,
                    'content_css' => $contentcss,
		    'language' => $lang,
		    'toolbar' => array(
			array('Source','-','Cut','Copy','Paste','PasteText','PasteFromWord'),
			array('Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'),
			array('Table','HorizontalRule','Smiley','SpecialChar','PageBreak'),
			'/',
			array('Styles','Format'),
			array('Bold','Italic','Underline'),
			array('NumberedList','BulletedList','-','Outdent','Indent','Blockquote'),
			array('Link','Unlink','Anchor','Image','Flash'),
			array('Maximize','-','About')
		    ),
		    'entities' => false,
		    'entities_greek'=> true,
		    'entities_latin' => false,
		    'stylesCombo_stylesSet' => 'moodle',
		    'contentsCss' => array('lib/editor/ckeditor/ckeditor/3.5/contents.css',), //, 'css/admin/ckeditor.css'),

                  );


        if (empty($options['legacy'])) {
            if (isset($options['maxfiles']) and $options['maxfiles'] != 0) {
		    //$params['file_browser_callback'] = "M.editor_ckeditor.filepicker";
		$params['filepicker']='M.editor_ckeditor.filepicker';
		$params['extraPlugins']='moodlefilebrowser';
            }
        }

        return $params;
    }
}