"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
;
(function ($, window, document, undefined) {
  'use strict';

  var merchant = merchant || {};
  var params = new URLSearchParams(window.location.search);
  var currentModule = params.get('module');
  $(document).ready(function () {
    // AjaxSave
    var $ajaxForm = $('.merchant-module-page-ajax-form');
    var $ajaxHeader = $('.merchant-module-page-ajax-header');
    var $ajaxSaveBtn = $('.merchant-module-save-button');
    $('.merchant-module-page-content').on('change keypress change.merchant', function (e) {
      if ($(e.target).hasClass('merchant-backup-file') || $(e.target).hasClass('merchant-search-field')) {
        return;
      }
      if (!$(this).is('.merchant-module-question-answer-textarea, .merchant-license-code-input')) {
        if (!merchant.show_save) {
          $ajaxHeader.addClass('merchant-show');
          $ajaxHeader.removeClass('merchant-saving');
          merchant.show_save = true;
        }
      }
      GroubField.initFlag();
    });

    // Warn the user before leaving the page with unsaved changes.
    window.addEventListener('beforeunload', function (e) {
      if (merchant.show_save) {
        e.preventDefault();
      }
    });
    $ajaxForm.ajaxForm({
      beforeSubmit: function beforeSubmit(arr) {
        $ajaxHeader.addClass('merchant-saving');

        // Serialize merchant[] fields into a single JSON payload
        // to bypass PHP's max_input_vars limit (Issue #608).
        var merchantData = {};
        var keepIndexes = [];
        arr.forEach(function (field, index) {
          if (field.name === 'merchant-search-field') {
            keepIndexes.push(index);
            return;
          }
          if (field.name && field.name.indexOf('merchant[') === 0) {
            var keys = field.name.match(/\[([^\]]*)\]/g);
            if (!keys) {
              return;
            }
            var obj = merchantData;
            for (var i = 0; i < keys.length - 1; i++) {
              var key = keys[i].slice(1, -1);
              if (obj[key] === undefined) {
                var nextKey = keys[i + 1] ? keys[i + 1].slice(1, -1) : '';
                obj[key] = /^\d+$/.test(nextKey) ? [] : {};
              }
              obj = obj[key];
            }
            var lastKey = keys[keys.length - 1].slice(1, -1);
            if (lastKey === '') {
              // Array notation (e.g. name="...[show_pages][]") — push into parent array.
              var parentKey = keys[keys.length - 2].slice(1, -1);
              var parentObj = merchantData;
              for (var j = 0; j < keys.length - 2; j++) {
                parentObj = parentObj[keys[j].slice(1, -1)];
              }
              if (!Array.isArray(parentObj[parentKey])) {
                parentObj[parentKey] = [];
              }
              parentObj[parentKey].push(field.value);
            } else {
              obj[lastKey] = field.value;
            }
          } else {
            keepIndexes.push(index);
          }
        });

        // Ensure unchecked checkboxes inside hydrated FC rows are saved as 0.
        // serializeArray() omits unchecked checkboxes entirely, which would
        // silently drop the field from the JSON payload and lose the "off" state.
        $('.merchant-flexible-content .layout:not([data-deferred])').each(function () {
          $(this).find('.layout-body input[type="checkbox"]:not(:checked)').each(function () {
            var name = $(this).attr('name') || '';
            if (name.indexOf('merchant[') !== 0) {
              return;
            }

            // Skip checkbox_multiple (name ends with []) — absence = empty array.
            if (/\[\]$/.test(name)) {
              return;
            }
            var keys = name.match(/\[([^\]]*)\]/g);
            if (!keys) {
              return;
            }
            var obj = merchantData;
            for (var i = 0; i < keys.length - 1; i++) {
              var key = keys[i].slice(1, -1);
              if (obj[key] === undefined) {
                obj[key] = {};
              }
              obj = obj[key];
            }
            var lastKey = keys[keys.length - 1].slice(1, -1);
            if (obj[lastKey] === undefined) {
              obj[lastKey] = 0;
            }
          });
        });

        // Merge deferred (non-hydrated) layout row data into merchantData.
        // These rows have no form inputs, only a data-fields-json attribute.
        $('.merchant-flexible-content .layout[data-deferred="1"]').each(function () {
          var jsonStr = $(this).attr('data-fields-json');
          if (!jsonStr) {
            return;
          }
          var deferredData;
          try {
            deferredData = JSON.parse(jsonStr);
          } catch (e) {
            return;
          }
          var $control = $(this).closest('.merchant-flexible-content-control');
          var fcFieldId = $control.attr('data-id');
          var rowIndex = parseInt($(this).find('.layout-count').text(), 10) - 1;
          if (!merchantData[fcFieldId]) {
            merchantData[fcFieldId] = {};
          }

          // Build the row data from deferred values + layout/flexible_id from hidden inputs.
          var rowData = $.extend({}, deferredData.values || {});
          rowData.layout = $(this).attr('data-type') || '';
          rowData.flexible_id = $(this).find('.flexible-id').val() || '';
          merchantData[fcFieldId][rowIndex] = rowData;
        });
        var kept = keepIndexes.map(function (i) {
          return arr[i];
        });
        kept.push({
          name: 'merchant_json_payload',
          value: JSON.stringify(merchantData)
        });
        arr.length = 0;
        kept.forEach(function (item) {
          arr.push(item);
        });
      },
      success: function success() {
        $ajaxHeader.removeClass('merchant-show');
        merchant.show_save = false;

        // Module Alert after Ajax Save
        if (!$('.merchant-module-action').hasClass('merchant-enabled')) {
          var $moduleAlert = $('.merchant-module-alert');
          $moduleAlert.addClass('merchant-show');
          $(document).off('click.merchant-alert-close');
          $(document).on('click.merchant-alert-close', function (e) {
            if (!$(e.target).closest('.merchant-module-alert-wrapper').length) {
              $moduleAlert.removeClass('merchant-show');
              $(document).off('click.merchant-alert-close');
            }
          });
        }
        $(document).trigger('save.merchant', [currentModule]);
      }
    });
    var $disableModuleSubmitBtn = $('.merchant-module-question-answer-button');
    var $disableModuleTextField = $('.merchant-module-question-answer-textarea');
    $disableModuleTextField.on('input', function () {
      $disableModuleSubmitBtn.prop('disabled', $(this).val().trim() === '');
    });
    $disableModuleSubmitBtn.on('click', function (e) {
      e.preventDefault();
      var message = $disableModuleTextField.val();
      if (!message.trim()) {
        alert('Please provide the required information.');
        return;
      }
      var $button = $(this);
      $('.merchant-module-question-answer-dropdown').removeClass('merchant-show');
      $('.merchant-module-question-thank-you-dropdown').addClass('merchant-show');
      window.wp.ajax.post('merchant_module_feedback', {
        subject: $disableModuleTextField.attr('data-subject'),
        message: message,
        module: $button.closest('.merchant-module-action').find('.merchant-module-page-button-action-activate').data('module'),
        nonce: window.merchant.nonce
      });
    });
    $('.merchant-module-page-button-action-activate').on('click', function (e) {
      e.preventDefault();
      if ($(this).hasClass('merchant-module-deactivated-by-bp')) {
        return false;
      }
      $('.merchant-module-question-list-dropdown').removeClass('merchant-show');
      $('.merchant-module-question-answer-dropdown').removeClass('merchant-show');
      $('.merchant-module-question-answer-form').removeClass('merchant-show');
      $('.merchant-module-question-answer-title').removeClass('merchant-show');
      $('.merchant-module-question-thank-you-dropdown').removeClass('merchant-show');
      $('.merchant-module-question-answer-textarea').val('');
      window.wp.ajax.post('merchant_module_activate', {
        module: $(this).data('module'),
        nonce: window.merchant.nonce
      }).done(function () {
        $('body').removeClass('merchant-module-disabled').addClass('merchant-module-enabled');
        $('.merchant-module-action').addClass('merchant-enabled');
      });
    });
    $('.merchant-module-page-button-action-deactivate').on('click', function (e) {
      e.preventDefault();
      window.wp.ajax.post('merchant_module_deactivate', {
        module: $(this).data('module'),
        nonce: window.merchant.nonce
      }).done(function () {
        $('body').removeClass('merchant-module-enabled').addClass('merchant-module-disabled');
        $('.merchant-module-action').removeClass('merchant-enabled');
        $('.merchant-module-question-list-dropdown').addClass('merchant-show');
      });
    });
    $('.merchant-module-question-list-dropdown li').on('click', function (e) {
      $disableModuleSubmitBtn.prop('disabled', $disableModuleTextField.val().trim() === '');
      var $question = $(this);
      var target = $question.data('answer-target');
      var $answer = $('[data-answer-title="' + target + '"]');
      if ($answer.length) {
        $answer.addClass('merchant-show').siblings().removeClass('merchant-show');
        $('.merchant-module-question-answer-dropdown').addClass('merchant-show');
        $('.merchant-module-question-answer-textarea').attr('data-subject', $question.text().trim());
      } else {
        $('.merchant-module-question-thank-you-dropdown').addClass('merchant-show');
        $('.merchant-module-question-answer-dropdown').removeClass('merchant-show');
      }
      $('.merchant-module-question-answer-textarea').val('');
      $('.merchant-module-question-list-dropdown').removeClass('merchant-show');
    });
    $('.merchant-module-dropdown-close').on('click', function (e) {
      e.preventDefault();
      $(this).closest('.merchant-module-dropdown').removeClass('merchant-show');
    });
    $('.merchant-module-page-button-deactivate').on('click', function (e) {
      e.preventDefault();
      var $button = $(this);
      var $dropdown = $('.merchant-module-deactivate-dropdown');
      $dropdown.toggleClass('merchant-show');
      $(document).off('click.merchant-close');
      $(document).on('click.merchant-close', function (e) {
        if (!$(e.target).closest('.merchant-module-deactivate').length) {
          $dropdown.removeClass('merchant-show');
          $(document).off('click.merchant-close');
        }
      });
    });

    // Create a function that initializes a single range or all ranges in a context for range field
    function initMerchantRange() {
      var rangeFields = $(document).find('.merchant-range');
      if (rangeFields.length === 0) {
        return;
      }
      rangeFields.each(function () {
        var $range = $(this);
        var $rangeInput = $range.find('.merchant-range-input');
        var $numberInput = $range.find('.merchant-range-number-input');
        $rangeInput.on('change input merchant.range merchant-init.range', function (e) {
          var $range = $(this);
          var value = (e.type === 'merchant' ? $numberInput.val() : $range.val()) || 0;
          var min = $range.attr('min') || 0;
          var max = $range.attr('max') || 1;
          var percentage = (value - min) / (max - min) * 100;
          if ($('body').hasClass('rtl')) {
            $range.css({
              'background': 'linear-gradient(to left, #3858E9 0%, #3858E9 ' + percentage + '%, #ddd ' + percentage + '%, #ddd 100%)'
            });
          } else {
            $range.css({
              'background': 'linear-gradient(to right, #3858E9 0%, #3858E9 ' + percentage + '%, #ddd ' + percentage + '%, #ddd 100%)'
            });
          }
          $rangeInput.val(value);
          $numberInput.val(value);
        }).trigger('merchant-init.range');
        $numberInput.on('change input blur', function () {
          if ($rangeInput.hasClass('merchant-range-input')) {
            $rangeInput.val($(this).val()).trigger('merchant.range');
          }
        });
      });
    }

    // 1. Initialize on DOM ready (existing fields)
    initMerchantRange();
    $(document).on('click', '.merchant-module-page-setting-field-hidden-desc-trigger', function () {
      var $trigger = $(this);
      $trigger.toggleClass('expanded');
      var showText = $trigger.attr('data-show-text');
      var hiddenText = $trigger.attr('data-hidden-text');
      $(this).find('span:first').text($trigger.text() === showText ? hiddenText : showText);
      $(this).closest('.merchant-module-page-setting-field').find('.merchant-module-page-setting-field-hidden-desc').stop(true, true).slideToggle('fast');
    });
    var moduleBackup = {
      init: function init() {
        this.events();
      },
      events: function events() {
        $(document).on('click', '#download-backup-button', this.download.bind(this));
        $(document).on('click', '#restore-backup-button', this.restore.bind(this));
        $(document).on('change', '#merchant-backup-file', this.removeBackupFile.bind(this));
      },
      download: function download(e) {
        var self = this;
        e.preventDefault();
        var container = $('.merchant-module-page-setting-fields .backup-section');
        self.hideError(container);
        var module_id = $(e.target).attr('data-module-id');
        // ajax get
        $.ajax({
          url: merchant_admin_options.ajaxurl,
          type: 'GET',
          data: {
            action: 'merchant_get_module_settings',
            nonce: merchant_admin_options.ajaxnonce,
            module_id: module_id
          },
          beforeSend: function beforeSend() {
            self.showLoadingIndicator(container);
          },
          success: function success(response) {
            if (response.success) {
              var moduleSettings = response.data;
              var fileName = 'merchant-' + module_id + '-backup-' + new Date().toISOString().slice(0, 10) + '-' +
              //time
              new Date().getHours() + '-' + new Date().getMinutes() + '-' + new Date().getSeconds() + '.json';
              self.downloadJson(moduleSettings, fileName);
              self.hideLoadingIndicator(container);
            } else {
              self.displayError(response.data.message, container);
              self.hideLoadingIndicator(container);
            }
          },
          error: function error(xhr, status, _error) {
            self.displayError(_error, container);
            self.hideLoadingIndicator(container);
          }
        });
      },
      restore: function restore(e) {
        var self = this;
        e.preventDefault();
        var container = $('.merchant-module-page-setting-fields .restore-section');
        self.hideError(container);
        var module_id = $(e.target).attr('data-module-id');
        var file = $('.merchant-backup-file').prop('files')[0];

        // Check if file is selected and it's a JSON file
        if (file && file.type === 'application/json') {
          var reader = new FileReader();
          reader.onload = function (e) {
            // Send the raw file content instead of parsing it
            var moduleSettings = e.target.result;
            // AJAX post
            $.ajax({
              url: merchant_admin_options.ajaxurl,
              type: 'POST',
              data: {
                action: 'merchant_restore_module_settings',
                nonce: merchant_admin_options.ajaxnonce,
                module_id: module_id,
                module_settings: moduleSettings
              },
              beforeSend: function beforeSend() {
                self.showLoadingIndicator(container);
              },
              success: function success(response) {
                if (response.success) {
                  merchant.show_save = false;
                  window.location.href = response.data.redirect_url;
                } else {
                  self.displayError(response.data.message, container);
                }
                self.hideLoadingIndicator(container);
              },
              error: function error(xhr, status, _error2) {
                self.displayError(_error2, container);
                self.hideLoadingIndicator(container);
              }
            });
          };
          reader.readAsText(file);
        } else {
          self.displayError(merchant_admin_options.invalid_file, container);
        }
      },
      removeBackupFile: function removeBackupFile(e) {
        var _file$;
        var file = $(e.target);
        if (!file.length) {
          return;
        }
        var crossIcon = $('.backup-file-remove');
        if (((_file$ = file[0]) === null || _file$ === void 0 ? void 0 : _file$.files.length) > 0) {
          crossIcon.show();
        } else {
          crossIcon.hide();
        }
        crossIcon.on('click', function () {
          file.val('');
          $(this).hide();
        });
      },
      downloadJson: function downloadJson(data, filename) {
        if (_typeof(data) === 'object') {
          data = JSON.stringify(data);
        }
        var blob = new Blob([data], {
          type: "application/json"
        });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
      },
      displayError: function displayError(errorMsg, container) {
        container.append("<div class=\"error-message\"><p>".concat(errorMsg, "</p></div>"));
      },
      hideError: function hideError(container) {
        container.find('.error-message').remove();
      },
      showLoadingIndicator: function showLoadingIndicator(container) {
        container.find('.merchant-loading-spinner').addClass('show');
      },
      hideLoadingIndicator: function hideLoadingIndicator(container) {
        container.find('.merchant-loading-spinner').removeClass('show');
      }
    };
    moduleBackup.init();

    // Add support for toggle field inside flexible content.
    var flexibleToggleField = {
      init: function init(field) {
        this.events();
      },
      events: function events() {
        $(document).on('click', '.merchant-flexible-content .merchant-toggle-switch .toggle-switch-label span', function () {
          var checkBox = $(this).closest('.merchant-toggle-switch').find('.toggle-switch-checkbox');
          checkBox.prop('checked', !checkBox.prop('checked'));
        }).trigger('merchant.change');
      }
    };
    var dateTimePickerField = {
      init: function init() {
        this.initiate_datepicker();
        this.events();
      },
      initiate_datepicker: function initiate_datepicker() {
        var elements = $('.merchant-module-page-setting-field .merchant-datetime-field');
        if (elements.length === 0) {
          return;
        }
        elements.each(function (index) {
          var input = $(this).find('input'),
            options = {
              locale: JSON.parse(merchant_datepicker_locale),
              selectedDates: input.val() ? [new Date(input.val())] : [],
              onSelect: function onSelect(_ref) {
                var date = _ref.date,
                  formattedDate = _ref.formattedDate,
                  datepicker = _ref.datepicker;
                if (typeof formattedDate === "undefined") {
                  // allow removing date
                  // input.val('');
                  datepicker.$el.value = '';
                }
                input.trigger('change.merchant');
                input.trigger('change.merchant-datepicker', [formattedDate, input, options, index]);
              }
            },
            fieldOptions = $(this).data('options');
          // add buttons to fieldOptions
          fieldOptions.buttons = ['clear'];
          if (fieldOptions) {
            if (fieldOptions.minDate !== undefined && fieldOptions.minDate === 'today') {
              fieldOptions.minDate = new Date();
              if (fieldOptions.timeZone !== undefined && fieldOptions.timeZone !== '') {
                fieldOptions.minDate = new Date(fieldOptions.minDate.toLocaleString('en-US', {
                  timeZone: fieldOptions.timeZone
                }));
              }
            }
            options = Object.assign(options, fieldOptions);
          }
          var datepickerObj = new AirDatepicker(input.getPath(), options);
          input.attr('readonly', true);
          $(document).trigger('initiated.merchant-datepicker', [datepickerObj, input, options, index]);
        });
      },
      events: function events() {
        var self = this;
        $(document).on('merchant-flexible-content-added', function () {
          self.initiate_datepicker();
        });
      }
    };
    dateTimePickerField.init();

    // Sortable.
    var SortableField = {
      init: function init(field) {
        this.events();
      },
      events: function events() {
        var self = this;
        $('.merchant-sortable').each(function () {
          var field = $(this),
            input = field.find('.merchant-sortable-input');

          // Init sortable.
          $(field.find('ul.merchant-sortable-list').first()).sortable({
            // Update value when we stop sorting.
            update: function update() {
              input.val(self.sortableGetNewVal(field)).trigger('change.merchant');
            }
          }).disableSelection().find('li').each(function () {
            // Enable/disable options when we click on the eye of Thundera.
            $(this).find('i.visibility').click(function () {
              $(this).toggleClass('dashicons-visibility-faint').parents('li:eq(0)').toggleClass('invisible');
            });
          }).click(function () {
            // Update value when click in the eye.
            if ($(event.target).hasClass('dashicons-visibility')) {
              input.val(self.sortableGetNewVal(field)).trigger('change.merchant');
            }
          });
        });
      },
      sortableGetNewVal: function sortableGetNewVal(field) {
        var items = $(field.find('li'));
        var newVal = [];
        _.each(items, function (item) {
          if (!$(item).hasClass('invisible')) {
            newVal.push($(item).data('value'));
          }
        });
        return JSON.stringify(newVal);
      }
    };

    // Initialize Sortable.
    SortableField.init();
    flexibleToggleField.init();

    // When adding/duplicating new item, refresh sorting
    $(document).on('merchant-flexible-content-added', function (e, $layout) {
      var $sortableWrapper = $layout.find('.merchant-sortable-repeater-control');
      var $sortableElement = $sortableWrapper.find('.merchant-sortable-repeater.sortable');
      SortableRepeaterField.makeFieldsSortable($sortableElement);
    });

    // Sortable Repeater.
    var SortableRepeaterField = {
      init: function init(field) {
        var self = this;

        // Update the values for all our input fields and initialise the sortable repeater.
        $('.merchant-sortable-repeater-control').each(function () {
          // If there is an existing customizer value, populate our rows
          var defaultValuesArray = JSON.parse($(this).find('.merchant-sortable-repeater-input').val());
          var numRepeaterItems = defaultValuesArray.length;
          if (numRepeaterItems > 0) {
            // Add the first item to our existing input field
            $(this).find('.repeater-input').val(defaultValuesArray[0]);

            // Create a new row for each new value
            if (numRepeaterItems > 1) {
              // var i;
              for (var i = 1; i < numRepeaterItems; ++i) {
                self.appendRow($(this), defaultValuesArray[i]);
              }
            }
          }

          // Todo: remove
          // Make our Repeater fields sortable. Doesn't work with flexible content
          // if (!$(this).hasClass('disable-sorting')) {
          //     $(this).find('.merchant-sortable-repeater.sortable').sortable({
          //         update: function (event, ui) {
          //             self.getAllInputs($(this).parent());
          //         }
          //     });
          // }
        });

        // Events.
        this.events();
      },
      events: function events() {
        var self = this;

        // Remove item starting from its parent element
        $(document).on('click', '.merchant-sortable-repeater.sortable .customize-control-sortable-repeater-delete', function (event) {
          event.preventDefault();
          $(this).parent().slideUp('fast', function () {
            var parentContainer = $(this).parent().parent();
            $(this).remove();
            self.getAllInputs(parentContainer);
          });
          $(document).trigger('merchant-sortable-repeater-item-deleted');
        });

        // Add new item
        $(document).on('click', '.customize-control-sortable-repeater-add', function (event) {
          event.preventDefault();
          self.appendRow($(this).parent());
          self.getAllInputs($(this).parent());
        });

        // Refresh our hidden field if any fields change
        $(document).on('change', '.merchant-sortable-repeater.sortable', function () {
          self.getAllInputs($(this).parent());
        });
        $(document).on('focusout', '.merchant-sortable-repeater.sortable .repeater-input', function () {
          self.getAllInputs($(this).parent());
        });
      },
      /**
       * Append a new row to our list of elements.
       *
       */
      appendRow: function appendRow($element) {
        var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
        var newRow = '<div class="repeater" style="display:none"><input type="text" value="' + defaultValue + '" class="repeater-input" /><span class="dashicons dashicons-menu"></span><a class="customize-control-sortable-repeater-delete" href="#"><span class="dashicons dashicons-no-alt"></span></a></div>';
        $element.find('.sortable').append(newRow);
        var $newItem = $element.find('.sortable').find('.repeater:last');
        $newItem.slideDown('slow', function () {
          $(this).find('input').focus();
        });

        // Make Repeater fields sortable; Putting here works better with flexible content
        this.makeFieldsSortable($element.find('.sortable'));
        $(document).trigger('merchant-sortable-repeater-item-added', [$newItem, $element.find('.sortable')]);
      },
      makeFieldsSortable: function makeFieldsSortable($sortableElement) {
        if (!$sortableElement.hasClass('disable-sorting')) {
          $sortableElement.sortable({
            update: function update(event, ui) {
              SortableRepeaterField.getAllInputs($sortableElement.parent());
            }
          });
        }
      },
      /**
       * Get the values from the repeater input fields and add to our hidden field.
       *
       */
      getAllInputs: function getAllInputs($element) {
        var inputValues = $element.find('.repeater-input').map(function () {
          return $(this).val();
        }).toArray();

        // Keep one empty item if all deleted.
        if (!inputValues.length) {
          inputValues.push('');
        }

        // Add all the values from our repeater fields to the hidden field (which is the one that actually gets saved)
        $element.find('.merchant-sortable-repeater-input').val(JSON.stringify(inputValues));
        // Important! Make sure to trigger change event so Customizer knows it has to save the field
        $element.find('.merchant-sortable-repeater-input').trigger('change');
        $element.find('.merchant-sortable-repeater-input').trigger('sortable.repeater.change');
      }
    };

    // Initialize Sortable Repeater.
    SortableRepeaterField.init();
    var GroubField = {
      init: function init() {
        var self = this;
        self.initAccordion();
        self.initFlag();
      },
      initAccordion: function initAccordion() {
        var self = this;
        $('.merchant-group-field.has-accordion').each(function () {
          var element = $(this);
          element.accordion({
            collapsible: true,
            header: "> .title-area",
            heightStyle: "content",
            active: element.hasClass('open') ? 0 : false
          });
        });
      },
      initFlag: function initFlag() {
        $('.merchant-group-field.has-flag').each(function () {
          var element = $(this);
          var field_id = element.data('id');
          var status_field = element.find(".merchant-field-".concat(field_id, "_status select"));
          var selected_value = status_field.val();
          var selected_label = status_field.find('option:selected').text();
          var status_element = element.find('.field-status');
          status_element.removeClass('hidden active inactive').text(selected_label).addClass(selected_value);
        });
      }
    };
    var ReviewsSelector = {
      accordion: null,
      activePopupContainer: null,
      // Initialize the Reviews Selector
      init: function init() {
        var self = this;
        self.initAccordion();
        self.events();
        // Skip the flexible content hidden elements that will be cloned when initialize the sortable reviews functionality
        self.makeSelectedReviewsSortable($('.merchant-reviews-selector').not('.layouts .merchant-reviews-selector'));
      },
      // Set up event listeners
      events: function events() {
        var self = this;
        $(document).on('input', '.merchant-reviews-selector .products-search', self.ajaxSearch.bind(self));
        $(document).on('change', '.merchant-reviews-selector .product-review input[type="checkbox"]', self.toggleReview.bind(self));
        $(document).on('click', '.merchant-reviews-selector .review-photo img', self.requestReviewImages.bind(self));
        $(document).on('click', '.review-photos-popup .overlay', self.dismissImagesGallery.bind(self));
        $(document).on('click', '.merchant-reviews-selector .product-reviews-load-more button', self.loadMoreReviews.bind(self));
        $(document).on('click', '.merchant-reviews-selector .popup-trigger', self.initPopUp.bind(self));
        $(document).on('click', '.merchant-reviews-selector .popup-header .close, .merchant-reviews-selector > .overlay', self.dismissPopUp.bind(self));
        $(document).on('click', '.merchant-reviews-selector .product-review-delete', self.deleteSelectedReview.bind(self));
        // Listen to escape key
        $(document).on('keyup', function (e) {
          if (e.key === "Escape") {
            var isGallery = self.dismissImagesGallery();
            if (isGallery) {
              return;
            }
            self.dismissPopUp(self);
          }
        });
        $(document).on('merchant-flexible-content-added', function (e, layout) {
          self.makeSelectedReviewsSortable();
          self.initAccordion();
        });
      },
      // Make selected reviews sortable
      makeSelectedReviewsSortable: function makeSelectedReviewsSortable() {
        var container = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : $(document).find('.merchant-reviews-selector').not('.layouts .merchant-reviews-selector');
        var self = this;
        var selectedReviews = container.find('.selected-reviews .product-reviews');
        selectedReviews.sortable({
          axis: 'y',
          cursor: 'move',
          helper: 'original',
          handle: '.product-review-move',
          cancel: '',
          placeholder: 'placeholder',
          // Add a class for styling
          update: function update(event, ui) {
            var container = ui.item.closest('.merchant-reviews-selector');
            self.saveModifications(container);
          },
          start: function start(event, ui) {
            ui.placeholder.height(ui.item.height()); // Match height
          }
        });
        // $('.product-reviews').sortable('refresh');
        // console.log('selectedReviews',selectedReviews.sortable('instance'))
      },
      // Delete selected review
      deleteSelectedReview: function deleteSelectedReview(e) {
        var self = this;
        var review = $(e.target).closest('.product-review');
        var container = review.closest('.merchant-reviews-selector');
        review.remove();
        self.saveModifications(container);
        self.updateSelectedReviewsCheckboxState(container);
        self.countSelectedReviewsInProduct(container);
      },
      // Handle AJAX search for reviews
      ajaxSearch: function ajaxSearch(e) {
        var self = this;
        var searchField = $(e.target);
        var container = searchField.closest('.merchant-reviews-selector');
        var header = container.find('.popup-header');

        // check value length > 3
        if (searchField.val().length === 0 || searchField.val().length >= 3) {
          $.ajax({
            url: merchant_admin_options.ajaxurl,
            type: 'POST',
            data: {
              action: 'merchant_search_reviews',
              search: searchField.val(),
              nonce: merchant_admin_options.ajaxnonce
            },
            beforeSend: function beforeSend() {
              header.addClass('loading');
              self.destroyAccordion(container);
            },
            success: function success(response) {
              if (response.success) {
                header.removeClass('popup-error');
                container.find('.products-search-results').html(response.data);
                self.initAccordion(container);
              } else {
                header.addClass('popup-error');
              }
            },
            complete: function complete() {
              header.removeClass('loading');
            },
            error: function error() {
              header.addClass('popup-error');
            }
          });
        }
      },
      // Load more reviews
      loadMoreReviews: function loadMoreReviews(e) {
        var self = this;
        var product = $(e.target).closest('.product-item');
        var offset = product.find('.product-review').length;
        $.ajax({
          url: merchant_admin_options.ajaxurl,
          type: 'POST',
          data: {
            action: 'merchant_load_more_reviews',
            product_id: product.attr('data-id'),
            offset: offset,
            nonce: merchant_admin_options.ajaxnonce
          },
          beforeSend: function beforeSend() {
            product.addClass('loading');
          },
          success: function success(response) {
            if (response.success) {
              product.find('.product-reviews .reviews-wrapper').append(response.data.reviews);
              if (response.data.load_more === '') {
                self.hideLoadMoreButton(product);
              }
              self.updateSelectedReviewsCheckboxState(product.closest('.merchant-reviews-selector'));
              self.countSelectedReviewsInProduct(product.closest('.merchant-reviews-selector'));
            }
          },
          complete: function complete() {
            product.removeClass('loading');
          }
        });
      },
      // Hide load more button
      hideLoadMoreButton: function hideLoadMoreButton(product) {
        product.find('.product-reviews-load-more').remove();
      },
      // Initialize the popup
      initPopUp: function initPopUp(e) {
        var self = this;
        var container = $(e.target).closest('.merchant-reviews-selector');
        var popup = container.find('.selector-popup');
        var overlay = container.find('.overlay');
        popup.addClass('active');
        overlay.addClass('active');
        self.activePopupContainer = container;
      },
      // Dismiss the popup
      dismissPopUp: function dismissPopUp() {
        var self = this;
        var container = self.activePopupContainer;
        if (container === null) {
          return;
        }
        var popup = container.find('.selector-popup');
        var overlay = container.find('.overlay');
        popup.removeClass('active');
        overlay.removeClass('active');
        self.activePopupContainer = null;
      },
      // Initialize the accordion
      initAccordion: function initAccordion() {
        var container = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : $(document).find('.merchant-reviews-selector');
        var self = this;
        container.each(function () {
          var elements = $(this).find('.popup-content .product-item.product-item-has-reviews');
          elements.each(function () {
            elements.accordion({
              collapsible: true,
              heightStyle: "content",
              icons: false,
              // Disable icons
              active: elements.hasClass('opened') ? 0 : false,
              activate: function activate(event, ui) {
                // Check if a new panel is being activated
                if (ui.newPanel.length) {
                  // Add 'opened' class to the parent product item
                  ui.newPanel.parent().addClass('opened');
                } else {
                  // Remove 'opened' class from all product items
                  elements.removeClass('opened');
                }
              }
            });
          });
          elements.find('a').on('click', function (e) {
            e.stopPropagation(); // Prevent event from bubbling up to the accordion header
          });
          self.updateSelectedReviewsCheckboxState($(this));
          self.countSelectedReviewsInProduct($(this));
        });
      },
      // Destroy the accordion
      destroyAccordion: function destroyAccordion(container) {
        var elements = container.find('.selector-popup .product-item.product-item-has-reviews');
        elements.each(function () {
          if ($(this).hasClass('ui-accordion')) {
            // Check if it has the accordion class
            $(this).accordion('destroy');
          }
        });
      },
      // Toggle review selection
      toggleReview: function toggleReview(e) {
        var self = this;
        var checked = $(e.target).prop('checked');
        var reviewsFieldContainer = $(e.target).closest('.merchant-reviews-selector');
        var review = $(e.target).closest('.product-review');
        var reviewId = review.attr('data-id');
        var selectedReviews = reviewsFieldContainer.find('.selected-reviews .product-reviews');
        var selectedReview = selectedReviews.find(".product-review[data-id=\"".concat(reviewId, "\"]"));
        if (checked) {
          if (selectedReview.length === 0) {
            var newReview = review.clone();
            selectedReviews.append(newReview);
            self.makeSelectedReviewsSortable();
          }
        } else {
          selectedReview.remove();
        }
        setTimeout(function () {
          self.saveModifications(reviewsFieldContainer);
        }, 150);
      },
      // Save modifications to the selected reviews
      saveModifications: function saveModifications(container) {
        var self = this;
        var reviewsSavedIdsField = container.find('.review-saved-ids');
        var selectedReviews = container.find('.selected-reviews .product-reviews');
        var reviews = selectedReviews.find('.product-review');
        var reviewsIds = [];
        reviews.each(function () {
          var id = $(this).attr('data-id');
          reviewsIds.push(id);
        });
        reviewsSavedIdsField.val(reviewsIds.join(','));
        reviewsSavedIdsField.trigger('change');
        // reviewsSavedIds;
        self.updateSelectedReviewsCheckboxState(container);
        self.countSelectedReviewsInProduct(container);
      },
      // Mark selected reviews checkbox
      updateSelectedReviewsCheckboxState: function updateSelectedReviewsCheckboxState(container) {
        var popupContent = container.find('.selector-popup');
        var reviewsSavedIds = container.find('.review-saved-ids');
        var reviews = popupContent.find('.product-review');
        var reviewsIds = reviewsSavedIds.val().split(',');
        reviews.each(function () {
          var reviewId = $(this).attr('data-id');
          var checkbox = container.find(".product-review[data-id=\"".concat(reviewId, "\"] input[type=\"checkbox\"]"));
          if (reviewsIds.includes(reviewId)) {
            checkbox.prop('checked', true);
          } else {
            checkbox.prop('checked', false);
          }
        });
      },
      // Count selected reviews in product
      countSelectedReviewsInProduct: function countSelectedReviewsInProduct(container) {
        var self = this;
        var products = container.find('.selector-popup .product-item');
        var reviewsSavedIds = container.find('.review-saved-ids');
        var reviewsIds = reviewsSavedIds.val().split(',');
        products.each(function () {
          var selectedReviews = 0;
          var product = $(this);
          var productReviews = product.find('.product-review');
          productReviews.each(function () {
            var review = $(this);
            var reviewId = review.attr('data-id');
            if (reviewsIds.includes(reviewId)) {
              selectedReviews++;
            }
          });
          product.find('.selected-reviews-count .counter').text(selectedReviews);
        });
      },
      // Request review images
      requestReviewImages: function requestReviewImages(e) {
        var reviewContainer = $(e.target).closest('.product-review');
        var image = $(e.target);
        var reviewId = reviewContainer.attr('data-id');
        $.ajax({
          url: merchant_admin_options.ajaxurl,
          type: 'POST',
          data: {
            action: 'merchant_get_review_images',
            review_id: reviewId,
            nonce: merchant_admin_options.ajaxnonce
          },
          beforeSend: function beforeSend() {
            // Show loading spinner
            image.addClass('loading');
          },
          success: function success(response) {
            if (response.success) {
              $('body').append(response.data);
              setTimeout(function () {
                $('body').find('.review-photos-popup').addClass('active');
              }, 300);
            }
          },
          complete: function complete() {
            // Hide loading spinner
            image.removeClass('loading');
          }
        });
      },
      // Dismiss the images gallery
      dismissImagesGallery: function dismissImagesGallery() {
        var gallery = $('body').find('.review-photos-popup');
        if (gallery.length) {
          gallery.removeClass('active');
          setTimeout(function () {
            gallery.remove();
          }, 300);
          return true;
        }
        return false;
      }
    };

    // Flexible Content.
    var FlexibleContentField = {
      init: function init(field) {
        var self = this;

        // Update the values for all our input fields and initialise the sortable repeater.
        $('.merchant-flexible-content-control').each(function () {
          var hasAccordion = $(this).hasClass('has-accordion'),
            $content = $(this).find('.merchant-flexible-content');
          var campaignId = params.get('campaign_id');
          var campaignIndex = 0;

          // Find the campaignIndex based on campaignId
          if (campaignId) {
            $content.find('input.flexible-id').each(function (index) {
              if ($(this).val() === campaignId) {
                campaignIndex = index;
                return false;
              }
            });
          }
          if (hasAccordion) {
            $content.accordion({
              active: campaignIndex,
              // Open the campaign with campaign index
              collapsible: true,
              //header: "> div > .layout-header",
              header: function header(elem) {
                return elem.find('.layout__inner > .layout-header');
              },
              heightStyle: "content",
              beforeActivate: function beforeActivate(event, ui) {
                // Hydrate deferred layout before the accordion animation
                // so the panel opens with content already rendered.
                if (ui.newPanel.length) {
                  var $layout = ui.newPanel.closest('.layout');
                  if ($layout.attr('data-deferred') === '1') {
                    self.hydrateLayout($layout);
                  }
                }
              }
            }).sortable({
              axis: 'y',
              cursor: 'move',
              helper: 'original',
              handle: '.customize-control-flexible-content-move',
              stop: function stop(event, ui) {
                $content.trigger('merchant.sorted');
                self.refreshNumbers($content);
                $content.accordion("refresh");
              }
            });
          } else {
            $content.sortable({
              axis: 'y',
              cursor: 'move',
              helper: 'original',
              handle: '.customize-control-flexible-content-move',
              stop: function stop(event, ui) {
                $content.trigger('merchant.sorted');
                self.refreshNumbers($content);
                $content.accordion("refresh");
              }
            });
          }
        });
        this.updateLayoutTitle();
        this.updateDiscountPercentMaxVal();
        // Events.
        this.events();
      },
      updateLayoutTitle: function updateLayoutTitle() {
        // Update the title for all layout header.
        $('.merchant-flexible-content .layout').each(function () {
          var title = $(this).find('.layout-title[data-title-field]');
          if (title.length) {
            var input = $(this).find('.layout-body .merchant-field-' + title.data('title-field') + ' input');
            input.on('change keyup', function () {
              title.text($(this).val());
            });
            title.text(input.val());
          }
        });
      },
      updateDiscountPercentMaxVal: function updateDiscountPercentMaxVal() {
        $('.merchant-flexible-content .layout').each(function () {
          var $layout = $(this);
          var $discountType = $layout.find('.merchant-module-page-setting-field[data-id="discount_type"]');
          var $discountVal = $layout.find('.merchant-module-page-setting-field[data-id="discount_value"]');
          $discountVal = $discountVal.length ? $discountVal : $layout.find('.merchant-module-page-setting-field[data-id="discount"]');
          $discountVal = $discountVal.length ? $discountVal : $layout.find('.merchant-module-page-setting-field[data-id="discount_amount"]');
          var checkedValue = $discountType.find('input:checked').val();

          // Set/Remove max value based on the discount type.
          checkedValue === 'percentage_discount' || checkedValue === 'percentage' ? $discountVal.find('input').attr('max', 100) : $discountVal.find('input').removeAttr('max');
        });
        $('.merchant-module-page-setting-fields');
      },
      generateUUID: function generateUUID() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
          var r = Math.random() * 16 | 0;
          var v = c === 'x' ? r : r & 0x3 | 0x8;
          return v.toString(16);
        });
      },
      events: function events() {
        var self = this;
        $(document).on('change', '.merchant-module-page-setting-field[data-id="discount_type"] input', function () {
          self.updateDiscountPercentMaxVal();
        });

        // Update "selected" attribute on select. It's Required for duplicating layouts.
        $(document).on('change', 'select', function () {
          var selectedValue = $(this).val();
          var isMultiple = $(this).prop('multiple');
          $(this).find('option').each(function () {
            var currentValue = $(this).val();
            var isSelected = isMultiple ? selectedValue.includes(currentValue) : currentValue === selectedValue;
            $(this).attr('selected', isSelected);
          });
        });
        $('.customize-control-flexible-content-add-button').on('click', function (event) {
          event.preventDefault();
          event.stopImmediatePropagation();
          if ($(this).parent().find('.customize-control-flexible-content-add-list a').length === 1) {
            // If there is only one layout, trigger click on it.
            $(this).parent().find('.customize-control-flexible-content-add-list a').trigger('click');
            return;
          }
          $(this).parent().find('.customize-control-flexible-content-add-list').toggleClass('active');
        });

        // Add new item
        $(document).on('click', '.customize-control-flexible-content-add', function (event) {
          event.preventDefault();
          event.stopImmediatePropagation();
          var $field = $('.merchant-flexible-content-control[data-id=' + $(this).data('id') + ']');
          var $layouts = $field.find('.layouts');
          var $selected = $(this).data('layout');
          var $layout = $layouts.find('.layout[data-type=' + $selected + ']').clone(true);
          var $content = $field.find('.merchant-flexible-content');
          var $items = $content.find('.layout');
          var uuid = self.generateUUID();
          $layout.find('input, select, textarea').each(function () {
            if ($(this).data('name')) {
              $(this).attr('name', $(this).data('name').replace('0', $items.length));
            }
            if ($(this).is(':checkbox, :radio') && $(this).attr('checked')) {
              $(this).prop('checked', true);
            }
          });
          $layout.attr('data-layout-id', uuid);
          $layout.find('.layout-count').text($items.length + 1);
          $layout.find('.flexible-id').val(uuid);
          $content.append($layout);
          $content.removeClass('empty');
          $(this).parent().removeClass('active');
          if ($layout.find('.merchant-module-page-setting-field-upload').length) {
            initUploadField($layout.find('.merchant-module-page-setting-field-upload'));
          }
          if ($layout.find('.merchant-module-page-setting-field-select_ajax').length) {
            initSelectAjax($layout.find('.merchant-module-page-setting-field-select_ajax'));
          }
          var parentDiv = $(this).closest('.merchant-flexible-content-control'),
            hasAccordion = parentDiv.hasClass('has-accordion');
          if (hasAccordion) {
            parentDiv.find('.merchant-flexible-content').accordion("refresh");
            // Expand the accordion last added
            parentDiv.find('.merchant-flexible-content').accordion("option", "active", -1);
          }
          GroubField.init();
          $(document).trigger('merchant-flexible-content-added', [$layout]);
          initMerchantRange();
          self.updateLayoutTitle();
          self.updateDiscountPercentMaxVal();
          $('.merchant-module-page-content').trigger('change.merchant');
        });

        // Duplicate item
        $(document).on('click', '.customize-control-flexible-content-duplicate', function (event) {
          event.preventDefault();
          event.stopImmediatePropagation();
          var $duplicateBtn = $(this);
          var $flexibleContentWrapper = $duplicateBtn.closest('.merchant-flexible-content-control[data-id=' + $duplicateBtn.data('id') + ']');
          var $flexibleContent = $flexibleContentWrapper === null || $flexibleContentWrapper === void 0 ? void 0 : $flexibleContentWrapper.find('.merchant-flexible-content');
          if (!$flexibleContentWrapper.length || !$flexibleContent.length) {
            return;
          }
          var $sourceLayout = $duplicateBtn.closest('.layout');
          if (!$sourceLayout.length) {
            return;
          }
          $sourceLayout.find('.layout-actions__inner').hide();

          // Clone the layout without data & events.
          var $clonedLayout = $sourceLayout.clone();
          var $items = $flexibleContent.find('.layout');
          var index = $sourceLayout.find('.layout-count').text();
          var uuid = self.generateUUID();
          $clonedLayout.attr('data-layout-id', uuid);
          $clonedLayout.find('.flexible-id').val(uuid);
          $clonedLayout.find('input, select, textarea').each(function () {
            var $input = $(this);
            var inputName = $input.attr('name');
            if (inputName) {
              var prefix = inputName.split('[')[0];
              var indexPart = inputName.match(/\[(.*?)\]/g);
              if (indexPart && indexPart.length > 1) {
                indexPart[1] = '[' + index + ']';
                var newName = "".concat(prefix).concat(indexPart.join(''));
                $input.attr('name', newName);
              }
            }
          });

          // Find select2, Remove and Re-init again. select.select2('destroy') doesn't work.
          $clonedLayout.find('select').each(function () {
            if ($(this).hasClass('select2-hidden-accessible')) {
              $(this).removeClass('select2-hidden-accessible').removeAttr('data-live-search').removeAttr('data-select2-id').removeAttr('aria-hidden').removeAttr('tabindex');

              // Remove the existing dropdown
              $(this).nextAll('.select2-container').remove();

              // Re-init
              $(this).select2();
            }
          });

          // Removing copied style to make the accordion work properly.
          $clonedLayout.find('.layout-body').removeAttr('style');

          // Append the cloned layout right after the source one with fadeIn effect.
          $clonedLayout.hide();
          $clonedLayout.insertAfter($sourceLayout);
          $clonedLayout.fadeIn();
          if ($clonedLayout.find('.merchant-module-page-setting-field-upload').length) {
            initUploadField($clonedLayout.find('.merchant-module-page-setting-field-upload'));
          }
          if ($clonedLayout.find('.merchant-module-page-setting-field-select_ajax').length) {
            initSelectAjax($clonedLayout.find('.merchant-module-page-setting-field-select_ajax'));
          }
          self.refreshNumbers($flexibleContent);
          $(document).trigger('merchant-flexible-content-added', [$clonedLayout]);
          if ($flexibleContentWrapper.hasClass('has-accordion')) {
            $flexibleContent.accordion('refresh');
          }
          self.updateLayoutTitle();
          GroubField.init();
          $('.merchant-module-page-content').trigger('change.merchant');
        });

        // Delete item
        $(document).on('click', '.customize-control-flexible-content-delete', function (event) {
          event.preventDefault();
          var $item = $(this).closest('.layout');
          var $content = $item.parent();
          $item.remove();
          if ($content.find('.layout').length === 0) {
            $content.addClass('empty');
          }
          self.refreshNumbers($content);
          $(document).trigger('merchant-flexible-content-deleted', [$item]);
          var parentDiv = $(this).closest('.merchant-flexible-content-control'),
            hasAccordion = parentDiv.hasClass('has-accordion');
          if (hasAccordion) {
            parentDiv.find('.merchant-flexible-content').accordion("refresh");
          }
          $('.merchant-module-page-content').trigger('change.merchant');
        });

        // Toggle Actions(delete/duplicate)
        $(document).on('click', '.layout-actions__toggle', function (e) {
          e.preventDefault();

          // Hide other opened elements
          hideOtherActions($(this).closest('.layout'));

          // Toggle the current element
          $(this).closest('.layout-actions').find('.layout-actions__inner').stop().slideToggle(300);
        });

        // Hide Actions when collapse/open
        $(document).on('click', '.layout-header', function () {
          hideOtherActions($(this).closest('.layout'));
        });
        $(document).on('merchant-flexible-content-added', function (e, $layout) {
          hideOtherActions($layout);
        });
        function hideOtherActions($layout) {
          if ($layout && $layout.length) {
            $layout.siblings().find('.layout-actions__inner').slideUp(300);
          }
        }

        // Dismiss actions menu on click outside or Escape.
        $(document).on('click', function (e) {
          if (!$(e.target).closest('.layout-actions').length) {
            $('.layout-actions__inner').slideUp(300);
          }
        });
        $(document).on('keydown', function (e) {
          if (e.key === 'Escape') {
            $('.layout-actions__inner').slideUp(300);
          }
        });
      },
      /**
       * Hydrate a deferred layout row.
       *
       * Clones the hidden template for the layout type, populates field values
       * from the data-fields-json attribute, initialises all widgets, and
       * replaces the empty layout-body content with the fully rendered fields.
       *
       * @param {jQuery} $layout The .layout element with data-deferred="1".
       */
      hydrateLayout: function hydrateLayout($layout) {
        var jsonStr = $layout.attr('data-fields-json');
        if (!jsonStr) {
          return;
        }
        var data;
        try {
          data = JSON.parse(jsonStr);
        } catch (e) {
          return;
        }
        var layoutType = $layout.attr('data-type');
        var $control = $layout.closest('.merchant-flexible-content-control');
        var $template = $control.find('.layouts .layout[data-type="' + layoutType + '"]');
        if (!$template.length) {
          return;
        }

        // Clone the template's layout-body content (the rendered fields).
        var $clonedBody = $template.find('.layout-body').clone();

        // Determine the correct row index from layout-count.
        var rowIndex = parseInt($layout.find('.layout-count').text(), 10) - 1;
        var fieldId = $control.attr('data-id');

        // Fix name attributes: replace data-name with name, and index [0] with [rowIndex].
        $clonedBody.find('input, select, textarea').each(function () {
          var dataName = $(this).attr('data-name');
          if (dataName) {
            $(this).attr('name', dataName.replace('[0]', '[' + rowIndex + ']'));
            $(this).removeAttr('data-name');
          }
        });

        // Populate field values from the resolved data.
        this.populateFieldValues($clonedBody, data, fieldId, rowIndex);

        // Sync color picker preview boxes to reflect populated values.
        $clonedBody.find('.merchant-color').each(function () {
          var colorVal = $(this).find('.merchant-color-input').val();
          if (colorVal) {
            $(this).find('.merchant-color-picker').css('background-color', colorVal);
          }
        });

        // Sync range slider values from their sibling number inputs.
        // populateFieldValues only sets the number input (it has the name attribute);
        // the range slider (no name) still holds the template default.
        $clonedBody.find('.merchant-range').each(function () {
          var numVal = $(this).find('.merchant-range-number-input').val();
          if (numVal !== undefined && numVal !== '') {
            $(this).find('.merchant-range-input').val(numVal);
          }
        });

        // Swap the content of the layout-body (not the element itself)
        // to preserve jQuery UI accordion's internal panel reference.
        var $existingBody = $layout.find('.layout-body');
        $existingBody.empty().append($clonedBody.children());

        // Initialise widgets within the hydrated layout.
        if ($existingBody.find('.merchant-module-page-setting-field-upload').length) {
          initUploadField($existingBody.find('.merchant-module-page-setting-field-upload'));
        }
        if ($existingBody.find('.merchant-module-page-setting-field-select_ajax').length) {
          initSelectAjax($existingBody.find('.merchant-module-page-setting-field-select_ajax'));
        }

        // Trigger events to re-initialise fields_group, color pickers, conditions, etc.
        $(document).trigger('merchant-flexible-content-added', [$layout]);
        GroubField.init();
        initMerchantRange();

        // Init layout title binding.
        var $title = $layout.find('.layout-title[data-title-field]');
        if ($title.length) {
          var titleFieldId = $title.attr('data-title-field');
          var $titleInput = $layout.find('.layout-body .merchant-field-' + titleFieldId + ' input');
          $titleInput.on('change keyup', function () {
            $title.text($(this).val());
          });
        }

        // Init discount max val.
        this.updateDiscountPercentMaxVal();

        // Remove the deferred flag and clean up.
        $layout.removeAttr('data-deferred');
        $layout.removeAttr('data-fields-json');

        // Refresh accordion to re-sync panel references after DOM change.
        var $content = $control.find('.merchant-flexible-content');
        if ($content.data('ui-accordion')) {
          $content.accordion('refresh');
        }

        // Trigger condition checks for the newly hydrated panel.
        $(document).trigger('merchant-admin-check-fields');
        $(document).trigger('merchant-admin-check-color-fields');
      },
      /**
       * Populate field values from deferred data into a cloned template body.
       *
       * @param {jQuery} $body    The cloned .layout-body element.
       * @param {Object} data     The parsed data-fields-json object.
       * @param {string} fieldId  The flexible content field ID.
       * @param {number} rowIndex The row index for name attribute construction.
       */
      populateFieldValues: function populateFieldValues($body, data, fieldId, rowIndex) {
        var values = data.values || {};
        var selectOptions = data.select_options || {};
        var productOptions = data.product_options || {};
        var reviewOptions = data.review_options || {};

        // Populate simple field values.
        $body.find('input, select, textarea').each(function () {
          var $el = $(this);
          var name = $el.attr('name') || '';

          // Handle checkbox_multiple: name ends with [] (e.g. merchant[fc][0][show_pages][]).
          var arrayMatch = name.match(/\[([^\]]+)\]\[\]$/);
          if (arrayMatch) {
            var arrayKey = arrayMatch[1];
            var arrVal = values[arrayKey];
            if (Array.isArray(arrVal) && $el.is(':checkbox')) {
              $el.prop('checked', arrVal.indexOf($el.val()) !== -1);
            }
            return;
          }

          // Extract the field key from name="merchant[fieldId][rowIndex][fieldKey]".
          var match = name.match(/\[([^\]]+)\]$/);
          if (!match) {
            return;
          }
          var fieldKey = match[1];
          var val = values[fieldKey];
          if (val === undefined || val === null) {
            return;
          }
          if ($el.is(':checkbox') || $el.is(':radio')) {
            // For checkboxes/radios, check if this input's value matches.
            if ($el.val() === String(val) || val === '1' && $el.is(':checkbox')) {
              $el.prop('checked', true);
            }
          } else if ($el.is('select')) {
            $el.val(val);
          } else {
            $el.val(val);
          }
        });

        // Populate select_ajax pre-selected options.
        $.each(selectOptions, function (selectFieldId, options) {
          var $select = $body.find('[data-id="' + selectFieldId + '"] select');
          if (!$select.length) {
            return;
          }

          // Clear any existing options and add resolved ones.
          $select.empty();
          $.each(options, function (_, opt) {
            $select.append($('<option>').val(opt.id).text(opt.text).prop('selected', true));
          });
        });

        // Populate products_selector pre-selected products.
        $.each(productOptions, function (prodFieldId, products) {
          var $container = $body.find('[data-id="' + prodFieldId + '"] .merchant-products-search-container');
          if (!$container.length) {
            return;
          }
          var $preview = $container.find('.merchant-selected-products-preview ul');
          var ids = [];
          $preview.empty();
          $.each(products, function (_, product) {
            ids.push(product.id);

            // Build <li> matching the structure from Merchant_Admin_Ajax::product_data_li().
            var $li = $('<li>').addClass('product-item').attr('data-id', product.id).attr('data-name', product.title);
            if (product.image) {
              $li.append($('<span>').addClass('img').append($('<img>').attr('src', product.image).attr('alt', product.title).attr('width', 30).attr('height', 30)));
            }
            $li.append($('<span>').addClass('data').append($('<span>').addClass('name').text(product.title), $('<span>').addClass('info').html(product.price)));
            if (product.type || product.id) {
              var typeHtml = (product.type || '') + '<br>#' + product.id;
              var $typeSpan = $('<span>').addClass('type');
              if (product.edit_url) {
                $typeSpan.append($('<a>').attr('href', product.edit_url).attr('target', '_blank').html(typeHtml));
              } else {
                $typeSpan.html(typeHtml);
              }
              $li.append($typeSpan);
            }
            $li.append($('<span>').addClass('remove hint--left').attr('aria-label', 'Remove').html('&times;'));
            $preview.append($li);
          });
          $container.find('.merchant-selected-products').val(ids.join(','));
        });

        // Populate reviews_selector pre-selected reviews.
        $.each(reviewOptions, function (reviewFieldId, reviews) {
          var $container = $body.find('[data-id="' + reviewFieldId + '"] .merchant-reviews-selector');
          if (!$container.length) {
            return;
          }
          var $selectedList = $container.find('.selected-reviews .product-reviews');
          var ids = [];
          $selectedList.empty();
          $.each(reviews, function (_, review) {
            ids.push(review.id);
            var $reviewEl = $('<div>').addClass('product-review').attr('data-review-id', review.id);
            $reviewEl.append($('<span>').addClass('review-author').text(review.author));
            $reviewEl.append($('<span>').addClass('review-content').text(review.content));
            $reviewEl.append($('<span>').addClass('review-rating').text('★'.repeat(parseInt(review.rating) || 0)));
            $reviewEl.append($('<span>').addClass('review-product').text(review.product_name));
            $reviewEl.append($('<span>').addClass('product-review-delete').html('&times;'));
            $reviewEl.append($('<span>').addClass('product-review-move').html('⋮⋮'));
            $selectedList.append($reviewEl);
          });
          $container.find('.merchant-selected-reviews').val(ids.join(','));
        });

        // Handle fields_group and compound (e.g. hook_select) nested values.
        // Recursively walks the value tree to find and set inputs
        // at any nesting depth (e.g. fields_group → hook_select → hook_name).
        var _setNestedValues = function setNestedValues(obj, namePrefix) {
          $.each(obj, function (key, val) {
            if (_typeof(val) === 'object' && val !== null && !Array.isArray(val)) {
              // Recurse into nested objects.
              _setNestedValues(val, namePrefix + '[' + key + ']');
            } else if (Array.isArray(val)) {
              // checkbox_multiple inside a nested object: check boxes whose value is in the array.
              $body.find('[name="' + namePrefix + '[' + key + '][]"]').each(function () {
                if ($(this).is(':checkbox')) {
                  $(this).prop('checked', val.indexOf($(this).val()) !== -1);
                }
              });
            } else {
              var $field = $body.find('[name="' + namePrefix + '[' + key + ']"]');
              if ($field.length) {
                if ($field.is(':checkbox') || $field.is(':radio')) {
                  if ($field.val() === String(val) || val === '1' && $field.is(':checkbox')) {
                    $field.prop('checked', true);
                  }
                } else {
                  $field.val(val);
                }
              }
            }
          });
        };
        $.each(values, function (key, val) {
          if (_typeof(val) === 'object' && val !== null && !Array.isArray(val)) {
            _setNestedValues(val, 'merchant[' + fieldId + '][' + rowIndex + '][' + key + ']');
          }
        });
      },
      refreshNumbers: function refreshNumbers($content) {
        $content.find('.layout').each(function (index) {
          var $count = $(this).find('.layout-count').text();
          var $inputIndex = parseInt($count) - 1;
          $(this).find('.layout-count').text(index + 1);
          $(this).find('input, select, textarea').each(function () {
            if ($(this).attr('name')) {
              $(this).attr('name', $(this).attr('name').replace('[' + $inputIndex + ']', '[*refreshed*' + index + ']'));
            }
          });
        });
        $content.find('.layout').each(function (index) {
          $(this).find('input, select, textarea').each(function () {
            // We've added *refreshed* to the attribute name in the prior loop as refreshing the numbers in the attribute can cause
            // checked boxes to be unchecked due to similar attribute names during the change while sorting, within this loop we remove them
            var nameAttr = $(this).attr('name');
            if (nameAttr) {
              // Check if name attribute exists
              $(this).attr('name', nameAttr.replace('*refreshed*', ''));
            }
          });
        });
        $content.parent().find('input').trigger('change.merchant');
      }
    };

    // Initialize Flexible Content.
    FlexibleContentField.init();
    GroubField.init();
    ReviewsSelector.init();

    // Products selector.
    // Handle keyup event for the search input
    var debounceTimer;
    $(document).on('keyup', '.merchant-module-page-setting-field-products_selector .merchant-search-field', function () {
      clearTimeout(debounceTimer);
      var categories = [];
      var $excluded = $(this).closest('[data-id="excluded_products"]');
      if ($excluded.length) {
        var $layout = $(this).closest('.layout');
        var rules = $layout.find('.merchant-field-rules_to_apply select').val() || $layout.find('.merchant-field-rules_to_display select').val() || $layout.find('.merchant-field-display_rules select').val();
        if (rules === 'categories' || rules === 'by_category') {
          categories = $layout.find('.merchant-field-category_slugs select').val() || $layout.find('.merchant-field-product_cats select').val();
        }
      }
      var parent = $(this).closest('.merchant-products-search-container');
      if ($(this).val() !== '') {
        parent.find('.merchant-searching').addClass('active');
        var data = {
          action: 'merchant_admin_products_search',
          nonce: merchant_admin_options.ajaxnonce,
          keyword: $(this).val(),
          product_types: $(this).data('allowed-types'),
          ids: parent.find('.merchant-selected-products').val(),
          categories: categories
        };
        debounceTimer = setTimeout(function () {
          $.post(merchant_admin_options.ajaxurl, data, function (response) {
            var results = parent.find('.merchant-selections-products-preview');
            results.show();
            results.html(response);
            parent.find('.merchant-searching').removeClass('active');
          });
        }, 250);
      } else {
        parent.find('.merchant-selections-products-preview').html('').hide();
      }
    });

    // Products selector: dismiss search results on click outside or Escape.
    $(document).on('click touch', function (e) {
      if (!$(e.target).closest('.merchant-products-search-container').length) {
        $('.merchant-selections-products-preview').html('').hide();
        $('.merchant-search-field').val('');
      }
    });
    $(document).on('keydown', '.merchant-search-field', function (e) {
      if (e.key === 'Escape') {
        var parent = $(this).closest('.merchant-products-search-container');
        parent.find('.merchant-selections-products-preview').html('').hide();
        $(this).val('').blur();
      }
    });

    // Products selector.
    // Handle click/touch event for the search results
    $(document).on('click touch', '.merchant-module-page-setting-field-products_selector .merchant-selections-products-preview li', function () {
      var parent = $(this).closest('.merchant-products-search-container'),
        valueField = parent.find('.merchant-selected-products'),
        oldValue = valueField.val(),
        multiple = parent.data('multiple') === 'multiple';
      if (parent.find('.merchant-selected-products-preview ul li').length > 0 && !multiple) {
        // replace the first item
        parent.find('.merchant-selected-products-preview ul li').remove();
        valueField.val('').change();
      }
      $(this).children('.remove').attr('aria-label', 'Remove').html('×');
      parent.find('.merchant-selected-products-preview ul').append($(this));
      parent.find('.merchant-selections-products-preview').html('').hide();
      parent.find('.merchant-search-field').val('').change();
      if (oldValue === '') {
        valueField.val($(this).data('id'));
      } else {
        if (!multiple) {
          valueField.val($(this).data('id')).change();
        } else {
          var newValue = oldValue.split(',');
          newValue.push($(this).data('id'));
          valueField.val(newValue.join(',')).change();
        }
      }
    });

    // Products selector.
    // Handle click/touch event for the remove button.
    $(document).on('click touch', '.merchant-selected-products-preview .remove', function () {
      var removeButton = $(this);
      var parent = removeButton.closest('.merchant-products-search-container'),
        valueField = parent.find('.merchant-selected-products'),
        id = removeButton.parent().data('id');
      removeButton.parent().remove();
      var currentValue = valueField.val().split(',');
      if (currentValue.length > 0) {
        for (var key in currentValue) {
          if (parseInt(currentValue[key]) === parseInt(id)) {
            currentValue.splice(key, 1);
          }
        }
      }
      valueField.val(currentValue.join(',')).change();
      valueField.trigger('change.merchant');
    });
    $(document).on('merchant-admin-check-fields merchant-flexible-content-added', function () {
      $('.merchant-module-page-setting-field').each(function () {
        var $field = $(this);
        if ($field.data('condition') && $field.data('condition').length) {
          var condition = $field.data('condition');
          var $target = $(this).closest('.layout-body').find('input[name*="' + condition[0] + '"],select[name*="' + condition[0] + '"]');
          if (!$target.length) {
            $target = $('input[name="merchant[' + condition[0] + ']"],select[name="merchant[' + condition[0] + ']"]');
          }
          if ($target.length) {
            var passed = false;
            switch (condition[1]) {
              case '==':
                if ($target.attr('type') === 'radio' || $target.attr('type') === 'checkbox') {
                  var checked = $target.parent().find('input:checked');
                  if (checked.length && checked.val() === condition[2]) {
                    passed = true;
                  }
                }
                if ($target.is('select') && $target.val() == condition[2]) {
                  passed = true;
                }
                break;
              case 'any':
                if ($target.attr('type') === 'radio' || $target.attr('type') === 'checkbox') {
                  var _checked = $target.parent().find('input:checked');
                  if (_checked.length && condition[2].split('|').includes(_checked.val())) {
                    passed = true;
                  }
                }
                if ($target.is('select') && condition[2].split('|').includes($target.val())) {
                  passed = true;
                }
                break;
            }
            if (passed) {
              $field.removeClass('merchant-hide').addClass('merchant-show');
            } else {
              $field.removeClass('merchant-show').addClass('merchant-hide');
            }
          }
        }
      });
    }).trigger('merchant.change');
    $(document).on('merchant-admin-check-fields merchant-flexible-content-added change keyup', function () {
      $(document).find('.merchant-module-page-setting-field').each(function () {
        var $field = $(this);
        if ($field.data('conditions')) {
          var conditions = $field.data('conditions'),
            passed = evaluateConditions(conditions, $field);
          if (passed) {
            $field.removeClass('merchant-hide').addClass('merchant-show');
          } else {
            $field.removeClass('merchant-show').addClass('merchant-hide');
          }
        }
      });
    }).trigger('merchant.change');
    $(document).on('change', '.merchant-module-page-setting-field', function () {
      $(document).trigger('merchant-admin-check-fields');
    }).trigger('merchant.change');
    $(document).trigger('merchant-admin-check-fields');
    $(document).on('merchant-admin-check-color-fields merchant-flexible-content-added', function () {
      $('.merchant-color').each(function () {
        var $color = $(this);
        var $picker = $color.find('.merchant-color-picker');
        var $input = $color.find('.merchant-color-input');
        var inited = false;
        var pickr;
        $picker.off('click').on('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          var $bodyHTML = $('body,html');
          $bodyHTML.addClass('merchant-height-auto');
          if (!inited) {
            pickr = new Pickr({
              el: $picker.get(0),
              container: 'body',
              theme: 'merchant',
              appClass: 'merchant-pcr-app',
              default: $input.val() || $picker.data('default-color') || '#212121',
              swatches: ['#000000', '#F44336', '#E91E63', '#673AB7', '#03A9F4', '#8BC34A', '#FFEB3B', '#FFC107', '#FFFFFF'],
              sliders: 'h',
              useAsButton: true,
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
                $input.val(colorCode).trigger('change.merchant');
              }
              $(document).trigger('merchant-color-picker-updated', [colorCode, $input]);
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
            inited = true;
          } else {
            pickr.setColor($input.val());
          }
        });
        $input.on('change keyup', function () {
          var colorCode = $(this).val();
          $picker.css({
            'background-color': colorCode
          });
        });
      });
    });
    $(document).trigger('merchant-admin-check-color-fields');

    // Create Page Control.
    var CreatePageControl = {
      init: function init() {
        this.events();
      },
      events: function events() {
        var self = this;
        $(document).on('click', '.merchant-create-page-control-button', function (e) {
          e.preventDefault();
          var $this = $(this),
            $create_message = $this.parent().find('.merchant-create-page-control-create-message'),
            $success_message = $this.parent().find('.merchant-create-page-control-success-message'),
            initial_text = $this.text(),
            creating_text = $this.data('creating-text'),
            created_text = $this.data('created-text'),
            page_title = $this.data('page-title'),
            page_meta_key = $this.data('page-meta-key'),
            page_meta_value = $this.data('page-meta-value'),
            option_name = $this.data('option-name'),
            nonce = $this.data('nonce');
          if (!page_title) {
            return false;
          }
          $(this).text(creating_text);
          $(this).attr('disabled', true);
          $.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
              action: 'merchant_create_page_control',
              page_title: page_title,
              page_meta_key: page_meta_key,
              page_meta_value: page_meta_value,
              option_name: option_name,
              nonce: nonce
            },
            success: function success(response) {
              self.ajaxResponseHandler(response, $this, $success_message, $create_message);
            }
          });
        });
      },
      ajaxResponseHandler: function ajaxResponseHandler(response, $this, $success_message, $create_message) {
        if ('success' === response.status) {
          var href = $success_message.find('a').attr('href'),
            newhref = href.replace('?post=&', '?post=' + response.page_id + '&');
          $success_message.find('a').attr('href', newhref);
          $success_message.css('display', 'block');
          $create_message.remove();
          $this.remove();
        }
      }
    };

    // Initialize Create Page Control.
    CreatePageControl.init();
    $('.merchant-module-page-setting-field-gallery').each(function () {
      var $this = $(this);
      var $button = $this.find('.merchant-gallery-button');
      var $input = $this.find('.merchant-gallery-input');
      var $images = $this.find('.merchant-gallery-images');
      var $remove = $this.find('.merchant-gallery-remove');
      var wpMediaFrame;
      var sortable = $images.sortable({
        helper: 'original',
        update: function update(event, ui) {
          var selectedIds = [];
          $images.find('.merchant-gallery-image').each(function () {
            selectedIds.push($(this).data('item-id'));
          });
          $input.val(selectedIds.join(',')).trigger('change');
        }
      });
      $remove.on('click', function (e) {
        e.preventDefault();
        $(this).parent().remove();
        var selectedIds = [];
        $images.find('.merchant-gallery-image').each(function () {
          selectedIds.push($(this).data('item-id'));
        });
        $input.val(selectedIds.join(',')).trigger('change');
      });
      $button.on('click', function (e) {
        var $btn = $(this);
        var ids = $input.val();
        var mode = ids ? 'edit' : 'add';
        e.preventDefault();
        if (typeof window.wp === 'undefined' || !window.wp.media || !window.wp.media.gallery) {
          return;
        }
        if (mode === 'add') {
          wpMediaFrame = window.wp.media({
            library: {
              type: 'image'
            },
            frame: 'post',
            state: 'gallery',
            multiple: true
          });
          wpMediaFrame.open();
        } else {
          wpMediaFrame = window.wp.media.gallery.edit('[gallery ids="' + ids + '"]');
        }
        wpMediaFrame.on('update', function (selection) {
          $images.empty();
          var selectedIds = selection.models.map(function (attachment) {
            var item = attachment.toJSON();
            var thumb = item.sizes && item.sizes.thumbnail && item.sizes.thumbnail.url ? item.sizes.thumbnail.url : item.url;
            $images.append('<div class="merchant-gallery-image" data-item-id="' + item.id + '"><i class="merchant-gallery-remove dashicons dashicons-no-alt"></i><img src="' + thumb + '" /></div>');
            return item.id;
          });
          $input.val(selectedIds.join(',')).trigger('change');
          $this.find('.merchant-gallery-remove').on('click', function (e) {
            e.preventDefault();
            $(this).parent().remove();
            var selectedIds = [];
            $images.find('.merchant-gallery-image').each(function () {
              selectedIds.push($(this).data('item-id'));
            });
            $input.val(selectedIds.join(',')).trigger('change');
          });
        });
      });
    });
    var initUploadField = function initUploadField(element) {
      var $this = element;
      var $button = $this.find('.merchant-upload-button');
      var $input = $this.find('.merchant-upload-input');
      var $wrapper = $this.find('.merchant-upload-wrapper');
      var $remove = $this.find('.merchant-upload-remove');
      var wpMediaFrame;
      $remove.on('click', function (e) {
        e.preventDefault();
        $(this).parent().remove();
        $input.val('').trigger('change');
      });
      $button.on('click', function (e) {
        e.preventDefault();
        if (typeof window.wp === 'undefined' || !window.wp.media) {
          return;
        }
        if (!wpMediaFrame) {
          wpMediaFrame = window.wp.media({
            library: {
              type: 'image'
            }
          });
        }
        wpMediaFrame.open();
        wpMediaFrame.on('select', function () {
          $wrapper.empty();
          var item = wpMediaFrame.state().get('selection').first().attributes;
          var thumb = item.sizes && item.sizes.thumbnail && item.sizes.thumbnail.url ? item.sizes.thumbnail.url : item.url;
          var sizes = item.sizes ? JSON.stringify(item.sizes) : '';
          $wrapper.append('<div class="merchant-upload-image" data-sizes=\'' + sizes + '\'><i class="merchant-upload-remove dashicons dashicons-no-alt"></i><img src="' + thumb + '" /></div>');
          $input.val(item.id).trigger('change');
          $this.find('.merchant-upload-button-drag-drop').hide();
          $this.find('.merchant-upload-remove').on('click', function (e) {
            e.preventDefault();
            $(this).parent().remove();
            $input.val('').trigger('change');
            $this.find('.merchant-upload-button-drag-drop').show();
          });
        });
      });
    };
    $('.merchant-module-page-setting-field-upload:not(.template)').each(function () {
      initUploadField($(this));
    });

    // Drag & Drag
    var events = ['dragenter', 'dragover', 'dragleave', 'drop'];
    jQuery.each(events, function (index, eventName) {
      $(document).on(eventName, '.merchant-upload-button-drag-drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
      });
    });
    $(document).on('dragenter', '.merchant-upload-button-drag-drop', function (e) {
      $(this).closest('.merchant-module-page-setting-field-upload').find('.merchant-upload-button').click();
    });
    var initSelectAjax = function initSelectAjax($selectAjax) {
      var $select = $selectAjax.find('select');
      var $source = $select.data('source');
      var $config = window.merchant_admin_options;
      var $object = {
        width: '100%',
        templateSelection: function templateSelection(category) {
          return category.text.replace(/&nbsp;-*\s*/g, '').trim();
        }
      };
      if ($source === 'post' || $source === 'product' || $source === 'user') {
        $object.minimumInputLength = 1;
        $object.ajax = {
          url: $config.ajaxurl,
          dataType: 'json',
          delay: 250,
          cache: true,
          data: function data(params) {
            return {
              action: 'merchant_admin_options_select_ajax',
              nonce: $config.ajaxnonce,
              term: params.term,
              source: $source
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
        };
      }
      $select.select2($object);
      $selectAjax.find('.select2-selection--multiple').append('<span class="merchant-select2-clear"></span>');
    };
    $('.merchant-module-page-setting-field-select_ajax:not(.template)').each(function () {
      initSelectAjax($(this));
    });
    $('.merchant-module-page-settings-responsive').each(function () {
      var $this = $(this);
      var $button = $this.find('.merchant-module-page-settings-devices button');
      var $container = $this.find('.merchant-module-page-settings-device-container');
      $button.on('click', function (e) {
        e.preventDefault();
        var $device = $(this).data('device');
        $button.removeClass('active');
        $container.removeClass('active');
        $(this).addClass('active');
        $container.each(function () {
          if ($(this).data('device') === $device) {
            $(this).addClass('active');
          }
        });
      });
    });
    $('.merchant-animated-buttons').each(function () {
      var $button = $(this).find('label');
      var $demo = $('.merchant-animation-demo');
      var animation;
      var animationHover;
      $button.on('click', function () {
        $demo.removeClass('merchant-animation-' + animation);
        $demo.removeClass('merchant-animation-' + animationHover);
        animation = $(this).find('input').attr('value');
        setTimeout(function () {
          $demo.addClass('merchant-animation-' + animation);
        }, 100);
        setTimeout(function () {
          $demo.removeClass('merchant-animation-' + animation);
        }, 1000);
      });
      $button.mouseover(function () {
        $demo.removeClass('merchant-animation-' + animation);
        animationHover = $(this).find('input').attr('value');
        $demo.addClass('merchant-animation-' + animationHover);
      }).mouseout(function () {
        $demo.removeClass('merchant-animation-' + animationHover);
      });
    });

    // Notifications Sidebar
    var $notificationsSidebar = $('.merchant-notifications-sidebar');
    if ($notificationsSidebar.length) {
      var $notifications = $('.merchant-notifications');
      $notifications.on('click', function (e) {
        e.preventDefault();
        var $notification = $(this);
        var latestNotificationDate = $notificationsSidebar.find('.merchant-notification:first-child .merchant-notification-date').data('raw-date');
        $notificationsSidebar.toggleClass('opened');
        if (!$notification.hasClass('read')) {
          $.post(window.merchant.ajax_url, {
            action: 'merchant_notifications_read',
            nonce: window.merchant.nonce,
            latest_notification_date: latestNotificationDate
          }, function (response) {
            if (response.success) {
              setTimeout(function () {
                $notification.addClass('read');
              }, 2000);
            }
          });
        }
      });

      // Hide fixed changelog items
      var merchantNotificationsContent = $('.merchant-notification-content');
      merchantNotificationsContent.each(function () {
        var notificationContentItem = $(this);
        // search for all spans that contains class "changelog-fixed" and then go to the parent li of that span and add class hidden
        var fixedItems = notificationContentItem.find('span.changelog-fixed');
        fixedItems.each(function () {
          var fixedItem = $(this);
          fixedItem.closest('li').remove();
        });
        // now check if all li inside this notificationContentItem are hidden, so we need to look for the parent .merchant-notification and remove it
        var allItems = notificationContentItem.find('li');
        var hiddenItems = notificationContentItem.find('li.hidden');
        if (allItems.length === hiddenItems.length) {
          notificationContentItem.closest('.merchant-notification').remove();
        }
      });
      $(window).on('scroll', function () {
        if (window.pageYOffset > 60) {
          $notificationsSidebar.addClass('closing');
          setTimeout(function () {
            $notificationsSidebar.removeClass('opened');
            $notificationsSidebar.removeClass('closing');
          }, 300);
        }
      });

      // Close Sidebar
      $('.merchant-notifications-sidebar-close').on('click', function (e) {
        e.preventDefault();
        $notificationsSidebar.addClass('closing');
        setTimeout(function () {
          $notificationsSidebar.removeClass('opened');
          $notificationsSidebar.removeClass('closing');
        }, 300);
      });
    }

    // Tabs Navigation.
    var tabs = $('.merchant-tabs-nav');
    if (tabs.length) {
      tabs.each(function () {
        var tabWrapperId = $(this).data('tab-wrapper-id');
        $(this).find('.merchant-tabs-nav-link').on('click', function (e) {
          e.preventDefault();
          var tabsNavLink = $(this).closest('.merchant-tabs-nav').find('.merchant-tabs-nav-link'),
            to = $(this).data('tab-to');

          // Tab Nav Item
          tabsNavLink.each(function () {
            $(this).closest('.merchant-tabs-nav-item').removeClass('active');
          });
          $(this).closest('.merchant-tabs-nav-item').addClass('active');

          // Tab Content
          var tabContentWrapper = $('.merchant-tab-content-wrapper[data-tab-wrapper-id="' + tabWrapperId + '"]');
          tabContentWrapper.find('> .merchant-tab-content').removeClass('active');
          tabContentWrapper.find('> .merchant-tab-content[data-tab-content-id="' + to + '"]').addClass('active');
        });
      });
    }

    // Module Alert
    var $moduleAlert = $('.merchant-module-alert');
    if ($moduleAlert.length) {
      $moduleAlert.find('.merchant-module-alert-close').on('click', function (e) {
        e.preventDefault();
        $moduleAlert.removeClass('merchant-show');
        $(document).off('click.merchant-alert-close');
      });
    }
  });

  /**
   * Check if a string is numeric.
   *
   * @param str the string to check
   *
   * @returns {boolean} true if numeric, false otherwise
   */
  function isNumeric(str) {
    if (typeof str != "string") return false; // we only process strings!
    return !isNaN(str) &&
    // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
    !isNaN(parseFloat(str)); // ...and ensure strings of whitespace fail
  }

  /**
   * Evaluate conditional fields.
   *
   * @param conditions the array of conditions
   * @param field the field that is being evaluated
   *
   * @returns {boolean}
   */
  function evaluateConditions(conditions) {
    var field = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
    var passed = false;
    if ('relation' in conditions) {
      //loop through terms
      var relation = conditions.relation.toUpperCase();
      // lowercase relation
      if (relation === 'OR') {
        for (var i = 0; i < conditions.terms.length; i++) {
          var term = conditions.terms[i];
          passed = evaluateConditions(term, field);
          if (passed) {
            return true;
          }
        }
      } else if (relation === 'AND') {
        var n = 0;
        for (var _i = 0; _i < conditions.terms.length; _i++) {
          // check if inner terms are passed
          var _term = conditions.terms[_i];
          if (evaluateConditions(_term, field)) {
            n++;
          }
        }
        if (n === conditions.terms.length) {
          passed = true;
        }
      }
    } else {
      var condition = '';
      if ('terms' in conditions) {
        condition = conditions.terms[0];
      } else {
        condition = conditions;
      }
      var $target = $('input[name="merchant[' + condition.field + ']"],select[name="merchant[' + condition.field + ']"]');
      if (!$target.length) {
        // check if inside flexible content
        var flexibleContentParent = field.closest('.layout-body');
        if (flexibleContentParent.length > 0) {
          $target = flexibleContentParent.find('.merchant-field-' + condition.field).find('input, select');
        }
      }
      if (!$target.length) {
        // Maybe the field is a multiple field
        $target = $('input[name="merchant[' + condition.field + '][]"],select[name="merchant[' + condition.field + '][]"]');
      }
      if (!$target.length) {
        // Maybe the field is inside fields group
        $target = $('.merchant-group-fields-container').find('.merchant-field-' + condition.field + ' input[name*="' + condition.field + '"],.merchant-field-' + condition.field + ' select[name*="' + condition.field + '"]');
      }
      var value = $target.val();
      if ($target.attr('type') === 'checkbox') {
        value = $target.is(':checked');
      }
      if ($target.attr('type') === 'radio') {
        value = $target.filter(':checked').val();
      }

      // check if the field is multiple checkbox
      if ($target.attr('type') === 'checkbox' && $target.length > 1) {
        value = [];
        $target.each(function () {
          if ($(this).is(':checked')) {
            value.push($(this).val());
          }
        });
      }

      // cast value as string if numeric
      if (isNumeric(value)) {
        value = Number(value);
      }

      // check if is array condition.value
      if (Array.isArray(condition.value)) {
        condition.value = condition.value.map(function (item) {
          if (isNumeric(item)) {
            return Number(item);
          }
          return item;
        });
      }
      if (condition.operator === '===' && value === condition.value) {
        passed = true;
      } else if (condition.operator === '!==' && value !== condition.value) {
        passed = true;
      } else if (condition.operator === '>' && value > condition.value) {
        passed = true;
      } else if (condition.operator === '<' && value < condition.value) {
        passed = true;
      } else if (condition.operator === '>=' && value >= condition.value) {
        passed = true;
      } else if (condition.operator === '<=' && value <= condition.value) {
        passed = true;
      } else if (condition.operator === 'in' && condition.value.includes(value)) {
        passed = true;
      } else if (condition.operator === '!in' && !condition.value.includes(value)) {
        passed = true;
      } else if (condition.operator === 'contains' && Array.isArray(value) && value.includes(condition.value)) {
        passed = true;
      } else if (condition.operator === '!contains' && Array.isArray(value) && !value.includes(condition.value)) {
        passed = true;
      }
    }
    return passed;
  }
})(jQuery, window, document);

// Extend jQuery to add getPath func to get accurate dynamic selector to an element.
jQuery.fn.extend({
  getPath: function getPath() {
    var pathes = [];
    this.each(function (index, element) {
      var path,
        $node = jQuery(element);
      while ($node.length) {
        var realNode = $node.get(0),
          name = realNode.localName;
        if (!name) {
          break;
        }
        name = name.toLowerCase();
        var parent = $node.parent();
        var sameTagSiblings = parent.children(name);
        if (sameTagSiblings.length > 1) {
          var allSiblings = parent.children();
          var index = allSiblings.index(realNode) + 1;
          if (index > 0) {
            name += ':nth-child(' + index + ')';
          }
        }
        path = name + (path ? ' > ' + path : '');
        $node = parent;
      }
      pathes.push(path);
    });
    return pathes.join(',');
  }
});