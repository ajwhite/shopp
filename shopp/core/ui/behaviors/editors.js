/*!
 * editors.js - Product & Category editor behaviors
 * Copyright © 2008-2010 by Ingenesis Limited
 * Licensed under the GPLv3 {@see license.txt}
 */

/**
 * Nested Menu behaviors
 **/
function NestedMenu (i,target,dataname,defaultlabel,data,items,sortoptions) {
	var $=jqnc(), _ = this;
	if (!sortoptions) sortoptions = {'axis':'y'};
	
	_.items = items;
	_.dataname = dataname;
	_.index = i;
	_.element = $('<li><div class="move"></div>'+
		'<input type="hidden" name="'+dataname.replace('[','-').replace(']','-')+'-sortorder[]" value="'+i+'" class="sortorder" />'+
		'<input type="hidden" name="'+dataname+'['+i+'][id]" class="id" />'+
		'<input type="text" name="'+dataname+'['+i+'][name]" class="label" />'+
		'<button type="button" class="delete"><img src="'+uidir+'/icons/delete.png" alt="Delete" width="16" height="16" /></button>'+
		'</li>').appendTo($(target).children('ul'));

	_.moveHandle = _.element.find('div.move');
	_.sortorder = _.element.find('input.sortorder');
	_.id = _.element.find('input.id');
	_.label = _.element.find('input.label');
	_.deleteButton = _.element.find('button.delete').click(function () {
		var deletes = $(target).find('input.deletes');
		if ($(_.id).val() != "") // Only need db delete if an id exists
			deletes.val( (deletes.val() == "")?$(_.id).val():deletes.val()+','+$(_.id).val() );
		if (items) _.itemsElement.remove();
		_.element.remove();
	});

	if (_.items) {
		if (items.type == "list") _.itemsElement = $('<ul></ul>').appendTo(items.target).hide();
		else _.itemsElement = $('<li></li>').appendTo(items.target).hide();
	}

	_.selected = function () {
		$(target).find('ul li').removeClass('selected');
		$(_.element).addClass('selected');
		if (items) {
			$(items.target).children().hide();
			$(_.itemsElement).show();	
		}
	}
	_.element
		.click(this.selected)
		.hover(function () { $(this).addClass('hover'); },
			   function () { $(this).removeClass('hover'); }
		);

	_.label.mouseup(function (e) { this.select(); }).focus(function () {
		$(this).keydown(function (e) {
			e.stopPropagation();
			if (e.keyCode == 13) $(this).blur().unbind('keydown');
		});
	});

	_.id.val(_.index);
	if (data && data.id) _.id.val(data.id);
	if (data && data.name) _.label.val(htmlentities(data.name));
	else _.label.val(defaultlabel+' '+_.index);

	// Enable sorting
	if (!$(target).children('ul').hasClass('ui-sortable'))
		$(target).children('ul').sortable(sortoptions);
	else $(target).children('ul').sortable('refresh');

}

function NestedMenuContent (i,target,dataname,data) {
	var $=jqnc();
	this.contents = $('<textarea name="'+dataname+'['+i+'][value]" cols="40" rows="7"></textarea>').appendTo(target);
	if (data && data.value) this.contents.val(htmlentities(data.value));
}

function NestedMenuOption (i,target,dataname,defaultlabel,data) {
	var $=jqnc(), _ = this;
	
	_.index = $(target).contents().length;
	_.element = $('<li class="option"><div class="move"></div>'+
		'<input type="hidden" name="'+dataname+'['+i+'][options]['+this.index+'][id]" class="id" />'+
		'<input type="text" name="'+dataname+'['+i+'][options]['+this.index+'][name]" class="label" />'+
		'<button type="button" class="delete"><img src="'+uidir+'/icons/delete.png" alt="delete" width="16" height="16" /></button>'+
		'</li>').appendTo(target);
	
	_.moveHandle = _.element.find('div.move');
	_.id = _.element.find('input.id');
	_.label = _.element.find('input.label');
	_.deleteButton = _.element.find('button.delete').click(function () { $(_.element).remove(); });
	
	_.element.hover(function () { $(this).addClass('hover'); },
					   function () { $(this).removeClass('hover'); });

	_.label.click(
		function () { this.select(); }
	).focus(function () {
		$(this).keydown(function (e) {
			e.stopPropagation();
			if (e.keyCode == 13) $(this).blur().unbind('keydown');
		});
	});
	
	_.id.val(_.index);
	if (data.id) _.id.val(data.id);
	if (data.name) _.label.val(htmlentities(data.name));
	if (!data.name) _.label.val(defaultlabel+' '+(_.index+1));
		
}


/**
 * Variations support
 **/
function loadVariations (options,prices) {
	if (!options) return;
	var $=jqnc();
	$.each(options,function (key,option) { 
		addVariationOptionsMenu(option); 
	});

	$.each(prices,function (key,price) { 
		if (this.context == "variation")
			Pricelines.add(this.options.split(","),this,'#variations-pricing');
	});
	Pricelines.updateVariationsUI();
	
	$.each(options,function (key,option) { 
		$.each(option.options,function(i,data) {
			if (data.linked == "on") Pricelines.linkVariations(data.id);
		});
	});
}

