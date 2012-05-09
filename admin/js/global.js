$ = jQuery.noConflict();

var Admin;

Admin = {
	start: function(){
		this.warmUpEngine();

		if(typeof(pagenow) != 'undefined' && ( pagenow == 'page' || pagenow == 'insert types here' ) ) {
			$('form#post').attr('enctype','multipart/form-data');
			$('form#post').attr('encoding','multipart/form-data');
		}
	},

	warmUpEngine: function(){
		// class 'first' to all first children of lists
		$('ul li:first-child, ol li:first-child').addClass('first');

		// make logo go to homepage
		$('#wphead h1').click(function(){
			$link = $(this).find('a:first');
			window.location = $link.attr('href');
		});
	}
};

$(document).ready(function () {
	Admin.start();
});