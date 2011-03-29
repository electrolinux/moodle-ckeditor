<?php
// vim: set ai et ts=4 sts=4 sw=4:

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
    const version = '3.5.2';

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
    }

    public function head_setup() {
    }

    public function use_editor($elementid, array $options=null, $fpoptions=null) {
        global $PAGE;

        $jsmodule = array(
            'name'     => 'editor_ckeditor',
            'fullpath' => '/lib/editor/ckeditor/module.js',
            'requires' => array('base','json'),
            /*'strings' => array(
                array('something', 'mymod'),
                array('confirmdelete', 'mymod'),
                array('yes', 'moodle'),
                array('no', 'moodle')
            )*/
        );
        //$PAGE->requires->js_init_call('M.mod_mymod.init_something', array('some', 'data', 'from', 'PHP'), false, $jsmodule);        
        
        $PAGE->requires->js('/lib/editor/ckeditor/ckeditor/'.self::version.'/ckeditor.js');
        $PAGE->requires->js_init_call('M.editor_ckeditor.init_editor', 
            array($elementid, $this->get_init_params($elementid, $options)), true, $jsmodule);
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

        $config = get_config('editor_ckeditor');

        if(!empty($config->toolbar))
        {
            $toolbar = self::toolbar_from_str($config->toolbar);
        }
        else
        {
            $toolbar = self::get_default_toolbar();
        }
        if(!empty($config->skin)) {
            $skin = $config->skin;
        }
        else {
            $skin='yui3';
        }
        $extraplugins = 'moodlemaximize,moodleemoticons';
        if(!empty($config->extraplugins))
        {
            $extraplugins .= (',' . $config->extraplugins);
        }


        $params = array(
            'elements' => $elementid,
            'relative_urls' => false,
            'language' => $lang,
            'toolbar' => json_encode($toolbar),
            'skin' => $skin,
            'entities' => false,
            'entities_greek'=> true,
            'entities_latin' => false,
            //'stylesCombo_stylesSet' => 'moodle',
            'contentsCss' => array('lib/editor/ckeditor/ckeditor/'.self::version.'/contents.css',), //, 'css/admin/ckeditor.css'),
            'emoticons' => $this->get_emoticons(),
            'extraPlugins' => $extraplugins,
        );


        if (empty($options['legacy'])) {
            if (isset($options['maxfiles']) and $options['maxfiles'] != 0) {
            //$params['file_browser_callback'] = "M.editor_ckeditor.filepicker";
            $params['filepicker']='M.editor_ckeditor.filepicker';
            $params['extraPlugins'].=',moodlefilebrowser';
            }
        }

        return $params;
    }

    protected function get_emoticons()
    {
        global $CFG, $PAGE, $OUTPUT;
        // based on the tinymce moodleemoticon plugin by David Mudrak
            
        $emoticonmanager = get_emoticon_manager();;
        $stringmanager = get_string_manager();
        $emoticons = $emoticonmanager->get_emoticons();
        // this is tricky - we must somehow include the information about the original
        // emoticon text so that we can replace the image back with it on editor save.
        // so we are going to encode the index of the emoticon. this will break when the
        // admin changes the mapping table while the user has the editor opened
        // but I am not able to come with better solution at the moment :-/
        $index = 0;
        $_emoticons = array();
        foreach ($emoticons as $emoticon) {
            $txt = $emoticon->text;
            $img = $OUTPUT->render(
            $emoticonmanager->prepare_renderable_emoticon($emoticon, array('class' => 'emoticon emoticon-index-'.$index)));
            if ($stringmanager->string_exists($emoticon->altidentifier, $emoticon->altcomponent)) {
            $alt = get_string($emoticon->altidentifier, $emoticon->altcomponent);
            } else {
            $alt = '';
            }
            $_emoticons[]=array('txt' => $txt, 'img' => $img, 'alt' => $alt, 'idx' => $index);
            $index++;
        }
        //var_dump($_emoticons);
        return $_emoticons;
    }

    // ================ static functions, mostly for settings

    public static function get_default_toolbar()
    {
        return array(
            array('Source','-','Cut','Copy','Paste','PasteText','PasteFromWord'),
            array('Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'),
            array('Table','HorizontalRule','Emoticon','SpecialChar','PageBreak'),
            array('Mmaximize','-','About'),
            '/',
            array('Styles','Format'),
            array('Bold','Italic','Underline'),
            array('NumberedList','BulletedList','-','Outdent','Indent','Blockquote'),
            array('Link','Unlink','Anchor','Image'),//'Flash','Code','fmath_formula'),
        );
    }
    
    /*
     * return a string representation of the toolbar
     */
    public static function get_raw_toolbar()
    {
        $toolbar = self::get_default_toolbar();
        $s='';
        $sep='';
        foreach($toolbar as $item)
        {
            if(is_array($item))
            {
                $s .= ( $sep . implode(',',$item));
            }
            else
            {
                $s .= ($sep . $item);
            }
            $sep = "\n";
        }
        return $s;
    }

    /*
     * return an array from the text representation 
     */    
    public static function toolbar_from_str($txt)
    {
        $toolbar=array();
        $bars = explode("/",$txt);
        $sep=false;
        foreach($bars as $bar)
        {
            if($sep)
                $toolbar[]=$sep;
            $groups = preg_split("/(\r\n|\n|\r)/m",$bar,-1,PREG_SPLIT_NO_EMPTY);
            foreach($groups as $group)
            {
                $toolbar[] = preg_split("/,/",$group,-1,PREG_SPLIT_NO_EMPTY);
            }
            $sep='/';
        }
        //print_object($toolbar); die();
        return $toolbar;
    }

    /*
     * return the Style combo definition as text for editing
     * (not used)
     */
    public static function get_styles()
    {
        global $CFG;

        $filename = $CFG->dirroot . '/lib/editor/ckeditor/stylescombo.js';
        return file_get_contents($filename);
    }

    public static function get_stylescombo_path()
    {
        global $CFG;
        return $CFG->dirroot . '/lib/editor/ckeditor/ckeditor/' . self::version . '/plugins/styles/styles/';
    }

    public static function get_skin()
    {
        return 'yui3';
    }
}