function addVariationOptionsMenu (data) {
	var $=jqnc(),
	 	menus = $('#variations-menu'),
	 	entries = $('#variations-list'),
		addMenuButton = $('#addVariationMenu'),
	 	addOptionButton = $('#addVariationOption'),
	 	linkOptionVariations = $('#linkOptionVariations'),
	 	id = variationsidx,
	 	menu = new NestedMenu(id,menus,'options[v]',OPTION_MENU_DEFAULT,data,
			{target:entries,type:'list'},
			{'axis':'y','update':function() { orderOptions(menus,entries) }});
	
	menu.addOption = function (data) {
		var init = false,option;
		if (!data) data = new Object();

		if (!data.id) {
			init = true;
 			data.id = optionsidx;
		} else if (data.id > optionsidx) optionsidx = data.id;
		
	 	option = new NestedMenuOption(menu.index,menu.itemsElement,'options[v]',NEW_OPTION_DEFAULT,data);
		optionsidx++;

		option.linkIcon = $('<img src="'+uidir+'/icons/linked.png" alt="linked" width="16" height="16" class="link" />').appendTo(option.moveHandle);
		option.linked = $('<input type="hidden" name="options[v]['+menu.index+'][options]['+option.index+'][linked]" class="linked" />').appendTo(option.element).change(function () {
			if ($(this).val() == "off")	option.linkIcon.addClass('invisible');
			if ($(this).val() == "on") option.linkIcon.removeClass('invisible');
		});
		if (data.linked) option.linked.val(data.linked).change();
		else option.linked.val('off').change();

		option.selected = function () {
			if (option.element.hasClass('selected')) {
				entries.find('ul li').removeClass('selected');
				selectedMenuOption = false;
			} else {
				entries.find('ul li').removeClass('selected');
				$(option.element).addClass('selected');
				selectedMenuOption = option;
			}
			linkOptionVariations.change();
		}
		option.element.click(option.selected);

		productOptions[option.id.val()] = option.label;
		option.label.blur(function() { updateVariationLabels(); });
		option.deleteButton.unbind('click');
	
		option.deleteButton.click(function () {
			if (menu.itemsElement.children().length == 1)
				deleteVariationPrices([option.id.val()],true);
			else deleteVariationPrices([option.id.val()]);
			option.element.remove();
		});
		
		if (!init) addVariationPrices(option.id.val());
		else addVariationPrices();
		
		entries.dequeue().animate({ scrollTop: entries.attr('scrollHeight')-entries.height() }, 200);
		option.label.click().focus().select().keydown(function(e) { 
			var key = e.keyCode || e.which; 
			if (key != 9) return;
			e.preventDefault();
			option.label.blur();
			addOptionButton.focus();
		});
		
		menu.items.push(option);
	}

	menu.items = new Array();
	if (data && data.options) $.each(data.options,function () { menu.addOption(this) });
	else {
		menu.addOption();
		menu.addOption();
	}
	menu.itemsElement.sortable({'axis':'y','update':function(){ orderVariationPrices() }});

	menu.element.unbind('click',menu.click);
	menu.element.click(function () {
		menu.selected();
		$(addOptionButton).unbind('click').click(menu.addOption);
	});
	optionMenus[variationsidx++] = menu;
	
	menu.deleteButton.click(function () {
		var deletedOptions = new Array();
		$(menu.itemsElement).find('li').not('.ui-sortable-helper').find('input.id').each(function (i,id) {
			deletedOptions.push($(id).val());
		});
		deleteVariationPrices(deletedOptions,true);
		// menu.element.remove();
	});
	
	if (!data) {
		entries.dequeue().animate({ scrollTop: entries.attr('scrollHeight')-entries.height() }, 200);
		menu.label.click().focus().select().keydown(function(e) { 
			var key = e.keyCode || e.which; 
			if (key != 9) return;
			e.preventDefault(); 
			addOptionButton.focus();
		});
	}
	
}

/**
 * buildVariations()
 * Creates an array of all possible combinations of the product variation options */
function buildVariations () {
	var $=jqnc(),i,setid,fields,index,
	 	combos = new Array(),							// Final list of possible variations
	 	optionSets = $('#variations-list ul'),			// Reference to the DOM-stored option set
	 	totalSets = optionSets.length,					// Total number of sets
	 	lastOptionSet = totalSets-1,					// Reference to the index of the last set
	 	address = new Array(optionSets.length),			// Helper to reference a specific option in a specific set
	 	totalOptions = new Array(optionSets.length),	// Reference list of total options of a set
	 	totalVariations = 0;							// The total variations possible
	
	// Identify total options in each set and calculate total permutations
	optionSets.each(function (id,set) {
		totalOptions[id] = $(set).children().length;	// Save the total options for this set
		
		// Calculate the total possibilities (options * options * options...)
		if (totalVariations == 0) totalVariations = $(set).children().length;
		else totalVariations = totalVariations * $(set).children().length;
		address[id] = 0;								// initialize our address helper list
	});

	// Build variation labels for each possible permutation
	for (i = 0; i < totalVariations; i++) {
		
		// Grab the label for the currently addressed option in each set
		// and add it to this variation permutation
		for (setid = 0; setid < optionSets.length; setid++) {
			fields = $(optionSets[setid]).children("li").not('.ui-sortable-helper').children("input.id");
			if (!combos[i]) combos[i] = [$(fields[address[setid]]).val()];
			else combos[i].push($(fields[address[setid]]).val());
		}
		
		// Figure out what is the next combination by trying to increment
		// the last option set address. If the last address exceeds the total options, 
		// reset it to 0 and increment the previous option set address 
		// (and if that exceeds its total, reset and so on)
		if (++address[lastOptionSet] >= totalOptions[lastOptionSet]) {
			for (index = lastOptionSet; index > -1; index--) {
				if (address[index] < totalOptions[index]) continue;
				address[index] = 0;
				if (index-1 > -1) address[(index-1)]++;
			}
		}
		
	}

	return combos;
}

