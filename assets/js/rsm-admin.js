jQuery(document).ready(function ($) {
  /*----------------------------------------------------------------------------*
   * Miscellaneous functions
   *----------------------------------------------------------------------------*/
  var RSM_Misc = {
    init: function () {
      this.ui();
      this.validation();
      this.ajax();
    },

    ui: function () {
      var self = this,
        ctrlDown = false;

      // Help toggle
      $('.rsm-help-link').on('click', function () {
        $(this)
          .toggleClass('active')
          .siblings()
          .find('.row')
          .find('.rsm-help-text')
          .slideToggle('fast');
      });

      // Advanced link toggle
      $('.rsm-advanced-link').on('click', function () {
        $(this)
          .toggleClass('active')
          .siblings()
          .find('.row')
          .find('.rsm-advanced')
          .slideToggle('fast');
      });

      // See more toggle
      $('.rsm-more-link').on('click', function () {
        $(this)
          .toggleClass('active')
          .siblings('.rsm-help-text')
          .slideToggle('fast');
      });

      // Force select-all
      $('.rsm-select-all').on('click', function () {
        $(this).select();
      });

      // Prevent copy button from other actions
      $('.rsm-copy-btn').on('click', function (event) {
        event.preventDefault();
      });

      // Setup clipboard copying
      var clipboard = new Clipboard('.rsm-copy-btn', {
        text: function (trigger) {
          return $(trigger)
            .closest('.input-group')
            .find('.rsm-copy-text')
            .val();
        }
      });

      // Handle successful clipboard copy
      clipboard.on('success', function (e) {
        var $input = $(e.trigger)
          .closest('.input-group')
          .find('.rsm-copy-text');
        $(e.trigger).tooltip('hide');
        $input
          .focus()
          .select()
          .attr('data-original-title', 'Copied!')
          .tooltip('show');
      });

      // Handle unsupported clipboard browsers
      clipboard.on('error', function (e) {
        var $input = $(e.trigger)
          .closest('.input-group')
          .find('.rsm-copy-text');
        $(e.trigger).tooltip('hide');
        $input
          .focus()
          .select()
          .attr('data-original-title', 'Press Ctrl+C to copy')
          .tooltip('show');
      });

      // When copy text loses focus, hide tooltip
      $('.rsm-copy-text').focusout(function () {
        $(this).removeAttr('data-original-title').tooltip('hide');
      });

      // iCheck radio buttons
      $('input:radio').icheck({
        autoAjax: false,
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' // optional
      });

      // Date picker
      $('.rsm-datepicker').datetimepicker({
        timepicker: false,
        format: 'Y-m-d',
        formatDate: 'Y-m-d',
        //minDate:0,
        closeOnDateSelect: true
      });

      // Time picker
      $('.rsm-timepicker').datetimepicker({
        datepicker: false,
        format: 'g:i A',
        formatTime: 'g:i A',
        step: 30
      });

      // iCheck checkbox buttons
      $('.rsm-checkbox').icheck({
        autoAjax: false,
        checkboxClass: 'icheckbox_square-blue',
        increaseArea: '20%' // optional
      });

      // Display the WP color picker
      $('.rsm-color-field').wpColorPicker();

      // Inserts text at the current cursor position
      function insertTextAtCursor(text) {
        var sel, range;
        if (window.getSelection) {
          sel = window.getSelection();
          if (sel.getRangeAt && sel.rangeCount) {
            range = sel.getRangeAt(0);
            range.deleteContents();
            var textNode = document.createTextNode(text);
            range.insertNode(textNode);
            sel.removeAllRanges();
            range = range.cloneRange();
            range.selectNode(textNode);
            range.collapse(false);
            sel.addRange(range);
          }
        } else if (document.selection && document.selection.createRange) {
          range = document.selection.createRange();
          range.pasteHTML(text);
          range.select();
        }
      }

      function displayCount(area) {
        // Fix for this.getText() linefeed glitch
        // --> https://github.com/mervick/emojionearea/issues/99
        var text = area.getText(),
          count = self.countSymbols(text.replace(/(\r\n|\n|\r)/gm, ''));

        // Update character count display
        $('#char-count').html(180 - count);

        // Because Validate doesn't work with DIV fields and JS doesn't handle UNICODE well, resort to basic validation
        if (count > 180) {
          $('#emoji-text-error').show();
          $('#rsm-btn-submit').prop('disabled', true);
        } else {
          $('#emoji-text-error').hide();
          $('#rsm-btn-submit').prop('disabled', false);
        }
      }

      // Emoji control
      var $emoji = $('.rsm-emoji').emojioneArea({
        attributes: { spellcheck: true },
        pickerPosition: 'top',
        filtersPosition: 'bottom',
        tonesStyle: 'square',
        hidePickerOnBlur: true,
        shortcuts: false,
        buttonTitle: 'Click to select an emoji icon to insert',
        events: {
          keydown: function (editor, e) {
            var text = this.getText(),
              count = self.countSymbols(text.replace(/(\r\n|\n|\r)/gm, '')),
              code = e.keyCode;

            // Note when ctrl/cmd button is pressed
            if (e.keyCode == 17 || e.keyCode == 91) ctrlDown = true;

            // Key codes for enter (13), backspace (8), tab (9), pgup (33), pgdown (34), home (36), end (35), arrow left up right down (37-40), delete (46), a (65), c (67), v (86), x (88), z (90)
            if (
              code == 13 ||
              (count >= 180 &&
                code != 8 &&
                code != 9 &&
                code != 33 &&
                code != 34 &&
                code != 35 &&
                code != 36 &&
                code != 37 &&
                code != 38 &&
                code != 39 &&
                code != 40 &&
                code != 46 &&
                !(ctrlDown && code == 65) &&
                !(ctrlDown && code == 67) &&
                !(ctrlDown && code == 86) &&
                !(ctrlDown && code == 88) &&
                !(ctrlDown && code == 90))
            ) {
              e.preventDefault();
            }
          },
          keyup: function (editor, e) {
            // Note when ctrl/cmd button is released
            if (e.keyCode == 17 || e.keyCode == 91) ctrlDown = false;
          },
          emojibtn_click: function (button, e) {
            var text = this.getText(),
              count = self.countSymbols(text.replace(/(\r\n|\n|\r)/gm, ''));
            if (count > 180) {
              $(this.editor[0]).find('img:last').remove();
              displayCount(this);
            }
          }
        }
      });

      if ($emoji[0]) {
        $emoji
          .data('emojioneArea')
          .on(
            'keydown keyup blur focus paste change emojibtn.click',
            function () {
              displayCount(this);
            }
          );
      }
      $('.rsm-shortcode').on('click', function () {
        if ($emoji[0]) {
          var area = $emoji[0].emojioneArea;
          $('.emojionearea-editor').focus();
          insertTextAtCursor($(this).attr('data-id'));
          displayCount(area);
        }
      });
    },

    // Count number of symbols
    // --> https://mathiasbynens.be/notes/javascript-unicode
    countSymbols: function (string) {
      var regexAstralSymbols = /[\uD800-\uDBFF][\uDC00-\uDFFF]/g,
        count = string.replace(regexAstralSymbols, '_').length,
        match1 = (string.match(/\{{first_name}}/g) || []).length,
        match2 = (string.match(/\{{last_name}}/g) || []).length,
        match3 = (string.match(/\{{full_name}}/g) || []).length;
      match4 = (string.match(/\{{day_of_week}}/g) || []).length;
      match5 = (string.match(/\{{date}}/g) || []).length;

      // Inflate count when shortcodes are used
      if (match1 > 0) count = count - match1 * 14 + match1 * 15;
      if (match2 > 0) count = count - match2 * 13 + match2 * 20;
      if (match3 > 0) count = count - match3 * 13 + match3 * 35;
      if (match4 > 0) count = count - match4 * 15 + match4 * 9;
      if (match5 > 0) count = count - match5 * 8 + match5 * 10;

      return count;
    },

    validation: function () {
      // Override JQuery Validator defaults for BS compatibility
      //  --> http://stackoverflow.com/questions/18754020/bootstrap-3-with-jquery-validation-plugin
      $.validator.setDefaults({
        highlight: function (element) {
          $(element).closest('.rsm-group').addClass('has-error');
        },
        unhighlight: function (element) {
          $(element).closest('.rsm-group').removeClass('has-error');
        },
        errorElement: 'span',
        errorClass: 'help-block error-help-block',
        errorPlacement: function (error, element) {
          if (
            element.parent('.input-group').length ||
            element.parent('.inline-label').length ||
            'checkbox' === element.prop('type') ||
            'radio' === element.prop('type')
          ) {
            error.insertAfter(element.parent());
          } else {
            error.insertAfter(element);
          }
        },
        ignore: '.ignore, :hidden'
      });

      // Time validate (12-hour) taken from official additional-methods.js
      $.validator.addMethod(
        'time12h',
        function (value, element) {
          return (
            this.optional(element) ||
            /^((0?[1-9]|1[012])(:[0-5]\d){1,2}(\ ?[AP]M))$/i.test(value)
          );
        },
        'Please enter a valid time in 12-hour AM/PM format'
      );

      // Validates a url to make sure it's using HTTPS and matches website URL
      $.validator.addMethod(
        'canvas_url',
        function (value, element, param) {
          return (
            param == false ||
            (value.indexOf('https') >= 0 &&
              value.indexOf(wp_vars.app_domain) >= 0)
          );
        },
        'For canvas redirect, HTTPS is required and the URL must be a page within this website.'
      );

      // Validates for a minimum value
      $.validator.addMethod('minStrict', function (value, element, param) {
        return value >= param;
      });
    },

    ajax: function () {
      // Force processing of notifications
      $('#pre-footer-refresh').on('click', function (e) {
        e.preventDefault();

        var refName = 'rsm-pre-footer-refresh-loading';

        // Show loading icon
        $('#' + refName).show();

        // Make ajax call
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: wp_vars.ajaxurl,
          data: {
            action: 'rsm_ajax_force_process',
            security: wp_vars.ajaxnonce
          },
          cache: false,
          success: function (response) {
            $('#' + refName).hide();
            if (response.success) {
              if (response.data) location.reload();
            }
          },
          error: function (response) {
            $('#' + refName).hide();
          }
        });
      });
    }
  };
  RSM_Misc.init();

  /*----------------------------------------------------------------------------*
   * Dashboard page
   *----------------------------------------------------------------------------*/
  var RSM_Dashboard = {
    init: function () {
      this.animate();
      this.confirm();
    },

    animate: function () {
      $('a[href*="#performance-top"]').on('click', function (e) {
        e.preventDefault();
        $('html,body').animate(
          {
            scrollTop: $(this.hash).offset().top - 40
          },
          1100
        );
      });
    },
    confirm: function () {
      $('#rsm-deactivate-license').on('click', function (e) {
        e.preventDefault();
        var msg =
          "WARNING:\n\nYou are about to release this license from use on this site. Note that releasing your license here will not affect your other WordPress sites.\n\nClick 'Cancel' to stop, 'OK' to release.";
        if (confirm(msg)) {
          $('#deactivate-form').submit();
          return true;
        } else {
          return false;
        }
      });
    }
  };
  if ($('.dashboard-page').length > 0) RSM_Dashboard.init();

  /*----------------------------------------------------------------------------*
   * Settings page
   *----------------------------------------------------------------------------*/
  var RSM_Settings = {
    init: function () {
      this.ui();
      this.validate();
      this.confirm();
      this.ajax();
    },

    segShowLoading: function (show) {
      if (show) {
        $('#seg-loading').show();
      } else {
        $('#seg-loading').hide();
      }
    },

    ui: function () {
      var self = this;

      // Smooth scroll on Lists page edit
      var $settingsTop = $('#settings-top');
      if (
        $('.settings-page').length > 0 &&
        $settingsTop.length > 0 &&
        !($('.settings-error').length > 0)
      ) {
        $('html, body').animate(
          {
            scrollTop: $settingsTop.offset().top - 40
          },
          1100
        );
      }

      // Update opt-in links with segment
      $('.rsm-app-segment').on('change', function () {
        var $segments = $(this).find(':selected'),
          segmentName = $segments.text(),
          segmentId = $segments.val(),
          $links = $(this).closest('.segments').next(),
          $linkText = $links.find('.rsm-optin-text'),
          $linkHTML = $links.find('.rsm-optin-html'),
          optinText = $linkText.attr('data-optin-url'),
          optinHTML = $linkHTML.attr('data-optin-url'),
          newOptinText =
            segmentId == 0
              ? optinText
              : optinText + '&segment=' + encodeURIComponent(segmentName),
          newOptinHTML =
            segmentId == 0
              ? optinHTML
              : optinHTML.replace(optinText, newOptinText);

        $linkText.val(newOptinText);
        $linkHTML.val(newOptinHTML);
      });

      // Update welcome elements
      $("input[name$='opt-welcome']")
        .on('change', function () {
          if ($("input[name$='opt-welcome']:checked").attr('value') == 'T') {
            $('.rsm-welcome').show();
            window.showWelcome = true;
          } else {
            $('.rsm-welcome').hide();
            window.showWelcome = false;
          }
        })
        .change();

      // Update autoresponder elements
      $("input[name$='opt-ar']")
        .on('change', function () {
          if ($("input[name$='opt-ar']:checked").attr('value') == 'T') {
            $('#rsm-ar-lists').show();
            window.showAr = true;
          } else {
            $('#rsm-ar-lists').hide();
            window.showAr = false;
          }
        })
        .change()
        .click(function () {
          window.showAr = $(this).val() == 'T';
        });

      // AR toggle buttons
      $('.rsm-ar-toggle').on('click', function () {
        $(this)
          .find('.rsm-ar-panel')
          .toggleClass('fa-chevron-right fa-chevron-down');
        $(this)
          .parent()
          .parent()
          .parent()
          .find('.rsm-ar-panel-detail')
          .slideToggle('fast');
      });

      // Note when using media library
      $("input[name$='opt-button']")
        .on('change', function () {
          window.useMedia = $('#rsm-media-upload-opt').is(':checked');
        })
        .change();

      // Handle media url
      $('#rsm-media-url').on('click', function () {
        $(this).siblings('.icheck-item').trigger('click');
        $(this).focus();
      });

      // Media uploader
      $('#rsm-media-upload').click(function (e) {
        e.preventDefault();
        $(this).siblings('.icheck-item').trigger('click');
        var wp_uploader = wp
          .media({
            title: 'Select an Image to Use',
            button: {
              text: 'Use this image'
            },
            multiple: false
          })
          .open()
          .on('select', function (e) {
            // This will return the selected image from the Media Uploader, the result is an object
            var uploaded_image = wp_uploader.state().get('selection').first();
            // We convert uploaded_image to a JSON object to make accessing it easier
            var image_url = uploaded_image.toJSON().url;
            $('#rsm-media-url').val(image_url);
            $('#rsm-media-upload-opt').val(image_url);
            $('#settings-form-btn').validate().element('#rsm-media-url');
          });
      });

      // Toggle floating button elements
      $("input[name$='opt-float-button']")
        .on('change', function () {
          if (
            $("input[name$='opt-float-button']:checked").attr('value') == '0'
          ) {
            $('#rsm-float-config').hide();
          } else {
            $('#rsm-float-config').show();
          }
        })
        .change();

      // Update autorun mode elements
      $("input[name$='opt-cron']")
        .on('change', function () {
          if ($("input[name$='opt-cron']:checked").attr('value') == 'wp') {
            $('.rsm-cron-service').hide();
          } else {
            $('.rsm-cron-service').show();
          }
        })
        .change();

      // Setup multiselect object
      $('[id$=-multiselect]').multiselect({
        buttonClass: 'btn rsm-multiselect',
        buttonText: function (options, select) {
          if (options.length === 0) {
            return 'None selected';
          } else if (options.length > 3) {
            return options.length + ' selected';
          } else {
            var labels = [];
            options.each(function () {
              if ($(this).attr('label') !== undefined) {
                labels.push($(this).attr('label'));
              } else {
                labels.push($(this).html());
              }
            });
            return labels.join(', ') + '';
          }
        },
        onChange: function (option, checked) {
          var msName = $(option).closest('select').attr('id'),
            arName = msName.replace('-multiselect', ''),
            $selected = $('#' + msName + ' option:selected');

          $('#ar-use-' + arName).prop('checked', $selected.length > 0);
        }
      });

      // Deselect all options when unchecked
      $('[id^=ar-use-]').on('click', function () {
        var arName = $(this).val(),
          $msObj = $('#' + arName + '-multiselect'),
          isChecked = $(this).is(':checked');

        if (!isChecked) {
          $msObj.multiselect('deselectAll', false);
        }
      });

      // Open Ctct popup to begin the OAuth flow
      $('#ctct-connect-popup').on('click', function () {
        var width = 500,
          height = 600,
          href = $(this).attr('href').toString();

        window.open(
          href,
          'CtctIntegration',
          'top=' +
            ($(window).height() / 2 - height / 2) +
            ', left=' +
            ($(window).width() / 2 - width / 2) +
            ', width=' +
            width +
            ', height=' +
            height
        );
        return false;
      });

      // Cron alternative link toggle
      $('#rsm-cron-alt-link').on('click', function (e) {
        e.preventDefault();
        $('#rsm-cron-alt').slideToggle('fast');
      });
    },

    /*isUrlValid: function (url) {
            return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
        },*/

    arShowResults: function (elem, status, message) {
      var color = status == 'success' ? 'bg-rsm-light-green' : 'bg-rsm-red',
        html =
          '<div class="alert ' +
          color +
          ' alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">&times;</button>' +
          message +
          '</div>';

      $('#' + elem + '-results').html(html);
    },

    arShowLoading: function (elem, show) {
      if (show) {
        $('#' + elem + '-loading').show();
      } else {
        $('#' + elem + '-loading').hide();
      }
    },

    arUpdateUI: function (elem, status) {
      if (status == 'connected') {
        $('.' + elem + '-img')
          .removeClass('inactive')
          .addClass('active');
        $('#' + elem + '-connected').show();
        $('#' + elem + '-disconnect').show();
        $('#' + elem + '-connect').html('Update');
        $('#integrate-' + elem).show();
        $('#integrate-none').hide();
      } else {
        $('.' + elem + '-img')
          .removeClass('active')
          .addClass('inactive');
        $('#' + elem + '-connected').hide();
        $('#' + elem + '-disconnect').hide();
        $('#' + elem + '-connect').html('Connect');
        $('#integrate-' + elem).hide();
        if (!$('[id$=-connected]').is(':visible')) {
          $('#integrate-none').show();
        }
      }
    },

    validate: function () {
      // Settings validation
      $('#settings-form')
        .on('submit', function (e) {
          if (
            window.showAr &&
            $('[id$=-multiselect] option:selected').length <= 0
          ) {
            e.preventDefault();
            $('#opt-ar-error').show();
          } else {
            $('#opt-ar-error').hide();
          }
        })
        .validate({
          rules: {
            'app-name': { required: true, maxlength: 32 },
            'app-id': { required: true, maxlength: 50 },
            'app-secret': { required: true, maxlength: 50 },
            'okay-url': { required: false, url: true, maxlength: 2083 },
            'cancel-url': { required: false, url: true, maxlength: 2083 },
            //'welcome-text': { required: function() { return ( window.showWelcome ); }, maxlength: 180 },
            'welcome-url': {
              required: function () {
                return window.showWelcome;
              },
              canvas_url: function () {
                return window.curRedirectType == 'I';
              },
              url: true,
              maxlength: 2083
            }
          },
          messages: {
            'app-name': {
              required: 'Facebook Display Name is required.',
              maxlength: 'Please enter no more than 32 characters.'
            },
            'app-id': {
              required: 'Facebook App ID is required.',
              maxlength: 'Please enter no more than 50 characters.'
            },
            'app-secret': {
              required: 'Facebook App Secret is required.',
              maxlength: 'Please enter no more than 50 characters.'
            },
            'okay-url': {
              url: 'Please enter a valid Okay URL, including http:// or https://',
              maxlength: 'Please enter no more than 2083 characters.'
            },
            'cancel-url': {
              url: 'Please enter a valid Cancel URL, including http:// or https://',
              maxlength: 'Please enter no more than 2083 characters.'
            },
            /*'welcome-text': {
                     required: "If sending a welcome notification, Welcome text is required.",
                     maxlength: "Please enter no more than 180 characters."
                     },*/
            'welcome-url': {
              required:
                'If sending a welcome notification, Redirect URL is required.',
              url: 'Please enter a valid Welcome URL, including http:// or https://',
              maxlength: 'Please enter no more than 2083 characters.'
            }
          }
        });

      // Settings opt-in button validation
      $('#settings-form-float-btn').validate({
        rules: {
          'float-list-id': { minStrict: 1 },
          'float-text': { required: true, maxlength: 128 },
          'float-color': { required: true, maxlength: 8 },
          'float-button-color': { required: true, maxlength: 8 }
        },
        messages: {
          'float-list-id': {
            minStrict: 'Please select an FB List.'
          },
          'float-text': {
            required: 'Button Text is required.',
            maxlength: 'Please enter no more than 128 characters.'
          },
          'float-color': {
            required: 'Text Color is required.',
            maxlength: 'Please enter hex color code in the format, #xxxxxx'
          },
          'float-button-color': {
            required: 'Button Text is required.',
            maxlength: 'Please enter hex color code in the format, #xxxxxx'
          }
        }
      });

      // Settings opt-in button validation
      $('#settings-form-btn').validate({
        rules: {
          'rsm-media-url': {
            required: function () {
              return window.useMedia;
            },
            url: function () {
              return window.useMedia;
            },
            maxlength: 2083
          }
        },
        messages: {
          'rsm-media-url': {
            required: 'Please select an image if using this option.',
            url: 'Please enter the complete image URL, including http:// or https://',
            maxlength: 'Please enter no more than 2083 characters.'
          }
        }
      });
    },

    confirm: function () {
      $('.rsm-delete-list').on('click', function () {
        var msg =
          "WARNING:\n\nYou are about to permanently delete the selected list. This will also delete any subscribers, campaigns, notifications (sent & unsent), sequences, segments and statistics associated with this list.\n\nPlease be certain you understand the above because this cannot be undone.\n\nClick 'Cancel' to stop, 'OK' to delete.";
        return confirm(msg);
      });
    },

    ajax: function () {
      var self = this;

      // Refresh autoresponder lists on App Settings page
      $('.rsm-refresh').on('click', function (e) {
        e.preventDefault();

        var arName = $(this).attr('id').replace('-refresh', ''),
          $msObj = $('#' + arName + '-multiselect'),
          refName = arName + '-refresh',
          selected = [];

        // Add all selected options to an array for reloading later
        $msObj.find('option:selected').each(function () {
          selected.push(this.value);
        });

        // Show loading icon
        self.arShowLoading(refName, true);

        // Make ajax call
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: wp_vars.ajaxurl,
          data: {
            action: 'rsm_ajax_ar_get_lists',
            ar_name: arName,
            security: wp_vars.ajaxnonce
          },
          cache: false,
          success: function (response) {
            self.arShowLoading(refName, false);
            if (response.success) {
              $msObj.find('option').remove().end().append(response.html);

              // Reload selected options
              for (var i = 0; i < selected.length; ++i) {
                $msObj
                  .find('option[value=' + selected[i] + ']')
                  .prop('selected', true);
              }
            }
          },
          error: function (response) {
            self.arShowLoading(refName, false);
            alert('Error while sending request: ' + response.data);
          }
        }).complete(function () {
          $msObj.multiselect('rebuild');
        });
      });

      // Connect autoresponder on Autoresponder page
      $('[id$=-connect]').on('click', function (e) {
        e.preventDefault();

        var arName = $(this).val(),
          validator = $('#ar-form').validate(),
          $msObj = $('#' + arName + '-multiselect'),
          apiKey = $('#' + arName + '-api-key').val(),
          options = {},
          selected = [];

        // Add all selected options to an array for reloading later
        $msObj.find('option:selected').each(function () {
          selected.push(this.value);
        });

        switch (arName) {
          case 'activecampaign':
            var valid = true,
              apiURL = $('#activecampaign-api-url').val();
            if (!apiURL) {
              validator.showErrors({
                'activecampaign-api-url': 'This field is required.'
              });
              valid = false;
            }
            if (!apiKey) {
              validator.showErrors({
                'activecampaign-api-key': 'This field is required.'
              });
              valid = false;
            }
            if (!valid) return;
            options = $.param(
              {
                api_url: apiURL
              },
              false
            );
            break;

          case 'aweber':
            if (!apiKey) {
              validator.showErrors({
                'aweber-api-key': 'This field is required.'
              });
              return;
            }
            break;

          case 'benchmark':
            if (!apiKey) {
              validator.showErrors({
                'benchmark-api-key': 'This field is required.'
              });
              return;
            }
            options = $.param(
              {
                double_optin: $('#benchmark-double-optin').is(':checked')
                  ? 'T'
                  : 'F'
              },
              false
            );
            break;

          case 'campaignmonitor':
            if (!apiKey) {
              validator.showErrors({
                'campaignmonitor-api-key': 'This field is required.'
              });
              return;
            }
            break;

          case 'ctct':
            if (!apiKey) {
              validator.showErrors({
                'ctct-api-key': 'This field is required.'
              });
              return;
            }
            break;

          case 'getresponse':
            if (!apiKey) {
              validator.showErrors({
                'getresponse-api-key': 'This field is required.'
              });
              return;
            }
            break;

          case 'icontact':
            var valid = true,
              username = $('#icontact-username').val(),
              password = $('#icontact-password').val();
            if (!username) {
              validator.showErrors({
                'icontact-username': 'This field is required.'
              });
              valid = false;
            }
            if (!apiKey) {
              validator.showErrors({
                'icontact-api-key': 'This field is required.'
              });
              valid = false;
            }
            if (!password) {
              validator.showErrors({
                'icontact-password': 'This field is required.'
              });
              valid = false;
            }
            if (!valid) return;
            options = $.param(
              {
                username: username,
                password: password
              },
              false
            );
            break;

          case 'infusionsoft':
            var valid = true,
              subdomain = $('#infusionsoft-subdomain').val();
            if (!subdomain) {
              validator.showErrors({
                'infusionsoft-subdomain': 'This field is required.'
              });
              valid = false;
            }
            if (!apiKey) {
              validator.showErrors({
                'infusionsoft-api-key': 'This field is required.'
              });
              valid = false;
            }
            if (!valid) return;
            options = $.param(
              {
                subdomain: subdomain
              },
              false
            );
            break;

          case 'mailchimp':
            if (!apiKey) {
              validator.showErrors({
                'mailchimp-api-key': 'This field is required.'
              });
              return;
            }
            options = $.param(
              {
                double_optin: $('#mailchimp-double-optin').is(':checked')
                  ? 'T'
                  : 'F',
                welcome_email: $('#mailchimp-welcome-email').is(':checked')
                  ? 'T'
                  : 'F'
              },
              false
            );
            break;

          case 'mailerlite':
            if (!apiKey) {
              validator.showErrors({
                'mailerlite-api-key': 'This field is required.'
              });
              return;
            }
            break;

          case 'sendinblue':
            if (!apiKey) {
              validator.showErrors({
                'sendinblue-api-key': 'This field is required.'
              });
              return;
            }
            break;

          case 'sendreach':
            var valid = true,
              privateKey = $('#sendreach-private-key').val();
            if (!privateKey) {
              validator.showErrors({
                'sendreach-private-key': 'This field is required.'
              });
              valid = false;
            }
            if (!apiKey) {
              validator.showErrors({
                'sendreach-api-key': 'This field is required.'
              });
              valid = false;
            }
            if (!valid) return;
            options = $.param(
              {
                private_key: privateKey
              },
              false
            );
            break;
        }

        // Show loading icon
        self.arShowLoading(arName, true);

        // Make ajax call
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: wp_vars.ajaxurl,
          data: {
            action: 'rsm_ajax_ar_connect',
            ar_name: arName,
            api_key: apiKey,
            options: options,
            security: wp_vars.ajaxnonce
          },
          cache: false,
          success: function (response) {
            console.log('success');
            self.arShowLoading(arName, false);
            if (!response.success) {
              self.arShowResults(arName, 'fail', response.data);
            } else {
              $msObj.find('option').remove().end().append(response.html);

              // Reload selected options
              for (var i = 0; i < selected.length; ++i) {
                $msObj
                  .find('option[value=' + selected[i] + ']')
                  .prop('selected', true);
              }
              self.arShowResults(arName, 'success', response.data);
              self.arUpdateUI(arName, 'connected');
            }
          },
          error: function (response) {
            console.log('error');
            self.arShowLoading(arName, false);
            self.arShowResults(
              arName,
              'fail',
              'Error while sending request: ' + response.data
            );
          }
        }).complete(function () {
          $msObj.multiselect('rebuild');
        });
      });

      // Disconnect autoresponder on Autoresponders page
      $('[id$=-disconnect]').on('click', function (e) {
        e.preventDefault();

        var arName = $(this).val();

        // Show loading icon
        self.arShowLoading(arName, true);

        // Make ajax call
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: wp_vars.ajaxurl,
          data: {
            action: 'rsm_ajax_ar_disconnect',
            ar_name: arName,
            security: wp_vars.ajaxnonce
          },
          cache: false,
          success: function (response) {
            self.arShowLoading(arName, false);
            if (!response.success) {
              self.arShowResults(arName, 'fail', response.data);
            } else {
              self.arShowResults(arName, 'success', response.data);
              self.arUpdateUI(arName, 'disconnected');
            }
          },
          error: function (response) {
            self.arShowLoading(arName, false);
            self.arShowResults(
              arName,
              'fail',
              'Error while sending request: ' + response.data
            );
          }
        });
      });

      $('#float-list-id').on('change', function () {
        self.segShowLoading(true);
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: wp_vars.ajaxurl,
          data: {
            action: 'rsm_ajax_get_segment_detail',
            'list-id': $('#float-list-id').val(),
            security: wp_vars.ajaxnonce
          },
          cache: false,
          success: function (response) {
            var $segmentId = $('#float-segment-id'),
              output = [];

            $segmentId
              .empty()
              .append('<option value="0">All Subscribers</option>');
            if (response.success) {
              $.each(response.data, function (key, value) {
                output.push(
                  '<option value=' +
                    value['segment_id'] +
                    '>' +
                    value['segment_name'] +
                    '</option>'
                );
              });
              $segmentId.append(output.join(''));
            }
          },
          complete: function (response) {
            self.segShowLoading(false);
          }
        });
      });
    }
  };
  if ($('.settings-page').length > 0) RSM_Settings.init();

  /*----------------------------------------------------------------------------*
   * Campaigns page
   *----------------------------------------------------------------------------*/
  var RSM_Campaigns = {
    init: function () {
      this.confirm();
      this.ajax();
    },

    confirm: function () {
      $('.rsm-delete-campaign').on('click', function () {
        var msg =
          "WARNING:\n\nYou are about to permanently delete the selected campaign. This will also delete any notifications (sent & unsent), sequences and statistics associated with this campaign.\n\nPlease be certain you understand the above because this cannot be undone.\n\nClick 'Cancel' to stop, 'OK' to delete.";
        return confirm(msg);
      });
      $('.rsm-delete-sequence').on('click', function () {
        var msg =
          "WARNING:\n\nYou are about to delete the selected sequence message. This will also delete any notifications (sent & unsent) and statistics associated with this message.\n\nPlease be certain you understand the above because this cannot be undone.\n\nClick 'Cancel' to stop, 'OK' to delete.";
        return confirm(msg);
      });
    },

    ajax: function () {
      $('#the-list').on('click', '.view_campaign a', function () {
        var data = {
          action: 'rsm_ajax_get_campaign_view',
          'campaign-id': $(this).data('campaign-id'),
          security: wp_vars.ajaxnonce
        };
        var $thickboxLog = $('#campaign_id_' + data.campaign_id);

        // Do not fetch data if we already did so
        if ($thickboxLog.data('view-state') == 'loaded') {
          return;
        }

        // Fetch the data
        $.get(wp_vars.ajaxurl, data, function (response, status) {
          $('#TB_ajaxContent').html(response);
          $thickboxLog.data('view-state', 'loaded');
        });
      });
    }
  };
  if ($('.campaigns-page').length > 0) RSM_Campaigns.init();

  /*----------------------------------------------------------------------------*
   * Campaigns config page
   *----------------------------------------------------------------------------*/
  var RSM_Campaigns_Config = {
    init: function () {
      this.ui();
      this.validate();
      this.ajax();
    },

    segShowLoading: function (show) {
      if (show) {
        $('#seg-loading').show();
      } else {
        $('#seg-loading').hide();
      }
    },

    ajaxUpdateSegmentCount: function () {
      var self = this,
        listId = $('#list-id').val(),
        segmentId = $('#segment-id').val();

      if (listId == 'Select FB List...') return;
      self.segShowLoading(true);

      // Update segment fields in real-time
      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: wp_vars.ajaxurl,
        data: {
          action: 'rsm_ajax_segment_count',
          data: {
            'list-id': listId,
            'segment-id': segmentId
          },
          security: wp_vars.ajaxnonce
        },
        cache: false,
        success: function (response) {
          $('#rsm-recipient-count').html(response.data);
        },
        error: function (response) {
          $('#rsm-recipient-count').html('0');
        },
        complete: function (response) {
          self.segShowLoading(false);
        }
      });
    },

    ui: function () {
      var self = this;

      // Smooth scroll on Sequence page edit
      var $campaignsTop = $('#campaigns-top');
      if ($campaignsTop.length > 0 && !($('.campaigns-error').length > 0)) {
        $('html, body').animate(
          {
            scrollTop: $campaignsTop.offset().top - 40
          },
          1100
        );
      }

      //$('.sequences tbody').sortable();

      // Update elements based on campaign type
      $('#campaign-type')
        .on('change', function () {
          var $btnSubmit = $('#rsm-btn-submit'),
            $btnAddMore = $('#rsm-btn-add-more'),
            mode = $('input[name=rsm-mode]').val();

          window.curCampType = this.value;
          $(this)
            .find('option:selected')
            .each(function () {
              if (window.curCampType == 'I') {
                $('.rsm-instant').show();
                $('.rsm-scheduled').hide();
                $('.rsm-sequence').hide();
                $btnSubmit.html(mode == 'add' ? 'Send Now' : 'Update');
              } else if (window.curCampType == 'L') {
                $('.rsm-instant').hide();
                $('.rsm-scheduled').show();
                $('.rsm-sequence').hide();
                $btnSubmit.html(mode == 'add' ? 'Schedule' : 'Update');
              } else if (window.curCampType == 'S') {
                $('.rsm-instant').hide();
                $('.rsm-scheduled').hide();
                $('.rsm-sequence').show();
                $btnSubmit.html(
                  mode == 'edit-seq' ? 'Update & Exit' : 'Save & Exit'
                );
                $btnAddMore.html(
                  mode == 'edit-seq' ? 'Update & Add More' : 'Save & Add More'
                );
              }
            });
        })
        .change();

      $('#segment-id')
        .on('change', function () {
          self.ajaxUpdateSegmentCount();
        })
        .change();
    },

    validate: function () {
      // Instant notification validation
      $('#campaigns-form').validate({
        rules: {
          'list-id': { minStrict: 1 },
          'campaign-name': { required: true, maxlength: 100 },
          'campaign-desc': { required: false, maxlength: 1024 },
          //'message-text': { required: true, maxlength: 180 },
          'redirect-url': {
            required: true,
            url: true,
            canvas_url: function () {
              return window.curRedirectType == 'I';
            },
            maxlength: 2083
          },
          'schedule-date': {
            required: function () {
              return window.curCampType == 'L';
            },
            date: true
          },
          'schedule-time': {
            required: function () {
              return window.curCampType == 'L';
            },
            time12h: true
          },
          'seq-delay': {
            required: function () {
              return window.curCampType == 'S';
            },
            min: 0,
            max: 65535
          }
        },
        messages: {
          'list-id': {
            minStrict: 'Please select an FB List.'
          },
          'campaign-name': {
            required: 'Campaign name is required.',
            maxlength: 'Please enter no more than 100 characters.'
          },
          'campaign-desc': {
            maxlength: 'Please enter no more than 1024 characters.'
          },
          'redirect-url': {
            required: 'Redirect URL is required.',
            url: 'Please enter a valid Redirect URL, including http:// or https://',
            maxlength: 'Please enter no more than 2083 characters.'
          },
          'schedule-date': {
            required: 'Schedule date is required.',
            date: 'Schedule date is invalid.'
          },
          'schedule-time': {
            required: 'Schedule time is required.'
          },
          'seq-delay': {
            required: 'Delay is required.',
            min: 'Sequence delay must be 0 or greater.',
            max: 'Please enter a value less than or equal to 65535.'
          }
        }
      });
    },

    ajax: function () {
      var self = this;

      $('#list-id').on('change', function () {
        self.segShowLoading(true);
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: wp_vars.ajaxurl,
          data: {
            action: 'rsm_ajax_get_segment_detail',
            'list-id': $('#list-id').val(),
            security: wp_vars.ajaxnonce
          },
          cache: false,
          success: function (response) {
            var $segmentId = $('#segment-id'),
              output = [];

            $segmentId
              .empty()
              .append('<option value="0">All Subscribers</option>');
            if (response.success) {
              $.each(response.data, function (key, value) {
                output.push(
                  '<option value=' +
                    value['segment_id'] +
                    '>' +
                    value['segment_name'] +
                    '</option>'
                );
              });
              $segmentId.append(output.join(''));
            }
          },
          complete: function (response) {
            self.ajaxUpdateSegmentCount();
          }
        });
      });
    }
  };
  if ($('.campaigns-config').length > 0) RSM_Campaigns_Config.init();

  /*----------------------------------------------------------------------------*
   * Subscribers page
   *----------------------------------------------------------------------------*/
  var RSM_Subscribers = {
    init: function () {
      this.ui();
      this.confirm();
      this.ajax();
    },

    ui: function () {
      // Handle the input file
      $('.btn-file :file').on('change', function () {
        var $input = $(this).parents('.input-group').find(':text'),
          label = $(this).val().replace(/\\/g, '/').replace(/.*\//, '');
        $input.val(label);
      });

      // Smooth scroll on Lists page edit
      var $importTop = $('#import-top');
      if (
        $('.subscribers-page').length > 0 &&
        $importTop.length > 0 &&
        !($('.settings-error').length > 0)
      ) {
        $('html, body').animate(
          {
            scrollTop: $importTop.offset().top - 40
          },
          1100
        );
      }
    },

    confirm: function () {
      $('.rsm-delete-subscriber').on('click', function () {
        var msg =
          "WARNING:\n\nYou are about to delete the selected subscriber. This will also delete any notifications (sent & unsent) and statistics associated with this subscriber.\n\nPlease be certain you understand the above because this cannot be undone.\n\nClick 'Cancel' to stop, 'OK' to delete.";
        return confirm(msg);
      });
    },

    ajax: function () {
      $('#the-list').on('click', '.view_subscriber a', function () {
        var data = {
          action: 'rsm_ajax_get_subscriber_view',
          'subscriber-id': $(this).data('subscriber-id'),
          security: wp_vars.ajaxnonce
        };
        var $thickboxLog = $('#subscriber_id_' + data.subscriber_id);

        // Do not fetch data if we already did so
        if ($thickboxLog.data('view-state') == 'loaded') {
          return;
        }

        // Fetch the data
        $.get(wp_vars.ajaxurl, data, function (response, status) {
          $('#TB_ajaxContent').html(response);
          $thickboxLog.data('view-state', 'loaded');
        });
      });
    }
  };
  if ($('.subscribers-page').length > 0) RSM_Subscribers.init();

  /*----------------------------------------------------------------------------*
   * Segmenting page
   *----------------------------------------------------------------------------*/
  var RSM_Segmenting = {
    init: function () {
      this.ui();
      this.confirm();
      this.ajax();
    },

    segGetCriteria: function () {
      var $controls = $('#seg-controls'),
        fields = {},
        rules = {},
        values = {};

      // Capture filtering criteria for saving
      $controls.find('.rsm-segment-field').each(function (index, item) {
        fields[index] = item.value;
      });
      $controls.find('.rsm-segment-rule').each(function (index, item) {
        rules[index] = item.value;
      });
      $controls
        .find(
          '.rsm-segment-input:visible, .rsm-segment-combo:visible, .rsm-segment-datepicker:visible'
        )
        .each(function (index, item) {
          values[index] = item.value;
        });

      return {
        fields: fields,
        rules: rules,
        values: values
      };
    },

    segUpdateEntryUI: function (elem, type, options) {
      var $input = elem.find('.rsm-segment-input'),
        $combo = elem.find('.rsm-segment-combo'),
        $date = elem.find('.rsm-segment-datepicker'),
        showInput = type == 'input' || type == 'none',
        showCombo = type == 'combo',
        showDate = type == 'datepicker',
        showNone = type == 'none';

      // Hide any lingering errors
      elem.find('.rsm-group').removeClass('has-error');
      elem.find('.error-help-block').hide();

      // Handle input and none
      if (showNone) $input.val('');
      $input.prop('disabled', showNone);
      $input.toggle(showInput);

      // Handle combobox
      if (showCombo) {
        var output = [];
        $.each(options, function (key, value) {
          for (var i in value) {
            output.push('<option>' + value[i] + '</option>');
          }
        });
        $combo.html(output.join(''));
      }
      $combo.toggle(showCombo);

      // Handle datepicker
      if (showDate) {
        $date.datetimepicker({
          timepicker: false,
          format: 'Y-m-d',
          formatDate: 'Y-m-d',
          closeOnDateSelect: true
        });
      }
      $date.toggle(showDate);
    },

    segShowLoading: function (show) {
      if (show) {
        $('#seg-loading').show();
      } else {
        $('#seg-loading').hide();
      }
    },

    segAddEntryRow: function (elem) {
      var self = this,
        $curEntry = elem.parents('.entry:first'),
        $newEntry = $($curEntry.clone()).insertAfter($curEntry);

      $newEntry.find("[name$=']']").each(function () {
        this.name = this.name.replace(/\[\d+\]/, '[' + window.entryIndex + ']');
      });
      window.entryIndex++;

      if ($('.entry').length > 1) $('.rsm-delete-rule').prop('disabled', false);

      elem.blur();
      $newEntry.find('.rsm-segment-input').val('');
      self.segUpdateEntryUI($newEntry, 'input');
    },

    ajaxUpdateSegmentCount: function () {
      if (!window.segCount) return;

      // Update segment fields in real-time
      var self = this,
        listId = $('#segment-list-id').val(),
        matchType = $("input[name$='opt-match']:checked").attr('value'),
        objCriteria = self.segGetCriteria();

      self.segShowLoading(true);

      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: wp_vars.ajaxurl,
        data: {
          action: 'rsm_ajax_segment_count',
          data: {
            'list-id': listId,
            'match-type': matchType,
            fields: objCriteria['fields'],
            rules: objCriteria['rules'],
            values: objCriteria['values']
          },
          security: wp_vars.ajaxnonce
        },
        cache: false,
        success: function (response) {
          $('#rsm-recipient-count').html(response.data);
        },
        error: function (response) {
          $('#rsm-recipient-count').html('0');
        },
        complete: function (response) {
          self.segShowLoading(false);
        }
      });
    },

    segResetUI: function () {
      var self = this,
        $entry = $('.entry');

      $('#opt-match-any').icheck('checked');

      $entry.slice(1).remove();
      $('#segment-list-id, .rsm-segment-field, .rsm-segment-rule')
        .find('option:eq(0)')
        .prop('selected', true);
      $('.rsm-segment-input').val('');

      self.segUpdateEntryUI($entry, 'input');
      $('#segment-results').find('.alert').remove();
      self.ajaxUpdateSegmentCount();
    },

    ajaxUpdateSegmentUI: function (segmentListId) {
      // Update segment fields in real-time
      var self = this;

      self.segShowLoading(true);
      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: wp_vars.ajaxurl,
        data: {
          action: 'rsm_ajax_get_segment_detail',
          'segment-id': segmentListId,
          security: wp_vars.ajaxnonce
        },
        cache: false,
        success: function (response) {
          if (!response.success) {
            self.segShowResults('false', response.data);
          } else {
            var data = response.data,
              len = data.length,
              listId = data[0]['list_id'],
              $controls = $('#seg-controls'),
              field_type,
              $field,
              $rule;

            // Temporarily suspend real-time segment counting until after loading
            window.segCount = false;
            self.segResetUI();

            // Update checkbox
            if (data[0]['match_type'] == 'any') {
              $('#opt-match-any').icheck('checked');
            } else {
              $('#opt-match-all').icheck('checked');
            }

            // Add necessary number of rows
            $('#segment-list-id').val(listId);
            for (var i = 0; i < len - 1; i++) {
              // -1 because we already have one row after UI reset
              self.segAddEntryRow($('.rsm-group'));
            }

            // Populate all criteria columns
            $controls.find('.entry').each(function (index, item) {
              field_type = data[index]['field'];

              $field = $(item).find('.rsm-segment-field');
              $field.val(field_type);

              $.when($field.trigger('change')).done(function () {
                $rule = $(item).find('.rsm-segment-rule');
                $rule.val(data[index]['rule']);

                $.when($rule.trigger('change')).done(function () {
                  switch (field_type) {
                    case 'email':
                    case 'first_name':
                    case 'last_name':
                    case 'uid':
                      $(item)
                        .find('.rsm-segment-input')
                        .val(data[index]['value']);
                      break;

                    case 'gender':
                    case 'locale':
                    case 'timezone':
                      $(item)
                        .find('.rsm-segment-combo')
                        .val(data[index]['value']);
                      break;

                    case 'clicked':
                    case 'optin_date':
                      $(item)
                        .find('.rsm-segment-datepicker')
                        .val(data[index]['value']);
                      break;
                  }
                });
              });
            });

            // Update segment count
            window.segCount = true;
            self.ajaxUpdateSegmentCount();
          }
        },
        error: function (response) {
          self.segShowResults(
            'fail',
            'Error loading segment: ' + response.data
          );
        },
        complete: function (response) {
          self.segShowLoading(false);
        }
      });
    },

    segShowResults: function (status, message) {
      var color = status == 'success' ? 'bg-rsm-light-green' : 'bg-rsm-red',
        html =
          '<div class="alert ' +
          color +
          ' alert-dismissable"><button aria-hidden="true" data-dismiss="alert" class="close" type="button">&times;</button>' +
          message +
          '</div>';

      $('#segment-results').html(html);
    },

    ui: function () {
      var self = this;
      window.entryIndex = 1;
      window.segMode = 'add';
      window.segCount = true;

      // Don't allow first rule to be deleted
      if ($('.entry').length == 1) $('.rsm-delete-rule').prop('disabled', true);
      self.segUpdateEntryUI($('.entry:first'), 'input');

      // Add new segment button
      $('#add-new-segment').on('click', function (e) {
        e.preventDefault();
        window.segMode = 'add';

        self.segResetUI();

        $('#submit-segment').text('Save Segment').prop('disabled', false);
        $('#segment-name').val('').focus();
      });

      $('#edit-segment').on('click', function (e) {
        e.preventDefault();
        window.segMode = 'update';

        var $segmentList = $('#segment-list'),
          $segmentName = $('#segment-name'),
          segmentListId = $segmentList.val(),
          selected = $segmentList.find(':selected').attr('data-name'); //.text();

        // Check that a segment is selected
        if ($.isNumeric(segmentListId)) {
          $segmentName.val(selected);
          $('#segment-results').find('.alert').remove();

          self.ajaxUpdateSegmentUI(segmentListId);

          //$('#submit-segment').text('Update Segment').prop("disabled", true);
          $('#submit-segment').prop('disabled', true);
          $segmentName.focus();
        } else {
          $segmentList.focus();
        }
      });

      $('#segment-list-id').on('change', function () {
        self.ajaxUpdateSegmentCount();
      });

      $("input[name$='opt-match']").on('change', function () {
        self.ajaxUpdateSegmentCount();
      });

      // Handles changing of field dropdown
      $('#seg-controls')
        .on('change', '.rsm-segment-field', function (e) {
          // Field rules
          var rules = {
            email: [
              'is equal to',
              'is not equal to',
              'contains',
              'does not contain',
              'is one of',
              'is not one of'
            ],
            first_name: [
              'is equal to',
              'is not equal to',
              'contains',
              'does not contain',
              'is one of',
              'is not one of'
            ],
            last_name: [
              'is equal to',
              'is not equal to',
              'contains',
              'does not contain',
              'is one of',
              'is not one of'
            ],
            gender: ['is equal to', 'is not equal to'],
            locale: ['is equal to', 'is not equal to'],
            timezone: ['is equal to', 'is not equal to'],
            clicked: ['never', 'any campaign', 'before', 'after'],
            optin_date: ['before', 'after'],
            uid: [
              'is equal to',
              'is not equal to',
              'is one of',
              'is not one of'
            ]
          };

          var $entry = $(this).parents('.entry'),
            field = $(this).val(),
            rls = rules[field] || [];

          // Update rules column
          var html = $.map(rls, function (rl) {
            return (
              '<option value="' +
              rl.replace(/ /g, '_') +
              '">' +
              rl +
              '</option>'
            );
          }).join('');

          html = '<option hidden>Select rule...</option>' + html;
          $entry.find('.rsm-segment-rule').html(html);

          //Update values column
          switch (field) {
            case 'email':
            case 'first_name':
            case 'last_name':
            case 'uid':
              self.segUpdateEntryUI($entry, 'input');
              break;

            case 'gender':
              self.segUpdateEntryUI($entry, 'combo', window.segGender);
              break;

            case 'locale':
              self.segUpdateEntryUI($entry, 'combo', window.segLocale);
              break;

            case 'timezone':
              self.segUpdateEntryUI($entry, 'combo', window.segTimezone);
              break;

            case 'clicked':
            case 'optin_date':
              self.segUpdateEntryUI($entry, 'datepicker');
              break;
          }
          self.ajaxUpdateSegmentCount();

          // Handles changing of rule dropdown
        })
        .on('change', '.rsm-segment-rule', function (e) {
          var $entry = $(this).parents('.entry'),
            field = $entry.find('.rsm-segment-field').val(),
            rule = $(this).val();

          if (field == 'clicked') {
            if (rule == 'before' || rule == 'after') {
              self.segUpdateEntryUI($entry, 'datepicker');
            } else {
              self.segUpdateEntryUI($entry, 'none');
            }
          }
          self.ajaxUpdateSegmentCount();

          // Handles clicking of add rule button
        })
        .on('click', '.rsm-add-rule', function (e) {
          e.preventDefault();
          self.segAddEntryRow($(this));

          // Handles clicking of delete rule button
        })
        .on('click', '.rsm-delete-rule', function (e) {
          e.preventDefault();
          var $entry = $('.entry');

          if ($entry.length > 1) $(this).parents('.entry:first').remove();
          if ($entry.length == 1) $('.rsm-delete-rule').prop('disabled', true);
          self.ajaxUpdateSegmentCount();
        })
        .on('blur', '.rsm-segment-input', function (e) {
          console.log('blur');
          self.ajaxUpdateSegmentCount();
        })
        .on(
          'change',
          '.rsm-segment-combo, .rsm-segment-datepicker',
          function (e) {
            console.log('change');
            self.ajaxUpdateSegmentCount();
          }
        );
    },

    confirm: function () {
      var self = this;

      $('#delete-segment').on('click', function () {
        var segmentListId = $('#segment-list').val(),
          msg =
            "WARNING:\n\nYou are about to permanently delete the selected segment. This will also delete any notifications (sent & unsent) and statistics associated with this segment.\n\nPlease be certain you understand the above because this cannot be undone.\n\nClick 'Cancel' to stop, 'OK' to delete.";

        if ($.isNumeric(segmentListId) && segmentListId >= 1) {
          if (confirm(msg)) {
            $.ajax({
              type: 'POST',
              dataType: 'json',
              url: wp_vars.ajaxurl,
              data: {
                action: 'rsm_ajax_delete_segment',
                'segment-id': segmentListId,
                security: wp_vars.ajaxnonce
              },
              cache: false,
              success: function (response) {
                if (!response.success) {
                  self.segShowResults('fail', response.data);
                } else {
                  // Add new segment name to list
                  $('#segment-list option:selected').remove();

                  self.segResetUI();
                  $('#segment-name').val('');
                  self.segShowResults('success', response.data);
                }
              },
              error: function (response) {
                self.segShowResults(
                  'fail',
                  'Error while sending request: ' + response.data
                );
              }
            });
          }
        }
        $(this).blur();
      });
    },

    ajax: function () {
      var self = this;

      // Setup field specific combo options
      window.segGender = [{ gender: 'Male' }, { gender: 'Female' }];

      // Fetch locale
      $.get(
        wp_vars.ajaxurl,
        { action: 'rsm_ajax_get_locale', security: wp_vars.ajaxnonce },
        function (response, status) {
          window.segLocale = response.data;
        }
      );

      // Fetch timezone
      $.get(
        wp_vars.ajaxurl,
        { action: 'rsm_ajax_get_timezone', security: wp_vars.ajaxnonce },
        function (response, status) {
          window.segTimezone = response.data;
        }
      );

      // Handle saving of segment
      $('#submit-segment').on('click', function (e) {
        e.preventDefault();

        var validator = $('#segment-form').validate(),
          $listId = $('#segment-list-id'),
          segmentName = $('#segment-name').val(),
          segmentListId = $listId.val(),
          segmentListName = $listId.find(':selected').text(),
          matchType = $("input[name$='opt-match']:checked").attr('value'),
          //$controls = $('#seg-controls'),
          objErrors = {};

        // Manual validation
        if (!segmentName) {
          objErrors['segment-name'] = 'Segment Name is required.';
        }
        if (!$.isNumeric(segmentListId) || segmentListId < 1) {
          objErrors['segment-list-id'] = 'Please select an FB List.';
        }
        /*
                $controls.find('.rsm-segment-field').each(function (index, item) {
                    if (item.value == 'Select field...') {
                        objErrors[this.name] = "Please select a field.";
                    }
                });
                $controls.find('.rsm-segment-rule').each(function (index, item) {
                    if (item.value == 'Select rule...') {
                        objErrors[this.name] = "Please select a rule.";
                    }
                });
                $controls.find('.rsm-segment-input:visible, .rsm-segment-combo:visible, .rsm-segment-datepicker:visible').each(function (index, item) {
                    var isDisabled = $(item).prop('disabled');
                    if (!item.value && !isDisabled) {
                        objErrors[this.name] = "Please enter a value.";
                    }
                });*/

        // Display any error encountered
        if (!$.isEmptyObject(objErrors)) {
          validator.showErrors(objErrors);
          return;
        }

        // Make ajax call
        var objCritera = self.segGetCriteria();
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: wp_vars.ajaxurl,
          data: {
            action: 'rsm_ajax_save_segment',
            mode: window.segMode,
            data: {
              'segment-name': segmentName,
              'list-id': segmentListId,
              'match-type': matchType,
              fields: objCritera['fields'],
              rules: objCritera['rules'],
              values: objCritera['values']
            },
            security: wp_vars.ajaxnonce
          },
          cache: false,
          success: function (response) {
            if (!response.success) {
              self.segShowResults('fail', response.data);
            } else {
              // Add new segment name to list
              $('#segment-list').append(
                $('<option>', {
                  value: response.segmentId,
                  text: segmentListName + ' >> ' + segmentName,
                  'data-name': segmentName,
                  'data-match': matchType
                })
              );

              self.segShowResults('success', response.data);
            }
          },
          error: function (response) {
            console.log('err:' + response);
            self.segShowResults(
              'fail',
              'Error while sending request: ' + response.data
            );
          }
        });
      });
    }
  };
  if ($('.segmenting-page').length > 0) RSM_Segmenting.init();

  /*----------------------------------------------------------------------------*
   * Log page
   *----------------------------------------------------------------------------*/
  var RSM_Log = {
    init: function () {
      this.confirm();
    },

    confirm: function () {
      $('.rsm-delete-notification').on('click', function () {
        var msg =
          "WARNING:\n\nYou are about to delete the selected notification.\n\nPlease be certain you understand the above because this cannot be undone.\n\nClick 'Cancel' to stop, 'OK' to delete.";
        return confirm(msg);
      });
    }
  };
  if ($('.log-page').length > 0) RSM_Log.init();

  /*----------------------------------------------------------------------------*
   * Help page
   *----------------------------------------------------------------------------*/
  var RSM_Help = {
    init: function () {
      this.ui();
    },

    ui: function () {
      //var anchor = window.location.hash.replace("#", "");
      //$("#" + anchor).siblings().collapse('show');

      $(window.location.hash).siblings().toggleClass('in');
    }
  };
  if ($('.help-page').length > 0) RSM_Help.init();
});
