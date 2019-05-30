/**
 * Section Icon
 * @author Frederick Hamon X Deux Huit Huit
 */
(function ($, S) {

	'use strict';

	var init = function () {
		S.Extensions.section_icon = JSON.parse($('script#section_icon').html());
	};

	$(init);
	
})(jQuery, window.Symphony);