function addVariationPrices (data) {
	if (data) return;
	var $=jqnc(), key, preKey
	 	updated = buildVariations(),
	 	variationPricing = $('#variations-pricing'),
	 	variationPricelines = $(variationPricing).children(),
	 	added = false;

	$(updated).each(function(id,options) {
	 	key = xorkey(options);
	 	preKey = xorkey(options.slice(0,options.length-1));
		if (preKey == "") preKey = -1;

		if (!Pricelines.row[key]) {
			if (Pricelines.row[preKey]) {
				Pricelines.row[key] = Pricelines.row[preKey];
				delete Pricelines.row[preKey];
				Pricelines.row[key].setOptions(options);
			} else {
				if (variationPricelines.length == 0) { // Append new row
					Pricelines.add(options,{context:'variation'},'#variations-pricing');
				} else { // Add after previous variation
					Pricelines.add(options,{context:'variation'},Pricelines.row[ xorkey(updated[(id-1)]) ].row,'after');
				}
				added = true;
			}
		}
	});
	if (added) Pricelines.updateVariationsUI();
}

function deleteVariationPrices (optionids,reduce) {
	var $=jqnc(),
	 	updated = buildVariations(),
	 	reduced = false,
		i,key,modOptions,newkey,dbPriceId;
		
	$(updated).each(function(id,options) {
		key = xorkey(options);

		for (i = 0; i < optionids.length; i++)  {
			if (options.indexOf(optionids[i]) != -1) {

				modOptions = new Array();
				$(options).each(function(index,option) {
					if (option != optionids[i]) modOptions.push(option);
				});
				newkey = xorkey(modOptions);
			
				if (reduce && !Pricelines.row[newkey]) {
					if (newkey != 0) Pricelines.row[newkey] = Pricelines.row[key];
					else Pricelines.row[key].row.remove();
					delete Pricelines.row[key];
					
					if (Pricelines.row[newkey]) {
						Pricelines.row[newkey].setOptions(modOptions);
						reduced = true;
					}
				} else {
					if (Pricelines.row[key]) {
						// Mark priceline for removal from db
						dbPriceId = $('#priceid-'+Pricelines.row[key].id).val();
						if ($('#deletePrices').val() == "") $('#deletePrices').val(dbPriceId);
						else $('#deletePrices').val($('#deletePrices').val()+","+dbPriceId);

						// Remove the priceline row from the ui/dom
						Pricelines.remove(key);
					}
				}
			
			}
		}

	});
	
	if (reduced) updateVariationsUI();

}

function optionMenuExists (label) {
	if (!label) return false;
	var $=jqnc(),
		found = false;
	$.each(optionMenus,function (id,menu) {
		if (menu && $(menu.label).val() == label) return (found = id);
	});
	if (optionMenus[found]) return optionMenus[found];
	return found;
}

function optionMenuItemExists (menu,label) {
	if (!menu || !menu.items || !label) return false;
	var $=jqnc(),
		found = false;
	$.each(menu.items,function (id,item) {
		if (item && $(item.label).val() == label) return (found = true);
	});
	return found;
}

function updateVariationLabels () {
	var $=jqnc(),
	 	updated = buildVariations();
	$(updated).each(function(id,options) {
		var key = xorkey(options);
		if (Pricelines.row[key]) Pricelines.row[key].updateLabel();
	});
}

function orderOptions (menus,options) {
	var $=jqnc();
	$(menus).find("ul li").not('.ui-sortable-helper').find('input.id').each(function (i,menuid) {
		if (menuid) $(optionMenus[$(menuid).val()].itemsElement).appendTo(options);
	});
	orderVariationPrices();
}

function orderVariationPrices () {
	var $=jqnc(), key
	 	updated = buildVariations();

	$(updated).each(function (id,options) {
		key = xorkey(options);
		if (key > 0 && Pricelines.row[key])
			Pricelines.reorderVariation(key,options);
	});

	Pricelines.updateVariationsUI("tabs");
}

// Magic key generator
function xorkey (ids) {
	for (var key=0,i=0; i < ids.length; i++) 
		key = key ^ (ids[i]*7001);
	return key;
}

function variationsToggle () {
	var $=jqnc(),
		toggle = $(this),
		ui = $('#variations'),
		baseprice = $('#product-pricing');
		
	if (toggle.attr('checked')) {
		if (Pricelines.row[0]) Pricelines.row[0].disable();
		baseprice.hide();
		ui.show();
	} else {
		ui.hide();
		baseprice.show();
		if (Pricelines.row[0]) Pricelines.row[0].enable();
	}
}

function addonsToggle () {
	var $=jqnc(),
		toggle = $(this),
		ui = $('#addons');
	
	if (toggle.attr('checked')) ui.show();
	else ui.hide();
}

function clearLinkedIcons () {
	jQuery('#variations-list input.linked').val('off').change();
}

function linkVariationsButton () {
	var $=jqnc();
	if (selectedMenuOption) {
		if (selectedMenuOption.linked.val() == 'off') {
			// If all are linked, unlink everything first
			if (Pricelines.allLinked()) {
				clearLinkedIcons();
				Pricelines.unlinkAll();
			}
			selectedMenuOption.linked.val('on').change();
			Pricelines.linkVariations(selectedMenuOption.id.val());
		} else {
			selectedMenuOption.linked.val('off').change();
			Pricelines.unlinkVariations(selectedMenuOption.id.val());	
		}
	} else {
		// Nothing selected, link/unlink all
		clearLinkedIcons();
		if (Pricelines.allLinked()) {
			Pricelines.unlinkAll();
		} else Pricelines.linkAll();
	}
	$(this).change();
}

