CKEDITOR.plugins.add('dragmath',   
  {    
    requires: ['dialog'],
	lang : ['en'], 
    init:function(a) { 
	var b="dragmath";
	var c=a.addCommand(b,new CKEDITOR.dialogCommand(b));
		c.modes={wysiwyg:1,source:0};
		c.canUndo=false;
	a.ui.addButton("Dragmath",{
					label:a.lang.dragmath.title,
					command:b,
					icon:this.path+"dragmath.gif"
	});
	CKEDITOR.dialog.add(b,this.path+"dialogs/dragmath.php")}
});