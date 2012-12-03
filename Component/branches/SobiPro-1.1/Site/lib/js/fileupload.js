/**
 * Created with JetBrains PhpStorm.
 * User: neo
 * Date: 3/12/2012
 * Time: 09:44
 * To change this template use File | Settings | File Templates.
 */
SobiPro.jQuery( document ).ready( function ()
{
	"use strict";
	SobiPro.jQuery( '.spFileUpload' ).ajaxForm({
//	    beforeSend: function() {
//	        status.empty();
//	        var percentVal = '0%';
//	        bar.width(percentVal)
//	        percent.html(percentVal);
//	    },
	    uploadProgress: function(event, position, total, percentComplete) {
	        var percentVal = percentComplete + '%';
	        bar.width(percentVal)
	        percent.html(percentVal);
	    },
		complete: function(xhr) {
			status.html(xhr.responseText);
		}
	});
} );
