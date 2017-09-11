/**
 * @link      http://ktreeportal.com/
 * @copyright Copyright (c) 2016 KTree.com.
 * @license   http://ktreeportal.com/license
 */
(function($) {

    if (typeof window.KTree == 'undefined') {
        window.KTree = {};
    }
    /**
     *
     */
    $.extend(KTree, {
        /**
         * Adds loader globally before ajax or some other actions.
         *
         * @param msg
         * @param container
         */
        addLoader: function(msg, container) {
            var text = (typeof msg == 'undefined') ? 'Please wait...' : msg;
            container = (typeof container == 'undefined') ? 'body' : container;
            var bodyHtml =
                '<div class="global-loader"><div class="ui-widget-overlay1"></div><div class="content-loader"><div class="loader"></div> ' +
                    text + '<div class="clear"></div></div></div>';
            $(container).append(bodyHtml);
        },
        removeLoader: function() {
            $('.global-loader').remove();
        },
        ajaxPrepareError: function(jqXHR, exception) {
            var msg = '';
            if (jqXHR.status === 0) {
                msg = 'Not connect.\n Verify Network.';
            } else if (jqXHR.status == 403) {
                msg = 'You are not allowed to perform this action. [403]';
            } else if (jqXHR.status == 404) {
                msg = 'Requested page not found. [404]';
            } else if (jqXHR.status == 500) {
                msg = 'Internal Server Error [500].';
            } else if (exception === 'parsererror') {
                msg = 'Requested JSON parse failed.';
            } else if (exception === 'timeout') {
                msg = 'Time out error.';
            } else if (exception === 'abort') {
                msg = 'Ajax request aborted.';
            } else {
                msg = 'Uncaught Error.\n' + jqXHR.responseText;
            }
            alert(msg);
            return {
                msg: msg,
                jqXHR: jqXHR
            };
        },
        ajaxNotifyError: function(msg, callBack) {
            if (typeof container == "undefined") {

            } else {
                $(container).show().html(msg);
            }
            callBack(msg);
        },
    		collapse : function(){
    			var icons = {
            header: 'ui-icon-plus',
            activeHeader: 'ui-icon-minus'
    			};
    			$(".card-view").accordion({
    			  icons: icons,
            collapsible: true,
            header: ".card-group-header",
            autoHeight : false ,
            active:false
    			});
    			$( "#toggle" ).button().on( "click", function() {
    			  if ( $( ".card-view" ).accordion( "option", "icons" ) ) {
    				$( ".card-view" ).accordion( "option", "icons", null );
    			  } else {
    				$( ".card-view" ).accordion( "option", "icons", icons );
    			  }
    			});
    		}
    });
})(jQuery);
