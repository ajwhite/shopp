/*!
 * system.js - Description
 * Copyright © 2012 by Ingenesis Limited. All rights reserved.
 * Licensed under the GPLv3 {@see license.txt}
 */

jQuery(document).ready(function($) {

	$('#errorlog').scrollTop($('#errorlog').attr('scrollHeight'));

	$.fn.storageEngineSettings = function (menu,context) {
		var $this = $(menu),
			selected = $this.val(),
			engine = (engines[selected]?engines[selected]:false),
			settings = {context:context},
			container = $('#'+context+'-storage-engine').empty();

			if (storageset != null && storageset[selected] != undefined && storageset[selected] != null) {
				$.each(storageset[selected],function (name,setting) {
					settings[name] = setting[context];
				});
			}
			ui = $.tmpl(engine,settings).appendTo(container).find('select').each(function () {
				$(this).val($(this).attr('title'));
			});

			$(window).scrollTop(0);

			return $this;
	};

	var progressbar = false,

		templates = $.each(engines,function (id,engine) {
			$.template(engine,$('#'+engine+'-editor'));
		}),
		imgsmenu = $('#image-storage').change(function () {
			$(this).storageEngineSettings(this,'image');
		}).change(),
		dlsmenu = $('#download-storage').change(function () {
			$(this).storageEngineSettings(this,'download');
		}).change();

	function progress () {
		var progressbar = $('#progress div.bar'),
			scale = $('#progress').outerWidth(),
			p = $('#reindexProcessor').get(0).contentWindow['indexProgress'];
		if (!p) p = 0;
		progressbar.animate({'width': Math.ceil(p*scale) +'px'},100);
		if (p == 1) return setTimeout($.fn.colorbox.close,1000);
		setTimeout(progress,100);
	}

	$('#rebuild-index').click(function () {
		setTimeout(progress,100);
		$.fn.colorbox({'title':$sys.indexing,
			'innerWidth':'250',
			'innerHeight':'50',
			'html':
			'<div id="progress"><div class="bar"><\/div><div class="gloss"><\/div><\/div><iframe id="reindexProcessor" width="0" height="0" src="'+$sys.indexurl+'"><\/iframe>'
		});
	});

});