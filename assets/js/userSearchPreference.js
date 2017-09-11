$(document).ready(function () {
    $(document).on("submit", ".advance-search-form", function (event)  {
      setTimeout(function(){ 
		$('.modal-backdrop').remove(); 
		$('body').removeClass('modal-open');
	}, 500);
    });
    $(document).on("submit", "#save-filter-form", function (event)  {
        event.preventDefault();
        var form = $("#save-filter-form");
        var data = form.data('yiiActiveForm');
        $.each(data.attributes, function () {
            this.status = 3;
        });
        form.yiiActiveForm('validate');
        if (!$('#save-filter-form .has-error').length) {
            KTree.addLoader();

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                dataType: 'json',
                data: form.serialize(),
                success: function (data) {

                    if (data.status == '200') {
                        KTree.removeLoader();
                        $('.grid-advance-search-form-reset').trigger('click');
                        $('#save_filters_form button.close').trigger('click');
                        var listPreference = $(document).find('.user_list_preference');
                        listPreference.val(data.id);
                        listPreference.append($('<option>', {value:data.id, text: data.text}));
                        listPreference.select2();
                        $('#save_filters_form').remove();
                        $('.modal-backdrop').remove();
                        $('#save-filter-form').find("input[type=text], textarea").val("");
                        $('#save-filter-form').find("input[type=text], textarea").removeAttr("aria-invalid");
                        $('body').find('alert').remove();
                        $('body').append("<div class='alert-success alert fade in' id= 'w6-success'><button class='close' aria-hidden='true' data-dismiss='alert'>×</button>"+data.message+'</div');
                    }
                    else if (data.status == '500') {
                        $('body').find('alert').remove();
                        $('body').append("<div id='w6-error' class='alert-danger alert fade in'><button class='close' aria-hidden='true' data-dismiss='alert' type='button'>×</button>"+ data.message + "</div>");
                        KTree.removeLoader();
                    }
                },
                error: function () {
                    KTree.removeLoader();
                    $('body').find('alert').remove();
                    $('body').append("<div id='w6-error' class='alert-danger alert fade in'><button class='close' aria-hidden='true' data-dismiss='alert' type='button'>×</button>"+ data.message + "</div>");
                    $('#save_filters_form button.close').trigger('click');
                    $('.modal-backdrop').remove();
                }
            });

        }
    });

    $(document).on('mouseover', '.select2-results__option i.info', function (e) {
        $(this).parent().removeClass('highlighted');
        $(this).parent().removeAttr('aria-selected');
    });
    $(document).on('mouseleave', '.select2-results__option i.info', function (e) {
        $(this).parent().addClass('highlighted');
        $(this).parent().attr('aria-selected', "true");
    });
    $(document).on('click', '.select2-results__option i.info', function (e) {
        var deleteurl = $(this).attr('list-id');
        var deleteItemId = $(this).attr('id');
        var searchFilterForm = $('#dynamic-advance-search-form').find('form');
        if (confirm('Are you Sure? You want to delete this Item')) {
            var listPreference = $(document).find('.user_list_preference');
            listPreference.select2('destroy');
            KTree.addLoader();
            $.ajax({
                url: deleteurl,
                type: 'POST',
                dataType: 'json',
                data:{id:deleteItemId},
                success: function (data) {
                    if (data.status == '200') {
                        KTree.removeLoader();

                        $('#delete_filters_form button.close').trigger('click');

                        $(".user_list_preference option[value=" + deleteItemId + "]").remove();

                        listPreference.trigger('change');
                        $('body').find('alert').remove();
                        $('body').append("<div class='alert-success alert fade in' id= 'w6-success'><button class='close' aria-hidden='true' data-dismiss='alert'>×</button>"+data.message+'</div');

                    }
                    else if (data.status == '500') {
                        $('body').find('alert').remove();
                        KTree.removeLoader();
                        $('body').append("<div id='w6-error' class='alert-danger alert fade in'><button class='close' aria-hidden='true' data-dismiss='alert' type='button'>×</button>"+ data.message + "</div>");
                    }
                },
                error: function () {
                  $('body').find('alert').remove();
                  KTree.removeLoader();
                  $('body').append("<div id='w6-error' class='alert-danger alert fade in'><button class='close' aria-hidden='true' data-dismiss='alert' type='button'>×</button>"+ data.message + "</div>");
                  $('#save_filters_form button.close').trigger('click');
                }
            });
        } else {
            return false;
        }
        return false;

    });
    $(document).on('click','#save_filters_form-button',function(){
      $('#save_filters_form').appendTo('body');
    });

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
    $(document).on("click",'.grid-advance-search-form-reset',function(){
      setTimeout(function(){ 
		$('.modal-backdrop').remove();$('body').removeClass('modal-open'); 
	}, 500);
    });
	$(document).on("click",'#advance_filter_label>a',function(){
		setTimeout(function(){ 
			$('.modal-backdrop').remove(); 
			$('body').removeClass('modal-open');
		}, 500);
	});
});
