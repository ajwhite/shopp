/*!
 * cart.js - Shopp cart behaviors
 * Copyright © 2008-2010 by Ingenesis Limited
 * Licensed under the GPLv3 {@see license.txt}
 */

/**
 * Makes a request to add the selected product/product variation
 * to the shopper's cart
 **/
function addtocart (form) {
	var $ = jqnc(),
		options = $(form).find('select.options'),selections=true;
	if (options) {
		options.each(function (i,menu) {
			if ($(menu).val() == "") return (selections = false);
		});

		if (!selections) {
			if (!$s.opreq) $s.opreq = "You must select the options for this item before you can add it to your shopping cart.";
			alert($s.opreq);
			return false;
		}
	}

	if ($(form).find('input.addtocart').hasClass('ajax-html'))
		ShoppCartAjaxRequest(form.action,$(form).serialize(),'html');
	else if ($(form).find('input.addtocart').hasClass('ajax'))
		ShoppCartAjaxRequest(form.action,$(form).serialize());
	else form.submit();

	return false;
}

/**
 * Overridable wrapper function to call cartajax.
 * Developers can recreate this function in their own
 * custom JS libraries to change the way cartajax is called.
 **/
function ShoppCartAjaxRequest (url,data,response) {
	if (!response) response = "json";
	var $ = jqnc(),
		datatype = ((response == 'json')?'json':'string');
	$.ajax({
		type:"POST",
		url:url,
		data:data+"&response="+response,
		timeout:10000,
		dataType:datatype,
		success:function (cart) {
			ShoppCartAjaxHandler(cart,response);
		},
		error:function () { }
	});
}

/**
 * Overridable wrapper function to handle cartajax responses.
 * Developers can recreate this function in their own
 * custom JS libraries to change the way the cart response
 * is processed and displayed to the shopper.
 **/
function ShoppCartAjaxHandler (cart,response) {
	var $ = jqnc(),label = '',Item=false,Totals=false,
		widget = $('.widget_shoppcartwidget div.widget-all'),
		wrapper = $('#shopp-cart-ajax'),
		ui = widget.length > 0?widget:wrapper,
		actions = ui.find('ul'),
		status = ui.find('p.status'),
		added = ui.find('div.added').empty().hide(), // clear any previous additions
		item = $('<div class="added"></div>');

	if (response == "html") return ui.html(cart);

	if (cart.Item) Item = cart.Item;
	if (cart.Totals) Totals = cart.Totals;

	if (added.length == 1) item = added;
	else item.prependTo(ui).hide();

	if (Item.option && Item.option.label && Item.option.label != '')
		label = ' ('+Item.option.label+')';

	if (Item.image)
		$('<p><img src="'+cart.imguri+cart.Item.image.id+'" alt="" width="96"  height="96" /></p>').appendTo(item);
	$('<p />').html('<strong>'+Item.name+'</strong>'+label).appendTo(item);

	$('<p />').html(asMoney(new Number(Item.unitprice))).appendTo(item);

	status.html('<a href="'+cart.url+'"><span id="shopp-sidecart-items">'+Totals.quantity+'</span> '+
				'<strong>Items</strong> &mdash; <strong>Total</strong> '+
				'<span id="shopp-sidecart-total">'+asMoney(new Number(Totals.total))+'</span></a>');

	if (actions.size() != 1) actions = $('<ul />').appendTo(ui);
	actions.html('<li><a href="'+cart.url+'">'+cart.label+'</a></li><li><a href="'+cart.checkouturl+'">'+cart.checkoutLabel+'</a></li>');
	item.slideDown();
}

/**
 * DOM-ready initialization
 **/
jQuery(document).ready(function($) {
	// Adds behaviors to shopping cart controls
	$('#cart #shipping-country').change(function () {
		this.form.submit();
	});

	$('input[type=image]').click(function () { $(this.form).submit(); });

	// "Add to cart" button behaviors
	$('input.addtocart').each(function() {
		var button = $(this),
			form = button.parents('form.product');
		if (!form) return false;
		form.bind('submit.addtocart',function (e) {
			e.preventDefault();
			if (form.hasClass('validate') && !validate(this)) return false;
			addtocart(this);
		});
		if (button.attr('type') == "button")
			button.click(function() { form.submit(); });

	});

});