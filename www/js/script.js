$.fn.error = function(msg){
	if(this.next().is('p.error') == true){
		if(msg != ''){
			this.next().text(msg);
		}else{
			this.removeClass('errorField');
			this.next().remove();
		}
	}else{
		if(msg != ''){
			$('<p class="error">').text(msg).insertAfter(this);
			this.addClass('errorField');
		}
	}
	
	return this;
};

$(function(){
	
	var i = document.createElement('input');
	var hasPlaceholders = 'placeholder' in i;
	
	i=null;
	
	if(!hasPlaceholders){
		// Darn. We don't have html5 placeholders. We will simulate instead.
		$('#header form input').mouseover(hidePlaceholder).focus(hidePlaceholder).mouseout(showPlaceholder).blur(showPlaceholder);
		
		$('#header form input').trigger('mouseout');
	}
	
	$('.i_url').live('blur', promptForMd5Check).live('focus', showFiles);
	
	$('.steps .remove, .version .remove').live('click', removeStep);
	
	$('.steps').children().each(function(){
		$('<a class="drag">Drag</a>').prependTo(this);
		$('<a class="remove" href="#">Remove</a>').appendTo(this);
	});
	
	$('.version').append('<a class="remove" href="#">Remove</a>');
	
	$('<div class="controls">').append(
		$('<a>').text('Copy').click(addCopy).attr('href', '#')
	).append(' '
	).append(
		$('<a>').text('Download').click(addDownload).attr('href', '#')
	).append(' '
	).append(
		$('<a>').text('Delete').click(addDelete).attr('href', '#')
	).append(' '
	).append(
		$('<a>').text('Run').click(addRun).attr('href', '#')
	).prependTo('.steps');
	
	$('.steps').sortable({
		connectWith: '.steps',
		items: '> *:not(.controls)',
		handle: '.drag'
	});//.disableSelection();
	
	$('.variable').live('click', showVariableMenu);
	
	$('#versions p').after(
		$('<a>').text('Add').click(addVersion).attr('href', '#')
	);
	
/*	$('#library').change(toggleLibrary);
	
	if($('#library').prop('checked')){
		toggleLibrary();
	}*/
	
	$('#body form.ajax').submit(formSubmit);
	
	lastVersion = $('#versions .version').length;
});

var focusElem;
var hoverElem;

function hidePlaceholder(e){
	if(e.type === 'mouseover'){
		hoverElem = this;
	}else if(e.type === 'focus'){
		focusElem = this;
	}
	$('label[for='+this.id+']').removeClass('prompt');
}
function showPlaceholder(e){
	if(e.type === 'mouseout' && focusElem == this){
		hoverElem = null;
		return;
	}else{
		focusElem = null;
	}
	if(e.target.value == ''){
		$('label[for='+this.id+']').addClass('prompt');
	}
}

//var unicodeChecking;
//var ansiChecking;

function promptForMd5Check(){
	//if((unicodeChecking && this.id == 'unicode_download') || (ansiChecking && this.id == 'ansi_url')){
	if($(this).data('url') == $(this).val()){
		return;
	}
	
	$(this).data('url', $(this).val());
	
	if($(this).val() != ''){
		var checking = $(this).data('checking');
		if(checking){
			checking.cancel();
		}
		
		var next = $(this).next();
		if(next.length == 1 && next[0].tagName.toLowerCase() == 'p'){
			next.remove();
		}
		
		var $p = $('<p>').text('Would you like the server to get the md5 sum? ').append(
			$('<a>').text('Yes').click(getMd5).attr('href', '#')
		).append(' ').append(
			$('<a>').text('No').click(dontCheckMd5).attr('href', '#')
		).insertAfter(this);
		
		$p.children().eq(0).focus();
	}
}

function getMd5(){
	var $elem = $(this);
	var $prev = $elem.parent().prev();
	var id = $prev[0].id;
	
	if($prev.data('checking')){
		$prev.data('checking').cancel();
	}
	
	$elem = $elem.parent();
	
	$.ajax({
		url: base+'plugins/get_md5',
		type: 'post',
		data: {url: $prev.val() },
		dataType: 'json',
		beforeSend: function(req){
			/*if(id == 'unicode_download'){
				unicodeChecking = req;
			}else{
				ansiChecking = req;
			}*/
			$(this).data('checking', req);
		//},
		//beforeSend: function(){
			$elem.text('Loading file ...');
		},
		complete: function(){
			/*if(id == 'unicode_download'){
				unicodeChecking = null;
			}else{
				ansiChecking = null;
			}*/
			$(this).data('checking', null);
		},
		success: function(data, textStatus, req){
			//var $md5 = $('#'+(id == 'unicode_download' ? 'unicode' : 'ansi')+'_md5');
			var $md5 = $prev.parent().next().find('input');
			var pageMd5 = $md5.val();
			if(pageMd5 == ''){
				$md5.val(data.md5);
				
				$elem.remove();
			}else if(data.md5 == pageMd5){
				//success
				$elem.text('The MD5 sums match.').addClass('green');
			}else{
				//ohhh no! the md5s don't match
				$elem.text('The MD5 sums do not match! The server got "'+data.md5+'".').addClass('error');
			}
			
			$prev.data('files', data.files);
			$prev.data('hashes', data.hashes);
			showFiles.call($prev[0]);
		},
		error: function(){
			$elem.text('There was an error getting the MD5 sum. Is the file at this URL?').addClass('error');
			$(this).data('checking', null);
		}
	});
	
	return false;
}

