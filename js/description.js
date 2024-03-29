// $Id: description.js 12129 2012-08-17 17:19:59Z david $

(function ($)
  {
    Drupal.behaviors.description = {
      attach: function (context)
        {
          $('.description', context).hide();

          $(':has(> .description)', context)
            .focusin(window.description_focusin ? window.description_focusin : function ()
              {
                var $this = $(this);
                var $description = $('.description', this);
                var $sidebar = $('#sidebar-first');
                var $content = $('#content');

                // Specific case for tooltips in YUI dialogs
                var $dialog = $this.closest('div.yui-panel');
                if ($dialog.length)
                {
                  var positionateDialog = function()
                    {
                      $description

                        // Remove position relative to align with respect to the dialog
                        .closest('.form-item').css('position', 'static').end()

                        .addClass('description-dialog')

                        .clone().prependTo($dialog)

                        // Show tooltip
                        .show();
                    };

                  positionateDialog();

                  return true;
                }

                // Let's see what is the best position,
                // - Right side (class description-right): tooltip top position
                // in the document shouldn't be in conflict with the sidebar
                // - Left side (class description-left): tooltip width must fit
                // in the space between the form fieldset and the left of the
                // document
                // - Bottom (class description): when right and left sides
                // don't work */
                if ($this.offset().top <= $sidebar.offset().top + $sidebar.height())
                {
                  // I have to render the tooltip to get tooltipWidth value
                  $description.addClass('description-left').show().css('visibility', 'hidden');
                  var tooltipWidth = $description.width() + parseInt($description.css('margin-right'));
                  $description.removeClass('description-left').css('visibility', 'visible').hide();

                  var offset = $this.closest('fieldset').offset();
                  if (offset && offset.left >= tooltipWidth)
                  {
                    $description.addClass('description-left');
                  }
                }
                else
                {
                  $description.addClass('description-right');
                }

                // Show the tooltip
                $description.show();
              })
            .focusout(window.description_focusout ? window.description_focusout : function ()
              {
                $('.description', this)
                  .removeClass('description-left')
                  .removeClass('description-right')
                  .hide();

                $('div.yui-panel > .description-dialog').remove();
              });
        } };
  })(jQuery);
