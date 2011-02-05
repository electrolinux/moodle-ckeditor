/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license

Moodle integration, (c) 2011, didier Belot <electrolinux@gmail.com>

*/

/**
 * @fileOverview Replace the "filebrowser" plugin, adding support for file browsing.
 *
 * No support for upload, as it is done in the moodle core_filepicker
 *
 * (see original filebrowser plugin doc)
 *
 */
( function()
{

	/*
	 * Call a function with a namespace, like M.editor_ckeditor.editor)
	 *
	 */
	function executeFunctionByName(functionName, context /*, args */) {
	    var args = Array.prototype.slice.call(arguments, 2);
	    var namespaces = functionName.split(".");
	    var func = namespaces.pop();
	    for (var i = 0; i < namespaces.length; i++) {
		context = context[namespaces[i]];
	    }
	    return context[func].apply(context, args);
	}

	/**
	 * The onClick function assigned to the 'Browse Server' button. Opens the
	 * file browser and updates target field when file is selected.
	 *
	 * @param {CKEDITOR.event}
	 *            evt The event object.
	 */
	function browseServer( evt )
	{
		var dialog = this.getDialog();
		var editor = dialog.getParentEditor();

		editor._.filebrowserSe = this;
		var params = this.filebrowser.params || {};
		params.CKEditor = editor.name;
		//params.CKEditorFuncNum = editor._.filebrowserFn;
		params.CKEditorFuncNum = editor._.moodlefilebrowserFn;
		if ( !params.langCode )
			params.langCode = editor.langCode;

		if ( !params.dlgType) {
			params.dlgType = this.filebrowser.dlgType;
		}
		// load core_filepicker...
		var callback = editor.config.filepicker;
		//callback(params);
		executeFunctionByName(callback,window,params);
	}


	/**
	 * Traverse through the content definition and attach filebrowser to
	 * elements with 'filebrowser' attribute.
	 *
	 * @param String
	 *            dialogName Dialog name.
	 * @param {CKEDITOR.dialog.dialogDefinitionObject}
	 *            definition Dialog definition.
	 * @param {Array}
	 *            elements Array of {@link CKEDITOR.dialog.contentDefinition}
	 *            objects.
	 */
	function attachFileBrowser( editor, dialogName, definition, elements )
	{
		var element, fileInput;

		for ( var i in elements )
		{
			element = elements[ i ];

			if ( element.type == 'hbox' || element.type == 'vbox' )
				attachFileBrowser( editor, dialogName, definition, element.children );

			if ( !element.filebrowser )
				continue;

			if ( typeof element.filebrowser == 'string' )
			{
				var fb =
				{
					action : ( element.type == 'fileButton' ) ? 'QuickUpload' : 'Browse',
					target : element.filebrowser
				};
				element.filebrowser = fb;
			}

			if ( element.filebrowser.action == 'Browse' )
			{
				//var url = element.filebrowser.url;
				//if ( url === undefined )
				var dlgType = element.filebrowser.dlgType;
				if ( dlgType === undefined )
				{
					//url = editor.config[ 'filebrowser' + ucFirst( dialogName ) + 'BrowseUrl' ];
					//if ( url === undefined )
					//	url = editor.config.filebrowserBrowseUrl;
					dlgType = dialogName;
					if ( dlgType === undefined )
						dlgType = 'file';
				}

				//if ( url )
				if ( dlgType )
				{
					element.onClick = browseServer;
					//element.filebrowser.url = url;
					element.filebrowser.dlgType = dlgType;
					element.hidden = false;
				}
			}
			
			else if ( element.filebrowser.action == 'QuickUpload' && element[ 'for' ] )
			{
				/*
				url = element.filebrowser.url;
				if ( url === undefined )
				{
					url = editor.config[ 'filebrowser' + ucFirst( dialogName ) + 'UploadUrl' ];
					if ( url === undefined )
						url = editor.config.filebrowserUploadUrl;
				}

				if ( url )
				{
					var onClick = element.onClick;
					element.onClick = function( evt )
					{
						// "element" here means the definition object, so we need to find the correct
						// button to scope the event call
						var sender = evt.sender;
						if ( onClick && onClick.call( sender, evt ) === false )
							return false;

						return uploadFile.call( sender, evt );
					};

					element.filebrowser.url = url;
					element.hidden = false;
					setupFileElement( editor, definition.getContents( element[ 'for' ][ 0 ] ).get( element[ 'for' ][ 1 ] ), element.filebrowser );
				}*/
				/*
				dlgType = element.filebrowser.dlgType;
				if ( dlgType === undefined )
				{
					dlgType =  dialogName ;
					if ( dlgType === undefined )
						dlgType = 'file';
				}

				if ( dlgType )
				{
					var onClick = element.onClick;
					element.onClick = function( evt )
					{
						// "element" here means the definition object, so we need to find the correct
						// button to scope the event call
						var sender = evt.sender;
						if ( onClick && onClick.call( sender, evt ) === false )
							return false;

						return uploadFile.call( sender, evt );
					};

					element.filebrowser.dlgType = dlgType;
					element.hidden = false;
					setupFileElement( editor, definition.getContents( element[ 'for' ][ 0 ] ).get( element[ 'for' ][ 1 ] ), element.filebrowser );
				}*/
			}
		}
	}

	/**
	 * Updates the target element with the url of uploaded/selected file.
	 *
	 * @param {String}
	 *            url The url of a file.
	 */
	function updateTargetElement( url, sourceElement )
	{
		var dialog = sourceElement.getDialog();
		var targetElement = sourceElement.filebrowser.target || null;
		url = url.replace( /#/g, '%23' );

		// If there is a reference to targetElement, update it.
		if ( targetElement )
		{
			var target = targetElement.split( ':' );
			var element = dialog.getContentElement( target[ 0 ], target[ 1 ] );
			if ( element )
			{
				element.setValue( url );
				dialog.selectPage( target[ 0 ] );
			}
		}
	}

	/**
	 * Returns true if filebrowser is configured in one of the elements.
	 *
	 * @param {CKEDITOR.dialog.dialogDefinitionObject}
	 *            definition Dialog definition.
	 * @param String
	 *            tabId The tab id where element(s) can be found.
	 * @param String
	 *            elementId The element id (or ids, separated with a semicolon) to check.
	 */
	function isConfigured( definition, tabId, elementId )
	{
		if ( elementId.indexOf( ";" ) !== -1 )
		{
			var ids = elementId.split( ";" );
			for ( var i = 0 ; i < ids.length ; i++ )
			{
				if ( isConfigured( definition, tabId, ids[i] ) )
					return true;
			}
			return false;
		}

		var elementFileBrowser = definition.getContents( tabId ).get( elementId ).filebrowser;
		return ( elementFileBrowser && elementFileBrowser.dlgType );
	}

	function setUrl( fileUrl, data )
	{
		var dialog = this._.filebrowserSe.getDialog(),
			targetInput = this._.filebrowserSe[ 'for' ],
			onSelect = this._.filebrowserSe.filebrowser.onSelect;

		if ( targetInput )
			dialog.getContentElement( targetInput[ 0 ], targetInput[ 1 ] ).reset();

		if ( typeof data == 'function' && data.call( this._.filebrowserSe ) === false )
			return;

		if ( onSelect && onSelect.call( this._.filebrowserSe, fileUrl, data ) === false )
			return;

		// The "data" argument may be used to pass the error message to the editor.
		if ( typeof data == 'string' && data )
			alert( data );

		if ( fileUrl )
			updateTargetElement( fileUrl, this._.filebrowserSe );
	}

	CKEDITOR.plugins.add( 'moodlefilebrowser',
	{
		init : function( editor, pluginPath )
		{
			editor._.moodlefilebrowserFn = CKEDITOR.tools.addFunction( setUrl, editor );
		}
	} );

	CKEDITOR.on( 'dialogDefinition', function( evt )
	{
		var definition = evt.data.definition,
			element;
		// Associate filebrowser to elements with 'filebrowser' attribute.
		for ( var i in definition.contents )
		{
			if ( ( element = definition.contents[ i ] ) )
			{
				attachFileBrowser( evt.editor, evt.data.name, definition, element.elements );
				if ( element.hidden && element.filebrowser )
				{
					element.hidden = !isConfigured( definition, element[ 'id' ], element.filebrowser );
				}
			}
		}
	} );

} )();

