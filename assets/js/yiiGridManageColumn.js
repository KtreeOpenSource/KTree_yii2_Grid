/**
 * Yii gridManageColumn widget.
 *
 * This is the JavaScript widget used by the ktree\app\grid\GridView widget for manage columns.
 *
 * @link      http://ktreeportal.com/
 * @copyright Copyright (c) 2016 KTree.com.
 * @license   http://ktreeportal.com/license
 */
(function ($) {
    $.fn.gridManageColumn = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.gridManageColumn');
            return false;
        }
    };
    var gridData = {};
    var defaults = {
        hiddenColumns: undefined,
        activeColumns: undefined,
        hiddenContainer: '.hidden-filed-container',
        visibleContainer: '.visible-filed-container'
    };
    var manageColumnEvents = {
        /**
         * openManageColumn event is triggered to open or close managecolumn popup.
         */
        openManageColumn: 'manageColumn',

    };

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $e = $(this);
                var settings = $.extend({}, defaults, options || {});
                var id = $e.attr('id');
                if (gridData[id] === undefined) {
                    gridData[id] = {};
                }
                gridData[id] = $.extend(gridData[id], {settings: settings});
                methods.buildList($('#' + id + '-manage-dialog ' + settings.hiddenContainer), settings.hiddenColumns);
                methods.buildList($('#' + id + '-manage-dialog ' + settings.visibleContainer), settings.activeColumns);
                methods.initSortable.apply($e);

                $(this).on('manage-column', function (e, state) {
                    if (state == 'open') {
                        $('#' + id + '-manage-dialog').dialog({
                            title: 'Select Columns',
                            modal: true,
                            draggable: false,
                            resizable: false,
                            width: 400,
                            height: 'auto',
                            close: function (event, ui) {
                                $(this).dialog('destroy');
                            }
                        });
                    }
                });
                $('#' + id + '-manage-dialog').on('keyup', '.search-list', function () {
                    var listContainer = $(this).closest('.body').find('ul.manage-col-sortable');
                    var pattern = $(this).val();
                    if (pattern != null && pattern != '') {
                        $('li', listContainer).hide();
                        $("li", listContainer).each(function (index) {
                            var item = $(this);
                            var selector = item.attr('data-filter');
                            var regex = new RegExp(pattern, 'ig');
                            if (selector.match(regex) != null) {
                                item.show();
                            }
                        });
                    } else {
                        $('li', listContainer).show();
                    }
                });
                $('#' + id + '-manage-dialog').on('click', 'button.save-manage-columns', function () {
                    var showColumnsArray = [];
                    var hideColumnsArray = [];
                     $('#'+id+'-manage-dialog').find(settings.visibleContainer+' li:not(.disable)').each(function (key) {
                        showColumnsArray.push($(this).attr('data-rel'));
                    });
                    $.ajax({
                        type: 'POST',
                        url: settings.gridurl,
                        dataType: 'json',
                        data: {
                            'columns': showColumnsArray,
                            'entity': settings.entity,
                            'grid_id': id
                        },
                        beforeSend: function () {
                            KTree.addLoader('Please wait...');
                        },
                        success: function (result) {
                            if (result.status){
                                window.location.reload();
                            }else{
                                KTree.ajaxNotifyError(result.message, function () {
                                    window.location.reload();
                                })
                            }

                        }, error: function (jqXHR, exception) {
                            KTree.ajaxNotifyError(KTree.ajaxPrepareError(jqXHR, exception), function () {
                                window.location.reload();
                            })
                        }
                    });
                });
            });
        },
        buildList: function ($container, $data) {
            $html = '<ul class="manage-col-sortable">';
            $.each($data, function (k, item) {
                var text = (item.attribute) ? item.label : item.header;
                if(item.fixed)
                  $html+='<li class="placeholder" style="height:0px;padding: 0px;border: none;"></li>';
                $html += '<li class="' + ((item.fixed) ? 'disable' : '') + '" data-filter="' + text + '" data-rel="' + item.attribute + '">' + text + '</li>';
            })
            $html += '</ul>';
            $container.html($html);
        },
        initSortable: function () {
            var $grid = $(this), event;
            var id = $grid.attr('id');
            var settings = gridData[$grid.attr('id')].settings;
            $items = $('li:not(.disable)',this);
            $('#' + id + '-manage-dialog ' + settings.hiddenContainer + '>ul, #' + id + '-manage-dialog ' + settings.visibleContainer + '>ul').sortable({
                connectWith: '#' + id + '-manage-dialog' + ' .manage-col-sortable',
                items: 'li:not(.disable)',
                forcePlaceholderSize: true
            }).disableSelection();
        }
    };
})(window.jQuery);