function linkVariationsButtonLabel () {
	var $=jqnc();
	if (selectedMenuOption) {
		if (selectedMenuOption.linked.val() == 'on') $(this).find('small').html(' '+UNLINK_VARIATIONS);
		else $(this).find('small').html(' '+LINK_VARIATIONS);
	} else {
		if (Pricelines.allLinked()) $(this).find('small').html(' '+UNLINK_ALL_VARIATIONS);
		else $(this).find('small').html(' '+LINK_ALL_VARIATIONS);
	}
	
}

/**
 * Addons support
 **/
function loadAddons (addons,prices) {
	var $=jqnc();
	if (!addons) return;

	$.each(addons,function (key,addon) {
		newAddonGroup(addon); 
	});

	$.each(prices,function (key,price) {
		if (price.context == "addon") {
			// Lookup which group this one belongs to
			var group = addonOptionsGroup[price.options];
			Pricelines.add(price.options,this,'#addon-pricegroup-'+group);
		}
			
	});
	Pricelines.updateVariationsUI();
}

function newAddonGroup (data) {
	var $=jqnc(),
	 	menus = $('#addon-menu'),
	 	entries = $('#addon-list'),
		addMenuButton = $('#newAddonGroup'),
	 	addOptionButton = $('#addAddonOption'),
	 	id = addon_group_idx,
	 	menu = new NestedMenu(id,menus,'options[a]',ADDON_GROUP_DEFAULT,data,
		{target:entries,type:'list'},
		{'axis':'y','update':function() { orderAddonGroups() }}
	);
	
	menu.itemsElement.attr('id','addon-group-'+id);
	menu.pricegroup = $('<div id="addon-pricegroup-'+id+'" />').appendTo('#addon-pricing');
	menu.pricegroupLabel = $('<label />').html('<h4>'+menu.label.val()+'</h4>').prependTo(menu.pricegroup);
	menu.updatePriceLabel = function () {
		menu.pricegroupLabel.html('<h4>'+menu.label.val()+'</h4>');
	}
	menu.label.blur(menu.updatePriceLabel);
	
	menu.addOption = function (data) {
		var init = false, option, optionid;
		if (!data) data = new Object();

		if (!data.id) {
			init = true;
 			data.id = addonsidx;
		} else if (data.id > addonsidx) addonsidx = data.id;
		
	 	option = new NestedMenuOption(menu.index,menu.itemsElement,'options[a]',NEW_OPTION_DEFAULT,data);
		addonsidx++;
		optionid = option.id.val();
		option.selected = function () {
			if (option.element.hasClass('selected')) {
				entries.find('ul li').removeClass('selected');
				selectedMenuOption = false;
			} else {
				entries.find('ul li').removeClass('selected');
				$(option.element).addClass('selected');
				selectedMenuOption = option;
			}
		}
		option.element.click(option.selected);

		productAddons[optionid] = option.label;
		option.label.blur(function() { Pricelines.row[optionid].updateLabel(); });
		option.deleteButton.unbind('click');
	
		option.deleteButton.click(function () {
			Pricelines.row[option.id.val()].row.remove();
			option.element.remove();
		});

		if (init) Pricelines.add(option.id.val(),{context:'addon'},menu.pricegroup);
		addonOptionsGroup[optionid] = menu.index;
		menu.items.push(option);
		
		entries.dequeue().animate({ scrollTop: entries.attr('scrollHeight')-entries.height() }, 200);
		option.label.click().focus().select().keydown(function(e) { 
			var key = e.keyCode || e.which; 
			if (key != 9) return;
			e.preventDefault(); 
			addOptionButton.focus();
		});
		
	}

	menu.items = new Array();
	if (data && data.options) $.each(data.options,function () { menu.addOption(this) });
	else {
		menu.addOption();
		menu.addOption();
	}
	menu.itemsElement.sortable({'axis':'y','update':function(){ orderAddonPrices(menu.index) }});

	menu.element.unbind('click',menu.click);
	menu.element.click(function () {
		menu.selected();
		$(addOptionButton).unbind('click').click(menu.addOption);
	});
 
	addonGroups[addon_group_idx++] = menu;
	
	menu.deleteButton.unbind('click');
	menu.deleteButton.click(function () {
		$('#addon-list #addon-group-'+menu.index+' li').not('.ui-sortable-helper').find('input.id').each(function (id,option) {
			if (Pricelines.row[$(option).val()])
				Pricelines.row[$(option).val()].row.remove();
		});
		menu.pricegroup.remove();
		menu.remove();
	});

	if (!data) {
		menus.dequeue().animate({ scrollTop: menus.attr('scrollHeight')-menus.height() }, 200);
		menu.label.click().focus().select().keydown(function(e) { 
			var key = e.keyCode || e.which; 
			if (key != 9) return;
			e.preventDefault(); 
			addMenuButton.focus();
		});
	}

}

function orderAddonGroups () {
	var $=jqnc(),menu;
	$('#addon-menu ul li').not('.ui-sortable-helper').find('input.id').each(function (i,menuid) {
		menu = addonGroups[$(menuid).val()];
		menu.pricegroup.appendTo('#addon-pricing');
	});
}

function orderAddonPrices (index) {
	var $=jqnc(),menu = addonGroups[index];
	$('#addon-list #addon-group-'+menu.index+' li').not('.ui-sortable-helper').find('input.id').each(function (id,option) {
		Pricelines.reorderAddon($(option).val(),menu.pricegroup);
	});
}

