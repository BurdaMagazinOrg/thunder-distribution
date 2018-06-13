/**
 * @file
 * Split Text CKEditor plugin.
 *
 * Split text on cursor position to new paragraph.
 *
 * @author Jakub Hnilička ahoj@jakubhnilicka.cz
 */

(function ($, Drupal, drupalSettings, CKEDITOR, window) {

  'use strict';

  CKEDITOR.plugins.add('splittext', {
    hidpi: true,
    requires: '',

    init: function (editor) {
      var $editorObject = getEditorObject(editor);

      // Add positions.
      $editorObject.closest('table').parent().find('.paragraph-item').each(function (index) {
        $(this).attr('data-paragraph-delta', index);
      });

      // Check if selection mode is modal.
      if ($editorObject.closest('table').parent().find('.paragraph-type-add-modal').length > 0) {
        // Check if editor is inside paragraph
        if ($editorObject.parents('.paragraph-item').length > 0) {
          editor.addCommand('splitTextBefore', {
            exec: function (editor) {
              split(editor, 'before');
            }
          });

          editor.addCommand('splitTextAfter', {
            exec: function (editor) {
              split(editor, 'after');
            }
          });

          editor.ui.addButton('SplitTextBefore', {
            label: 'Split Text Before',
            icon: this.path + 'icons/splittext-before.png',
            command: 'splitTextBefore'
          });

          editor.ui.addButton('SplitTextAfter', {
            label: 'Split Text After',
            icon: this.path + 'icons/splittext-after.png',
            command: 'splitTextAfter'
          });

          if (editor.addMenuItems) {
            editor.addMenuGroup('splittext');
            editor.addMenuItems({
              splittextbefore: {
                label: Drupal.t('Split Before'),
                command: 'splitTextBefore',
                icon: this.path + 'icons/splittext-before.png',
                group: 'splittext',
                order: 1
              },
              splittextafter: {
                label: Drupal.t('Split After'),
                command: 'splitTextAfter',
                icon: this.path + 'icons/splittext-after.png',
                group: 'splittext',
                order: 2
              }
            });
          }

          if (editor.contextMenu) {
            editor.contextMenu.addListener(function (element, selection) {
              var menuItems = {};
              menuItems = {
                splittextbefore: CKEDITOR.TRISTATE_OFF,
                splittextafter: CKEDITOR.TRISTATE_OFF
              };
              return menuItems;
            });
          }
        }
      }

      function getRootElement(element) {
        // Get parents of selected element
        var parents = element.getParents();
        // Select first element under body tag html -> body -> element
        return parents[2];
      }

      function getEditorObject(editor) {
        return $('#' + editor.name);
      }

      function getParagraphDelta(editor) {
        return editor.closest('.paragraph-item').data('paragraph-delta');
      }

      function getParagraphType(editor) {
        return editor.closest('.paragraph-item').data('paragraph-type');
      }

      function split(editor, direction) {
        window.direction = direction;
        // Get editor object
        var editorObject = getEditorObject(editor);
        // Get editor body tag element
        var body = editor.document.getBody();
        // Get Selection
        var selection = editor.getSelection();
        // Get selected element
        var selectedElement = selection.getStartElement();
        // Select first element under body tag html -> body -> element
        var rootElement = getRootElement(selectedElement);
        // Get next element of selected element
        var fromElement = (direction === 'after') ? rootElement.getNext() : body.getFirst();
        // Get last element of body
        var toElement = (direction === 'after') ? body.getLast() : rootElement;

        if (fromElement !== null && toElement !== null) {
          // Create new range on document
          var range = editor.createRange(editor.document);
          // Set start of range to next element
          range.setStartBefore(fromElement);
          // Get Delta
          var delta = getParagraphDelta(editorObject);
          // Set end of range to last element
          if (direction === 'after') {
            range.setEndAfter(toElement);
          }
          else {
            range.setEndBefore(toElement);
            delta++;
          }
          // Copy cutted content tovariable and delete it
          window.newContent = range.extractContents();
          // Get content of editor
          window.oldContent = editor.getData();
          // Get editor drupal selector
          window.originalEditorSelector = editorObject.data('drupal-selector');

          // Modal add mode
          if (editorObject.closest('table').parent().find('.paragraph-type-add-modal').length > 0) {
            var $buttonWrapper = editorObject.closest('table').parent().find('.paragraphs-add-dialog-template').parent();
            window.triggeringElementName = $('.js-hide input[name$="_add_more"]', $buttonWrapper).attr('name');
            // Enable splitting
            window.split_trigger = true;
            Drupal.modalAddParagraphs.setValues($buttonWrapper, {
              add_more_select: getParagraphType(editorObject),
              add_more_delta: delta
            });
          }

          // Editor id is changed after submit
          $(document).once('ajax-paragraph').ajaxComplete(function (e, xhr, settings) {
            var split_trigger = false;
            if (typeof window.split_trigger !== 'undefined') {
              split_trigger = window.split_trigger;
            }

            var eventElement = null;
            if (settings.extraData._triggering_element_name) {
              eventElement = settings.extraData._triggering_element_name;
            }
            if (eventElement === window.triggeringElementName && split_trigger === true) {
              // Content
              var originalEditorContent = window.newContent.getHtml();
              var newEditorContent = window.oldContent;
              var originalEditorSelector = window.originalEditorSelector;

              // Get original editor id
              var originalEditor = $('[data-drupal-selector="' + originalEditorSelector + '"]');
              var originalEditorId = originalEditor.attr('id');

              // Get new editor id
              var newEditor = $('td .ajax-new-content textarea');
              var newEditorId = newEditor.attr('id');

              // Set content to editors
              if (typeof originalEditorId !== 'undefined') {
                CKEDITOR.instances[originalEditorId].setData(originalEditorContent, {
                  callback: function () {
                    // Mark textarea as changed
                    this.updateElement();
                    this.element.data('editor-value-is-changed', true);
                  }
                });
              }
              if (typeof newEditorId !== 'undefined') {
                CKEDITOR.instances[newEditorId].setData(newEditorContent, {
                  callback: function () {
                    // Mark textarea as changed
                    this.updateElement();
                    this.element.data('editor-value-is-changed', true);
                  }
                });
              }
            }
            window.split_trigger = false;
          });
        }
      }
    }
  });
})(jQuery, Drupal, drupalSettings, CKEDITOR, this);
