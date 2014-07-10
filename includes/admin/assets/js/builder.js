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
							$('#builder_blvd #manage_layouts').prepend(response[1]);
							$('#builder_blvd #manage_layouts .ajax-update').fadeIn(500, function(){
								setTimeout( function(){
									$('#builder_blvd #manage_layouts .ajax-update').fadeOut(500, function(){
										$('#builder_blvd #manage_layouts .ajax-update').remove();
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
			$('.themeblvd-builder-legacy-1 #section-layout_sidebar .controls .warning').remove();

    		if( value == 'layout' ) {
    			parent.find('#section-layout_sample').hide();
	    		parent.find('#section-layout_existing').fadeIn('fast');
	    		$('.themeblvd-builder-legacy-1 #section-layout_sidebar .controls').prepend('<p class="warning">'+themeblvd.sidebar_layout_set+'</p>');
	    		$('.themeblvd-builder-legacy-1 #layout_sidebar').hide().closest('.tb-fancy-select').hide();
    		} else if( value == 'sample' ) {
	    		parent.find('#section-layout_existing').hide();
	    		parent.find('#section-layout_sample').fadeIn('fast');
    		} else {
	    		parent.find('#section-layout_existing').hide();
	    		parent.find('#section-layout_sample').hide();
	    		$('.themeblvd-builder-legacy-1 #layout_sidebar').show().closest('.tb-fancy-select').show();
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
				handle: '.top-widget-name',
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
			$('#builder_blvd').themeblvd('options', 'editor');
			$('#builder_blvd').themeblvd('options', 'code-editor');
			$('#builder_blvd').themeblvd('options', 'column-widths');
			$('#builder_blvd').themeblvd('options', 'sortable');

			// Setup each "Columns" and "Content" element
			$('#builder_blvd .element-columns, #builder_blvd .element-content').each(function(){
				builder_blvd.columns( $(this).closest('.widget') );
			});

			// Setup content block options, which open in a modal
			if ( $.isFunction( $.fn.ThemeBlvdModal ) ) {
				$('#builder_blvd .tb-content-block-options-link').ThemeBlvdModal({
			        build: true,
			        form: true,
			        padding: false,
			        size: 'medium',
			        on_load: builder_blvd.content_block_options_load
			    });
			}

			// Setup element background options, which open in a modal
			if ( $.isFunction( $.fn.ThemeBlvdModal ) ) {
				$('#builder_blvd .tb-element-background-options').ThemeBlvdModal({
			        build: true,
			        form: true,
			        padding: false,
			        size: 'medium',
			        on_load: builder_blvd.content_block_options_load // We're going to piggy back this
			    });
			}

			// Element Labels
			$('#builder_blvd').on('click', '.element-label .label-text', function(){

				var $el = $(this),
					$input = $el.closest('.element-label').find('.label-input');

				$el.css('opacity', '0');
				$input.show().focus();

				$input.on('keydown.edit-label, focusout.edit-label', function(event){

					if ( ( event.type == 'keydown' && event.keyCode == 13 ) || event.type == 'focusout' ) {

						if ( ! $input.val() ) {
							$input.val('...');
						}

						$el.css('opacity', '1').text($input.val());
						$input.hide();

						$input.off('keydown.edit-label, focusout.edit-label');
						event.preventDefault();
						return false;
					}

				});

			});

			// Take us to the tab
			$('#builder_blvd .nav-tab-wrapper a').removeClass('nav-tab-active');
			$('#builder_blvd .nav-tab-wrapper a.nav-edit-builder').css('display', 'inline-block').addClass('nav-tab-active');
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
    	mini_edit : function ( layout_id, nonce, $builder, new_layout_created )
    	{
			var data = {
				action: 'themeblvd_mini_edit_layout',
				security: nonce,
				data: layout_id
			};
    		$.post(ajaxurl, data, function(response) {

				// Insert response
				$builder.find('.ajax-mitt').html(response);

				// Wait 1 second before bringing everything back.
				setTimeout(function () {

					builder_blvd.edit_now($builder);
					$builder.find('.meta-box-nav .ajax-overlay').fadeOut('fast');
					$builder.find('.meta-box-nav .ajax-loading').fadeOut('fast');
					$builder.find('.ajax-mitt').css({
						'height': 'auto',
						'overflow': 'visible'
					});
					$builder.find('#edit_layout .ajax-overlay-layout-switch').fadeOut('fast').remove();

					// If a new layout was just created, we need to confirm and get the user there to edit it.
					if ( new_layout_created ) {
						builder_blvd.confirm_new_layout();
					}

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
				// User has selected to edit an existing layout,
				// opposed to creating a new one.
				new_layout_id = object.val();
			}

	    	var $builder = object.closest('#builder_blvd'),
				old_layout_id = $builder.find('input[name=tb_layout_id]').val(),
				nonce = $builder.find('input[name=_tb_save_builder_nonce]').val();

			// Check if the user was previously editing another layout or not.
			if( old_layout_id )
			{

				// User is currently editing another layout and now wants to change.

				// Verify action with user, to save the current layout or not.
				tbc_confirm(themeblvd.save_switch_layout, {'verify':true}, function(r) {

					// Trigger loading indicators
					$builder.find('.meta-box-nav .ajax-overlay').css('visibility', 'visible').fadeIn('fast');
					$builder.find('.meta-box-nav .ajax-loading').css('visibility', 'visible').fadeIn('fast');
					$builder.find('#edit_layout').prepend('<div class="ajax-overlay-layout-switch"></div>');
					$builder.find('#edit_layout .ajax-overlay-layout-switch').fadeIn('fast');
					$builder.find('.ajax-mitt').css({
						'height': $builder.find('.ajax-mitt').outerHeight()+'px',
						'overflow': 'hidden'
					});

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
							builder_blvd.mini_edit( new_layout_id, nonce, $builder, new_layout_created );
						});
					}
					else
					{
						// User clicked "No"
						// Fetch interface to edit new layout
						builder_blvd.mini_edit( new_layout_id, nonce, $builder, new_layout_created );
					}

				});
			}
			else
			{
				// User is not currently editing another layout.

				// Trigger loading indicators
				$builder.find('#add_layout').fadeOut('fast');
				$builder.find('.meta-box-nav .ajax-overlay').css('visibility', 'visible').fadeIn('fast');
				$builder.find('.meta-box-nav .ajax-loading').css('visibility', 'visible').fadeIn('fast');
				$builder.find('#edit_layout').prepend('<div class="ajax-overlay-layout-switch"></div>');
				$builder.find('#edit_layout .ajax-overlay-layout-switch').fadeIn('fast');
				$builder.find('.ajax-mitt').css({
					'height': $builder.find('.ajax-mitt').outerHeight()+'px',
					'overflow': 'hidden'
				});

				// No previous layout, so we don't need to ask the user if they want to save it.
				builder_blvd.mini_edit( new_layout_id, nonce, $builder, new_layout_created );
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

    		var $wrap = $('#tb-editor-builder');

    		// Hide loader
    		$wrap.find('#add_layout .ajax-loading').hide();

    		// Switch user to editing the new layout
			tbc_alert.init(themeblvd.layout_created, 'success', '#builder_blvd');
			$wrap.find('.meta-box-nav li a').removeClass('nav-tab-active');
			$wrap.find('.meta-box-nav li:first a').addClass('nav-tab-active');
			$wrap.find('#add_layout').hide();
			$wrap.find('#edit_layout').show();

			// Put user at the start of meta box
			/*
			$('html,body').animate({
				scrollTop: $('#tb-editor-builder').offset().top - 60
			}, 'fast');
			*/

			// Clear name field back on new layout form
			$wrap.find('#layout_name').val('');

    	},

    	// Setup editing a layout when loaded on page
    	// load (i.e. the meta box when editing pages).
    	edit_now : function ( $builder )
    	{

    		// Setup hints
			$builder.find('.sortable:not(:has(div))').addClass('empty');
			$builder.find('.sortable:has(div)').removeClass('empty');

			// Setup sortables
			$builder.find('.sortable').sortable({
				handle: '.top-widget-name',
				connectWith: '.sortable'
			});

			// Sortable binded events
			$builder.find('.sortable').bind( 'sortreceive', function(event, ui) {
				$builder.find('.sortable:not(:has(div))').addClass('empty');
				$builder.find('.sortable:has(div)').removeClass('empty');
			});

			// Setup widgets
			$builder.find('.widget').themeblvd('widgets');

			// Setup options
			$builder.themeblvd('options', 'setup');
			$builder.themeblvd('options', 'media-uploader');
			$builder.themeblvd('options', 'editor');
			$builder.themeblvd('options', 'code-editor');
			$builder.themeblvd('options', 'column-widths');
			$builder.themeblvd('options', 'sortable');

			// Setup each "Columns" element
			$('#builder_blvd .element-columns, #builder_blvd .element-content').each(function(){
				builder_blvd.columns( $(this).closest('.widget') );
			});

			// Setup content block options, which open in a modal
			if ( $.isFunction( $.fn.ThemeBlvdModal ) ) {
				$('#builder_blvd .tb-content-block-options-link').ThemeBlvdModal({
			        build: true,
			        form: true,
			        padding: false,
			        size: 'medium',
			        on_load: builder_blvd.content_block_options_load
			    });
			}

			// Setup element background options, which open in a modal
			if ( $.isFunction( $.fn.ThemeBlvdModal ) ) {
				$('#builder_blvd .tb-element-background-options').ThemeBlvdModal({
			        build: true,
			        form: true,
			        padding: false,
			        size: 'medium',
			        on_load: builder_blvd.content_block_options_load // We're going to piggy back this
			    });
			}

			// Element Labels
			$builder.on('click', '.element-label .label-text', function(){

				var $el = $(this),
					$input = $el.closest('.element-label').find('.label-input');

				$el.css('opacity', '0');
				$input.show().focus();

				$input.on('keydown.edit-label, focusout.edit-label', function(event){

					if ( ( event.type == 'keydown' && event.keyCode == 13 ) || event.type == 'focusout' ) {

						if ( ! $input.val() ) {
							$input.val('...');
						}

						$el.css('opacity', '1').text($input.val());
						$input.hide();

						$input.off('keydown.edit-label, focusout.edit-label');
						event.preventDefault();
						return false;
					}

				});

			});

    	},

    	// Setup each columns element
    	columns : function ( $element )
    	{

    		// Sortable content blocks
			var prev_col_num, new_col_num, content;

			$element.find('.column-blocks').sortable({
				handle: '.content-block-handle h3',
				connectWith: '#'+$element.closest('.widget').attr('id')+' .column-blocks',
				remove: function(event, ui) {

					// Add "mini-empty" class to column,
					// if empty
					content = $(this).html();
					if ( ! content.trim().length ) {
						$(this).addClass('mini-empty');
					}

					// Set column number that block
					// is being removed from
					prev_col_num = $(this).closest('.column').find('.col-num').val();

				},
				receive: function(event, ui) {

					var el = $(this), data_field_name;

					// If the current, receiving column
					// was empty, now it's not.
					el.removeClass('mini-empty');

					// Update the name field for any options
					// in this block.
					new_col_num = el.closest('.column').find('.col-num').val();

					ui.item.find('input, textarea, select, option').each(function(){

						var field = $(this),
							name = field.attr('name');

						if ( name ) {
							field.attr('name', name.replace('col_'+prev_col_num, 'col_'+new_col_num) );
						}
					});

					// Update data-field-name
					data_field_name = ui.item.data('field-name');
					data_field_name = data_field_name.replace('col_'+prev_col_num, 'col_'+new_col_num);
					ui.item.data('field-name', data_field_name);

					// Setup editor links
					ui.item.themeblvd('options', 'editor');

				}
			});

			// Check if sortable columns are empty to start
			$element.find('.column-blocks').each(function(){
				var content = $(this).html();
				if ( ! content.trim().length ) {
					$(this).addClass('mini-empty');
				}
			});

    		// Button to add new content blocks
    		$element.find('.columns-config .add-block').each(function(){
    			$(this).click(function(){

    				var column = $(this).closest('.column'),
    					element_id = column.closest('.widget.element-options').attr('id'),
						col_num = column.find('.col-num').val(),
						primary_query = false,
						values = column.find('.block-type').val(),
						values = values.split('=>'),
						type = values[0],
						query = values[1],
						$block = '';

					// Make sure the user doesn't have more than one "primary"
					// query element. This just means that they can't add
					// two elements that both use WordPress's primary loop.
					// Examples would be anything that's paginated. Most other
					// elements that require posts to be pulled are done with
					// get_posts() in order to have multiple on a single page.
					// This can't be done, really, with anything paginated.
					if ( query == 'primary' ) {

						// Run a check for other primary query items.
						$('#builder_blvd #builder .element-query').each(function(){
							if( $(this).val() == 'primary' ) {
								primary_query = true;
							}
						});

						// Check if primary_query was set to true
						if ( primary_query ) {
							// Say, what? We found a second primary? Halt everything!
							tbc_confirm(themeblvd.primary_query, {'textOk':'Ok'});
							return false;
						}
					}

    				var data = {
						action: 'themeblvd_add_block',
						data: element_id+'[(=>)]'+type+'[(=>)]'+col_num
					};
		    		$.post(ajaxurl, data, function(response) {

		    			// Split response
						response = response.split('[(=>)]');

						// Insert new content block
						column.find('.column-blocks').append(response[1]).removeClass('mini-empty');

						// Locate the content block just added
						$block = column.find('#'+response[0]);

						// For those furious clickers, amek sure no "add" classes
						// got left behind from previously added elements.
						$('#builder_blvd .add').removeClass('add');

						// Give it a temporary green glow to show it's just been added.
						$block.addClass('add');
						window.setTimeout(function(){
							$block.removeClass('add');
						}, 500);

						// Setup non-binded options
						$block.themeblvd('options', 'setup');
						$block.themeblvd('options', 'media-uploader');
						$block.themeblvd('options', 'editor');
						$block.themeblvd('options', 'code-editor');
						$block.themeblvd('options', 'column-widths');
						$block.themeblvd('options', 'sortable');

						// Setup content block options, which open in a modal
						if ( $.isFunction( $.fn.ThemeBlvdModal ) ) {
							$block.find('.tb-content-block-options-link').ThemeBlvdModal({
						        build: true,
						        form: true,
						        padding: false,
						        size: 'medium',
						        on_load: builder_blvd.content_block_options_load
						    });
						}

					});

    				return false;
    			});
    		});

			// Adjust how many columns displayed, based on selection
			$element.find('.select-col-num').on('change', function(){
				$element.find('.columns-config').removeClass('columns-1 columns-2 columns-3 columns-4 columns-5').addClass('columns-'+$(this).val());
			});

    	},

    	// Used for the on_load() callback when
    	// linking to options in modal
    	content_block_options_load : function( modal ) {

    		var self = this;

    		// General framework options setup
    		self.$modal_window.themeblvd('options', 'bind');
    		self.$modal_window.themeblvd('options', 'setup');
			self.$modal_window.themeblvd('options', 'media-uploader');
			self.$modal_window.themeblvd('options', 'editor');
			self.$modal_window.themeblvd('options', 'code-editor');
			self.$modal_window.themeblvd('options', 'column-widths');
			self.$modal_window.themeblvd('options', 'sortable');

			// Custom actions for "Display" options of elements
			var $display = self.$modal_window.find('.element-background-options'),
				bg = $display.find('#section-bg_type select').val(),
				padding = $display.find('#section-apply_padding input').is(':checked');

			if ( bg == 'none' && ! padding  ) {
				$display.find('#section-suck_up, #section-suck_down').show();
			} else {
				$display.find('#section-suck_up, #section-suck_down').hide();
			}

			$display.find('#section-bg_type select').on('change', function(){
				if ( $(this).val() == 'none' ) {
					$display.find('#section-suck_up, #section-suck_down').show();
				} else {
					$display.find('#section-suck_up, #section-suck_down').hide();
				}
			});

			$display.find('#section-apply_padding input').on('change', function(){
				if ( $(this).is(':checked') ) {
					$display.find('#section-suck_up, #section-suck_down').hide();
				} else {
					$display.find('#section-suck_up, #section-suck_down').show();
				}
			});
    	}
	};

	/*-----------------------------------------------------------------------------------*/
	/* General setup
	/*-----------------------------------------------------------------------------------*/

	// Hide secret tab when page loads
	$('#builder_blvd.primary .nav-tab-wrapper a.nav-edit-builder').hide();

	// If the active tab is on edit layout page, we'll
	// need to override the default functionality of
	// the Options Framework JS, because we don't want
	// to show a blank page.
	if (typeof(localStorage) != 'undefined' ) {
		if( localStorage.getItem('activetab') == '#edit_layout') {
			$('#builder_blvd.primary .group').hide();
			$('#builder_blvd.primary .group:first').fadeIn();
			$('#builder_blvd.primary .nav-tab-wrapper a:first').addClass('nav-tab-active');
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
	/* Editor Builder -- Used on the Edit Page screen
	/*-----------------------------------------------------------------------------------*/

	var $wrap = $('#tb-editor-builder'),
		$builder = $wrap.find('#builder_blvd');


	// If they have the "Custom Layout" page template
	// saved, show the Builder to start.
	if ( $('#page_template').val() == 'template_builder.php' ) {

		var $button = $wrap.find('#content-layout'),
			$container = $button.closest('.wp-editor-wrap');;

		$wrap.addClass('builder-active');
		$button.addClass('tb-active');

		if ( $container.hasClass('html-active') ) {
			$container.removeClass('html-active');
			$container.addClass('tb-html-active');
		} else if ( $container.hasClass('tmce-active') ) {
			$container.removeClass('tmce-active');
			$container.addClass('tb-tmce-active');
		}

		// Make media buttons inactive
		$('#wp-content-media-buttons').addClass('tb-inactive').prepend('<div class="tb-overlay"></div>').find('.tb-overlay').animate({opacity: .8}, 200);

		// Hide current WP editor
		$('#wp-content-editor-container, #post-status-info').hide();

		// Show builder
		$wrap.find('#builder_blvd').show();

	}

	// Setup nav for showing builder
	$wrap.find('#content-layout').on('click', function(){

		var $button = $(this),
			$container = $button.closest('.wp-editor-wrap');

		if ( $button.hasClass('tb-active') ) {
			return false;
		}

		$wrap.addClass('builder-active');
		$button.addClass('tb-active');

		if ( $container.hasClass('html-active') ) {
			$container.removeClass('html-active');
			$container.addClass('tb-html-active');
		} else if ( $container.hasClass('tmce-active') ) {
			$container.removeClass('tmce-active');
			$container.addClass('tb-tmce-active');
		}

		// Make media buttons inactive
		$('#wp-content-media-buttons').addClass('tb-inactive').prepend('<div class="tb-overlay"></div>').find('.tb-overlay').animate({opacity: .8}, 200);

		// Hide current WP editor
		$('#wp-content-editor-container, #post-status-info').hide();

		// Show builder
		$wrap.find('#builder_blvd').show();

		return false;
	});

	// If they click the "Text" or "Visual" tab and it's
	// already active, put the wrap class back.
	$wrap.find('#content-html, #content-tmce').on('click', function(){

		var $button = $(this),
			$container = $(this).closest('.wp-editor-wrap');

		if ( $container.hasClass('tb-html-active') || $container.hasClass('tb-tmce-active') ) {
			$wrap.removeClass('builder-active');
			$container.find('.tb-switch-editor').removeClass('tb-active');
			$('#wp-content-media-buttons').removeClass('tb-inactive').find('.tb-overlay').remove();
			$('#wp-content-editor-container, #post-status-info').show();
			$button.closest('#tb-editor-builder').find('#builder_blvd').hide();
		}

		if ( $button.attr('id') == 'content-html' && $container.hasClass('tb-html-active') ) {
			$container.removeClass('tb-html-active');
			$container.addClass('html-active');
		}

		if ( $button.attr('id') == 'content-tmce' && $container.hasClass('tb-tmce-active') ) {
			$container.removeClass('tb-tmce-active');
			$container.addClass('tmce-active');
		}

		return false;
	});

	$builder.find('#edit_layout').show();

	$builder.find('.add-new-layout').on('click', function(){
		var $button = $(this);
		$wrap.addClass('new-layout-active');
		$button.hide().closest('.meta-box-nav').find('.cancel-new-layout, .save-new-layout').show();
		$builder.find('#edit_layout').hide();
		$builder.find('#add_layout').show();
		return false;
	});

	$builder.find('.cancel-new-layout').on('click', function(){
		var $button = $(this);
		$wrap.removeClass('new-layout-active');
		$button.hide();
		$button.closest('.meta-box-nav').find('.save-new-layout').hide();
		$button.closest('.meta-box-nav').find('.add-new-layout').show();
		$builder.find('#add_layout').hide();
		$builder.find('#edit_layout').show();
		return false;
	});

	// Initiate Builder interface when loaded on page
	// load with the meta box on Edit Page screen.
	builder_blvd.edit_now( $builder );

	// Switch layouts
	$builder.on('change', '#tb-layout-toggle', function(){
		builder_blvd.change_layout( $(this) );
	});

	// Add new layout
	$builder.find('.save-new-layout').on('click', function(){

		var $button = $(this),
			$parent = $button.closest('#builder_blvd'),
			name = $parent.find('#layout_name').val(),
			nonce = $parent.find('input[name=_tb_new_builder_nonce]').val(),
			form_data = $('#post').serialize();

		// Tell user they forgot a name
		if( ! name ) {
			tbc_confirm(themeblvd.no_name, {'textOk':'Ok'});
		    return false;
		}

		// Put buttons back
		$button.hide();
		$button.closest('.meta-box-nav').find('.cancel-new-layout').hide();
		$button.closest('.meta-box-nav').find('.add-new-layout').show();

		// Loader
		$button.closest('.metabox-holder').find('.ajax-loading').fadeIn('fast');

		// Prep and exececute first Ajax call.
		var data = {
			action: 'themeblvd_add_layout',
			security: nonce,
			data: form_data
		};
		$.post(ajaxurl, data, function(new_layout_id) {
			builder_blvd.update_layout_toggle(new_layout_id);
			builder_blvd.change_layout($button, new_layout_id, true);
			$wrap.removeClass('new-layout-active');
		});

		return false;
	});

	/*-----------------------------------------------------------------------------------*/
	/* Manage Layouts Page
	/*-----------------------------------------------------------------------------------*/

	var $manage = $('#manage_layouts');

	// Edit layout (via Edit Link on manage page)
	$manage.on('click', '.edit-tb_layout', function(){
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
	$manage.on('click', '.row-actions .trash a', function(){
		var href = $(this).attr('href'),
			id = href.replace('#', ''),
			ids = 'posts%5B%5D='+id;
		builder_blvd.delete_layout( ids, 'click' );
		return false;
	});

	// Delete layouts via bulk action
	$manage.on('submit', '#manage_builder', function(){
		var value = $(this).find('select[name="action"]').val(),
			ids = $(this).serialize();
		if( value == 'trash' ) {
			builder_blvd.delete_layout( ids, 'submit' );
		}
		return false;
	});

	/*-----------------------------------------------------------------------------------*/
	/* Add New Layout Page
	/*-----------------------------------------------------------------------------------*/

	$add_layout = $('#add_layout');

	$add_layout.find('#layout_start').each( function(){
		builder_blvd.add_layout( $(this) );
	});

	$add_layout.find('#layout_start').change(function(){
		builder_blvd.add_layout( $(this) );
	});

	$add_layout.find('#layout_sample').each( function(){
		builder_blvd.sample_preview( $(this) );
	});

	$add_layout.find('#layout_sample').change(function(){
		builder_blvd.sample_preview( $(this) );
	});

	// Add new layout
	$add_layout.find('#add_new_builder').submit(function(){
		var el = $(this),
			data = el.serialize(),
			load = el.find('.ajax-loading'),
			name = el.find('input[name="tb_new_layout[layout_name]"]').val(),
			nonce = el.find('input[name="_tb_new_builder_nonce"]').val();

		// Tell user they forgot a name
		if( ! name ) {
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

	var $builder = $('#builder_blvd');

	// Add new element
	$builder.on('click', '#add_new_element', function(){

		var $button = $(this),
			id,
			trim_front,
			trim_back,
			element_id,
			primary_query = false,
			overlay = $button.parent().find('.ajax-overlay'),
			load = $button.parent().find('.ajax-loading');
			values = $button.parent().find('select').val(),
			values = values.split('=>'),
			type = values[0],
			query = values[1],
			$element = '';

		// Make sure the user doesn't have more than one "primary"
		// query element. This just means that they can't add
		// two elements that both use WordPress's primary loop.
		// Examples would be anything that's paginated. Most other
		// elements that require posts to be pulled are done with
		// get_posts() in order to have multiple on a single page.
		// This can't be done, really, with anything paginated.
		if ( query == 'primary' ) {

			// Run a check for other primary query items.
			$('#builder_blvd #builder .element-query').each(function(){
				if( $(this).val() == 'primary' ) {
					primary_query = true;
				}
			});

			// Check if primary_query was set to true
			if ( primary_query ) {
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
				overlay.show();
				load.fadeIn('fast');
			},
			success: function(response)
			{
				trim_front = response.split('<div id="');
				trim_back = trim_front[1].split('" class="widget element-options"');
				element_id = trim_back[0];

				$('#builder_blvd #edit_layout .primary.sortable').append(response);
				$('#builder_blvd #edit_layout .primary.sortable').removeClass('empty');

				$element = $('#'+element_id);

				// For those furious clickers, amek sure no "add" classes
				// got left behind from previously added elements.
				$('#builder_blvd .add').removeClass('add');

				$element.addClass('add');
				window.setTimeout(function(){
					$element.removeClass('add');
				}, 500);

				$element.themeblvd('widgets');
				$element.themeblvd('options', 'setup');
				$element.themeblvd('options', 'bind');
				$element.themeblvd('options', 'media-uploader');
				$element.themeblvd('options', 'editor');
				$element.themeblvd('options', 'code-editor');
				$element.themeblvd('options', 'column-widths');
				$element.themeblvd('options', 'sortable');

				if ( $element.find('.widget-content').hasClass('element-columns') ) {
					builder_blvd.columns( $element );
				}

				if ( $element.find('.widget-content').hasClass('element-content') ) {
					builder_blvd.columns( $element );
				}

				$element.fadeIn();
				load.fadeOut('fast');
				overlay.fadeOut('fast');

				// Setup element background options, which open in a modal
				if ( $.isFunction( $.fn.ThemeBlvdModal ) ) {
					$element.find('.tb-element-background-options').ThemeBlvdModal({
				        build: true,
				        form: true,
				        padding: false,
				        size: 'medium',
				        on_load: builder_blvd.content_block_options_load // We're going to piggy back this
				    });
				}
			}
		});
		return false;
	});

	// Duplicate element
	$builder.on('click', '.duplicate-element', function(){

		var $link = $(this),
			$element = $link.closest('.widget'),
	        nonce = $builder.find('input[name="_tb_save_builder_nonce"]').val(),
	        data = $element.find('input, select, textarea').serialize(),
	        $new_element;

	    $.ajax({
            type: "POST",
            url: ajaxurl,
            data:
            {
                action: 'themeblvd_dup_element',
                security: nonce,
                data: data
            },
            success: function(response) {

                // [0] => Element ID
                // [1] => HTML of new content block
                response = response.split('[(=>)]');

                // Add HTML for new element directly after element
                // that was copied.
                $element.after( response[1] );

                // Cache the new HTML element we just appended
                $new_element = $('#'+response[0]);

                // Temporarily add green border/shadow to the newly added element.
                $new_element.addClass('add');
                window.setTimeout(function(){
                    $new_element.removeClass('add');
                }, 500);

                // Setup Theme Blvd namespace options
                $new_element.themeblvd('widgets');
				$new_element.themeblvd('options', 'setup');
				$new_element.themeblvd('options', 'bind');
				$new_element.themeblvd('options', 'media-uploader');
				$new_element.themeblvd('options', 'editor');
				$new_element.themeblvd('options', 'code-editor');
				$new_element.themeblvd('options', 'column-widths');
				$new_element.themeblvd('options', 'sortable');

                if ( $new_element.find('.widget-content').hasClass('element-columns') ) {
					builder_blvd.columns( $new_element );
				}

				if ( $new_element.find('.widget-content').hasClass('element-content') ) {
					builder_blvd.columns( $new_element );
				}

				// Setup content block options, which open in a modal
				if ( $.isFunction( $.fn.ThemeBlvdModal ) ) {
					$new_element.find('.tb-content-block-options-link').ThemeBlvdModal({
				        build: true,
				        form: true,
				        padding: false,
				        size: 'medium',
				        on_load: builder_blvd.content_block_options_load
				    });
				}

				// Setup element background options, which open in a modal
				if ( $.isFunction( $.fn.ThemeBlvdModal ) ) {
					$new_element.find('.tb-element-background-options').ThemeBlvdModal({
				        build: true,
				        form: true,
				        padding: false,
				        size: 'medium',
				        on_load: builder_blvd.content_block_options_load // We're going to piggy back this
				    });
				}

            }
        });

		return false;
	});

	// Save Layout
	$builder.on('submit', '#edit_builder', function(){
		var el = $(this),
			data = el.serialize(),
			load = el.find('#publishing-action .ajax-loading'),
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

	// Delete item by ID passed through link's href
	$builder.on('click', '.delete-element', function(){

		var item = $(this).attr('href'),
			$item = $(item),
			$section = $item.closest('.sortable');

		tbc_confirm($(this).attr('title'), {'confirm':true}, function(r){
	    	if(r) {
	        	$item.addClass('delete fade-out');
                window.setTimeout(function(){
                    $item.remove();
                    console.log($section.html());
                    if ( ! $section.html().trim().length ) {
                        $section.addClass('empty');
                    }
                }, 750);
	        }
	    });
	    return false;
	});

	// Delete layout (via Delete Link on edit layout page)
	$builder.on('click', '#edit_layout .delete_layout', function(){
		var href = $(this).attr('href'), id = href.replace('#', ''), ids = 'posts%5B%5D='+id;
		builder_blvd.delete_layout( ids, 'click', 'edit_page' );
		return false;
	});

});