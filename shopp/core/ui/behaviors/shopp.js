/*!
 * shopp.js - Shopp behavioral utility library
 * Copyright © 2008-2010 by Ingenesis Limited
 * Licensed under the GPLv3 {@see license.txt}
 */

/**
 * Provides shorthand for returning a clean jQuery object
 **/
function jqnc () { return jQuery.noConflict(); }

/**
 * Returns a copy/clone of an object
 **/
function copyOf (src) {
	var target = new Object(),v;
	for (v in src) target[v] = src[v];
	return target;
}

/**
 * Provides indexOf method for browsers that
 * that don't implement JavaScript 1.6 (IE for example)
 **/
if (!Array.indexOf) {
	Array.prototype.indexOf = function(obj) {
		for (var i = 0; i < this.length; i++)
			if (this[i] == obj) return i;
		return -1;
	};
}

/**
 * Return a valid currency format
 * (returns a valid provided format, or from Shopp Settings or a baseline default)
 **/
function getCurrencyFormat (f) {
	if (f && f.currency) return f; // valid parameter format
	if (sjss && sjss.d)
		return {	// from base of operations
			"cpos":sjss.cp,
			"currency":sjss.c,
			"precision":parseInt(sjss.p,10),
			"decimals":sjss.d,
			"thousands":sjss.t,
			"grouping":sjss.g
		};
	return {		// Default currency format
		"cpos":true,
		"currency":"$",
		"precision":2,
		"decimals":".",
		"thousands":",",
		"grouping":[3]
	};
}

/**
 * Add notation to an integer to display it as money.
 * @param int n Number to convert
 * @param array f Format settings
 **/
function asMoney (n,f) {
	f = getCurrencyFormat(f);

	n = formatNumber(n,f);
	if (f.cpos) return f.currency+n;
	return n+f.currency;
}

/**
 * Add notation to an integer to display it as a percentage.
 * @param int n Number to convert
 * @param array f Format settings
 **/
function asPercent (n,f,p,pr) {
	f = getCurrencyFormat(f);

	f.precision = p?p:1;
	return formatNumber(n,f,pr)+"%";
}

/**
 * Formats a number to denote thousands with decimal precision.
 * @param int n Number to convert
 * @param array f Format settings
 * @param boolean pr Use precision instead of fixed
 **/
function formatNumber (n,f,pr) {
	f = getCurrencyFormat(f);

	n = asNumber(n);
	var digits,i,
		whole=fraction=0,
		divide = false,
		sequence = '',
		ng = [],
		d = n.toFixed(f.precision).toString().split("."),
		grouping = f.grouping;

	n = "";
	whole = d[0];
	if (d[1]) fraction = d[1];

	if (grouping.indexOf(',') > -1) grouping = grouping.split(',');
	else grouping = [grouping];

	i = 0;
	lg=grouping.length-1;
	while(whole.length > grouping[Math.min(i,lg)]) {
		if (grouping[Math.min(i,lg)] == '') break;
		divide = whole.length - grouping[Math.min(i++,lg)];
		sequence = whole;
		whole = sequence.substr(0,divide);
		ng.unshift(sequence.substr(divide));
	}
	if (whole) ng.unshift(whole);

	n = ng.join(f.thousands);
	if (n == '') n = 0;

	fraction = (pr)?new Number('0.'+fraction).toString().substr(2,f.precision):fraction;
	fraction = (!pr || pr && fraction.length > 0)?f.decimals+fraction:'';

	if (f.precision > 0) n += fraction;

	return n;
}

/**
 * Convert a field with numeric and non-numeric characters
 * to a true integer for calculations.
 * @param int n Number to convert
 * @param array f Format settings
 **/
function asNumber (n,f) {
	if (!n) return 0;
	f = getCurrencyFormat(f);

	if (n instanceof Number) return new Number(n.toFixed(f.precision));

	n = n.toString().replace(f.currency,''); // Remove the currency symbol
	n = n.toString().replace(new RegExp(/(\D\.|[^\d\,\.])/g),''); // Remove non-digits followed by periods and any other non-numeric string data
	n = n.toString().replace(new RegExp('\\'+f.thousands,'g'),''); // Remove thousands

	if (f.precision > 0)
		n = n.toString().replace(new RegExp('\\'+f.decimals,'g'),'.'); // Convert decimal delimter

	if (isNaN(new Number(n)))
		n = n.replace(new RegExp(/\./g),"").replace(new RegExp(/\,/),"\.");

	return new Number(n);
}

/**
 * Utility class to build a list of functions (callbacks)
 * to be executed as needed
 **/
function CallbackRegistry () {
	this.callbacks = new Array();

	this.register = function (name,callback) {
		this.callbacks[name] = callback;
	};

	this.call = function(name,arg1,arg2,arg3) {
		this.callbacks[name](arg1,arg2,arg3);
	};

	this.get = function(name) {
		return this.callbacks[name];
	};
}

/**
 * Rounds Number objects to a specified precision
 **/
if (!Number.prototype.roundFixed) {
	Number.prototype.roundFixed = function(precision) {
		var power = Math.pow(10, precision || 0);
		return String(Math.round(this * power)/power);
	};
}

/**
 * Usability behavior to add automatic select-all to a field
 * when activating the field by mouse click
 **/
function quickSelects (target) {
	jQuery('input.selectall').mouseup(function () { this.select(); });
}

/**
 * Converts HTML-encoded entities
 **/
function htmlentities (string) {
	if (!string) return "";
	string = string.replace(new RegExp(/&#(\d+);/g), function() {
		return String.fromCharCode(RegExp.$1);
	});
	return string;
}

function debuglog (o) {
	if (window.console != undefined) {
		console.log(o);
	}
}

/**
 * Parse JSON data with native browser parsing or
 * as a last resort use evil(), er... eval()
 **/
jQuery.parseJSON = function (data) {
	if (typeof (JSON) !== 'undefined' &&
		typeof (JSON.parse) === 'function') {
			try {
				return JSON.parse(data);
			} catch (e) {
				return false;
			}
	} else return eval('(' + data + ')');
};

/**
 * DOM-ready initialization
 **/
jQuery(document).ready(function() {
	var $=jqnc();

	// Automatically reformat currency and money inputs
	$('input.currency, input.money').change(function () {
		this.value = asMoney(this.value); }).change();

	quickSelects();

});