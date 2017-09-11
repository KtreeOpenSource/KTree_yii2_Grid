/*!
 * Tabledit v1.2.3 (https://github.com/markcell/jQuery-Tabledit)
 * Copyright (c) 2015 Celso Marques
 * Licensed under MIT (https://github.com/markcell/jQuery-Tabledit/blob/master/LICENSE)
 */
/**
 * @description Inline editor for HTML tables compatible with Bootstrap
 * @version 1.2.3
 * @author Celso Marques
 */
if (typeof jQuery === 'undefined') {
    throw new Error('Tabledit requires jQuery library.');
}

(function($) {
    'use strict';

    $.fn.Tabledit = function(options) {
        if (!this.is('table')) {
            throw new Error('Tabledit only works when applied to a table.');
        }
        var submit_status = 1;
        var $table = this;
        var defaults = {
            url: options.url,
            inputClass: 'form-control input-sm',
            toolbarClass: 'btn-toolbar',
            groupClass: 'btn-group btn-group-sm',
            dangerClass: 'danger',
            warningClass: 'warning',
            mutedClass: 'text-muted',
            eventType: 'click',
            rowIdentifier: 'id',
            hideIdentifier: false,
            autoFocus: true,
            editButton: true,
            deleteButton: true,
            saveButton: true,
            restoreButton: true,
            buttons: {
                edit: {
                    class: 'btn btn-sm btn-default',
                    html: '<span class="glyphicon glyphicon-pencil"></span>',
                    action: 'edit'
                },
                delete: {
                    class: 'btn btn-sm btn-default',
                    html: '<span class="glyphicon glyphicon-trash"></span>',
                    action: 'delete'
                },
                save: {
                    class: 'btn btn-sm btn-success',
                    html: 'Save'
                },
                restore: {
                    class: 'btn btn-sm btn-warning',
                    html: 'Restore',
                    action: 'restore'
                },
                confirm: {
                    class: 'btn btn-sm btn-danger',
                    html: 'Confirm'
                }
            },
            onDraw: function() {
                return;
            },
            onSuccess: function(data, textStatus, jqXHR) {
                if (data.status) {
                    $(".tabledit-error.currentEdit").parent().removeClass("has-error");
                    $(".tabledit-error.currentEdit").html('');
                    Mode.view($(".tabledit-error:visible").parent());

                    //$(".tabledit-error.currentEdit").removeClass("currentEdit");
                    var $grid = $('#' + options.gridId);
                    $grid.yiiGridView("editReload", editAttribute);
                    return;
                } else {
                    var errors = data.message;
                    $.each(data.message, function(index, value) {
                        $(".tabledit-error.currentEdit." + index).parent().addClass("has-error");
                        $(".tabledit-error.currentEdit." + index).html(value);
                        //    $(".tabledit-error").removeClass("currentEdit");
                        //    Mode.view($(".tabledit-error:visible").parent()) ;
                    });
                    return false;
                }
            },
            onFail: function() {
                return;
            },
            onAlways: function() {
                return;
            },
            onAjax: function(action, serialize) {
                var editText = serialize.substr(0, serialize.indexOf('&action'));
                if (editText)
                    return true;
                else
                    return false;
            }
        };

        var settings = $.extend(true, defaults, options);
        var $lastEditedRow = 'undefined';
        var $lastDeletedRow = 'undefined';
        var $lastRestoredRow = 'undefined';
        var editFlag = 1;
        var editAttribute;

        /**
         * Draw Tabledit structure (identifier column, editable columns, toolbar column).
         *
         * @type {object}
         */
        var Draw = {
            columns: {
                identifier: function() {
                    // Hide identifier column.
                    if (settings.hideIdentifier) {
                        $table.find('th:nth-child(' + parseInt(settings.columns.identifier[0]) + 1 +
                            '), tbody td:nth-child(' + parseInt(settings.columns.identifier[0]) + 1 +
                            ')').hide();
                    }

                    var $td = $table.find('tbody td:nth-child(' + (parseInt(settings.columns.identifier[
                        0]) + 1) + ')');

                    $td.each(function() {
                        var type = $(this).find('input').attr('type');
                        if (type != 'checkbox') {
                            // Create hidden input with row identifier.
                            var span = '<span class="tabledit-span tabledit-identifier">' + $(
                                this).text() + '</span>';
                            var input =
                                '<input class="tabledit-input tabledit-identifier" type="hidden" name="' +
                                    settings.columns.identifier[1] + '" value="' + $(this).parent()
                                    .attr('data-key') + '" disabled>';
                            var error = '<span class="tabledit-error tabledit-identifier" id="' +
                                settings.columns.identifier[1] + '_' + $(this).parent().attr(
                                'data-key') + '" disabled></span>';
                            // Add elements to table cell.
                            $(this).html(span + input + error);

                            // Add attribute "id" to table row.
                            $(this).parent('tr').attr(settings.rowIdentifier, $(this).text());
                        }
                    });
                },
                editable: function() {

                    for (var i = 0; i < settings.columns.editable.length; i++) {

                        //var $td = $table.find('tbody td:nth-child(' + (parseInt(settings.columns.editable[i][0]) + 1) + ')');
                        var $td = $table.find('tbody > tr > td[editable-key=' + settings.columns.editable[
                            i]['attribute'] + ']');

                        $td.each(function() {

                            // Get text of this cell.
                            var text = $(this).html();
                            // Add pointer as cursor.
                            if (!settings.editButton) {
                                $(this).css('cursor', 'pointer');
                            }
                            $(this).addClass('tabledit-view-mode');
                        });
                    }
                },
                toolbar: function() {
                    if (settings.editButton || settings.deleteButton) {
                        var editButton = '';
                        var deleteButton = '';
                        var saveButton = '';
                        var restoreButton = '';
                        var confirmButton = '';

                        // Add toolbar column header if not exists.
                        if ($table.find('th.tabledit-toolbar-column').length === 0) {
                            $table.find('tr:first').append('<th class="tabledit-toolbar-column"></th>');
                        }

                        // Create edit button.
                        if (settings.editButton) {
                            editButton = '<button type="button" class="tabledit-edit-button ' +
                                settings.buttons.edit.class + '" style="float: none;display:none;">' +
                                settings.buttons.edit.html + '</button>';
                        }

                        // Create delete button.
                        if (settings.deleteButton) {
                            deleteButton = '<button type="button" class="tabledit-delete-button ' +
                                settings.buttons.delete.class + '" style="float: none;">' + settings.buttons
                                .delete.html + '</button>';
                            confirmButton = '<button type="button" class="tabledit-confirm-button ' +
                                settings.buttons.confirm.class +
                                '" style="display: none; float: none;">' + settings.buttons.confirm.html +
                                '</button>';
                        }

                        // Create save button.
                        if (settings.editButton && settings.saveButton) {
                            saveButton = '<button type="button" class="tabledit-save-button ' +
                                settings.buttons.save.class + '" style="display: none; float: none;">' +
                                settings.buttons.save.html + '</button>';
                        }

                        // Create restore button.
                        if (settings.deleteButton && settings.restoreButton) {
                            restoreButton = '<button type="button" class="tabledit-restore-button ' +
                                settings.buttons.restore.class +
                                '" style="display: none; float: none;">' + settings.buttons.restore.html +
                                '</button>';
                        }

                        var toolbar = '<div class="tabledit-toolbar ' + settings.toolbarClass +
                            '" style="text-align: left;">\n\
                                           <div class="' +
                            settings.groupClass + '" style="float: none;">' + editButton + deleteButton +
                            '</div>\n\
                                           ' + saveButton +
                            '\n\
                                           ' + confirmButton +
                            '\n\
                                           ' + restoreButton +
                            '\n\
                                       </div></div>';

                        // Add toolbar column cells.
                        $table.find('tr:gt(0)').append('<td style="white-space: nowrap; width: 1%;">' +
                            toolbar + '</td>');
                    }
                }
            }
        };

        /**
         * Change to view mode or edit mode with table td element as parameter.
         *
         * @type object
         */
        var Mode = {
            view: function(td) {
                editFlag = 1;
                // Get table row.
                var $tr = $(td).parent('tr');
                if (!$(td).hasClass('has-error')) {

                    // Disable identifier.
                    $(td).parent('tr').find('.tabledit-input.tabledit-identifier').prop('disabled',
                        true);
                    // Hide and disable input element.
                    $(td).find('.tabledit-input').hide().prop('disabled', true);
                    $(td).find('.tabledit-input-span').hide();
                    $(td).find('.tabledit-error').removeClass("currentEdit");
                    //Hide and disable error element
                    $(td).find('.tabledit-error').hide().prop('disabled', true);
                    // Show span element.
                    $(td).find('.tabledit-span').show();
                    // Add "view" class and remove "edit" class in td element.
                    $(td).removeClass('editable');
                    $(td).addClass('tabledit-view-mode').removeClass('tabledit-edit-mode');
                    // Update toolbar buttons.
                    if (settings.editButton) {
                        $tr.find('button.tabledit-save-button').hide();
                        $tr.find('button.tabledit-edit-button').removeClass('active').blur();
                    }
                }
            },
            edit: function(td) {
                Delete.reset(td);
                // Get table row.
                var $tr = $(td).parent('tr');
                // Enable identifier.
                $tr.find('.tabledit-input.tabledit-identifier').prop('disabled', false);
                // Hide span element.
                $(td).find('.tabledit-span').hide();
                // Get input element.
                var $input = $(td).find('.tabledit-input');
                $(td).find('.tabledit-input-span').show();
                // Enable and show input element.
                $input.prop('disabled', false).show();
                /*  if($(td).find('.editDatePicker').length){
                 if($(td).find('.editDatePicker').datepicker('isDisabled')) {
                 $(td).find('.editDatePicker').datepicker('option','disabled',false);
                 $(td).find('.editDatePicker').datepicker('show');
                 }
                 }*/
                // Get error element.
                var $error = $(td).find('.tabledit-error');
                // Enable and show input element.
                $error.prop('disabled', false).show();
                $error.addClass('currentEdit');
                // Focus on input element.
                if (settings.autoFocus) {
                    $input.focus();
                }
                // Add "edit" class and remove "view" class in td element.
                $(td).addClass('tabledit-edit-mode editable').removeClass('tabledit-view-mode');
                // Update toolbar buttons.
                if (settings.editButton) {
                    $tr.find('button.tabledit-edit-button').addClass('active');
                    $tr.find('button.tabledit-save-button').show();
                }
            }
        };

        /**
         * Available actions for edit function, with table td element as parameter or set of td elements.
         *
         * @type object
         */
        var Edit = {
            reset: function(td) {
                var flag = 1;
                $(td).each(function() {
                    if (this.classList.contains("has-error")) {
                        flag = 0;
                    }
                });
                if (flag) {
                    //  Edit.submit($table.find('td.tabledit-edit-mode'));
                    $(td).each(function() {

                        // Get input element.
                        var $input = $(this).find('.tabledit-input');
                        // Get span text.
                        var text = $(this).find('.tabledit-span').text();
                        // Set input/select value with span text.
                        if ($input.is('select')) {
                            $input.find('option').filter(function() {
                                return $.trim($(this).text()) === text;
                            }).attr('selected', true);
                        } else {
                            $input.val(text);
                        }

                        // Change to view mode.
                        Mode.view(this);

                    });

                    return true;
                } else {
                    return false;
                }

                //return ;
            },
            submit: function(td) {
                var flag = 0;
                var presentRow = '';
                $(td).each(function() {
                    if (this.classList.contains("modified")) {
                        flag = 1;
                        presentRow = $(this).find('.tabledit-input').attr('data-pk');
                    }
                });
                if (flag) {
                    // Send AJAX request to server.
                    var ajaxResult = ajax(settings.buttons.edit.action, presentRow);

                    if (ajaxResult === false) {
                        return;
                    } else {
                        $(td).each(function() {
                            // Get input element.
                            var $input = $(this).find('.tabledit-input');
                            // Set span text with input/select new value.
                            if ($input.is('select')) {
                                $(this).find('.tabledit-span').text($input.find(
                                    'option:selected').text());
                            } else {
                                $(this).find('.tabledit-span').text($input.val());
                            }
                            $(this).removeClass("modified");
                            // Change to view mode.

                            //Mode.view(this);
                        });

                        // Set last edited column and row.
                        $lastEditedRow = $(td).parent('tr');
                        return true;
                    }
                } else {
                    return false;
                }
            }
        };

        /**
         * Available actions for delete function, with button as parameter.
         *
         * @type object
         */
        var Delete = {
            reset: function(td) {
                // Reset delete button to initial status.
                $table.find('.tabledit-confirm-button').hide();
                // Remove "active" class in delete button.
                $table.find('.tabledit-delete-button').removeClass('active').blur();
            },
            submit: function(td) {
                Delete.reset(td);
                // Enable identifier hidden input.
                $(td).parent('tr').find('input.tabledit-identifier').attr('disabled', false);
                // Send AJAX request to server.
                var ajaxResult = ajax(settings.buttons.delete.action);
                // Disable identifier hidden input.
                $(td).parents('tr').find('input.tabledit-identifier').attr('disabled', true);

                if (ajaxResult === false) {
                    return;
                }

                // Add class "deleted" to row.
                $(td).parent('tr').addClass('tabledit-deleted-row');
                // Hide table row.
                $(td).parent('tr').addClass(settings.mutedClass).find(
                    '.tabledit-toolbar button:not(.tabledit-restore-button)').attr('disabled', true);
                // Show restore button.
                $(td).find('.tabledit-restore-button').show();
                // Set last deleted row.
                $lastDeletedRow = $(td).parent('tr');
            },
            confirm: function(td) {
                // Reset all cells in edit mode.
                $table.find('td.tabledit-edit-mode').each(function() {
                    Edit.reset(this);
                });
                // Add "active" class in delete button.
                $(td).find('.tabledit-delete-button').addClass('active');
                // Show confirm button.
                $(td).find('.tabledit-confirm-button').show();
            },
            restore: function(td) {
                // Enable identifier hidden input.
                $(td).parent('tr').find('input.tabledit-identifier').attr('disabled', false);
                // Send AJAX request to server.
                var ajaxResult = ajax(settings.buttons.restore.action);
                // Disable identifier hidden input.
                $(td).parents('tr').find('input.tabledit-identifier').attr('disabled', true);

                if (ajaxResult === false) {
                    return;
                }

                // Remove class "deleted" to row.
                $(td).parent('tr').removeClass('tabledit-deleted-row');
                // Hide table row.
                $(td).parent('tr').removeClass(settings.mutedClass).find('.tabledit-toolbar button').attr(
                    'disabled', false);
                // Hide restore button.
                $(td).find('.tabledit-restore-button').hide();
                // Set last restored row.
                $lastRestoredRow = $(td).parent('tr');
            }
        };

        /**
         * Send AJAX request to server.
         *
         * @param {string} action
         * @param {integer} presentRow
         */
        function ajax(action, presentRow) {
            var serialize = $table.find('.tabledit-input').serialize() + '&id=' + presentRow + '&action=' +
                action + '&model=' + options.model;
            var result = settings.onAjax(action, serialize);
            if (result === false) {
                return false;
            }

            var jqXHR = $.ajax({
                url: settings.url,
                data: serialize,
                type: "POST",
                dataType: 'JSON',
                //async:false,
                beforeSend: function() {
                    KTree.addLoader('Please wait...');
                },
                success: function(data, textStatus, jqXHR) {
                    KTree.removeLoader();
                    settings.onSuccess(data, textStatus, jqXHR);
                },
                error: function(jqXHR, exception) {
                    KTree.ajaxNotifyError(KTree.ajaxPrepareError(jqXHR, exception), function() {
                        KTree.removeLoader();
                    })
                }
            });

            jqXHR.fail(function(jqXHR, textStatus, errorThrown) {
                if (action === settings.buttons.delete.action) {
                    $lastDeletedRow.removeClass(settings.mutedClass).addClass(settings.dangerClass);
                    $lastDeletedRow.find('.tabledit-toolbar button').attr('disabled', false);
                    $lastDeletedRow.find('.tabledit-toolbar .tabledit-restore-button').hide();
                } else if (action === settings.buttons.edit.action) {
                    //$lastEditedRow.addClass(settings.dangerClass);
                }

                settings.onFail(jqXHR, textStatus, errorThrown);
            });

            jqXHR.always(function() {
                settings.onAlways();
            });

            return jqXHR;
        }

        //  Draw.columns.identifier();
        Draw.columns.editable();
        Draw.columns.toolbar();

        settings.onDraw();

        if (settings.deleteButton) {
            /**
             * Delete one row.
             *
             * @param {object} event
             */
            $table.on('click', 'button.tabledit-delete-button', function(event) {
                if (event.handled !== true) {
                    event.preventDefault();

                    // Get current state before reset to view mode.
                    var activated = $(this).hasClass('active');

                    var $td = $(this).parents('td');

                    Delete.reset($td);

                    if (!activated) {
                        Delete.confirm($td);
                    }

                    event.handled = true;
                }
            });

            /**
             * Delete one row (confirm).
             *
             * @param {object} event
             */
            $table.on('click', 'button.tabledit-confirm-button', function(event) {
                if (event.handled !== true) {
                    event.preventDefault();

                    var $td = $(this).parents('td');

                    Delete.submit($td);

                    event.handled = true;
                }
            });
        }

        if (settings.restoreButton) {
            /**
             * Restore one row.
             *
             * @param {object} event
             */
            $table.on('click', 'button.tabledit-restore-button', function(event) {
                if (event.handled !== true) {
                    event.preventDefault();

                    Delete.restore($(this).parents('td'));

                    event.handled = true;
                }
            });
        }

        if (settings.editButton) {

            $(document).on('focusout', $table.selector +
                '> tbody >tr > td > .tabledit-input-span >.tabledit-input:visible',
                function(event) {

                    $table.find('td.tabledit-edit-mode').each(function() {
                        if (this.classList.contains("modified")) {
                            editFlag = 0;
                        }
                    });
                    if (!editFlag)
                        Edit.submit($table.find('td.tabledit-edit-mode'));

                });
            /**
             * Activate row edit on single click
             */

            $($table.selector + '> tbody >tr>td').on('mousedown', function(event) {
                if ($(this).hasClass('action-column') || $(this).find('input').attr('type') ==
                    'checkbox') {
                    return false;
                }
                // if(submit_status){
                //event.stopPropagation();
                var parentRow = $(this).parent();
                var $button = parentRow.find('button.tabledit-edit-button');
                var activated = $button.hasClass('active');

                var submitResult;
                if ($table.find('td.tabledit-edit-mode')) {
                    submitResult = Edit.submit($table.find('td.tabledit-edit-mode'));
                }
                if (!activated && !submitResult) {


                    var editStatus = Edit.reset($table.find('td.tabledit-edit-mode'));
                    if (editStatus) {
                        $(parentRow.find('td.tabledit-view-mode').get().reverse()).each(function() {
                            Mode.edit(this);
                        });
                    }
                }
                //    }
            });


            /**
             * Activate edit mode on all columns.
             *
             * @param {object} event
             */
            $table.on('click', 'button.tabledit-edit-button', function(event) {
                if (event.handled !== true) {
                    event.preventDefault();

                    var $button = $(this);

                    // Get current state before reset to view mode.
                    var activated = $button.hasClass('active');

                    // Change to view mode columns that are in edit mode.
                    Edit.reset($table.find('td.tabledit-edit-mode'));

                    if (!activated) {
                        // Change to edit mode for all columns in reverse way.
                        $($button.parents('tr').find('td.tabledit-view-mode').get().reverse()).each(
                            function() {
                                Mode.edit(this);
                            });
                    }

                    event.handled = true;
                }
            });

            /**
             * Save edited row.
             *
             * @param {object} event
             */
            $table.on('click', 'button.tabledit-save-button', function(event) {
                if (event.handled !== true) {
                    event.preventDefault();

                    // Submit and update all columns.
                    Edit.submit($(this).parents('tr').find('td.tabledit-edit-mode'));

                    event.handled = true;
                }
            });
            /**
             * Change event when input is a select element.
             */
                //$table.on('change', 'select.tabledit-input:visible', function(event) {
            $(document).off('change', '.edit-select:visible').on("change", '.edit-select:visible', function(e) {
                $(this).closest('td').addClass('modified');
                editAttribute = $(this).closest('td').attr('editable-key');
                // Submit and update the column.
                Edit.submit($(this).closest('td'));
            });

            /**
             *triggered when the dropdown is closed
             */
            $(document).on("select2:close", '.edit-select-multi:visible', function(e) {
                $(this).closest('td').addClass('modified');
                editAttribute = $(this).closest('td').attr('editable-key');
                // Submit and update the column.
                Edit.submit($(this).closest('td'));

            });

            /*
             *Triggered when datepicker is closed
             */
            $(document).on("change", '.editDatePicker:visible', function(e) {
                $(this).closest('td').addClass('modified');
                editAttribute = $(this).closest('td').attr('editable-key');
                // Submit and update the column.
                Edit.submit($(this).closest('td'));
            });

            $(document).on("click", '.editDatePicker', function(e) {
                if ($(this).datepicker('isDisabled')) {
                    $(this).datepicker('option', 'disabled', false);
                    $(this).datepicker('show');
                }

            })

            $('body').on("click", function(e) {
                var flag = 1;
                if (!$(e.target).closest('tbody').length) {
                    $table.find('td.tabledit-edit-mode').each(function() {
                        if (this.classList.contains("has-error")) {
                            flag = 0;
                        }

                    });

                    if (flag && editFlag) {
                        if ($(e.target).parents('div').hasClass('ui-datepicker-header')) {
                            return false;
                        }
                        $table.find('td.tabledit-edit-mode').each(function() {
                            Mode.view(this);
                        });
                    }
                }
            });

        } else {
            /**
             * Change to edit mode on table td element.
             *
             * @param {object} event
             */
            $table.on(settings.eventType, 'tr:not(.tabledit-deleted-row) td.tabledit-view-mode', function(event) {
                if (event.handled !== true) {
                    event.preventDefault();

                    // Reset all td's in edit mode.
                    Edit.reset($table.find('td.tabledit-edit-mode'));

                    // Change to edit mode.
                    Mode.edit(this);

                    event.handled = true;
                }
            });

            /**
             * Change event when input is a select element.
             */
            $table.on('change', 'select.tabledit-input:visible', function() {
                if (event.handled !== true) {
                    // Submit and update the column.
                    Edit.submit($(this).parent('td'));

                    event.handled = true;
                }
            });

            /**
             * Click event on document element.
             *
             * @param {object} event
             */
            $(document).on('click', function(event) {
                var $editMode = $table.find('.tabledit-edit-mode');
                // Reset visible edit mode column.
                if (!$editMode.is(event.target) && $editMode.has(event.target).length === 0) {
                    Edit.reset($table.find('.tabledit-input:visible').parent('td'));
                }
            });
        }

        /**
         * Keyup event on document element.
         *
         * @param {object} event
         */
        $(document).on('keyup', function(event) {
            // Get input element with focus or confirmation button.
            var $input = $table.find('.tabledit-input:visible');
            var $button = $table.find('.tabledit-confirm-button');

            if ($input.length > 0) {
                var $td = $input.parents('td');
            } else if ($button.length > 0) {
                var $td = $button.parents('td');
            } else {
                return;
            }


            // Key?
            switch (event.keyCode) {
                case 9: // Tab.
                case 13: // Enter.
                    Edit.submit($td);
                    break;
                case 27: // Escape.
                    Edit.reset($td);
                    Delete.reset($td);
                    break;
                default:
                    editAttribute = $(event.target).closest('td').attr('editable-key');
                    $(event.target).closest('td').addClass('modified');
                    break;
            }
        });

        return this;
    };
}(jQuery));
