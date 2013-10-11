/**
 * Prints out the inline javascript needed for managing layouts.
 * This is an extension of what was already started in the
 * options-custom.js file.
 */

jQuery(document).ready(function($) {

	/*-----------------------------------------------------------------------------------*/
	/* Static Methods
	/*-----------------------------------------------------------------------------------*/

	var builder_blvd = {

		// Update Manage Layouts page's table
    	manager : function( table )
    	{
    		if(table)
			{
				// We already have the table, so just throw it in.
				$('#builder_blvd #manage_layouts .ajax-mitt').html(table);
			}
			else
			{
				// We don't have the table yet, so let's grab it.
				$.ajax({
					type: "POST",
					url: ajaxurl,
					data:
					{
						action: 'themeblvd_update_builder_table'
					},
					success: function(response)
					{
						$('#builder_blvd #manage_layouts .ajax-mitt').html(response);
					}
				});
			}
    	},

    	// Delete Layout
    	delete_layout : function( ids, action, location )
    	{
    		var nonce  = $('#manage_builder').find('input[name="_tb_manage_builder_nonce"]').val();
			tbc_confirm( themeblvd.delete_layout, {'confirm':true}, function(r)
			{
		    	if(r)
		        {
		        	$.ajax({
						type: "POST",
						url: ajaxurl,
						data:
						{
							action: 'themeblvd_delete_layout',
							security: nonce,
							data: ids
						},
						success: function(response)
						{

							// Prepare response
							response = response.split('[(=>)]');

							// Insert update message, fade it in, and then remove it
							// after a few seconds.
							$('#builder_blvd #manage_layout').prepend(response[1]);
							$('#builder_blvd #manage_layout .ajax-update').fadeIn(500, function(){
								setTimeout( function(){
									$('#builder_blvd #manage_layout .ajax-update').fadeOut(500, function(){
										$('#builder_blvd #manage_layout .ajax-update').remove();
									});
						      	}, 1500);

							});

							// Change number of layouts
							$('#builder_blvd .displaying-num').text(response[0]);

							// Update table
							if(action == 'submit')
							{
								$('#manage_builder').find('input[name="posts[]"]').each(function(){
									if( $(this).is(':checked') )
									{
										var id = $(this).val();
										if( $('#edit_layout-tab').hasClass(id+'-edit') )
										{
											$('#edit_layout-tab').hide();
										}
										$(this).closest('tr').remove();
									}
								});
							}
							else if(action == 'click')
							{
								var id = ids.replace('posts%5B%5D=', '');
								if( $('#edit_layout-tab').hasClass(id+'-edit') )
								{
									$('#edit_layout-tab').hide();
								}
								$('#row-'+id).remove();
							}

							// Uncheck all checkboxes
							$('#manage_builder option').removeAttr('checked');

							// Forward back to manage layouts page if
							// we're deleting this layout from the Edit
							// Layout page.
							if(location == 'edit_page')
							{
								$('#builder_blvd .group').hide();
								$('#builder_blvd .group:first').fadeIn();
								$('#builder_blvd .nav-tab-wrapper a:first').addClass('nav-tab-active');
							}
						}
					});
		        }
		    });
    	},

		// Manage add new layout form elements
		add_layout : function( object )
    	{
    		var value = object.val(), parent = object.closest('.subgroup');

    		// Always remove the warning.
			$('#section-layout_sidebar .controls .warning').remove();

    		if( value == 'layout' )
    		{
    			parent.find('#section-layout_sample').hide();
	    		parent.find('#section-layout_existing').fadeIn('fast');
	    		$('#section-layout_sidebar .controls').prepend('<p class="warning">'+themeblvd.sidebar_layout_set+'</p>');
	    		$('#layout_sidebar').hide().closest('.tb-fancy-select').hide();
    		}
    		else if( value == 'sample' )
    		{
	    		parent.find('#section-layout_existing').hide();
	    		parent.find('#section-layout_sample').fadeIn('fast');
	    		$('#section-layout_sidebar .controls').prepend('<p class="warning">'+themeblvd.sidebar_layout_set+'</p>');
	    		$('#layout_sidebar').hide().closest('.tb-fancy-select').hide();
    		}
    		else
    		{
	    		parent.find('#section-layout_existing').hide();
	    		parent.find('#section-layout_sample').hide();
	    		$('#layout_sidebar').show().closest('.tb-fancy-select').show();
    		}
    	},

    	// Toggle sample layout previews
    	sample_preview : function( select )
    	{
    		var parent = select.closest('.controls');
    		parent.find('.sample-layouts div').hide();
    		parent.find('#sample-'+select.val()).show();
    	},

    	// Enter into editing a layout via Ajax
    	edit : function ( name, page )
    	{
    		// Get the ID from the beginning
			var page = page.split('[(=>)]');

			// Prepare the edit tab
			$('#builder_blvd .nav-tab-wrapper a.nav-edit-builder').text(themeblvd.edit_layout+': '+name).addClass(page[0]+'-edit');
			$('#builder_blvd #edit_layout .ajax-mitt').html(page[1]);

			// Setup hints
			$('.sortable:not(:has(div))').addClass('empty');
			$('.sortable:has(div)').removeClass('empty');

			// Setup sortables
			$('.sortable').sortable({
				handle: '.widget-name',
				connectWith: '.sortable'
			});

			// Enable WP's post box toggles
			// requires: wp_enqueue_script('postbox');
			postboxes.add_postbox_toggles(pagenow, {
				pbshow: builder_blvd.show_widget,
				pbhide: builder_blvd.hide_widget
			});

			// Sortable binded events
			$('.sortable').bind( 'sortreceive', function(event, ui) {
				$('.sortable:not(:has(div))').addClass('empty');
				$('.sortable:has(div)').removeClass('empty');
			});

			// Setup widgets
			$('#builder_blvd .widget').themeblvd('widgets');

			// Setup options
			$('#builder_blvd').themeblvd('options', 'setup');
			$('#builder_blvd').themeblvd('options', 'media-uploader');

			// Take us to the tab
			$('#builder_blvd .nav-tab-wrapper a').removeClass('nav-tab-active');
			$('#builder_blvd .nav-tab-wrapper a.nav-edit-builder').show().addClass('nav-tab-active');
			$('#builder_blvd .group').hide();
			$('#builder_blvd .group:last').fadeIn();

    	},

    	// These methods are passed into WP's postboxes.add_postbox_toggles
    	// as the pbshow and bphide parameters. They allow the widgets to
    	// be toggled open and close.
    	hide_widget : function( id )
    	{
    		// Don't apply to Publish box
    		if( $('#'+id).hasClass('postbox-publish') )
    			return;

    		$('#'+id+' .tb-widget-content').hide();
    	},
    	show_widget : function( id )
    	{
    		$('#'+id+' .tb-widget-content').show();
    	},

    	// Retrieve interface to edit a layout from
    	// meta box when editing pages.
    	mini_edit : function ( layout_id, nonce, object, new_layout_created )
    	{
			var data = {
				action: 'themeblvd_mini_edit_layout',
				security: nonce,
				data: layout_id
			};
    		$.post(ajaxurl, data, function(response) {

				// Insert response
				object.find('.ajax-mitt').html(response);

				// Wait 1 second before bringing everything back.
				setTimeout(function () {

					builder_blvd.edit_now(object);
					object.find('.meta-box-nav .ajax-overlay').fadeOut('fast');
					object.find('.meta-box-nav .ajax-loading').fadeOut('fast');
					object.find('#edit_layout .ajax-overlay-layout-switch').fadeOut('fast').remove();

					// If a new layout was just created, we need to confirm and get the user there to edit it.
					if( new_layout_created )
						builder_blvd.confirm_new_layout();

				}, 1000);

			});
    	},

    	// Change layouts when in the meta box on Edit Page screen.
    	change_layout : function ( object, new_layout_id, new_layout_created )
    	{

    		// If this is coming from the creation of a new layout,
			// then we should already have the new layout ID, but
			// if not we need to collect it.
			if( ! new_layout_id )
			{
				// User has selected to eidt an existing layout,
				// opposed to creating a new one.
				new_layout_id = object.val();
			}

	    	var parent = object.closest('#builder_blvd'),
				old_layout_id = parent.find('input[name=tb_layout_id]').val(),
				nonce = parent.find('input[name=_tb_save_builder_nonce]').val();

			// Check if the user was previously editing another layout or not.
			if( old_layout_id )
			{

				// User is currently editing another layout and now wants to change.

				// Verify action with user, to save the current layout or not.
				tbc_confirm(themeblvd.save_switch_layout, {'verify':true}, function(r) {

					// Trigger loading indicators
					parent.find('.meta-box-nav .ajax-overlay').css('visibility', 'visible').fadeIn('fast');
					parent.find('.meta-box-nav .ajax-loading').css('visibility', 'visible').fadeIn('fast');
					parent.find('#edit_layout').prepend('<div class="ajax-overlay-layout-switch"></div>');
					parent.find('#edit_layout .ajax-overlay-layout-switch').fadeIn('fast');

					// Take action based on user selection
					if(r)
					{
						// User clicked "Yes"
						// Save old layout
						var data = {
							action: 'themeblvd_save_layout',
							security: nonce,
							data: $('#post').serialize()
						};
			    		$.post(ajaxurl, data, function(response) {
							// And now, fetch interface to edit new layout
							builder_blvd.mini_edit( new_layout_id, nonce, parent, new_layout_created );
						});
					}
					else
					{
						// User clicked "No"
						// Fetch interface to edit new layout
						builder_blvd.mini_edit( new_layout_id, nonce, parent, new_layout_created );
					}

				});
			}
			else
			{
				// User is not currently editing another layout.

				// Trigger loading indicators
				parent.find('.meta-box-nav .ajax-overlay').css('visibility', 'visible').fadeIn('fast');
				parent.find('.meta-box-nav .ajax-loading').css('visibility', 'visible').fadeIn('fast');
				parent.find('#edit_layout').prepend('<div class="ajax-overlay-layout-switch"></div>');
				parent.find('#edit_layout .ajax-overlay-layout-switch').fadeIn('fast');

				// No previous layout, so we don't need to ask the user if they want to save it.
				builder_blvd.mini_edit( new_layout_id, nonce, parent, new_layout_created );
			}
    	},

    	// Update the layout toggle select menu
    	update_layout_toggle : function ( current_layout_id )
    	{
	    	// Prep and exececute first Ajax call.
			var data = {
				action: 'themeblvd_layout_toggle',
				data: current_layout_id
			};
			$.post(ajaxurl, data, function(response) {
				$('#tb-layout-toggle').closest('.tb-fancy-select').replaceWith(response); // Why isn't this replacing the layout toggle select???
			});
    	},

    	// Confirm creation of new layout from Meta Box
    	// builder.
    	confirm_new_layout : function ()
    	{

    		var meta_box = $('#tb_builder');

    		// Hide loader
    		meta_box.find('#add_layout .ajax-loading').hide();

    		// Switch user to editing the new layout
			meta_box.find('.meta-box-nav li a').removeClass('nav-tab-active');
			meta_box.find('.meta-box-nav li:first a').addClass('nav-tab-active');
			meta_box.find('.group').hide();
			meta_box.find('#edit_layout').show();

			// Put user at the start of meta box
			$('html,body').animate({
				scrollTop: $('#tb_builder').offset().top - 30
			}, 'fast');

			// Show success message
			tbc_alert.init(themeblvd.layout_created, 'success', '#tb_builder');

			// Clear name field back on new layout form
			meta_box.find('#layout_name').val('');

    	},

    	// Setup editing a layout when loaded on page
    	// load (i.e. the meta box when editing pages).
    	edit_now : function ( object )
    	{

    		// Setup hints
			object.find('.sortable:not(:has(div))').addClass('empty');
			object.find('.sortable:has(div)').removeClass('empty');

			// Setup sortables
			object.find('.sortable').sortable({
				handle: '.widget-name',
				connectWith: '.sortable'
			});

			// Sortable binded events
			object.find('.sortable').bind( 'sortreceive', function(event, ui) {
				object.find('.sortable:not(:has(div))').addClass('empty');
				object.find('.sortable:has(div)').removeClass('empty');
			});

			// Setup widgets
			object.find('.widget').themeblvd('widgets');

			// Setup options
			object.themeblvd('options', 'setup');
			object.themeblvd('options', 'media-uploader');

    	}
	};

	/*-----------------------------------------------------------------------------------*/
	/* General setup
	/*-----------------------------------------------------------------------------------*/

	// Hide secret tab when page loads
	$('#builder_blvd .nav-tab-wrapper a.nav-edit-builder').hide();

	// If the active tab is on edit layout page, we'll
	// need to override the default functionality of
	// the Options Framework JS, because we don't want
	// to show a blank page.
	if (typeof(localStorage) != 'undefined' )
	{
		if( localStorage.getItem('activetab') == '#edit_layout')
		{
			$('#builder_blvd .group').hide();
			$('#builder_blvd .group:first').fadeIn();
			$('#builder_blvd .nav-tab-wrapper a:first').addClass('nav-tab-active');
		}
	}

	// Screen Options
	$(document).on('change', '#adv-settings input', function(){

		var checkbox = $(this),
			id = checkbox.attr('name'),
			section = 'section-'+id,
			section = section.replace('-hide', ''),
			nonce = $(this).closest('form').find('.security').val();

		if ( checkbox.is(":checked") ) {

			var value = new Array( id, "on" );
			$( '.'+section ).show();

		} else {

			var value = new Array( id, "off" );
			$( '.'+section ).hide();

		}

		var data = {
			action: 'themeblvd_save_screen_settings',
			security: nonce,
			data: value
		};
		$.post(ajaxurl, data, function(response) {
			// do nothing ...
		});

	});

	/*-----------------------------------------------------------------------------------*/
	/* Meta Box (layout builder used when editing pages directly)
	/*-----------------------------------------------------------------------------------*/

	// Setup Tabs for Builder meta box (extends
	// basic process above from Builder section).
	//
	// The reason we're using this revised method
	// for tab switching is to avoid issues with
	// fading tabs in/out in the Edit Page envionment
	// where there are more factors surrounding the
	// height and location of the tabs.
	$('#tb_builder').each(function(){
		var meta_box = $(this);
		meta_box.find('.meta-box-nav li:first a').addClass('nav-tab-active');
		meta_box.find('.group').hide();
		meta_box.find('#edit_layout').show();
		meta_box.find('.meta-box-nav li a').click(function(){
			var anchor = $(this), target = anchor.attr('href');
			meta_box.find('.meta-box-nav li a').removeClass('nav-tab-active');
			anchor.addClass('nav-tab-active');
			meta_box.find('.group').hide();
			meta_box.find(target).show();
			return false;
		});
	});

	// Initiate Builder interface when loaded on page
	// load with the meta box on Edit Page screen.
	$('#tb_builder').each( function(){
		builder_blvd.edit_now( $(this) );
	});

	// Switch layouts
	$(document).on('change', '#tb-layout-toggle', function(){
		builder_blvd.change_layout( $(this) );
	});

	// Add new layout
	$('#tb_builder #add_layout .button-primary').click(function(){

		var el = $(this),
			parent = el.closest('#builder_blvd'),
			meta_box = el.closest('#tb_builder'),
			name = parent.find('#layout_name').val(),
			nonce = parent.find('input[name=_tb_new_builder_nonce]').val(),
			form_data = $('#post').serialize();

		// Tell user they forgot a name
		if(!name)
		{
			tbc_confirm(themeblvd.no_name, {'textOk':'Ok'});
		    return false;
		}

		// Loader
		el.closest('.metabox-holder').find('.ajax-loading').fadeIn('fast');

		// Prep and exececute first Ajax call.
		var data = {
			action: 'themeblvd_add_layout',
			security: nonce,
			data: form_data
		};
		$.post(ajaxurl, data, function(new_layout_id) {
			builder_blvd.update_layout_toggle(new_layout_id);
			builder_blvd.change_layout(el, new_layout_id, true);
		});

		return false;
	});

	/*-----------------------------------------------------------------------------------*/
	/* Manage Layouts Page
	/*-----------------------------------------------------------------------------------*/

	// Edit layout (via Edit Link on manage page)
	$(document).on('click', '#builder_blvd #manage_layouts .edit-tb_layout', function(){
		var name = $(this).closest('tr').find('.post-title .title-link').text(),
			id = $(this).attr('href'),
			id = id.replace('#', '');

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data:
			{
				action: 'themeblvd_edit_layout',
				data: id
			},
			success: function(response)
			{
				builder_blvd.edit( name, response );
			}
		});
		return false;
	});

	// Delete layout (via Delete Link on manage page)
	$(document).on('click', '#builder_blvd .row-actions .trash a', function(){
		var href = $(this).attr('href'), id = href.replace('#', ''), ids = 'posts%5B%5D='+id;
		builder_blvd.delete_layout( ids, 'click' );
		return false;
	});

	// Delete layouts via bulk action
	$(document).on('submit', '#manage_builder', function(){
		var value = $(this).find('select[name="action"]').val(), ids = $(this).serialize();
		if(value == 'trash')
		{
			builder_blvd.delete_layout( ids, 'submit' );
		}
		return false;
	});

	/*-----------------------------------------------------------------------------------*/
	/* Add New Layout Page
	/*-----------------------------------------------------------------------------------*/

	$('#layout_start').each( function(){
		builder_blvd.add_layout( $(this) );
	});

	$('#layout_start').change(function(){
		builder_blvd.add_layout( $(this) );
	});

	$('#layout_sample').each( function(){
		builder_blvd.sample_preview( $(this) );
	});

	$('#layout_sample').change(function(){
		builder_blvd.sample_preview( $(this) );
	});

	// Add new layout
	$('#optionsframework #add_new_builder').submit(function(){
		var el = $(this),
			data = el.serialize(),
			load = el.find('.ajax-loading'),
			name = el.find('input[name="tb_new_layout[layout_name]"]').val(),
			nonce = el.find('input[name="_tb_new_builder_nonce"]').val();

		// Tell user they forgot a name
		if(!name)
		{
			tbc_confirm(themeblvd.no_name, {'textOk':'Ok'});
		    return false;
		}

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data:
			{
				action: 'themeblvd_add_layout',
				security: nonce,
				data: data
			},
			beforeSend: function()
			{
				load.fadeIn('fast');
			},
			success: function(response)
			{
				// Scroll to top of page
				$('body').animate( { scrollTop: 0 }, 100, function(){
					// Everything is good to go. So, forward
					// on to the edit layout page.
					builder_blvd.edit( name, response );
					tbc_alert.init(themeblvd.layout_created, 'success');
					el.find('input[name="options[layout_name]"]').val('');
				});

				// Update builder management table in background
				builder_blvd.manager();

				// Hide loader no matter what.
				load.hide();
			}
		});
		return false;
	});

	/*-----------------------------------------------------------------------------------*/
	/* Edit Layout Page
	/*-----------------------------------------------------------------------------------*/

	// Add new element
	$(document).on('click', '#optionsframework #add_new_element', function(){
		var el = $(this),
			id,
			trim_front,
			trim_back,
			element_id,
			primary_query = false,
			overlay = el.parent().find('.ajax-overlay'),
			load = el.parent().find('.ajax-loading');
			values = el.parent().find('select').val(),
			values = values.split('=>'),
			type = values[0],
			query = values[1];

		// Make sure the user doesn't have more than one "primary"
		// query element. This just means that they can't add
		// two elements that both use WordPress's primary loop.
		// Examples would be anything that's paginated. Most other
		// elements that require posts to be pulled are done with
		// get_posts() in order to have multiple on a single page.
		// This can't be done, really, with anything paginated.
		if(query == 'primary')
		{
			// Run a check for other primary query items.
			$('#builder_blvd #builder .element-query').each(function(){
				if( $(this).val() == 'primary' )
				{
					primary_query = true;
				}
			});

			// Check if primary_query was set to true
			if(primary_query)
			{
				// Say, what? We found a second primary? Halt everything!
				tbc_confirm(themeblvd.primary_query, {'textOk':'Ok'});
				return false;
			}
		}

		// User doesn't have more than one "primary" query item,
		// so let's proceed with the ajax.
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data:
			{
				action: 'themeblvd_add_element',
				data: type
			},
			beforeSend: function()
			{
				overlay.fadeIn('fast');
				load.fadeIn('fast');
			},
			success: function(response)
			{
				trim_front = response.split('<div id="');
				trim_back = trim_front[1].split('" class="widget element-options"');
				element_id = trim_back[0];
				$('#builder_blvd #edit_layout #primary .sortable').append(response);
				$('#builder_blvd #edit_layout #primary .sortable').removeClass('empty');
				$('#'+element_id).themeblvd('widgets');
				$('#'+element_id).themeblvd('options', 'setup');
				$('#'+element_id).themeblvd('options', 'bind');
				$('#'+element_id).themeblvd('options', 'media-uploader');
				$('#'+element_id).fadeIn();
				load.fadeOut('fast');
				overlay.fadeOut('fast');
			}
		});
		return false;
	});

	// Save Layout
	$(document).on('submit', '#optionsframework #edit_builder', function(){
		var el = $(this),
			data = el.serialize(),
			load = el.find('.publishing-action .ajax-loading'),
			nonce = el.find('input[name="_tb_save_builder_nonce"]').val(),
			current_name;

		$.ajax({
			type: "POST",
			url: ajaxurl,
			data:
			{
				action: 'themeblvd_save_layout',
				security: nonce,
				data: data
			},
			beforeSend: function()
			{
				load.fadeIn('fast');
			},
			success: function(response)
			{

				// Prepare response
				response = response.split('[(=>)]');

				// Insert update message, fade it in, and then remove it
				// after a few seconds.
				$('#builder_blvd #edit_layout').prepend(response[1]);

				// Make sure all "Layout Names" match on current edit layout page.
				current_name = $('#builder_blvd #post_title').val();
				$('#builder_blvd #edit_layout-tab').text(themeblvd.edit_layout+': '+current_name);
				$('#builder_blvd .postbox-publish h3').text(themeblvd.publish+' '+current_name);
				$('#builder_blvd .postbox-layout-info #post_name').val(response[0]);

				// Scroll to top of page
				$('body').animate( { scrollTop: 0 }, 50, function(){
					// Fade in the update message
					$('#builder_blvd #edit_layout .ajax-update').fadeIn(500, function(){
						setTimeout( function(){
							$('#builder_blvd #edit_layout .ajax-update').fadeOut(500, function(){
								$('#builder_blvd #edit_layout .ajax-update').remove();
							});
				      	}, 1500);

					});
				});
				load.fadeOut('fast');

				// Update builder management table in background
				builder_blvd.manager();
			}
		});
		return false;
	});

	// Delete layout (via Delete Link on edit layout page)
	$(document).on('click', '#builder_blvd #edit_layout .delete_layout', function(){
		var href = $(this).attr('href'), id = href.replace('#', ''), ids = 'posts%5B%5D='+id;
		builder_blvd.delete_layout( ids, 'click', 'edit_page' );
		return false;
	});

});