function readableFileSize (size) {
	var units = new Array("bytes","KB","MB","GB"),
		sized = size*1,
		unit = 0;
	if (sized == 0) return sized;
	while (sized > 1000) {
		sized = sized/1024;
		unit++;
	}
	return sized.toFixed(2)+" "+units[unit];
}

function unsavedChanges () {
	var mce = typeof(tinyMCE) != 'undefined' ? tinyMCE.activeEditor : false;

	if ( mce && !mce.isHidden() ) {
		if ( mce.isDirty() )
			return ShoppSettings.UNSAVED_CHANGES_WARNING;
	}
	if (changes && !saving) return ShoppSettings.UNSAVED_CHANGES_WARNING;	
}

/**
 * Add a product spec/detail
 **/
function addDetail (data) {
	var $=jqnc(),i,optionsmenu,
	 	menus = $('#details-menu'),
	 	entries = $('#details-list'),
	 	id = detailsidx++,
	 	menu = new NestedMenu(id,menus,'details','Detail Name',data,{target:entries});

	if (data && data.options) {
		optionsmenu = $('<select name="details['+menu.index+'][value]"></select>').appendTo(menu.itemsElement);
		for (i in data.options) $('<option>'+data.options[i]['name']+'</option>').appendTo(optionsmenu);		
		if (data && data.value) optionsmenu.val(htmlentities(data.value));	
	} else menu.item = new NestedMenuContent(menu.index,menu.itemsElement,'details',data);	

	
	if (!data || data.add) {
		menu.add = $('<input type="hidden" name="details['+menu.index+'][new]" value="true" />').appendTo(menu.element);
		menus.dequeue().animate({ scrollTop: menus.attr('scrollHeight')-menus.height() }, 200);
		menu.label.click().focus().select();
		menu.item.contents.keydown(function(e) { 
			var key = e.keyCode || e.which; 
			if (key != 9) return;
			e.preventDefault(); 
			$('#addDetail').focus();
		});

	}
}

/**
 * Image Uploads using SWFUpload or the jQuery plugin One Click Upload
 **/
