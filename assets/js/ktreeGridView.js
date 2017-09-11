/**
 * KTree GridView widget.
 *
 * This is the JavaScript widget used by the ktree\app\grid\GridView widget.

 */
(function ($) {
    $.fn.yiiGridView = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.yiiGridView');
            return false;
        }
    };

    var defaults = {
        filterUrl: undefined,
        filterSelector: undefined,
        savedListSelector: undefined
    };

    var gridData = {};

    var gridEvents = {
        /**
         * beforeFilter event is triggered before filtering the grid.
         * The signature of the event handler should be:
         *     function (event)
         * where
         *  - event: an Event object.
         *
         * If the handler returns a boolean false, it will stop filter form submission after this event. As
         * a result, afterFilter event will not be triggered.
         */
        beforeFilter: 'beforeFilter',
        /**
         * afterFilter event is triggered after filtering the grid and filtered results are fetched.
         * The signature of the event handler should be:
         *     function (event)
         * where
         *  - event: an Event object.
         */
        afterFilter: 'afterFilter'
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
                var enterPressed = false;
                $(document).off('click','#'+id+' .card-view .grid-card');
                $(document).on('click','#'+id+' .card-view .grid-card',function(){
                    $(this).find('.card-checkbox input[type="checkbox"]').trigger('click');
                });
                $(document).on('click','#'+id+' .card-view .grid-card .card-checkbox input[type="checkbox"]',function(){
                    $card = $(this).closest('li.grid-card');
                    if($(this).is(':checked')){
                        $card.addClass('selected');
                    }else{
                        $card.removeClass('selected');
                    }
                });
                $(document).off('click','#'+id+' .group-header');
                $(document).on('click','#'+id+' .group-header',function(){
                    $(this).closest('tbody').next('tbody').toggle();
                    $('span',this).toggleClass('glyphicon-minus');
                });
                $(document).off('change.yiiGridView keydown.yiiGridView', settings.filterSelector)
                    .on('change.yiiGridView keydown.yiiGridView', settings.filterSelector, function (event) {
                        if (event.type === 'keydown') {
                            if (event.keyCode !== 13) {
                                return; // only react to enter key
                            } else {
                                enterPressed = true;
                            }
                        } else {
                            // prevent processing for both keydown and change events
                            if (enterPressed) {
                                enterPressed = false;
                                return;
                            }
                            enterPressed = true;
                        }
                        if(event.currentTarget.name == 'user_list_preference') {
                            settings.savedListSelector = 'select[name="user_list_preference"]';
                        }
                        methods.applyFilter.apply($e);

                        return false;
                    });
                  var pos = settings.filterUrl.indexOf('?');
                  var entity = settings.entity;
                  var displayMode = settings.filterUrl.lastIndexOf('display-mode=');
                  var displayMode = displayMode < 0 ?'' : settings.filterUrl.substring(displayMode+13, displayMode+settings.filterUrl.substring(displayMode).indexOf("&"));
                  var groupBy = settings.filterUrl.indexOf('group-by-attribute=');
                  var groupBy = groupBy < 0 ?'' : settings.filterUrl.substring(groupBy+19, groupBy+settings.filterUrl.substring(groupBy).indexOf("&"));
                  var perPage = settings.filterUrl.indexOf('per-page=');
                  var perPage = perPage < 0 ?'' : settings.filterUrl.substring(perPage+9, perPage+settings.filterUrl.substring(perPage).indexOf("&"));
                  var changeData={'gridId':id,'entity':entity,'displayMode':displayMode,'groupBy':groupBy,'perPage':perPage};
                  if(displayMode || groupBy || perPage){
                    methods.saveGridChanges(changeData,settings);
                  }
                  if(groupBy || $(".card-group-by option:selected").val()){
                    KTree.collapse();
                  }
                  var grid = $(this);
                  var pjaxContainer = grid.closest('[data-pjax-container]');
                  var pjaxContainerId= '#'+pjaxContainer.attr('id');
                  $(pjaxContainerId).on('pjax:end', function() {
                    function formatState (state) {
                      if (!state.id) { return state.text; }
                      var $state = $(
                        '<span>'+ state.text + '</span> <i title="Remove" class="info glyphicon glyphicon-trash" id = "'+ state.id +'" list-id ="'+deleteUrl+'" ></i>'
                      );
                      return $state;
                    };

                      $(document).find(".user_list_preference_search").select2({
                        templateResult: formatState,
                        placeholder: "Select ListView",
                        allowClear: true,
                        containerCssClass:'user-list-preference-select2'
                      }).on("select2:unselecting", function(e) {
                        $(this).data('state', 'unselected');
                      }).on("select2:open", function(e) {
                        if ($(this).data('state') === 'unselected') {
                            $(this).removeData('state');
                            var self = $(this);
                            setTimeout(function() {
                                self.select2('close');
                            }, 1);
                        }
                    });
                  });
            });
        },
        applyFilter: function () {
            var $grid = $(this), event;
            var settings = gridData[$grid.attr('id')].settings;
            var data = {};
            $.each($(settings.filterSelector), function () {
                if (!(this.name in data)) {
                    data[this.name] = [];
                }
            });
            if(settings.savedListSelector == 'select[name="user_list_preference"]') {
                $.each($(settings.savedListSelector).serializeArray(), function () {
                    if (!(this.name in data)) {
                        data[this.name] = [];
                    }
                    data[this.name].push(this.value);
                });
                data['is_user_list_preference'] = [1];
                data['group-by-attribute'] = [$(".card-group-by option:selected").val()];
            }
            else {
                $.each($(settings.filterSelector).serializeArray(), function () {
                    if (!(this.name in data)) {
                        data[this.name] = [];
                    }
                    data[this.name].push(this.value);
                });
                data['is_user_list_preference'] = [0];
            }
            var namesInFilter = Object.keys(data);
            $.each(yii.getQueryParams(settings.filterUrl), function (name, value) {
                if (namesInFilter.indexOf(name) === -1 && namesInFilter.indexOf(name.replace(/\[\]$/, '')) === -1) {
                    if (!$.isArray(value)) {
                        value = [value];
                    }
                    if (!(name in data)) {
                        data[name] = value;
                    } else {
                        $.each(value, function (i, val) {
                            if ($.inArray(val, data[name])) {
                                data[name].push(val);
                            }
                        });
                    }
                }
            });
            var pos = settings.filterUrl.indexOf('?');
            var url = pos < 0 ? settings.filterUrl : settings.filterUrl.substring(0, pos);
            if($grid.closest('form').length>0){
                $grid.find('div.gridview-filter-form').remove();
                var $form = $('<div/>', {
                    class: 'gridview-filter-form',
                    style: 'display:none',
                    'data-pjax': ''
                }).appendTo($grid);
            }else{
                $grid.find('form.gridview-filter-form').remove();
                var $form = $('<form/>', {
                    action: url,
                    method: 'get',
                    class: 'gridview-filter-form',
                    style: 'display:none',
                    'data-pjax': ''
                }).appendTo($grid);
            }

            $.each(data, function (name, values) {
                if(values.length) {
                    $.each(values, function (index, value) {
                        $form.append($('<input/>').attr({type: 'hidden', name: name, value: value}));
                    });
                }
                else {
                    $form.append($('<input/>').attr({type: 'hidden', name: name, value: ''}));
                }


            });
            event = $.Event(gridEvents.beforeFilter);
            $grid.trigger(event);
            if (event.result === false) {
                return;
            }
            if($grid.closest('form').length>0){
                methods.submitFilters($form,url,data,$grid);
            }else{
                 $form.submit();
            }
            $grid.trigger(gridEvents.afterFilter);
        },
        submitFilters:function(form,url,data,$grid){
           var pjaxContainer = $grid.closest('[data-pjax-container]');
           var filterParams = form.find('input').serialize();
           var pjaxContainerId= '#'+pjaxContainer.attr('id');
           $.pjax.reload(pjaxContainerId, {url: url + '?' + filterParams, container: pjaxContainerId, push: false,timeout:false, replace: false});
        },
        setSelectionColumn: function (options) {
            var $grid = $(this);
            var id = $(this).attr('id');
            if (gridData.id === undefined) {
                gridData[id] = {};
            }
            gridData[id].selectionColumn = options.name;
            if (!options.multiple || !options.checkAll) {
                return;
            }
            var checkAll = "#" + id + " input[name='" + options.checkAll + "']";
            var inputs = "#" + id + " input[name='" + options.name + "']";
            $(document).off('click.yiiGridView', checkAll).on('click.yiiGridView', checkAll, function () {
                $grid.find("input[name='" + options.name + "']:enabled").prop('checked', this.checked);
            });
            $(document).off('click.yiiGridView', inputs + ":enabled").on('click.yiiGridView', inputs + ":enabled", function () {
                var all = $grid.find("input[name='" + options.name + "']").length == $grid.find("input[name='" + options.name + "']:checked").length;
                $grid.find("input[name='" + options.checkAll + "']").prop('checked', all);
            });
        },

        getSelectedRows: function () {
            var $grid = $(this);
            var data = gridData[$grid.attr('id')];
            var keys = [];
            if (data.selectionColumn) {
                $grid.find("input[name='" + data.selectionColumn + "']:checked").each(function () {
                    keys.push($(this).parent().closest('tr').data('key'));
                });
            }
            return keys;
        },

        destroy: function () {
            return this.each(function () {
                $(window).unbind('.yiiGridView');
                $(this).removeData('yiiGridView');
            });
        },

        data: function () {
            var id = $(this).attr('id');
            return gridData[id];
        },
        saveGridChanges: function(gridData,settings){
          $.ajax({
              type: 'POST',
              url: settings.saveGridChange,
              dataType:'json',
              data: gridData,
              beforeSend:function(){
                      KTree.addLoader('Please wait...');
              },
              success: function (result) {
                  if(result.status)
                      KTree.removeLoader();
              },
              error:function(){
                KTree.removeLoader();
              }
            });
        },
        editReload:function(editAttribute){
          var $grid = $(this);
          var settings = gridData[$grid.attr('id')].settings;
          var data = [];
          $(".card-group-by > option").each(function () {
                  data.push($(this).val());
          });
          if($.inArray(editAttribute,data)>-1 && $(".card-group-by").val()==editAttribute)
          {
            var pjaxContainer = $grid.closest('[data-pjax-container]');
            var pjaxContainerId= '#'+pjaxContainer.attr('id');
            $.pjax.reload(pjaxContainerId, {url: settings.filterUrl , container: pjaxContainerId, push: false,timeout:false, replace: false});
          }
        }
    };
})(window.jQuery);
