/**
 * Prints out the inline javascript needed for managing layouts.
 * This is an extension of what was already started in the
 * options-custom.js file.
 */
jQuery(document).ready(function($) {
  /*------------------------------------------------------------*/
  /* General Variables
	/*------------------------------------------------------------*/

  var nag_inserted = false;

  var l10n = {};

  if ('undefined' !== typeof themeblvdL10n) {
    l10n = themeblvdL10n;
  } else if ('undefined' !== typeof themeblvd) {
    l10n = themeblvd;
  }

  var themeBlvdVersion = l10n.framework_version;

  /*------------------------------------------------------------*/
  /* Static Methods
	/*------------------------------------------------------------*/

  var builderBlvd = {
    /**
     * Compares two software version numbers (e.g. "1.7.1" or "1.2b").
     *
     * This function was born in http://stackoverflow.com/a/6832721.
     *
     * @param   {string} v1                      The first version to be compared.
     * @param   {string} v2                      The second version to be compared.
     * @param   {object} options                 Optional flags that affect comparison behavior.
     * @param   {bool}   options.lexicographical True. Compares each part of the version strings lexicographically instead of naturally; this allows suffixes such as "b" or "dev" but will cause "1.10" to be considered smaller than "1.2".
     * @param   {bool}   options.zeroExtend      True. Changes the result if one version string has less parts than the other. In this case the shorter string will be padded with "zero" parts instead of being considered smaller.
     * @returns {number|NaN} :
     *    1. 0 if the versions are equal</li>
     *    2. a negative integer iff v1 < v2</li>
     *    3. a positive integer iff v1 > v2</li>
     *    4. NaN if either version string is in the wrong format</li>
     *
     * @copyright by Jon Papaioannou (["john", "papaioannou"].join(".") + "@gmail.com")
     */
    versionCompare: function(v1, v2, options) {
      var lexicographical = options && options.lexicographical,
        zeroExtend = options && options.zeroExtend,
        v1parts = v1.split('.'),
        v2parts = v2.split('.');

      function isValidPart(x) {
        return (lexicographical ? /^\d+[A-Za-z]*$/ : /^\d+$/).test(x);
      }

      if (!v1parts.every(isValidPart) || !v2parts.every(isValidPart)) {
        return NaN;
      }

      if (zeroExtend) {
        while (v1parts.length < v2parts.length) v1parts.push('0');
        while (v2parts.length < v1parts.length) v2parts.push('0');
      }

      if (!lexicographical) {
        v1parts = v1parts.map(Number);
        v2parts = v2parts.map(Number);
      }

      for (var i = 0; i < v1parts.length; ++i) {
        if (v2parts.length == i) {
          return 1;
        }

        if (v1parts[i] == v2parts[i]) {
          continue;
        } else if (v1parts[i] > v2parts[i]) {
          return 1;
        } else {
          return -1;
        }
      }

      if (v1parts.length != v2parts.length) {
        return -1;
      }

      return 0;
    },

    /**
     * Insert nag to the user. Currently only used when changes
     * have been made to the layout.
     */
    nag: function() {
      if (nag_inserted) {
        return false;
      }

      window.onbeforeunload = function() {
        return true;
      };

      nag_inserted = true;
    },

    /**
     * Bind anything that needs to be binded when the markup for
     * a custom layout is updated.
     */
    edit: function($builder) {
      // Setup options for initially loaded options on the page
      $builder.themeblvd('options', 'setup');
      $builder.themeblvd('options', 'media-uploader');
      $builder.themeblvd('options', 'editor');
      $builder.themeblvd('options', 'code-editor');
      $builder.themeblvd('options', 'column-widths');
      $builder.themeblvd('options', 'sortable');

      // Widgets
      // $builder.themeblvd('widgets'); // This happens automatically because of CSS classes in markup

      // Setup hints
      $builder.find('.sortable:not(:has(div))').addClass('empty');
      $builder.find('.sortable:has(div)').removeClass('empty');

      // Setup sorting w/jQuery UI
      builderBlvd.sort_setup();

      // Setup column & hero slider elements
      $builder
        .find('.element-columns, .element-jumbotron_slider')
        .each(function() {
          builderBlvd.columns($(this).closest('.widget'));
        });

      // If editing a page, show/hide "Clear Layout" button
      if (!$.trim($builder.find('#builder').html())) {
        $builder
          .closest('#builder_blvd')
          .find('#tb-clear-layout')
          .hide();
      } else {
        $builder
          .closest('#builder_blvd')
          .find('#tb-clear-layout')
          .show();
      }

      if (builderBlvd.versionCompare(themeBlvdVersion, '2.7.0') < 0) {
        // Framework 2.5-2.6

        if ($.isFunction($.fn.ThemeBlvdModal)) {
          $builder
            .find(
              '.edit-section-display, .edit-element-display, .edit-block-display'
            )
            .ThemeBlvdModal({
              button: themeblvd.apply,
              build: true,
              form: true,
              padding: false,
              size: 'medium',
              on_load: builderBlvd.content_block_options_load // We're going to piggy back this
            });

          $builder.find('.tb-block-options-link').ThemeBlvdModal({
            button: themeblvd.apply,
            build: true,
            form: true,
            padding: false,
            size: 'medium',
            on_load: builderBlvd.content_block_options_load
          });
        }
      } else {
        // Framework 2.7+

        // Setup section/element/block display options, which open in a modal
        $builder
          .find(
            '.edit-section-display, .edit-element-display, .edit-block-display'
          )
          .themeblvd('modal', null, {
            button: l10n.apply,
            build: true,
            form: true,
            padding: false,
            size: 'medium',
            onLoad: builderBlvd.content_block_options_load // We're going to piggy back this
          });

        // Setup content block options, which open in a modal
        $builder.find('.tb-block-options-link').themeblvd('modal', null, {
          button: l10n.apply,
          build: true,
          form: true,
          padding: false,
          size: 'medium',
          onLoad: builderBlvd.content_block_options_load,
          onSave: builderBlvd.content_block_options_save
        });
      }
    },

    /**
     * Setup sorting.
     */
    sort_setup: function() {
      var $sortable = $('#builder_blvd #tb-edit-layout .sortable'),
        prev_section,
        new_section,
        data_field_name;

      // Incase we're re-binding
      $sortable.each(function() {
        var $el = $(this);

        if ($el.is(':ui-sortable')) {
          $el.sortable('destroy');
        }
      });

      $sortable.sortable({
        handle: '.top-widget-name',
        connectWith: '.sortable',
        remove: function(event, ui) {
          // Cache the section's ID that the element is coming from
          prev_section = $(this)
            .closest('.element-section')
            .attr('id');
        },
        receive: function(event, ui) {
          // Add any needed empty classes.
          $builder.find('.sortable:not(:has(div))').addClass('empty');
          $builder.find('.sortable:has(div)').removeClass('empty');

          // Update the name field for any options in this section.

          new_section = $(this)
            .closest('.element-section')
            .attr('id');

          ui.item.find('input, textarea, select, option').each(function() {
            var field = $(this),
              name = field.attr('name');

            if (name) {
              field.attr('name', name.replace(prev_section, new_section));
            }
          });

          ui.item.find('.tb-sortable-option').each(function() {
            var $el = $(this);
            data_field_name = $el.data('name');
            data_field_name = data_field_name.replace(
              prev_section,
              new_section
            );
            $el.data('name', data_field_name);
          });

          data_field_name = ui.item.data('field-name');
          data_field_name = data_field_name.replace(prev_section, new_section);
          ui.item.data('field-name', data_field_name);

          // Re-bind sortable option type
          ui.item.themeblvd('options', 'sortable');
        },
        stop: function(event, ui) {
          ui.item.themeblvd('options', 'editor');

          // Layout changed.
          builderBlvd.nag();
        }
      });
    },

    /**
     * Toggle sample layout previews.
     */
    sample_preview: function($select) {
      var $parent = $select.closest('.controls');
      $parent.find('.sample-layouts div').hide();
      $parent.find('#sample-' + $select.val()).show();
    },

    /**
     * These methods are passed into WP's postboxes.add_postbox_toggles
     * as the pbshow and bphide parameters. They allow the widgets to
     * be toggled open and close.
     */
    hide_widget: function(id) {
      var $widget = $('#' + id);

      // Don't apply to Publish box
      if ($widget.hasClass('postbox-publish')) {
        return;
      }

      $widget.find('.tb-widget-content').hide();
    },
    show_widget: function(id) {
      var $widget = $('#' + id);

      $widget.find('.tb-widget-content').show();
    },

    /**
     * Setup each columns element.
     */
    columns: function($element) {
      // Sortable content blocks
      var prev_col_num, new_col_num, content;

      $element.find('.column-blocks').sortable({
        handle: '.block-widget-name h3',
        connectWith:
          '#' + $element.closest('.widget').attr('id') + ' .column-blocks',
        remove: function(event, ui) {
          // Add "mini-empty" class to column,
          // if empty
          content = $(this).html();
          if (!content.trim().length) {
            $(this).addClass('mini-empty');
          }

          // Set column number that block
          // is being removed from
          prev_col_num = $(this)
            .closest('.column')
            .find('.col-num')
            .val();
        },
        receive: function(event, ui) {
          var $el = $(this),
            data_field_name;

          // If the current, receiving column
          // was empty, now it's not.
          $el.removeClass('mini-empty');

          // Update the name field for any options
          // in this block.
          new_col_num = $el
            .closest('.column')
            .find('.col-num')
            .val();

          ui.item.find('input, textarea, select, option').each(function() {
            var field = $(this),
              name = field.attr('name');

            if (name) {
              field.attr(
                'name',
                name.replace('col_' + prev_col_num, 'col_' + new_col_num)
              );
            }
          });

          ui.item.find('.tb-sortable-option').each(function() {
            var $el = $(this);
            data_field_name = $el.data('name');
            data_field_name = data_field_name.replace(
              'col_' + prev_col_num,
              'col_' + new_col_num
            );
            $el.data('name', data_field_name);
          });

          // Update data-field-name
          data_field_name = ui.item.data('field-name');
          data_field_name = data_field_name.replace(
            'col_' + prev_col_num,
            'col_' + new_col_num
          );
          ui.item.data('field-name', data_field_name);
        },
        stop: function(event, ui) {
          // Layout changed.
          builderBlvd.nag();
        }
      });

      // Show/Hide column setup options
      $element.find('.edit-columns-config').off('click.tb-col-config'); // Avoid duplicates
      $element
        .find('.edit-columns-config')
        .on('click.tb-col-config', function() {
          var $link = $(this);

          if ($link.data('showing')) {
            $link
              .closest('.element-options')
              .find('.columns-setup')
              .stop()
              .slideUp(200);
            $link
              .stop()
              .text($link.data('text-show'))
              .data('showing', 0);
          } else {
            $link
              .closest('.element-options')
              .find('.columns-setup')
              .stop()
              .slideDown(200);
            $link
              .stop()
              .text($link.data('text-hide'))
              .data('showing', 1);
          }

          return false;
        });

      // Check if sortable columns are empty to start
      $element.find('.column-blocks').each(function() {
        var content = $(this).html();
        if (!content.trim().length) {
          $(this).addClass('mini-empty');
        }
      });

      // Button to add new content blocks
      $element.find('.columns-config .add-block').off('click.tb-add-block'); // Avoid duplicates
      $element
        .find('.columns-config .add-block')
        .on('click.tb-add-block', function() {
          var $column = $(this).closest('.column'),
            section_id = $column.closest('.element-section').attr('id'),
            element_id = $column.closest('.widget.element-options').attr('id'),
            col_num = $column.find('.col-num').val(),
            type = $column.find('.block-type').val(),
            $block = '';

          if (!type) {
            tbc_confirm(l10n.no_element, { textOk: 'Ok' });
            return false;
          }

          var data = {
            action: 'themeblvd_add_block',
            data:
              'section_id=' +
              section_id +
              '&element_id=' +
              element_id +
              '&block_type=' +
              type +
              '&col_num=' +
              col_num
          };

          $.post(ajaxurl, data, function(response) {
            // Split response
            response = response.split('[(=>)]');

            // Insert new content block
            $column
              .find('.column-blocks')
              .append(response[1])
              .removeClass('mini-empty');

            // Locate the content block just added
            $block = $column.find('#' + response[0]);

            // For those furious clickers, amek sure no "add" classes
            // got left behind from previously added elements.
            $('#builder_blvd .add').removeClass('add');

            // Give it a temporary green glow to show it's just been added.
            $block.addClass('add');
            window.setTimeout(function() {
              $block.removeClass('add');
            }, 500);

            // Setup non-binded options
            $block.themeblvd('options', 'setup');
            $block.themeblvd('options', 'media-uploader');
            $block.themeblvd('options', 'editor');
            $block.themeblvd('options', 'code-editor');
            $block.themeblvd('options', 'column-widths');
            $block.themeblvd('options', 'sortable');

            if (builderBlvd.versionCompare(themeBlvdVersion, '2.7.0') < 0) {
              // Framework 2.5-2.6

              if ($.isFunction($.fn.ThemeBlvdModal)) {
                // Setup content block options, which open in a modal
                $block.find('.tb-block-options-link').ThemeBlvdModal({
                  button: themeblvd.apply,
                  build: true,
                  form: true,
                  padding: false,
                  size: 'medium',
                  on_load: builderBlvd.content_block_options_load
                });

                // Setup block display options, which open in a modal
                $element.find('.edit-block-display').ThemeBlvdModal({
                  button: themeblvd.apply,
                  build: true,
                  form: true,
                  padding: false,
                  size: 'medium',
                  on_load: builderBlvd.content_block_options_load // We're going to piggy back this
                });
              }
            } else {
              // Framework 2.7+

              // Setup content block options, which open in a modal
              $block.find('.tb-block-options-link').themeblvd('modal', null, {
                button: l10n.apply,
                build: true,
                form: true,
                padding: false,
                size: 'medium',
                onLoad: builderBlvd.content_block_options_load,
                onSave: builderBlvd.content_block_options_save
              });

              // Setup block display options, which open in a modal
              $element.find('.edit-block-display').themeblvd('modal', null, {
                button: l10n.apply,
                build: true,
                form: true,
                padding: false,
                size: 'medium',
                onLoad: builderBlvd.content_block_options_load // We're going to piggy back this
              });
            }

            // Layout changed.
            builderBlvd.nag();
          });

          return false;
        });

      // Adjust column header, and how many columns are displayed to the user
      // Note: `themeblvd_update_columns` used in framework 2.5-2.6 and
      // `themeblvd-update-columns` used in framework 2.7+
      $element
        .find('.column-width-input')
        .off('themeblvd_update_columns themeblvd-update-columns'); // Avoid duplicates
      $element
        .find('.column-width-input')
        .on('themeblvd_update_columns themeblvd-update-columns', function() {
          var $input = $(this),
            $header = $input
              .closest('.element-options')
              .find('.columns-header'),
            config = $input.val(),
            num = config.split('-'),
            num = num.length,
            str = '';

          if (num == 1) {
            str = num + ' ' + l10n.column;
          } else {
            str = num + ' ' + l10n.columns;
          }

          $header.find('.col-count').text(str);
          $header.find('.col-config').text(config.replace(/-/g, ' - '));
          $element
            .find('.columns-config')
            .removeClass('columns-1 columns-2 columns-3 columns-4 columns-5')
            .addClass('columns-' + num);

          // Layout changed.
          builderBlvd.nag();
        });
    },

    // Used for the onLoad() callback when
    // linking to options in modal
    content_block_options_load: function(self) {
      if (!self) {
        var self = this; // Framework 2.5-2.6
      }

      var $modalWindow = self.$modalWindow;

      if (!$modalWindow) {
        $modalWindow = self.$modal_window; // Framework 2.5-2.6
      }

      // General framework options setup
      $modalWindow.themeblvd('options', 'bind');
      $modalWindow.themeblvd('options', 'setup');
      $modalWindow.themeblvd('options', 'media-uploader');
      $modalWindow.themeblvd('options', 'editor');
      $modalWindow.themeblvd('options', 'code-editor');
      $modalWindow.themeblvd('options', 'column-widths');
      $modalWindow.themeblvd('options', 'sortable');

      // Custom actions for "Display" options of elements
      var $display = $modalWindow.find('.element-display-options'),
        bg = $display.find('#section-bg_type select').val(),
        padding = $display.find('#section-apply_padding input').is(':checked');

      // Bind check for paginated elements
      $modalWindow.on('change', '.tb-query-check .of-input', function() {
        var $el = $(this),
          has_paginated = false;

        if ($el.val() == 'paginated') {
          $el.addClass('current');

          $('.tb-query-check .of-input').each(function() {
            if (!$(this).hasClass('current') && $(this).val() == 'paginated') {
              has_paginated = true;
            }
          });

          if (has_paginated) {
            tbc_confirm(l10n.primary_query, { textOk: 'Ok' });
          }

          $el.removeClass('current');

          return false;
        }
      });
    },

    // Make sure any content editors save from block
    // options modal.
    content_block_options_save: function(self) {
      if ('undefined' != typeof tinymce) {
        self.$modalWindow.find('.tb-editor-input').each(function() {
          var editorID = $(this).attr('id'),
            editor = tinymce.get(editorID);

          if (editor) {
            content = editor.save();
          }
        });
      }
    },

    // Save all code editor data to textareas.
    save_code_editors: function() {
      if ('undefined' === typeof window.themeblvd) {
        return;
      }

      if ('undefined' === typeof window.themeblvd.options) {
        return;
      }

      var optionsAdmin = window.themeblvd.options,
        editorID = null,
        editor = null;

      if (optionsAdmin.codeEditors) {
        for (editorID in optionsAdmin.codeEditors) {
          optionsAdmin.codeEditors[editorID].codemirror.save();
        }
      }
    }
  };

  /*------------------------------------------------------------*/
  /* Editor Builder -- Originally used for classic editor's
	/* Edit Page screen, but has since been expanded.
	/*------------------------------------------------------------*/

  var $wrap = $('#tb-editor-builder'),
    $template = $('#page_template'); // WP <select> for page template

  // Cancel any unsaved changes warning, if saving changes.
  $('#post').on('submit', function() {
    window.onbeforeunload = null;
  });

  // Show or hide the Builder, depending on if the
  // user selects the "Custom Layout" page template.
  $template.on('change', function() {
    if ($(this).val() == 'template_builder.php') {
      $wrap.addClass('template-active');
    } else {
      $wrap.removeClass('template-active');
    }
  });

  // Make sure any controls outside of the custom layout
  // are binded with option JS
  $wrap.find('.meta-box-nav').themeblvd('options', 'bind');
  $wrap.find('.meta-box-nav').themeblvd('options', 'setup');

  // When user syncs with template
  $wrap.find('#tb-template-sync').on('change', function() {
    var $select = $(this);

    if ($select.val()) {
      $wrap.find('#tb-edit-layout').hide();
      $wrap.find('#tb-sync-layout').show();
      $wrap
        .find('#tb-sync-layout .title')
        .text($select.find('option[value="' + $select.val() + '"]').text());
      $wrap.find('.sync-overlay').fadeIn(100);
    } else {
      $wrap.find('#tb-sync-layout').hide();
      $wrap.find('#tb-edit-layout').show();
      $wrap.find('.sync-overlay').fadeOut(100);
    }

    // Layout changed.
    builderBlvd.nag();
  });

  // Unsync layout from button
  $wrap.find('#tb-template-unsync').on('click', function() {
    $wrap.find('#tb-sync-layout').hide();
    $wrap.find('#tb-edit-layout').show();
    $wrap.find('.sync-overlay').fadeOut(100);

    $wrap.find('#tb-template-sync').val('');

    // Layout changed.
    builderBlvd.nag();
  });

  // Apply/Merge template
  $wrap.on('change', '#tb-template-apply', function() {
    var $select = $(this),
      info = $select.val(),
      $overlay = $wrap.find('.ajax-overlay.full-overlay');

    // Are they sure they want to delete current
    // layout and apply template?
    tbc_confirm(l10n.template_apply, { confirm: true }, function(r) {
      if (r) {
        $overlay.fadeIn(100);

        var data = {
          action: 'themeblvd_apply_template',
          security: $wrap.find('input[name="tb_nonce"]').val(),
          data: $select.closest('form').serialize(),
          info: info
        };

        $.post(ajaxurl, data, function(r) {
          $wrap.find('#tb-edit-layout .ajax-mitt').html(r);
          builderBlvd.edit($wrap.find('#tb-edit-layout'));

          data = {
            action: 'themeblvd_get_meta',
            security: $wrap.find('input[name="tb_nonce"]').val(),
            key: '_tb_builder_styles',
            post_id: $wrap.find('input[name="tb_post_id"]').val()
          };

          $.post(ajaxurl, data, function(r) {
            var $textarea = $wrap.find('#tb-custom-styles-textarea');

            $textarea.val(r);

            if ($textarea.data('CodeMirrorInstance')) {
              $textarea.data('CodeMirrorInstance').setValue(r);
            }

            // Finalize all.
            $overlay.fadeOut(200);
          });
        });
      }
    });

    // Put the select menu back to first value (blank)
    $select.val('');
  });

  // Save current layout as new template
  $wrap.find('#tb-save-new-template').on('click', function() {
    var args = {
      confirm: true,
      input: true,
      input_desc: l10n.template_desc,
      textOk: l10n.template_save,
      className: 'text-center'
    };

    tbc_confirm('<h3>' + l10n.template_title + '</h3>', args, function(name) {
      if (name) {
        // Name entered, create new template
        var form_data = $wrap
          .find('#builder')
          .find('input, option, select, textarea')
          .serialize();

        var data = {
          action: 'themeblvd_add_template',
          security: $wrap.find('input[name="tb_nonce"]').val(),
          data:
            'tb_template_name=' +
            encodeURIComponent(name) +
            '&' +
            form_data +
            '&_tb_builder_styles=' +
            $('#tb-custom-styles-textarea').val() +
            '&post_id=' +
            $wrap.find('input[name="tb_post_id"]').val()
        };

        $wrap.find('.ajax-overlay.full-overlay').fadeIn(100);

        $.post(ajaxurl, data, function(r) {
          r = r.split('[(=>)]');

          // Confirm
          tbc_confirm(r[0]);

          // Hide overlay
          $wrap.find('.ajax-overlay.full-overlay').fadeOut(200);

          // Update the starting point <select>
          var $apply = $wrap.find('.select-layout.apply');
          $apply
            .html(r[1])
            .find('.textbox')
            .text($apply.find('option[value=""]').text());

          // Update the template sync <select>
          var $sync = $wrap.find('.select-layout.sync');
          $sync
            .html(r[2])
            .find('.textbox')
            .text($sync.find('option[value=""]').text());
        });
      } else if (name === '') {
        // Forgot name
        tbc_confirm(l10n.no_name);
      }
    });

    return false;
  });

  // Custom CSS
  if (builderBlvd.versionCompare(themeBlvdVersion, '2.7.0') < 0) {
    // Framework 2.5-2.6

    $wrap.find('.meta-box-nav').themeblvd('options', 'code-editor');
  } else {
    // Framework 2.7+

    $('#tb-custom-styles-link').themeblvd('modal', null, {
      button: l10n.apply,
      form: true,
      padding: false,
      size: 'medium',
      onSave: function(modal) {
        builderBlvd.save_code_editors();
      }
    });
  }

  // Clear Layout
  $wrap.find('#tb-clear-layout').on('click', function() {
    var $select = $(this),
      $overlay = $wrap.find('.ajax-overlay.full-overlay'),
      post_id = $wrap.find('input[name="tb_post_id"]').val();

    // Are they sure they want to delete the current
    // layout's data?
    tbc_confirm(l10n.clear_layout, { confirm: true }, function(r) {
      if (r) {
        $overlay.fadeIn(100);

        var data = {
          action: 'themeblvd_clear_layout',
          security: $wrap.find('input[name="tb_nonce"]').val(),
          data: post_id
        };

        $.post(ajaxurl, data, function(r) {
          $wrap.find('#tb-edit-layout .ajax-mitt').html(r);
          $wrap.find('#tb-custom-styles-textarea').val('');
          builderBlvd.edit($wrap.find('#tb-edit-layout'));
          $overlay.fadeOut(200);
        });
      }
    });

    return false;
  });

  /*------------------------------------------------------------*/
  /* Page: Manage Templates
	/*------------------------------------------------------------*/

  // Delete layouts via bulk action
  $('#manage_layouts')
    .find('form')
    .on('submit.check', function(e) {
      e.preventDefault();

      var $form = $(this),
        action = $form.find('select[name="action"]').val(),
        values = false;

      if (action != 'trash') {
        return false;
      }

      $form.find('input[name="posts[]"]').each(function() {
        if ($(this).is(':checked')) {
          values = true;
        }
      });

      if (values) {
        tbc_confirm(l10n.delete_layout, { confirm: true }, function(r) {
          if (r) {
            $form.off('submit.check').submit();
          }
        });
      } else {
        tbc_confirm(l10n.no_layouts);
      }
    });

  /*------------------------------------------------------------*/
  /* Page: Add Template
	/*------------------------------------------------------------*/

  var $add_template = $('#add_layout');

  $add_template.themeblvd('init');
  $add_template.themeblvd('options', 'setup');
  $add_template.themeblvd('options', 'bind');

  $add_template
    .find('.builder-samples #sample, .builder_samples #sample')
    .each(function() {
      builderBlvd.sample_preview($(this));
    });

  $add_template
    .find('.builder-samples #sample, .builder_samples #sample')
    .on('change', function() {
      builderBlvd.sample_preview($(this));
    });

  // Validate new template submission
  $add_template.find('form').submit(function(e) {
    // Tell user they forgot a name
    if (
      !$(this)
        .find('#section-name .of-input')
        .val()
    ) {
      tbc_confirm(l10n.no_name, { textOk: 'Ok' });
      e.preventDefault();
      return false;
    }
  });

  // @deprecated
  $add_template.find('.builder_samples').hide();
  $add_template.find('.trigger select').on('change', function() {
    if ($(this).val() == 'sample') {
      $add_template.find('.builder_samples').show();
    } else {
      $add_template.find('.builder_samples').hide();
    }
  });

  /*------------------------------------------------------------*/
  /* Page: Edit Template
	/*------------------------------------------------------------*/

  var $edit_template = $('#builder_blvd.primary');

  // Fade out notices
  $edit_template
    .find('.fade')
    .delay(3000)
    .animate({ height: 0, opacity: 0 }, 500, function() {
      $(this).remove();
    });

  // Screen Options
  /*
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
	*/

  // Delete layout
  $edit_template.on('click', '.delete-layout', function() {
    var href = this.href;

    tbc_confirm(l10n.delete_layout, { confirm: true }, function(r) {
      if (r) {
        location.href = href;
      }
    });

    return false;
  });

  // Enable WP's post box toggles
  // requires: wp_enqueue_script('postbox');
  $edit_template.each(function() {
    postboxes.add_postbox_toggles('nav-menus', {
      // using 'nav-menus' as the current page is a hack to get WP not to fire save_state() method, which triggers AJAX errors.
      pbshow: builderBlvd.show_widget,
      pbhide: builderBlvd.hide_widget
    });
  });

  // Update template (ajax)
  $edit_template.find('.ajax-save-template').on('click', function() {
    // Reset unsaved changes nag.
    window.onbeforeunload = null;

    // Save any codemirror instances to the form.
    builderBlvd.save_code_editors();

    // Save any wp editors.
    if ('undefined' !== typeof tinymce) {
      tinymce.triggerSave();
    }

    // Submit the form data and save.
    var $form = $(this).closest('form'),
      $load = $form.find('.spinner'),
      nonce = $form.find('input[name="tb_nonce"]').val(),
      data = $form.serialize();

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'themeblvd_save_template',
        security: nonce,
        data: data
      },
      beforeSend: function() {
        $load.css('visibility', 'visible');
      },
      success: function(r) {
        var message = '';

        if ('layout' == $form.find('input[name="context"]').val()) {
          message = l10n.layout_updated;
        } else {
          message = l10n.template_updated;
        }

        // Add updated success message.
        $edit_template.find('.themeblvd-updated').remove(); // Remove previous, if exists.

        $edit_template
          .find('.meta-box-nav')
          .before(
            '<div class="themeblvd-updated updated" style="display:none;"><p><strong>' +
              message +
              '</strong></p><button type="button" class="notice-dismiss"></button></div>'
          );

        $edit_template
          .find('.themeblvd-updated')
          .fadeIn(500)
          .find('.notice-dismiss')
          .on('click', function() {
            $(this)
              .closest('.themeblvd-updated')
              .fadeOut(500, function() {
                $(this).remove();
              });
          });

        // Reset layout changed nag.
        nag_inserted = false;

        $('#tb-builder-notice').fadeOut(500, function() {
          $(this).remove();
        });

        $load.css('visibility', 'hidden');
      }
    });

    return false;
  });

  /*------------------------------------------------------------*/
  /* Layout Builder (general)
	/*------------------------------------------------------------*/

  var $builder_wrap = $('#builder_blvd'),
    $builder = $builder_wrap.find('#tb-edit-layout');

  // Setup nag.
  $('#wpbody-content').on(
    'click',
    '#tb-builder-notice .notice-dismiss',
    function() {
      $(this)
        .closest('#tb-builder-notice')
        .fadeOut(300, function() {
          $(this).remove();
        });

      return false;
    }
  );

  // Watch for changes on any forms within builder.
  $builder_wrap.on('change', ':input', function() {
    if ($builder_wrap.hasClass('manage') || $builder_wrap.hasClass('add')) {
      return;
    }

    if (
      $(this).hasClass('tb-select-element') ||
      $(this).hasClass('block-type')
    ) {
      return;
    }

    // Layout changed.
    builderBlvd.nag();
  });

  $builder_wrap.on('mousedown', '.jquery-ui-slider-wrap', function() {
    // Layout changed.
    builderBlvd.nag();
  });

  $builder_wrap.on(
    'themeblvd-color-change',
    '.wp-picker-input-wrap > input',
    function() {
      // Layout changed.
      builderBlvd.nag();
    }
  );

  $('body').on(
    'themeblvd-modal-close themeblvd-modal-insert',
    '.themeblvd-modal .button, .themeblvd-modal .button-link',
    function() {
      // Layout changed.
      builderBlvd.nag();
    }
  );

  $builder_wrap.on(
    'sortstop',
    '.tb-sortable-option .item-container',
    function() {
      // Layout changed.
      builderBlvd.nag();
    }
  );

  $builder_wrap.on(
    'mouseup',
    '.tb-sortable-option .add-item, .tb-sortable-option .delete-sortable-items',
    function() {
      // Layout changed.
      builderBlvd.nag();
    }
  );

  // Options init
  $builder.themeblvd('init');
  $builder.themeblvd('options', 'bind');

  // Initial builder loaded on page
  builderBlvd.edit($builder);

  // Section and Element Labels
  $builder.on('click', '.dynamic-label .label-text', function() {
    var $el = $(this),
      $input = $el.closest('.dynamic-label').find('.label-input');

    $el.css('opacity', '0');
    $input.show().focus();

    $input.on('keydown.edit-label, focusout.edit-label', function(event) {
      if (
        (event.type == 'keydown' && event.keyCode == 13) ||
        event.type == 'focusout'
      ) {
        if (!$input.val()) {
          $input.val('...');
        }

        $el.css('opacity', '1').text($input.val());
        $input.hide();

        $input.off('keydown.edit-label, focusout.edit-label').trigger('blur');
        event.preventDefault();

        return false;
      }
    });
  });

  // Add new section
  $builder.on('click', '.tb-add-new-section', function() {
    var $button = $(this),
      $overlay = $button.parent().find('.ajax-overlay'),
      $load = $button.parent().find('.ajax-loading'),
      $section;

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'themeblvd_add_section',
        data: ''
      },
      beforeSend: function() {
        $overlay.show();
        $load.fadeIn('fast');
      },
      success: function(response) {
        var trim_front = response.split('<div id="');
        trim_back = trim_front[1].split('" class="element-section"');
        section_id = trim_back[0];

        // Insert HTML markup
        $builder.find('#builder').append(response);

        // If no sections existed, remove overlay on adding new
        // elements and hide the default "Add Section" hero button.
        $builder
          .closest('#builder_blvd')
          .find('#tb-clear-layout')
          .fadeIn('fast');
        $builder.find('.manage-elements .ajax-overlay').fadeOut('fast');
        $builder.find('.tb-no-sections').hide();

        // Remove loading elements
        $load.fadeOut('fast');
        $overlay.fadeOut('fast');

        // Cache element we've just inderted
        $section = $builder.find('#' + section_id);

        // Re-setup sorting across all sections w/jQuery UI
        builderBlvd.sort_setup(); // will destroy previous instant and re-bind

        // Will be empty initially
        $section.find('.sortable').addClass('empty');

        if (builderBlvd.versionCompare(themeBlvdVersion, '2.7.0') < 0) {
          // Framework 2.5-2.6

          if ($.isFunction($.fn.ThemeBlvdModal)) {
            $section.find('.edit-section-display').ThemeBlvdModal({
              button: themeblvd.apply,
              build: true,
              form: true,
              padding: false,
              size: 'medium',
              on_load: builderBlvd.content_block_options_load // We're going to piggy back this
            });
          }
        } else {
          // Framework 2.7+

          // Bind popup for display options
          $section.find('.edit-section-display').themeblvd('modal', null, {
            button: l10n.apply,
            build: true,
            form: true,
            padding: false,
            size: 'medium',
            onLoad: builderBlvd.content_block_options_load // We're going to piggy back this
          });
        }

        // Layout changed.
        builderBlvd.nag();
      }
    });

    return false;
  });

  // Delete section
  $builder.on('click', '.delete-section', function() {
    var $button = $(this);

    tbc_confirm($button.data('confirm'), { confirm: true }, function(r) {
      if (r) {
        // Fade out and delete section
        $button
          .closest('.element-section')
          .animate({ height: 0, opacity: 0 }, 500, function() {
            $(this).remove();

            // If no more sections, show the placeholder items.
            if (!$.trim($builder.find('#builder').html())) {
              $builder
                .closest('#builder_blvd')
                .find('#tb-clear-layout')
                .fadeOut('fast');
              $builder.find('.manage-elements .ajax-overlay').fadeIn('fast');
              $builder.find('.tb-no-sections').slideDown();
            }

            // Layout changed.
            builderBlvd.nag();
          });
      }
    });

    return false;
  });

  // Shift sections up/down
  $builder.on('click', '.shift-section-up, .shift-section-down', function() {
    var $button = $(this),
      direction = 'down',
      $section = $button.closest('.element-section'),
      $next_section;

    if ($button.hasClass('shift-section-up')) {
      direction = 'up';
    }

    if (direction == 'up') {
      $next_section = $section.prev('.element-section');

      if ($section.is('.element-section:first-child')) {
        tbc_confirm(l10n.shift_up_error, { textOk: 'Ok' });
      } else {
        $section.slideUp(200, function() {
          $next_section.before($section);
          $section.slideDown(200);
        });
      }
    } else {
      $next_section = $section.next('.element-section');

      if ($section.is('.element-section:last-child')) {
        tbc_confirm(l10n.shift_down_error, { textOk: 'Ok' });
      } else {
        $section.slideUp(200, function() {
          $next_section.after($section);
          $section.slideDown(200);
        });
      }
    }

    $section.addClass('add');
    window.setTimeout(function() {
      $section.removeClass('add');
    }, 500);

    // Layout changed.
    builderBlvd.nag();

    return false;
  });

  // Add new element
  $builder.on('click', '#add_new_element', function() {
    var $button = $(this),
      id,
      trim_front,
      trim_back,
      element_id,
      overlay = $button.parent().find('.ajax-overlay.add-element'),
      load = $button.parent().find('.ajax-loading');
    (type = $button
      .parent()
      .find('select')
      .val()),
      (section = $builder.find('.element-section:first-child').attr('id')),
      ($element = '');

    if (!type) {
      tbc_confirm(l10n.no_element, { textOk: 'Ok' });
      return false;
    }

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'themeblvd_add_element',
        data: 'section_id=' + section + '&element_type=' + type
      },
      beforeSend: function() {
        overlay.show();
        load.fadeIn('fast');
      },
      success: function(response) {
        trim_front = response.split('<div id="');
        trim_back = trim_front[1].split('" class="widget element-options"');
        element_id = trim_back[0];

        $builder
          .find('.element-section:first-child .elements')
          .append(response)
          .removeClass('empty');

        $element = $('#' + element_id);

        // For those furious clickers, make sure no "add" classes
        // got left behind from previously added elements.
        $('#builder_blvd .add').removeClass('add');

        $element.addClass('add');
        window.setTimeout(function() {
          $element.removeClass('add');
        }, 500);

        // $element.themeblvd('widgets');
        $element.themeblvd('options', 'setup');
        // $element.themeblvd('options', 'bind');
        $element.themeblvd('options', 'media-uploader');
        $element.themeblvd('options', 'editor');
        $element.themeblvd('options', 'code-editor');
        $element.themeblvd('options', 'column-widths');
        $element.themeblvd('options', 'sortable');

        if (
          $element.find('.widget-content').hasClass('element-columns') ||
          $element.find('.widget-content').hasClass('element-jumbotron_slider')
        ) {
          builderBlvd.columns($element);
        }

        $element.fadeIn();
        load.fadeOut('fast');
        overlay.fadeOut('fast');

        if (builderBlvd.versionCompare(themeBlvdVersion, '2.7.0') < 0) {
          // Framework 2.5-2.6

          if ($.isFunction($.fn.ThemeBlvdModal)) {
            // Setup element display options, which open in a modal
            $element.find('.edit-element-display').ThemeBlvdModal({
              button: themeblvd.apply,
              build: true,
              form: true,
              padding: false,
              size: 'medium',
              on_load: builderBlvd.content_block_options_load // We're going to piggy back this
            });
          }
        } else {
          // Framework 2.7+

          // Setup element display options, which open in a modal
          $element.find('.edit-element-display').themeblvd('modal', null, {
            button: l10n.apply,
            build: true,
            form: true,
            padding: false,
            size: 'medium',
            onLoad: builderBlvd.content_block_options_load // We're going to piggy back this
          });
        }

        // Layout changed.
        builderBlvd.nag();
      }
    });

    return false;
  });

  // Element nav, to toggle between options and display options.
  $builder.on('click', '.element-options-nav a', function() {
    var $link = $(this),
      target = $link.data('target'),
      $element = $link.closest('.widget');

    $link
      .closest('ul')
      .find('li')
      .removeClass('active');
    $link.closest('li').addClass('active');

    if (target == 'display') {
      $element.find('.element-display-options-wrap').removeClass('hide');
      $element.find('.element-options-wrap').addClass('hide');
    } else {
      $element.find('.element-options-wrap').removeClass('hide');
      $element.find('.element-display-options-wrap').addClass('hide');
    }

    return false;
  });

  // Duplicate element
  $builder.on('click', '.duplicate-element', function() {
    var $link = $(this),
      $element = $link.closest('.widget'),
      data = $element.find('input, option, select, textarea').serialize(),
      $new_element;

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'themeblvd_dup_element',
        data: data
      },
      success: function(r) {
        // [0] => Element ID
        // [1] => HTML of new content block
        r = r.split('[(=>)]');

        // Add HTML for new element directly after element
        // that was copied.
        $element.after(r[1]);

        // Cache the new HTML element we just appended
        $new_element = $('#' + r[0]);

        // Temporarily add green border/shadow to the newly added element.
        $new_element.addClass('add');
        window.setTimeout(function() {
          $new_element.removeClass('add');
        }, 500);

        // Setup Theme Blvd namespace options
        // $new_element.themeblvd('widgets');
        $new_element.themeblvd('options', 'setup');
        // new_element.themeblvd('options', 'bind');
        $new_element.themeblvd('options', 'media-uploader');
        $new_element.themeblvd('options', 'editor');
        $new_element.themeblvd('options', 'code-editor');
        $new_element.themeblvd('options', 'column-widths');
        $new_element.themeblvd('options', 'sortable');

        if (
          $new_element.find('.widget-content').hasClass('element-columns') ||
          $new_element
            .find('.widget-content')
            .hasClass('element-jumbotron_slider')
        ) {
          builderBlvd.columns($new_element);
        }

        if (builderBlvd.versionCompare(themeBlvdVersion, '2.7.0') < 0) {
          // Framework 2.5-2.6

          if ($.isFunction($.fn.ThemeBlvdModal)) {
            // Setup content block options, which open in a modal
            $new_element.find('.tb-block-options-link').ThemeBlvdModal({
              button: themeblvd.apply,
              build: true,
              form: true,
              padding: false,
              size: 'medium',
              on_load: builderBlvd.content_block_options_load
            });

            // Setup element display options, which open in a modal
            $new_element
              .find('.edit-element-display, .edit-block-display')
              .ThemeBlvdModal({
                button: themeblvd.apply,
                build: true,
                form: true,
                padding: false,
                size: 'medium',
                on_load: builderBlvd.content_block_options_load // We're going to piggy back this
              });
          }
        } else {
          // Framework 2.7+

          // Setup content block options, which open in a modal
          $new_element.find('.tb-block-options-link').themeblvd('modal', null, {
            button: l10n.apply,
            build: true,
            form: true,
            padding: false,
            size: 'medium',
            onLoad: builderBlvd.content_block_options_load,
            onSave: builderBlvd.content_block_options_save
          });

          // Setup element display options, which open in a modal
          $new_element
            .find('.edit-element-display, .edit-block-display')
            .themeblvd('modal', null, {
              button: l10n.apply,
              build: true,
              form: true,
              padding: false,
              size: 'medium',
              onLoad: builderBlvd.content_block_options_load // We're going to piggy back this
            });
        }

        // Layout changed.
        builderBlvd.nag();
      }
    });

    return false;
  });

  // Duplicate block
  $builder.on('click', '.duplicate-block', function() {
    var $link = $(this),
      $block = $link.closest('.block'),
      $new_block;

    $.ajax({
      type: 'POST',
      url: ajaxurl,
      data: {
        action: 'themeblvd_dup_block',
        data: $block.find('input, option, select, textarea').serialize()
      },
      success: function(r) {
        // [0] => Element ID
        // [1] => Block ID
        // [2] => Column number
        // [3] => HTML of new content block
        r = r.split('[(=>)]');

        // Add HTML for new content block
        $block.after(r[3]);

        // Cache the new HTML element we just appended
        $new_block = $('#' + r[1]);

        // Temporarily add green border/shadow to the newly added element.
        $new_block.addClass('add');
        window.setTimeout(function() {
          $new_block.removeClass('add');
        }, 500);

        // Setup Theme Blvd namespace options
        $new_block.themeblvd('options', 'setup');
        $new_block.themeblvd('options', 'media-uploader');
        $new_block.themeblvd('options', 'editor');
        $new_block.themeblvd('options', 'code-editor');
        $new_block.themeblvd('options', 'sortable');

        if (builderBlvd.versionCompare(themeBlvdVersion, '2.7.0') < 0) {
          if ($.isFunction($.fn.ThemeBlvdModal)) {
            // And bind the modal window for the settings link
            $new_block.find('.tb-block-options-link').ThemeBlvdModal({
              button: themeblvd.apply,
              build: true,
              form: true,
              padding: false,
              size: 'medium',
              on_load: builderBlvd.content_block_options_load
            });

            // Setup element display options, which open in a modal
            $new_block.find('.edit-block-display').ThemeBlvdModal({
              button: themeblvd.apply,
              build: true,
              form: true,
              padding: false,
              size: 'medium',
              on_load: builderBlvd.content_block_options_load
            });
          }
        } else {
          // And bind the modal window for the settings link
          $new_block.find('.tb-block-options-link').themeblvd('modal', null, {
            button: l10n.apply,
            build: true,
            form: true,
            padding: false,
            size: 'medium',
            onLoad: builderBlvd.content_block_options_load,
            onSave: builderBlvd.content_block_options_save
          });

          // Setup element display options, which open in a modal
          $new_block.find('.edit-block-display').themeblvd('modal', null, {
            button: l10n.apply,
            build: true,
            form: true,
            padding: false,
            size: 'medium',
            onLoad: builderBlvd.content_block_options_load
          });
        }

        // Layout changed.
        builderBlvd.nag();
      }
    });
    return false;
  });

  // Delete item by ID passed through link's href
  $builder.on('click', '.delete-element', function() {
    var $item = $(this).closest('.widget'),
      $section = $item.closest('.sortable');

    tbc_confirm($(this).attr('title'), { confirm: true }, function(r) {
      if (r) {
        $item.addClass('delete fade-out');

        window.setTimeout(function() {
          $item.slideUp(200, function() {
            $(this).remove();

            if (!$section.html().trim().length) {
              $section.addClass('empty');
            }
          });

          // Delete any stray tooltips
          $('.themeblvd-tooltip').remove();
        }, 750);

        // Layout changed.
        builderBlvd.nag();
      }
    });

    return false;
  });

  // Delete item by ID passed through link's href
  $builder.on('click', '.delete-block', function() {
    var $item = $(this).closest('.block-widget'),
      $section = $item.closest('.column-blocks');

    tbc_confirm($(this).attr('title'), { confirm: true }, function(r) {
      if (r) {
        $item.addClass('delete fade-out');

        window.setTimeout(function() {
          $item.slideUp(200, function() {
            $(this).remove();

            if (!$section.html().trim().length) {
              $section.addClass('mini-empty');
            }
          });

          // Delete any stray tooltips
          $('.themeblvd-tooltip').remove();
        }, 750);

        // Layout changed.
        builderBlvd.nag();
      }
    });

    return false;
  });

  // Bind check for paginated elements
  $builder.on('change', '.tb-query-check .of-input', function() {
    var $el = $(this),
      has_paginated = false;

    if ($el.val() == 'paginated') {
      $el.addClass('current');

      $('.tb-query-check .of-input').each(function() {
        if (!$(this).hasClass('current') && $(this).val() == 'paginated') {
          has_paginated = true;
        }
      });

      if (has_paginated) {
        tbc_confirm(l10n.primary_query, { textOk: 'Ok' });
      }

      $el.removeClass('current');

      return false;
    }
  });
});