function ImageUploads (id,type) {
	var $ = jqnc(),
	swfu,
	settings = {
		button_text: '<span class="button">'+ADD_IMAGE_BUTTON_TEXT+'</span>',
		button_text_style: '.button { text-align: center; font-family:"Lucida Grande","Lucida Sans Unicode",Tahoma,Verdana,sans-serif; font-size: 9px; color: #333333; }',
		button_text_top_padding: 3,
		button_height: "22",
		button_width: "100",
		button_image_url: uidir+'/icons/buttons.png',
		button_placeholder_id: "swf-uploader-button",
		upload_url : ajaxurl,
		flash_url : uidir+'/behaviors/swfupload/swfupload.swf',
		file_queue_limit : 0,
		file_size_limit : filesizeLimit+'b',
		file_types : "*.jpg;*.jpeg;*.png;*.gif",
		file_types_description : "Web-compatible Image Files",
		file_upload_limit : filesizeLimit,
		post_params : {
			action:'shopp_upload_image',
			parent:id,
			type:type
		},

		swfupload_loaded_handler : swfuLoaded,
		file_queue_error_handler : imageFileQueueError,
		file_dialog_complete_handler : imageFileDialogComplete,
		upload_start_handler : startImageUpload,
		upload_progress_handler : imageUploadProgress,
		upload_error_handler : imageUploadError,
		upload_success_handler : imageUploadSuccess,

		custom_settings : {
			loaded: false,
			targetHolder : false,
			progressBar : false,
			sorting : false
			
		},
		debug: imageupload_debug
		
	}
	
	// Initialize image uploader
	
	if (flashuploader)
		swfu = new SWFUpload(settings);

	// Browser image uploader
	$('#image-upload').upload({
		name: 'Filedata',
		action: ajaxurl,
		params: {
			action:'shopp_upload_image',
			type:type
		},
		onSubmit: function() {
			this.targetHolder = $('<li id="image-uploading"><input type="hidden" name="images[]" value="" /><div class="progress"><div class="bar"></div><div class="gloss"></div></div></li>').appendTo('#lightbox');
			this.progressBar = this.targetHolder.find('div.bar');
			this.sorting = this.targetHolder.find('input');
		},
		onComplete: function(results) {
			console.log(results);
			var image,img,deleteButton;
			if (results == "") {
				$(this.targetHolder).remove();
				alert(SERVER_COMM_ERROR);
				return true;
			}
			image = $.parseJSON(results);
			if (image.error) {
				$(this.targetHolder).remove();
				alert(image.error);
				return true;
			}
			this.targetHolder.attr({'id':'image-'+image.id});
			this.sorting.val(image.id);
			img = $('<img src="?siid='+image.id+'" width="96" height="96" class="handle" />').appendTo(this.targetHolder).hide();
			deleteButton = $('<button type="button" name="deleteImage" value="'+image.src+'" title="Delete product image&hellip;" class="deleteButton"><img src="'+uidir+'/icons/delete.png" alt="-" width="16" height="16" /></button>').appendTo($(this.targetHolder)).hide();
	
			$(this.progressBar).animate({'width':'76px'},250,function () { 
				$(this).parent().fadeOut(500,function() {
					$(this).remove(); 
					$(img).fadeIn('500');
					enableDeleteButton(deleteButton);
				});
			});
		}
	});
	
	$(document).load(function() {
		if (!swfu.loaded) $('#product-images .swfupload').remove();
	});
	
	sorting();
	$('#lightbox li').each(function () {
		$(this).dblclick(function () {
			var id = $(this).attr('id')+"-details",
				src = $('#'+id),
				srcid = src.find('input[type=hidden]').val(),
				srcthumb = src.find('img'),
				srctitle = src.find('input.imagetitle'),
				srcalt = src.find('input.imagealt'),
				srcCropped = src.find('input.imagecropped'),
				ui = $('<div class="image-details-editor">'+
							'<div class="details-editor">'+
							'<img class="thumb" width="96" height="96" />'+
								'<div class="details">'+
									'<p><label>'+IMAGE_DETAILS_TITLE_LABEL+': </label><input type="text" name="title" /></p>'+
									'<p><label>'+IMAGE_DETAILS_ALT_LABEL+': </label><input type="text" name="alt" /></p>'+
								'</div>'+
							'</div>'+
							'<div class="cropping">'+
							'<p class="clear">'+IMAGE_DETAILS_CROP_LABEL+': '+
							'<select name="cropimage"><option></option></select></p>'+
							'<div class="cropui"></div><br class="clear"/>'+
						'</div></div>'),
				thumb = ui.find('img').attr('src',srcthumb.attr('src')),
				titlefield = ui.find('input[name=title]').val(srctitle.val()).change(function () {
					srctitle.val(titlefield.val());
				}),
				altfield = ui.find('input[name=alt]').val(srcalt.val()).change(function () {
					srcalt.val(altfield.val());
				}),
				cropping = ui.find('div.cropping').hide(),
				croptool = ui.find('div.cropui'),
				cropselect = ui.find('select[name=cropimage]').change(function () {
					if (cropselect.val() == '') {
						croptool.empty();
						$.fn.colorbox.resize();
						return;
					}
					
					var d = cropselect.val().split(':'),
						init = srcCropped.filter('input[alt='+cropselect.val()+']').val().split(',');
					croptool.empty().scaleCrop({
						imgsrc:'?siid='+srcid,
						target:{width:parseInt(d[0]),height:parseInt(d[1])},
						init:{x:parseInt(init[0]),y:parseInt(init[1]),s:new Number(init[2])}
					}).ready(function () {
						$.fn.colorbox.resize();
					}).bind('change.scalecrop',function (e,c) {
						if (c) srcCropped.filter('input[alt='+cropselect.val()+']').val(c.x+','+c.y+','+c.s);
					});
				});
		
			if (srcCropped.size() > 0) {
				srcCropped.each(function (i,e) {
					var d = $(e).attr('alt');
					$('<option value="'+d+'">'+(i+1)+': '+d.replace(':','&times;')+'</option>').appendTo(cropselect);
				});
				cropping.show();
			}

			$.fn.colorbox({'title':IMAGE_DETAILS_TEXT,'html':ui})
			
		});
		enableDeleteButton($(this).find('button.deleteButton'));
	});

	function swfuLoaded () {
		$('#browser-uploader').hide();	
		swfu.loaded = true;
	}
	
	function sorting () {
		if ($('#lightbox li').size() > 0) $('#lightbox').sortable({'opacity':0.8});
	}

	function imageFileQueueError (file, error, message) {

		if (error == SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert("You selected too many files to upload at one time. " + (message === 0 ? "You have reached the upload limit." : "You may upload " + (message > 1 ? "up to " + message + " files." : "only one file.")));
			return;
		} else alert(message);
	
	}

	function imageFileDialogComplete (selected, queued) {
		try {
			this.startUpload();
		} catch (ex) {
			this.debug(ex);
		}
	}

	function startImageUpload (file) {
		this.targetHolder = $('<li class="image uploading"><input type="hidden" name="images[]" /><div class="progress"><div class="bar"></div><div class="gloss"></div></div></li>').appendTo($('#lightbox'))
		this.progressBar = this.targetHolder.find('div.bar');
		this.sorting = this.targetHolder.find('input');
	}

	function imageUploadProgress (file, loaded, total) {
		this.progressBar.animate({'width':Math.ceil((loaded/total)*200)+'px'},100);
	}

	function imageUploadError (file, error, message) {
		//console.log(error+": "+message);
	}

	function imageUploadSuccess (file, results) {
		var image,img,deleteButton;
		image = $.parseJSON(results);
		if (!image.id) {
			this.targetHolder.remove();
			if (image.error) alert(image.error);
			else alert(UNKNOWN_UPLOAD_ERROR);
			return true;
		}
	
		this.targetHolder.attr({'id':'image-'+image.id});
		this.sorting.val(image.id);
		img = $('<img src="?siid='+image.id+'" width="96" height="96" class="handle" />').appendTo(this.targetHolder).hide();
		deleteButton = $('<button type="button" name="deleteImage" value="'+image.id+'" title="Delete product image&hellip;" class="deleteButton"><img src="'+uidir+'/icons/delete.png" alt="-" width="16" height="16" /></button>').appendTo(this.targetHolder).hide();
		sorting();
		
		this.progressBar.animate({'width':'76px'},250,function () { 
			$(this).parent().fadeOut(500,function() {
				$(this).remove(); 
				$(img).fadeIn('500');
				enableDeleteButton(deleteButton);
			});
		});
	}
	
	function enableDeleteButton (button) {
		button.hide();

		button.parent().hover(function() {
			button.show();
		},function () {
			button.hide();
		});
	
		button.click(function() {
			if (confirm(DELETE_IMAGE_WARNING)) {
				$('#deleteImages').val(($('#deleteImages').val() == "")?button.val():$('#deleteImages').val()+','+button.val());
				button.parent().fadeOut(500,function() {
					$(this).remove();
				});
			}
		});
	}

}

