/**
 * Ajax Popup Handler Module
 */

define([	"order!libs/jQuery/jquery-1.7.1.min",
        	"commsy/commsy_functions_8_0_0"], function() {
	return {
		cid: null,
		mod: null,
		
		init: function(commsy_functions, parameters) {
			this.cid = commsy_functions.getURLParam('cid');
			this.mod = commsy_functions.getURLParam('mod');
			
			// set preconditions
			this.setPreconditions(commsy_functions, this.loadPopup, {handle: this, commsy_functions: commsy_functions, handling: parameters});
		},

		setPreconditions: function(commsy_functions, callback, parameters) {
			var preconditions = {
			};

			// register preconditions
			commsy_functions.registerPreconditions(preconditions, callback, parameters);
		},

		loadPopup: function(preconditions, parameters) {
			var commsy_functions = parameters.commsy_functions;
			var module = parameters.handling.module;
			var actors = parameters.handling.objects;
			var handle = parameters.handle;
			
			jQuery.each(actors, function() {
				jQuery(this).bind('click', {commsy_functions: commsy_functions, module: module, handle: handle}, handle.onClick);
			});
		},
		
		onClick: function(event) {
			var commsy_functions = event.data.commsy_functions;
			var module = {module: event.data.module};
			var handle = event.data.handle;
			
			var cid = commsy_functions.getURLParam('cid');
			
			jQuery.ajax({
				type: 'POST',
				url: 'commsy.php?cid=' + cid + '&mod=ajax&fct=popup&action=getHTML',
				data: JSON.stringify(module),
				contentType: 'application/json; charset=utf-8',
				dataType: 'json',
				error: function() {
					console.log("error while getting popup");
				},
				success: function(data, status) {
					if(status === 'success') {
						// we recieved html - append it
						jQuery('body').prepend(data);
						
						// reinvoke CKEditor
						require(['commsy/ck_editor'], function($) {
							// call init
							$.init(commsy_functions, {register_on: jQuery('div[id="ckeditor"]'), input_object: jQuery('input[id="ckeditor_content"]')});
						});
						
						// reinvoke Uploadify
						require(['commsy/uploadify'], function($) {
							// call init
							$.init(commsy_functions, {register_on: jQuery('input[id="uploadify"]'), upload_object: jQuery('a[id="uploadify_doUpload"]'), clear_object: jQuery('a[id="uploadify_clearQuery"]')});
						});
						
						// setup popup
						handle.setupPopup();
					}
				}
			});
			
			// stop processing
			return false;
		},
		
		close: function(event) {
			// remove popup html from dom
			jQuery('div[id="popup_wrapper"]').remove();
			
			return false;
		},
		
		create: function(event) {
			var handle = event.data.handle;
			
			// collect form data
			var form_objects = jQuery('div[id="popup_wrapper"] input[name^="form_data"]');
			
			// build object
			var data = {
				form_data: [],
				module: handle.mod
			};
			jQuery.each(form_objects, function() {
				data.form_data.push({
					name:	jQuery(this).attr('name'),
					value:	jQuery(this).attr('value')
				});
			});
			
			// ajax request
			jQuery.ajax({
				type: 'POST',
				url: 'commsy.php?cid=' + handle.cid + '&mod=ajax&fct=popup&action=create',
				data: JSON.stringify(data),
				contentType: 'application/json; charset=utf-8',
				dataType: 'json',
				error: function() {
					console.log("error while processing popup action");
				},
				success: function(data, status) {
					console.log(data);
					//handle.preconditionsSuccess(data);
				}
			});
			
			return false;
		},
		
		setupTabs: function() {
			var handle = this;
			
			// register click for tabs
			jQuery('div[class="tab_navigation"] a').each(function(index) {
				jQuery(this).bind('click', {index: index}, handle.onClickTab);
			});
		},
		
		onClickTab: function(event) {
			var target = jQuery(event.currentTarget);
			var index = event.data.index;
			
			// set all tabs inactive
			jQuery('div[class="tab_navigation"] a').each(function() {
				jQuery(this).attr('class', 'pop_tab');
			})
			
			// set target active
			target.attr('class', 'pop_tab_active');
			
			// switch display
			// get divs
			var content_divs = jQuery('div[id="popup_tabcontent"] div[class^="settings_area"]');
			
			// set class for divs
			content_divs.each(function(i) {
				if(index === i) {
					jQuery(this).attr('class', 'settings_area');
				} else {
					jQuery(this).attr('class', 'settings_area hidden');
				}
			});
			
			return false;
		},
		
		setupPopup: function() {
			// register click for close button
			jQuery('a[id="popup_close"]').click(this.close);
			
			// register click for abort button
			jQuery('input[id="popup_button_abort"]').click(this.close);
			
			// register click for create button
			jQuery('input[id="popup_button_create"]').bind('click', {handle: this}, this.create);
			
			// setup tabs
			this.setupTabs();
		}
	};
});