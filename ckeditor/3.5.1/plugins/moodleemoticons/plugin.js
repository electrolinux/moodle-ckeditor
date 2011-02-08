/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license

Moodle 2 integration (c) 2011, didier Belot
largely inspired by the ckeditor's moodleemoticon plugin by David Mudrak
*/

CKEDITOR.plugins.add( 'moodleemoticons',
{
	requires : [ 'dialog' ],

	init : function( editor )
	{
		//editor.config.smiley_path = editor.config.smiley_path || ( this.path + 'images/' );
		editor.addCommand( 'emoticon', new CKEDITOR.dialogCommand( 'emoticon' ) );
		editor.ui.addButton( 'Emoticon',
			{
				label : editor.lang.smiley.toolbar,
				icon: this.path + 'moodleemoticons.gif',
				command : 'emoticon'
			});
		// emoticons dialog
		CKEDITOR.dialog.add( 'emoticon', this.path + 'dialogs/emoticon.js' );
		// The getData event is fired before CKEditor put data back into textarea
		// Used to transform emoticon's img tag back to their respective text smiley
		//
		// another alternative is to use editor.dataProcessor.dataFilter, but 
		// i can't get it working (see below)
		editor.on( 'getData', function( evt ) {
			var emoticons = editor.config.emoticons,
				data = evt.data.dataValue,
				regImg = new RegExp('<img[^>]*>','gm'),
				matches,
				regs=[],
				repl=[],
				x;
			if(data) 
			{
				matches = regImg.exec(data); 
				while(matches) 
				{
					idxmatch = /emoticon emoticon-index-(\d+)/.exec(matches[0]);
					if (idxmatch && idxmatch.length)
					{
						regs.push(matches[0]);
						index = idxmatch[1];
						repl.push(emoticons[index].txt);
					}
					matches = regImg.exec(data);
				}
				for(x=0;x<regs.length;x++)
				{
					data = data.replace(regs[x],repl[x]);
				}

				evt.data.dataValue = data;
			} else {
				//alert('no data!!');
			}
		});

	},
	afterInit: function( editor )
	{
		var config = editor.config,
		dataProcessor = editor.dataProcessor,
		htmlFilter = dataProcessor && dataProcessor.htmlFilter;
		dataFilter = dataProcessor && dataProcessor.dataFilter;
		if (!htmlFilter || !dataFilter) return;
		var _emoticons = {},
		    regs=[],
		    emoticons = config.emoticons,
		    size = emoticons.length,
		    i,txt,img,search,reg;
		for(i=0; i < size; i++) {
			txt = emoticons[i].txt;
			img = emoticons[i].img;
			_emoticons[txt]=img;
			regs[i]=txt.replace(/[-[\]{}()*+?.,\\^$|#\s\/]/g, '\\$&');
		}
		search = '(' + regs.join('|') + ')';
		//alert(search);
		emoticons_regex = new RegExp(search,'g');

		function getEmoticon( em )
		{
			for(txt in _emoticons) {
				if(em == txt) {
					return _emoticons[txt];
				}
			}
		}
		// transform smileys into img tag
		dataFilter.addRules(
			{
				text: function( text )
				{
					return text.replace( emoticons_regex, getEmoticon );
				}
			});
		// transform img tag to smileys
		// don't know how to replace an element by a text node, so i give up
		// -- this transformation is now done in editor.on( "getData" )
		/*
		htmlFilter.addRules(
			{
				elements :
				{
					'img' : function( element )
					{
						var attributes = element.attributes,
						classe = attributes['class'],
						emoticontxt;
						//alert('className:' + classe);
						var matches = /^emoticon emoticon-index-([0-9]+)$/.exec(classe);
						alert('className = "' + classe + '", matches.length = ' + matches.length);
						if(matches && matches.length == 2) 
						{
							var index = matches[1];
							var search = new RegExp('class="emoticon emoticon-index-'.concat(index, '"'));
							for (var emotxt in this._emoticons) {
							    if (search.test(this._emoticons[emotxt])) {
								emoticontxt = emotxt;
								break;
							    }
							}
							if (emoticontxt) {
								var node = editor.document.createTextNode(emoticontxt);
								//element.parentNode.replaceChild(node,element);
								return node;
							}

						}
					}
				}
			});
		*/
	}


} );