jQuery.fn.FileChooser = function (line,status) {
	var $ = jqnc(),
		importurl = $('#import-url'),
		attach = $('#attach-file'),
		dlpath = $('#download_path-'+line),
		dlname = $('#download_file-'+line),
		file = $('#file-'+line),
		stored = false,
		progressbar = false;
		
	fileUploads.updateLine(line,status);
	importurl.unbind('keydown').unbind('keypress').suggest(
			sugg_url+'&action=shopp_storage_suggestions&t=download', 
			{ delay:500, minchars:3, multiple:false, onSelect:function () { importurl.change(); } }
	).change(function () {
		var $this = $(this);
		$this.removeClass('warning').addClass('verifying');
		$.ajax({url:fileverify_url+'&action=shopp_verify_file&t=download',
				type:"POST",
				data:'url='+$this.val(),
				timeout:10000,
				dataType:'text',
				success:function (results) {
					$this.attr('class','fileimport');
					if (results == "OK") return $this.addClass('ok');
					if (results == "NULL") $this.attr('title',FILE_NOT_FOUND_TEXT);
					if (results == "ISDIR") $this.attr('title',FILE_ISDIR_TEXT);
					if (results == "READ") $this.attr('title',FILE_NOT_READ_TEXT);
					$this.addClass("warning");
				}
		});
	});
	
	attach.click(function () {
		$.fn.colorbox.hide()
		if (stored) {
			dlpath.val(importurl.val());
			importurl.val('').attr('class','fileimport');
			return true;
		}
		
		var importid = false,
			importdata = false,
			importfile = importurl.val(),
			importing = function () {
				$.ajax({url:fileimportp_url+'&action=shopp_import_file_progress&proc='+importid,
					timeout:500,
					dataType:'text',
					success:function (status) {
						var total = parseInt(importdata.size);
							width = Math.ceil((status/total)*76),
							progressbar = file.find('div.progress > div.bar');
						if (status < total) setTimeout(importing,1000);
						else { // Completed
							if (progressbar) progressbar.css({'width':'100%'}).fadeOut(500,function () {
								if (!importdata.name) return $this.attr('class','');
								file.attr('class','file '+importdata.mime.replace('/',' ')).html(importdata.name+'<br /><small>'+readableFileSize(importdata.size)+'</small>');
								dlpath.val(importdata.path); dlname.val(importdata.name);
								importurl.val('').attr('class','fileimport');
							});
							return;				
						}
						if (progressbar) progressbar.animate({'width':width+'px'},500);
					}
				});

			}
		
		file.attr('class','').html('<div class="progress"><div class="bar"></div><div class="gloss"></div></div><iframe width="0" height="0" src="'+fileimport_url+'&action=shopp_import_file&url='+importfile+'"></iframe>');
		file.find('iframe').load(function () {
			var f = $(this).contents().find('body').html();
			importdata = $.parseJSON(f);
			
			console.log(importdata);
			if (importdata.error) return file.attr('class','error').html('<small>'+importdata.error+'</small>');
			if (!importdata.path) return file.attr('class','error').html('<small>'+FILE_UNKNOWN_IMPORT_ERROR+'</small>');
			
			if (importdata.stored) {
				file.attr('class','file '+importdata.mime.replace('/',' ')).html(importdata.name+'<br /><small>'+readableFileSize(importdata.size)+'</small>');
				dlpath.val(importdata.path); dlname.val(importdata.name);
				importurl.val('').attr('class','fileimport');
				return;
			} else {
				savepath = importdata.path.split('/');
				importid = savepath[savepath.length-1];
				importing();
			}
		});

	});

	$(this).colorbox({'title':'File Selector','innerWidth':'360','innerHeight':'140','inline':true,'href':'#chooser'});
}


/**
 * File upload handlers for product download files using SWFupload
 **/