function dontCheckMd5(){
	var $elem = $(this).parent().remove();
	
	return false
}

function showFiles(){
	var files = $(this).data('files');
	var hashes = $(this).data('hashes');
	if(files == null){
		return;
	}
	
	var $filesBox = $(this).closest('fieldset').data('filesBox');
	
	if($filesBox == null){
		$filesBox = $('<div class="filesBox">Files: (add hashes for files you want to validate)<ol class="noBullets"></ol></div>').insertAfter($(this).closest('fieldset'));
		$(this).closest('fieldset').data('filesBox', $filesBox);
	}
	
	var $ol = $filesBox.find('ol');
	
	$ol.empty();
	
	var len = files.length;
	for(var i=0; i<len; i++){
		$li = $('<li>').append(
			$('<span class="name">').text(files[i])
		);
		
		if(hashes[i] != ''){
			$li.append(
				$('<span class="md5">').text(hashes[i]),
				$('<a id="addhash_' + i + '">').text('Add Hash').click(storeValidHash)
			);
		}
		
		$li.appendTo($ol);
	}
}


function storeValidHash(hash, file){
	var $link = $(this);
	var $li = $link.parent();
	
	$.ajax({
		url: base+'plugins/store_valid_hash',
		type: 'post',
		data: {hash: $li.find('.md5').text(), file: $li.find('.name').text(), response: 'ok' },
		success: function(data) { storeValidHashResponse($link, data); }
	});
}

function storeValidHashResponse($link, data){
	var msg = '';
	
	if(data.error == true){
		msg = 'Already added';
	}else{ 
		msg = 'Added';
	}
	
	$('<span class="added">').text(msg).replaceAll($link);
	
	return false;
}

var i=0;

function addCopy(){
	$(this).parent().parent().append(
		'<div class="copy"><a class="drag">Drag</a>'+
		'<div><label for="from_'+i+'">From</label><input type="text" id="from_'+i+'" class="wide i_from" /></div> '+
		'<div><label for="to_'+i+'">To</label><a class="variable" href="#">$PLUGINDIR$</a><input type="text" class="path i_to" id="to_'+i+'" /></div> '+
		'<div class="options"><input type="checkbox" id="validate_'+i+'" class="i_validate" value="1" /><label for="validate_'+i+'">Validate</label>'+
		'<input type="checkbox" id="backup_'+i+'" class="i_backup" value="1" /><label for="backup_'+i+'">Backup</label>'+
		'<input type="checkbox" id="directory_'+i+'" checked="checked" class="i_directory" value="1" /><label for="directory_'+i+'">Directory</label></div>'+
		'<a href="#" class="remove">Remove</a></div>'
	);
	i++;
	
	return false;
}
function addDownload(){
	$(this).parent().parent().append(
		'<div class="download"><a class="drag">Drag</a>'+
		'<div><label for="from_'+i+'">Download Url</label><input type="text" class="i_url wide" id="from_'+i+'" /></div> '+
		'<div><label for="md5_'+i+'">Md5</label><input type="text" id="md5_'+i+'" class="wide i_md5" /></div>'+
		'<a href="#" class="remove">Remove</a></div>'
	);
	i++;
	
	return false;
}
function addDelete(){
	$(this).parent().parent().append(
		'<div class="delete"><a class="drag">Drag</a>'+
		'<div><label for="delete_'+i+'">Delete</label><a class="variable" href="#">$PLUGINDIR$</a><input type="text" class="path i_delete" id="delete_'+i+'" /></div>'+
		'<a href="#" class="remove">Remove</a></div>'
	);
	i++;
	
	return false;
}
function addRun(){
	$(this).parent().parent().append(
		'<div class="run"><a class="drag">Drag</a>'+
		'<div><label for="run_'+i+'">Run File</label><input type="text" class="wide i_run" id="run_'+i+'" /></div> '+
		'<div><label for="arguments_'+i+'">Arguments</label><input type="text" class="path i_arguments" id="arguments_'+i+'" /></div>'+
		'<div class="options"><input type="checkbox" id="outside_npp_'+i+'" class="i_outside" value="1" /><label for="outside_npp_'+i+'">Run when Notepad++ is closed</label></div>'+
		'<a href="#" class="remove">Remove</a></div>'
	);
	i++;
	
	return false;
}
function removeStep(){
	$(this).parent().remove();
	
	return false;
}

