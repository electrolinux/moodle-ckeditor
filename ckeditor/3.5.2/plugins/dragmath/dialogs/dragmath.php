<?php
require("../../../../../../../../config.php");
    $drlang = str_replace('_utf8', '', current_language());     // use more standard language codes
    $drlangmapping = array('cs'=>'cz', 'pt_br'=>'pt-br');
    // fix non-standard lang names
    if (array_key_exists($drlang, $drlangmapping)) {
        $drlang = $drlangmapping[$drlang];
    }
    if (!file_exists("$CFG->dirroot/lib/DragMath/applet/lang/$drlang.xml")) {
        $drlang = 'en';
    }
@header('Content-Type: application/javascript; charset=utf-8');
?>
CKEDITOR.dialog.add("dragmath",function(e){	
	return{
		title:e.lang.dragmath.title,
		resizable : CKEDITOR.DIALOG_RESIZE_BOTH,
		minWidth:540,
		minHeight:310,
		onShow:function(){ 
		},
		onLoad:function(){ 
				dialog = this; 
				this.setupContent();
		},
		onOk:function(){
		var tex = document.dragmath.getMathExpression();
		tex = tex.replace('<', '&lt;');
        sInsert = tex.replace('>', '&gt;');  
			if ( sInsert.length > 0 ) 
			e.insertHtml(sInsert); 
		},
		contents:[
			{	id:"drnfo",
				name:'drinfo',
				label:e.lang.dragmath.commonTab,
				elements:[
				  {type:'html',
				  html:'<div class="dragmathbg"><applet name="dragmath" codebase="<?php echo $CFG->httpswwwroot; ?>/lib/dragmath/applet" code="Display/MainApplet.class" archive="DragMath.jar,lib/AbsoluteLayout.jar,lib/swing-layout-1.0.jar,lib/jdom.jar,lib/jep.jar" width="540" height="310"><param name="language" value="<?php echo $drlang; ?>" /><param name="outputFormat" value="MoodleTex" /></applet></div>'
				}]
			}
		]
	};
});