function FileUploader (button,defaultButton) {
	var $ = jqnc(), _ = this;

	_.settings = {
		button_text: '<span class="button">'+UPLOAD_FILE_BUTTON_TEXT+'</span>',
		button_text_style: '.button { text-align: center; font-family:"Lucida Grande","Lucida Sans Unicode",Tahoma,Verdana,sans-serif; font-size: 9px; color: #333333; }',
		button_text_top_padding: 3,
		button_height: "22",
		button_width: "100",
		button_image_url: uidir+'/icons/buttons.png',
		button_placeholder_id: button,
		button_action: SWFUpload.BUTTON_ACTION.SELECT_FILE,
		flash_url : uidir+'/behaviors/swfupload/swfupload.swf',
		upload_url : ajaxurl,
		file_queue_limit : 1,
		file_size_limit : filesizeLimit+'b',
		file_types : "*.*",
		file_types_description : "All Files",
		file_upload_limit : filesizeLimit,
		post_params : {
			action:'shopp_upload_file'
		},
				
		swfupload_loaded_handler : swfuLoaded,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_start_handler : startUpload,
		upload_progress_handler : uploadProgress,
		upload_success_handler : uploadSuccess,
		
		custom_settings : {
			loaded : false,
			targetCell : false,
			targetLine : false,
			progressBar : false
		},
		debug: fileupload_debug
		
	}
	
	// Initialize file uploader
	
	if (flashuploader)
		_.swfu = new SWFUpload(_.settings);
	
	// Browser-based AJAX uploads
	defaultButton.upload({
		name: 'Filedata',
		action: ajaxurl,
		params: { action:'shopp_upload_file' },
		onSubmit: function() {
			this.targetHolder.attr('class','').html('');
			$('<div class="progress"><div class="bar"></div><div class="gloss"></div></div>').appendTo(this.targetHolder);
			this.progressBar = this.targetHolder.find('div.bar');
		},
		onComplete: function(results) {
			var filedata = $.parseJSON(results),
				targetHolder = this.targetHolder;
			if (filedata.error) {
				targetHolder.html("No download file.");
				alert(filedata.error);
				return true;
			}
			filedata.type = filedata.type.replace(/\//gi," ");
			$(this.progressBar).animate({'width':'76px'},250,function () { 
				$(this).parent().fadeOut(500,function() {
					targetHolder.attr('class','file '+filedata.type).html(filedata.name+'<br /><small>'+readableFileSize(filedata.size)+'</small><input type="hidden" name="price['+linenum+'][download]" value="'+filedata.id+'" />');
					$(this).remove(); 
				});
			});
		}
	});

	$(_).load(function () {
		if (!_.swfu.loaded) $(defaultButton).parent().parent().find('.swfupload').remove();
	});
	
	function swfuLoaded () {
		$(defaultButton).hide();
		this.loaded = true;
	}
	
	_.updateLine = function (line,status) {
		_.swfu.targetLine = line;
		_.swfu.targetCell = status;
	}
		
	function fileQueueError (file, error, message) {
		if (error == SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert("You selected too many files to upload at one time. " + (message === 0 ? "You have reached the upload limit." : "You may upload " + (message > 1 ? "up to " + message + " files." : "only one file.")));
			return;
		} else {
			alert(message);
		}

	}
	
	function fileDialogComplete (selected, queued) {
		$.fn.colorbox.hide();

		if (!selected) return;
		try {
			this.startUpload();
		} catch (ex) {
			this.debug(ex);
		}
		
	}

	function startUpload (file) {
		this.targetCell.attr('class','').html('');
		$('<div class="progress"><div class="bar"></div><div class="gloss"></div></div>').appendTo(this.targetCell);
		this.progressBar = this.targetCell.find('div.bar');
	}

	function uploadProgress (file, loaded, total) {
		this.progressBar.animate({'width':Math.ceil((loaded/total)*76)+'px'},100);
	}

	function uploadSuccess (file, results) {
		var filedata = $.parseJSON(results),
			targetCell = this.targetCell,
			i = this.targetLine;
		if (!filedata.id && !filedata.name) {
			$(this.targetHolder).html(NO_DOWNLOAD);
			if (filedata.error) alert(filedata.error);
			else alert(UNKNOWN_UPLOAD_ERROR);
			return true;
		}

		filedata.type = filedata.type.replace(/\//gi," ");
		$(this.progressBar).animate({'width':'76px'},250,function () { 
			$(this).parent().fadeOut(500,function() {
				$(this).remove(); 
				$(targetCell).attr('class','file '+filedata.type).html(filedata.name+'<br /><small>'+readableFileSize(filedata.size)+'</small><input type="hidden" name="price['+i+'][download]" value="'+filedata.id+'" />');
			});
		});
	}

}

function SlugEditor (id,type) {
	var $ = jqnc(), _ = this;
	
	_.edit_permalink = function () {
			var i, c = 0,
			 	editor = $('#editable-slug'),
			 	revert_editor = editor.html(),
			 	real_slug = $('#slug_input'),
			 	revert_slug = real_slug.html(),
			 	buttons = $('#edit-slug-buttons'),
			 	revert_buttons = buttons.html(),
			 	full = $('#editable-slug-full').html();
		
			buttons.html('<button type="button" class="save button">'+SAVE_BUTTON_TEXT+'</button> <button type="button" class="cancel button">'+CANCEL_BUTTON_TEXT+'</button>');
			buttons.children('.save').click(function() {
				var slug = editor.children('input').val();
				$.post(editslug_url+'&action=shopp_edit_slug', 
					{ 'id':id, 'type':type, 'slug':slug },
					function (data) {
						editor.html(revert_editor);
						buttons.html(revert_buttons);
						if (data != -1) {
							editor.html(data);
							$('#editable-slug-full').html(data);
							real_slug.val(data);
						}
						_.enable();
					},'text');
			});
			$('#edit-slug-buttons .cancel').click(function() {
				editor.html(revert_editor);
				buttons.html(revert_buttons);
				real_slug.attr('value', revert_slug);
				_.enable();
			});
			
			for(i=0; i < full.length; ++i) if ('%' == full.charAt(i)) c++;
			slug_value = (c > full.length/4)? '' : full;
			
			editor.html('<input type="text" id="new-post-slug" value="'+slug_value+'" />').children('input').keypress(function(e) {
				// on enter, just save the new slug, don't save the post
				var key = e.which;
				if (key == 13 || key == 27) e.preventDefault();
				if (13 == key) buttons.children('.save').click();
				if (27 == key) buttons.children('.cancel').click();
				real_slug.val(this.value)
			}).focus();

	}
	
	_.enable = function () {
		$('#edit-slug-buttons').children('.edit-slug').click(function () { _.edit_permalink(); });
		$('#edit-slug-buttons').children('.view').click(function () { document.location.href=canonurl+$('#editable-slug-full').html(); });
		$('#editable-slug').click(function() { $('#edit-slug-buttons').children('.edit-slug').click(); });		
	}

	_.enable();
}