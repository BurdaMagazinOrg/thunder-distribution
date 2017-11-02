/**
 * @file
 * Split Text CKEditor plugin.
 *
 * Split text on cursor position to new paragraph.
 */

(function ($, Drupal, drupalSettings, CKEDITOR) {

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
            }, splittextafter: {
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
            return {
              splittextbefore: CKEDITOR.TRISTATE_OFF,
              splittextafter: CKEDITOR.TRISTATE_OFF
            };
          });
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
        // Get editor object
        var editorObject = getEditorObject(editor);
        // Get editor body tag element
        var body = editor.document.getBody();
        // Get selected element
        var selectedElement = editor.getSelection().getStartElement();
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
          // Get delta of paragraph
          var delta = getParagraphDelta(editorObject);
          // Set end of range to last element
          if (direction === 'after') {
            range.setEndAfter(toElement);
          }
          else {
            range.setEndBefore(toElement);
            delta = delta + 1;
          }
          // Copy cutted content tovariable and delete it
          var newContent = range.extractContents();
          // Get content of editor
          var oldContent = editor.getData();
          // Get editor drupal selector
          var originalEditorSelector = editorObject.data('drupal-selector');

          // Trigger add button
          var $buttonWrapper;
          var triggeringElementName;
          // Modal add mode
          if (editorObject.closest('table').parent().find('.paragraph-type-add-modal').length > 0) {
            $buttonWrapper = editorObject.closest('table').parent().find('.paragraphs-add-dialog-template').parent();
            triggeringElementName = $('.js-hide input[name$="_add_more"]', $buttonWrapper).attr('name');
            Drupal.modalAddParagraphs.setValues($buttonWrapper, {
              add_more_select: getParagraphType(editorObject),
              add_more_delta: delta
            });
          }
          // Editor id is changed after submit
          $(document).ajaxComplete(function (e, xhr, settings) {
            var eventElement = settings.extraData._triggering_element_name;
            if (eventElement === triggeringElementName) {
              // Paragraph is added above original
              // Get new paragraph delta
              var newDelta = $('.paragraph-item').length - 1;

              // Get original editor id
              var originalEditor = $('[data-drupal-selector="' + originalEditorSelector + '"]');
              var originalEditorId = originalEditor.attr('id');

              // Get new editor id
              var newEditorSelector = originalEditorSelector.replace(/[0-9]-subform/g, newDelta + '-subform');
              var newEditor = $('[data-drupal-selector="' + newEditorSelector + '"]');
              var newEditorId = newEditor.attr('id');

              // Set content to editors
              if (newEditorId !== undefined) {
                CKEDITOR.instances[newEditorId].setData(oldContent, {
                  callback: function () {
                    this.updateElement();
                    this.element.data('editor-value-is-changed', true);
                  }
                });
              }
              if (originalEditorId !== undefined) {
                CKEDITOR.instances[originalEditorId].setData(newContent.getHtml(), {
                  callback: function () {
                    this.updateElement();
                    this.element.data('editor-value-is-changed', true);
                  }
                });
              }
            }
          });
        }
      }
    }
  });
})(jQuery, Drupal, drupalSettings, CKEDITOR);