var variables = Array('$PLUGINDIR$', '$CONFIGDIR$', '$NPPDIR$'); //'$PLUGINFILENAME$', 
function showVariableMenu(e){
	e.preventDefault();
	
	var $menu = $('#variables');
	if($menu.length > 0){
		return;
	}
	
	var vars = $.extend([], variables);
	var current = $(this).text();
	
	var index = vars.indexOf(current);
	vars.splice(index, 1);
	
	var $ul = $('<ul id="variables">').data('elem', this).click(variableSelected);
	
	for(var i=0; i<vars.length; i++){
		$('<li><a>'+vars[i]+'</a></li>').appendTo($ul);
	}
	
	$ul.appendTo('body');
	
	var pos = $(this).position();
	
	$ul.css({
		top: pos.top + $(this).outerHeight() - 1,
		left: pos.left + $(this).outerWidth() - $ul.width() - 2
	});
	
	$('body').click(hideVariablesMenu);
}
function variableSelected(e){
	var $elem = $($('#variables').data('elem'));
	
	$elem.text($(e.target).text());
}
function hideVariablesMenu(){
	$('#variables').remove();
	
	$('body').unbind('click', hideVariablesMenu);
}

function formSubmit(){
	var $submit = $(':submit', this).attr('disabled', 'disabled');
	$submit.data('originalVal', $submit.val()).val('Saving...');
	
	nameFormElements('unicode_install', $('#unicode .steps:eq(0)').children());
	nameFormElements('unicode_uninstall', $('#unicode .steps:eq(1)').children());
	nameFormElements('ansi_install', $('#ansi .steps:eq(0)').children());
	nameFormElements('ansi_uninstall', $('#ansi .steps:eq(1)').children());
	
	$.ajax({
		url: this.action,
		type: this.method,
		data: $(this).serialize(),
		dataType: 'json',
		success: function(data){
			if(data.url){
				window.location = base+data.url;
			}else{
				var list = new Array();
				
				$.each(data.errors, function(name, msg){
					list.push(
						$(':input[name="'+name+'"]').error(msg)
					);
				})
				
				$(list).eq(0).focus();
				
				$submit.removeAttr('disabled').val($submit.data('originalVal'));
			}
		},
		complete: function(){
			// We don't want duplicates from error messages
			$submit.closest('form').find('input.temp').remove();
		},
		error: function(){
			$submit.removeAttr('disabled').val($submit.data('originalVal'));
		}
	});
	
	return false;
}

function nameFormElements(prefix, $steps){
	var len = $steps.length;
	// i=1 so we miss the controls
	for(var i=1; i<len; i++){
		var $item = $steps.eq(i);
		var type = $item[0].className;
		
		if(type == 'copy'){
			var name = prefix+'['+(i-1)+'][copy][';
			$item.find('.i_from').attr('name', name+'from]');
			$item.find('.i_to').attr('name', name+'to]');
			$item.find('.i_validate').attr('name', name+'validate]');
			$item.find('.i_backup').attr('name', name+'backup]');
			$item.find('.i_directory').attr('name', name+'is_dir]');
		        $item.find('.i_replace').attr('name', name+'replace]');
		        $item.find('.i_recursive').attr('name', name+'recursive]');
			$item.find('.i_isgpup').attr('name', name+'isgpup]');	
			$item.append(
				$('<input type="hidden" name="'+name+'variable]" class="temp" />').val($item.find('.variable').text())
			);
		}else if(type == 'download'){
			var name = prefix+'['+(i-1)+'][download][';
			$item.find('.i_url').attr('name', name+'url]');
			$item.find('.i_md5').attr('name', name+'md5]');
		}else if(type == 'delete'){
			var name = prefix+'['+(i-1)+'][delete][';
			$item.find('.i_delete').attr('name', name+'delete]');
			
			$item.append(
				$('<input type="hidden" name="'+name+'variable]" class="temp" />').val($item.find('.variable').text())
			);
		}else if(type == 'run'){
			var name = prefix+'['+(i-1)+'][run][';
			$item.find('.i_run').attr('name', name+'run]');
			$item.find('.i_arguments').attr('name', name+'arguments]');
			$item.find('.i_outside').attr('name', name+'outside]');
		}
	}
}

var lastVersion;
function addVersion(){
	$(this).parent().append(
		'<div class="version">' +
		'<div><label for="md5_'+lastVersion+'">MD5</label> <input type="text" id="md5_'+lastVersion+'" name="version['+lastVersion+'][md5]" class="wide" /></div>' +
		'<div><label for="version_'+lastVersion+'">Version</label> <input type="text" id="version_'+lastVersion+'" name="version['+lastVersion+'][number]" /></div>' +
		'<div><label for="comment_'+lastVersion+'">Comment</label> <input type="text" id="comment_'+lastVersion+'" name="version['+lastVersion+'][comment]" class="wide" /></div>' +
		'<a href="#" class="remove">Remove</a></div>'
	);
	lastVersion++;
	return false;
}

/*function toggleLibrary(){
	$('#unicode div').eq(0).toggle();
	$('#ansi div').eq(0).toggle();
}*/
