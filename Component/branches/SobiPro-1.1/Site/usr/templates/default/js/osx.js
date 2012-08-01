/*
 * SimpleModal OSX Style Modal Dialog
 * http://www.ericmmartin.com/projects/simplemodal/
 * http://code.google.com/p/simplemodal/
 *
 * Copyright (c) 2010 Eric Martin - http://ericmmartin.com
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Revision: $Id: osx.js 1199 2011-04-15 18:44:16Z Radek Suski $
 */

jQuery( function() {
	jQuery.noConflict();
	var OSX = {
		container: null,
		init: function () {
			jQuery( "input.osx, a.osx, button.osx" ).click( function ( e ) {
				e.preventDefault();	
				jQuery("#osx-modal-content").modal({
					overlayId: 'osx-overlay',
					containerId: 'osx-container',
					closeHTML: null,
					minHeight: 80,
					opacity: 65, 
					position: ['0',],
					overlayClose: true,
					onOpen: OSX.open,
					onClose: OSX.close,
					persist: true
				});
			});
		},
		open: function (d) {
			var self = this;
			self.container = d.container[0];
			d.overlay.fadeIn('slow', function () {
				jQuery("#osx-modal-content", self.container).show();
				var title = jQuery("#osx-modal-title", self.container);
				title.show();
				d.container.slideDown('slow', function () {
					setTimeout(function () {
						var h = jQuery("#osx-modal-data", self.container).height()
							+ title.height()
							+ 20; // padding
						d.container.animate(
							{height: h}, 
							100,
							function () {
								jQuery("div.close", this.container).show();
								jQuery("#osx-modal-data", this.container).show();
							}
						);
					}, 300);
				});
			});
		},
		close: function (d) {
			var self = this;
			d.container.animate(
				{
					top:"-" + (d.container.height() + 20)
				}, 500,
				function () {
					try { SP_Save(); } catch ( e ) {}
					self.close();					
				}
			);
		}
	};

	OSX.init();

});