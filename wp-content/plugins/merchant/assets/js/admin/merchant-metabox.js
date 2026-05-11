"use strict";

;
(function ($) {
  'use strict';

  $.fn.merchantMetabox = function () {
    function initSelectMultiple($container) {
      var $selectMultiple = $container.find('.merchant-metabox-field-select select[multiple]');
      if ($selectMultiple.length) {
        $selectMultiple.select2({
          width: '100%'
        });
        $selectMultiple.each(function () {
          var $select = $(this);
          if ($select.next('.select2-container').find('.select2-selection--multiple').length) {
            $select.next('.select2-container').find('.select2-selection--multiple').append('<span class="merchant-select2-clear"></span>');
          }
        });
      }
    }
    function initMerchantRange($container) {
      var $rangeFields = $container.find('.merchant-range');
      if ($rangeFields.length) {
        $rangeFields.each(function () {
          var $range = $(this);
          var $rangeInput = $range.find('.merchant-range-input');
          var $numberInput = $range.find('.merchant-range-number-input');
          $rangeInput.on('change input merchant.range merchant-init.range', function (e) {
            var $thisRange = $(this);
            var value = (e.type === 'merchant' ? $numberInput.val() : $thisRange.val()) || 0;
            var min = $thisRange.attr('min') || 0;
            var max = $thisRange.attr('max') || 100;
            var percentage = (value - min) / (max - min) * 100;
            if ($('body').hasClass('rtl')) {
              $thisRange.css({
                'background': 'linear-gradient(to left, #3858E9 0%, #3858E9 ' + percentage + '%, #ddd ' + percentage + '%, #ddd 100%)'
              });
            } else {
              $thisRange.css({
                'background': 'linear-gradient(to right, #3858E9 0%, #3858E9 ' + percentage + '%, #ddd ' + percentage + '%, #ddd 100%)'
              });
            }
            $rangeInput.val(value);
            $numberInput.val(value);
          }).trigger('merchant-init.range');
          $numberInput.on('change input blur', function () {
            $rangeInput.val($(this).val()).trigger('merchant.range');
          });
        });
      }
    }
    function initSelectAjax($selectAjax) {
      $selectAjax.each(function () {
        var $select = $(this).find('select');
        var source = $select.data('source');
        var config = window.merchant_metabox;
        $select.select2({
          width: '100%',
          minimumInputLength: 1,
          ajax: {
            url: config.ajaxurl,
            dataType: 'json',
            delay: 250,
            cache: true,
            data: function data(params) {
              return {
                action: 'merchant_select_ajax',
                nonce: config.ajaxnonce,
                term: params.term,
                source: source
              };
            },
            processResults: function processResults(response, params) {
              if (response.success) {
                return {
                  results: response.data
                };
              }
              return {};
            }
          }
        });
        $selectAjax.find('.select2-selection--multiple').append('<span class="merchant-select2-clear"></span>');
      });
    }
    return this.each(function () {
      var $this = $(this);
      var $tabs = $this.find('.merchant-metabox-tab');
      var $contents = $this.find('.merchant-metabox-content');
      $tabs.each(function () {
        var $tab = $(this);
        $tab.on('click', function (e) {
          e.preventDefault();
          var $content = $contents.eq($tab.index());
          $tab.addClass('active').siblings().removeClass('active');
          $content.addClass('active').siblings().removeClass('active');
          $(document).trigger('merchant-metabox-content-show', $content);
        });
      });
      var $flexibleContent = $contents.find('.merchant-metabox-field-flexible-content');
      if ($flexibleContent.length) {
        $flexibleContent.each(function () {
          var $selectAjaxSelector = '.merchant-metabox-field-flexible-content-select-ajax';
          var $selectAjax = $($selectAjaxSelector);
          if ($selectAjax.length) {
            initSelectAjax($selectAjax);
          }
          var $list = $(this).find('.merchant-metabox-field-flexible-content-list');
          $list.sortable({
            axis: 'y',
            cursor: 'move',
            helper: 'original',
            handle: '.merchant-metabox-field-flexible-content-move',
            stop: function stop(event, ui) {
              $list.find('> li').each(function (index) {
                var $countSelector = '.merchant-metabox-field-flexible-content-item-count';
                var $count = $(this).find($countSelector).text();
                var $inputIndex = parseInt($count) - 1;
                $(this).find($countSelector).text(index);
                $(this).find('input, select').each(function () {
                  if ($(this).attr('name')) {
                    $(this).attr('name', $(this).attr('name').replace('[' + $inputIndex + ']', '[' + (index - 1) + ']'));
                  }
                });
              });
            }
          });
          $flexibleContent.find('.merchant-metabox-field-flexible-content-add-button').on('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            $(this).parent().find('.merchant-metabox-field-flexible-content-add-list').toggleClass('active');
          });
          $flexibleContent.find('.merchant-metabox-field-flexible-content-add').on('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var $layouts = $('.merchant-metabox-field-flexible-content-list[data-id=' + $(this).data('id') + ']');
            var $layout = $(this).data('layout');
            var $items = $layouts.find('> li');
            var $item = $layouts.find('> li').first().clone(true);
            $item.find(' > div').each(function () {
              if ($(this).data('layout') !== $layout) {
                $(this).remove();
              } else {
                $(this).children().appendTo($(this).parent());
                $(this).remove();
              }
            });
            $item.find('input, select').each(function () {
              if ($(this).data('name')) {
                $(this).attr('name', $(this).data('name').replace('0', $items.length - 1));
              }
            });
            $item.find('.merchant-metabox-field-flexible-content-item-count').text($items.length);
            $item.find('.merchant-metabox-field-flexible-content-select-ajax-clone').each(function () {
              $(this).removeClass('merchant-metabox-field-flexible-content-select-ajax-clone');
              $(this).addClass('merchant-metabox-field-flexible-content-select-ajax');
            });
            $item.removeClass('hidden');
            $layouts.append($item);
            $selectAjax = $($selectAjaxSelector);
            if ($selectAjax.length) {
              initSelectAjax($selectAjax);
            }
            initSelectMultiple($item);
            $(this).parent().removeClass('active');
            $layouts.removeClass('empty');
          });
          $flexibleContent.find('.merchant-metabox-field-flexible-content-remove').on('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var $item = $(this).closest('li');
            var $layouts = $item.parent();
            $item.remove();
            if ($layouts.find('>li').length === 1) {
              $layouts.addClass('empty');
            }
          });
        });
      }
      var $repeater = $contents.find('.merchant-metabox-field-repeater, .merchant-metabox-field-flexible-content-repeater');
      if ($repeater.length) {
        $repeater.each(function () {
          var $list = $(this).find('ul');
          $list.sortable({
            axis: 'y',
            cursor: 'move',
            helper: 'original',
            handle: '.merchant-metabox-field-repeater-move'
          });
          $repeater.find('.merchant-metabox-field-repeater-add').on('click', function (e) {
            e.preventDefault();
            var $items = $list.find('li');
            var $item = $list.find('li').first().clone(true);
            var $input = $item.find('input');
            if ($item.find('.merchant-metabox-field-repeater-list-item-fields').length) {
              $item.find('input').each(function () {
                $(this).attr('name', $(this).data('name').replace('0', $items.length - 1));
              });
            } else {
              $input.attr('name', $input.data('name'));
            }
            $item.removeClass('hidden');
            $list.append($item);
          });
          $repeater.find('.merchant-metabox-field-repeater-remove').on('click', function (e) {
            e.preventDefault();
            $(this).closest('li').remove();
          });
        });
      }
      var $uploads = $contents.find('.merchant-metabox-field-uploads');
      if ($uploads.length) {
        $uploads.each(function () {
          var $list = $(this).find('ul');
          $list.sortable({
            axis: 'y',
            cursor: 'move',
            helper: 'original',
            handle: '.merchant-metabox-field-uploads-move'
          });
          $uploads.find('.merchant-metabox-field-uploads-add').on('click', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var $list = $(this).parent().find('ul');
            var $items = $list.find('li');
            var $item = $items.first().clone(true);
            $item.find('input').each(function () {
              $(this).attr('name', $(this).data('name').replace('0', $items.length));
            });
            $item.removeClass('hidden');
            $list.append($item);
          });
          var wpMediaFrame;
          var wpMediaInput;
          $uploads.find('.merchant-metabox-field-uploads-upload').on('click', function (e) {
            e.preventDefault();
            wpMediaInput = $(this).closest('li').find(' > input');
            if (!wpMediaInput.attr('name').length) {
              return;
            }
            if (wpMediaFrame && wpMediaFrame.options.library.type === $list.data('library')) {
              wpMediaFrame.open();
              return;
            }
            wpMediaFrame = window.wp.media({
              library: {
                type: $list.data('library') || 'image'
              }
            }).open();
            wpMediaFrame.on('select', function () {
              var attachment = wpMediaFrame.state().get('selection').first().toJSON();
              wpMediaInput.val(attachment.url);
            });
          });
          $uploads.find('.merchant-metabox-field-uploads-thumbnail-upload').on('click', function (e) {
            e.preventDefault();
            wpMediaInput = $(this).parent().find('input');
            if (!wpMediaInput.attr('name').length) {
              return;
            }
            if (wpMediaFrame && wpMediaFrame.options.library.type === 'image') {
              wpMediaFrame.open();
              return;
            }
            wpMediaFrame = window.wp.media({
              library: {
                type: 'image'
              }
            }).open();
            wpMediaFrame.on('select', function () {
              var attachment = wpMediaFrame.state().get('selection').first().toJSON();
              var thumbnail = wpMediaInput.parent();
              wpMediaInput.val(attachment.id);
              thumbnail.find('span').hide();
              thumbnail.find('img').remove();
              thumbnail.find('.merchant-metabox-field-uploads-thumbnail-remove').show();
              thumbnail.find('.merchant-metabox-field-uploads-thumbnail-upload').append($('<img>').attr({
                'src': attachment.url
              }));
            });
          });
          $uploads.find('.merchant-metabox-field-uploads-thumbnail-remove').on('click', function (e) {
            e.preventDefault();
            var thumbnail = $(this).parent();
            thumbnail.find('span').show();
            thumbnail.find('img').remove();
            thumbnail.find('input').val('');
            thumbnail.find('.merchant-metabox-field-uploads-thumbnail-remove').hide();
          });
          $uploads.find('.merchant-metabox-field-uploads-remove').on('click', function (e) {
            e.preventDefault();
            $(this).closest('li').remove();
          });
        });
      }
      var $sizeChart = $contents.find('.merchant-metabox-field-size-chart');
      if ($sizeChart.length) {
        $sizeChart.on('multidimensional', function (event, $table) {
          var $wrap = $table || $sizeChart;
          $wrap.find('input').each(function () {
            var $input = $(this);
            var liIndex = Math.max(0, $input.closest('li').index() - 1);
            var trIndex = Math.max(0, $input.closest('tr').index() - 1);
            var tdIndex = Math.max(0, $input.closest('td').index());
            this.name = this.name.replace(/(\[\d+\])\[sizes\](\[\d+\])(\[\d+\])/, '[' + liIndex + '][sizes][' + trIndex + '][' + tdIndex + ']');
            this.name = this.name.replace(/(\[\d+\])\[name\]/, '[' + liIndex + '][name]');
          });
        });
        $sizeChart.each(function () {
          var $list = $(this).find('ul');
          $sizeChart.on('click', '.merchant-add', function (e) {
            e.preventDefault();
            var $item = $list.find('li').first().clone(true);
            var $input = $item.find('input');
            $input.each(function () {
              $(this).attr('name', $(this).data('name'));
              $(this).removeAttr('data-name');
            });
            $item.removeClass('hidden');
            $list.append($item);
            $sizeChart.trigger('multidimensional', [$item]);
          });
          $sizeChart.on('click', '.merchant-add-col', function (e) {
            e.preventDefault();
            var $td = $(this).closest('td');
            var $table = $(this).closest('table');
            var $columns = $(this).closest('tbody').find('tr td:nth-child(' + ($td.index() + 1) + ')');
            $columns.each(function () {
              var $column = $(this);
              var $clone = $column.clone(true);
              $clone.find('input').val('');
              $column.after($clone);
            });
            $sizeChart.trigger('multidimensional', [$table]);
          });
          $sizeChart.on('click', '.merchant-del-col', function (e) {
            e.preventDefault();
            var $td = $(this).closest('td');
            var $table = $(this).closest('table');
            var $count = $(this).closest('tr').find('td').length;
            var $target = $(this).closest('tbody').find('tr td:nth-child(' + ($td.index() + 1) + ')');
            if ($count > 2) {
              $target.remove();
            } else {
              $target.find('input').val('');
            }
            $sizeChart.trigger('multidimensional', [$table]);
          });
          $sizeChart.on('click', '.merchant-add-row', function (e) {
            e.preventDefault();
            var $tr = $(this).closest('tr');
            var $table = $(this).closest('table');
            var $clone = $tr.clone(true);
            $clone.find('input').val('');
            $tr.after($clone);
            $sizeChart.trigger('multidimensional', [$table]);
          });
          $sizeChart.on('click', '.merchant-del-row', function (e) {
            e.preventDefault();
            var $tr = $(this).closest('tr');
            var $table = $(this).closest('table');
            var $count = $(this).closest('tbody').find('tr').length;
            if ($count > 2) {
              $tr.remove();
            } else {
              $tr.find('input').val('');
            }
            $sizeChart.trigger('multidimensional', [$table]);
          });
          $sizeChart.on('click', '.merchant-remove', function (e) {
            e.preventDefault();
            $(this).closest('li').remove();
            $sizeChart.trigger('multidimensional');
          });
          $sizeChart.on('click', '.merchant-duplicate', function (e) {
            e.preventDefault();
            var $li = $(this).closest('li');
            var $clone = $li.clone(true);
            $li.after($clone);
            $sizeChart.trigger('multidimensional');
          });
        });
      }
      var $mediaField = $('.merchant-metabox-field-media');
      if ($mediaField.length) {
        $mediaField.each(function () {
          var $field = $(this);
          var $input = $field.find('.merchant-metabox-field-media-input');
          var $image = $field.find('.merchant-metabox-field-media-preview img');
          var $upload = $field.find('.merchant-metabox-field-media-upload');
          var $remove = $field.find('.merchant-metabox-field-media-remove');
          var placeholder = $image.data('placeholder');
          var wpMediaFrame;
          $upload.on('click', function (e) {
            e.preventDefault();
            if (wpMediaFrame) {
              wpMediaFrame.open();
              return;
            }
            wpMediaFrame = window.wp.media({
              library: {
                type: 'image'
              }
            });
            wpMediaFrame.on('select', function () {
              var attachment = wpMediaFrame.state().get('selection').first().toJSON();
              var thumbnail;
              if (attachment && attachment.sizes && attachment.sizes.thumbnail) {
                thumbnail = attachment.sizes.thumbnail.url;
              } else {
                thumbnail = attachment.url;
              }
              $input.val(attachment.id);
              $image.attr('src', thumbnail);
              $remove.removeClass('hidden');
            });
            wpMediaFrame.open();
          });
          $remove.on('click', function (e) {
            e.preventDefault();
            $input.val('');
            $image.attr('src', placeholder);
            $remove.addClass('hidden');
          });
        });
      }
      var $selectAjax = $('.merchant-metabox-field-select-ajax');
      if ($selectAjax.length) {
        initSelectAjax($selectAjax);
      }
      initSelectMultiple($this);
      initMerchantRange($this);
      var $attributes = $('.merchant-metabox-field-wc-attributes');
      if ($attributes.length) {
        $attributes.each(function () {
          var $sortable = $(this).find('ul');
          $sortable.sortable({
            axis: 'y',
            cursor: 'move',
            helper: 'original'
          });
        });
      }
      $(document).on('merchant-metabox-content-show', function (event, content) {
        var $content = $(content);
        if (!$content.data('code-editor-initalized')) {
          var $codeEditors = $('.merchant-metabox-field-code-editor', $content);
          if ($codeEditors.length) {
            $codeEditors.each(function () {
              var $textarea = $(this).find('textarea');
              var editorSettings = wp.codeEditor.defaultSettings || {};
              editorSettings.codemirror = _.extend({}, editorSettings.codemirror, {
                gutters: []
              });
              var editor = wp.codeEditor.initialize($textarea, editorSettings);
              editor.codemirror.on('keyup', function (instance) {
                instance.save();
              });
            });
          }
          $content.data('code-editor-initalized', true);
        }
      });
      var $depends = $contents.find('[data-depend-on]');
      if ($depends.length) {
        $depends.each(function () {
          var $depend = $(this);
          var $target = $contents.find('[name="' + $depend.data('depend-on') + '"]');
          if (!$target.data('depend-on')) {
            $target.on('change', function () {
              var $dependOn = $contents.find('[data-depend-on="' + $depend.data('depend-on') + '"]');
              if ($(this).is(':checked')) {
                $dependOn.removeClass('merchant-metabox-field-hidden');
              } else {
                $dependOn.addClass('merchant-metabox-field-hidden');
              }
            });
            $target.data('depend-on', true);
          }
        });
      }

      // Initialize Date Time Pickers
      var $dateTimeFields = $contents.find('.merchant-metabox-datetime-field');
      if ($dateTimeFields.length) {
        $dateTimeFields.each(function (index) {
          var $field = $(this);
          var $input = $field.find('input');
          var fieldOptions = $field.data('options') || {};

          // Base options with event handling
          var options = {
            locale: typeof merchant_datepicker_locale !== 'undefined' ? typeof merchant_datepicker_locale === 'string' ? JSON.parse(merchant_datepicker_locale) : merchant_datepicker_locale : {},
            selectedDates: [$input.val() ? new Date($input.val()) : ''],
            onSelect: function onSelect(_ref) {
              var date = _ref.date,
                formattedDate = _ref.formattedDate,
                datepicker = _ref.datepicker;
              if (typeof formattedDate === "undefined") {
                // Allow removing date
                datepicker.$el.value = '';
              }
              $input.trigger('change');
              $input.trigger('change.merchant-datepicker', [formattedDate, $input, options, index]);
            }
          };

          // Add default buttons
          fieldOptions.buttons = ['clear'];

          // Convert 'today' string to actual Date object
          if (fieldOptions.minDate !== undefined && fieldOptions.minDate === 'today') {
            fieldOptions.minDate = new Date();
            if (fieldOptions.timeZone !== undefined && fieldOptions.timeZone !== '') {
              fieldOptions.minDate = new Date(fieldOptions.minDate.toLocaleString('en-US', {
                timeZone: fieldOptions.timeZone
              }));
            }
          }

          // Merge field options with base options
          options = Object.assign(options, fieldOptions);

          // Initialize datepicker
          new AirDatepicker($input[0], options);

          // Make input readonly to prevent manual typing
          $input.attr('readonly', true);
        });
      }
      $(document).on('click', '.merchant-metabox-color-field .merchant-color-picker', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var $picker = $(this);
        var $field = $picker.closest('.merchant-metabox-color-field');
        var $input = $field.find('.merchant-color-input');
        var pickr = $picker.data('pickr');
        if (!pickr) {
          try {
            var $bodyHTML = $('body,html');
            $bodyHTML.addClass('merchant-height-auto');
            pickr = new Pickr({
              el: $picker.get(0),
              container: $picker.parent().get(0),
              theme: 'merchant',
              appClass: 'merchant-pcr-app',
              default: $input.val() || $picker.data('default-color') || '#212121',
              swatches: ['#000000', '#F44336', '#E91E63', '#673AB7', '#03A9F4', '#8BC34A', '#FFEB3B', '#FFC107', '#FFFFFF'],
              sliders: 'h',
              useAsButton: true,
              position: 'bottom-start',
              components: {
                hue: true,
                preview: true,
                opacity: true,
                interaction: {
                  input: true,
                  clear: true
                }
              },
              i18n: {
                'btn:clear': 'Default'
              }
            });
            pickr.on('change', function (color) {
              var colorCode;
              if (color.a === 1) {
                pickr.setColorRepresentation('HEX');
                colorCode = color.toHEXA().toString(0);
              } else {
                pickr.setColorRepresentation('RGBA');
                colorCode = color.toRGBA().toString(0);
              }
              $picker.css({
                'background-color': colorCode
              });
              if ($input.val() !== colorCode) {
                $input.val(colorCode).trigger('change');
              }
            });
            pickr.on('clear', function () {
              var defaultColor = $picker.data('default-color');
              if (defaultColor) {
                pickr.setColor(defaultColor);
              } else {
                $picker.css({
                  'background-color': 'white'
                });
                $input.val('');
              }
            });
            pickr.on('hide', function () {
              $bodyHTML.removeClass('merchant-height-auto');
            });
            $picker.data('pickr', pickr);
            setTimeout(function () {
              pickr.show();
            }, 200);
          } catch (error) {
            console.error('Error creating Pickr:', error);
          }
        } else {
          pickr.setColor($input.val());
          pickr.show();
        }
      });
      $(document).on('change keyup', '.merchant-metabox-color-field .merchant-color-input', function () {
        var $input = $(this);
        var $picker = $input.siblings('.merchant-color-picker');
        var colorCode = $input.val();
        $picker.css({
          'background-color': colorCode
        });
      });

      // Enhanced Conditional Logic (conditions attribute)
      function evaluateConditions(conditions, $field) {
        var operator = conditions.relation || 'AND';
        var results = [];
        for (var i = 0; i < conditions.terms.length; i++) {
          var term = conditions.terms[i];
          var $target = $contents.find('[name="' + term[0] + '"]');
          if (!$target.length) {
            continue;
          }
          var passed = false;
          var targetValue = null;

          // Get target value based on input type
          if ($target.attr('type') === 'checkbox') {
            // For checkboxes/switchers: return '1' if checked, '0' if not
            targetValue = $target.is(':checked') ? '1' : '0';
          } else if ($target.attr('type') === 'radio') {
            var $checked = $target.filter(':checked');
            targetValue = $checked.length ? $checked.val() : '';
          } else if ($target.is('select')) {
            targetValue = $target.val();
          } else {
            targetValue = $target.val();
          }

          // Evaluate condition
          switch (term[1]) {
            case '==':
              if (Array.isArray(targetValue)) {
                passed = targetValue.indexOf(term[2]) !== -1;
              } else {
                passed = targetValue == term[2];
              }
              break;
            case '!=':
              if (Array.isArray(targetValue)) {
                passed = targetValue.indexOf(term[2]) === -1;
              } else {
                passed = targetValue != term[2];
              }
              break;
            case 'any':
              var allowedValues = term[2].split('|');
              if (Array.isArray(targetValue)) {
                passed = targetValue.some(function (val) {
                  return allowedValues.indexOf(val) !== -1;
                });
              } else {
                passed = allowedValues.indexOf(targetValue) !== -1;
              }
              break;
            case 'not_any':
              var disallowedValues = term[2].split('|');
              if (Array.isArray(targetValue)) {
                passed = !targetValue.some(function (val) {
                  return disallowedValues.indexOf(val) !== -1;
                });
              } else {
                passed = disallowedValues.indexOf(targetValue) === -1;
              }
              break;
          }
          results.push(passed);
        }

        // Combine results based on operator
        if (operator === 'OR') {
          return results.indexOf(true) !== -1;
        } else {
          return results.indexOf(false) === -1;
        }
      }

      // Check for fields with conditions attribute
      var $conditionalFields = $contents.find('[data-conditions]');
      if ($conditionalFields.length) {
        var checkConditions = function checkConditions() {
          $conditionalFields.each(function () {
            var $field = $(this);
            var conditions = $field.data('conditions');
            if (conditions && conditions.terms) {
              var passed = evaluateConditions(conditions, $field);
              if (passed) {
                $field.removeClass('merchant-metabox-field-hidden');
              } else {
                $field.addClass('merchant-metabox-field-hidden');
              }
            }
          });
        };

        // Run on load
        checkConditions();

        // Bind events
        $contents.find('input, select, textarea').on('change', checkConditions);
      }
    });
  };
  $(document).ready(function ($) {
    $('.merchant-metabox').merchantMetabox();
  });
})(jQuery);