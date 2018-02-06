$(document).ready(function() {
	
	$('#bt_nav').click(function(event){
		event.preventDefault();
		if (!$(this).hasClass('open')) {
			$('#header').animate({marginTop:'365px'},300);
			$('#nav').slideDown(300);
			$(this).addClass('open');
		} else {
			$('#header').animate({marginTop:'0'},300);
			$('#nav').slideUp(300);
			$(this).removeClass('open');
		}
	});
	
	$('#sliderHome').cycle({
		timeout:2500,
		fx:'fade',
		pager: '#sliderNav',
		next:'#sliderNext', 
	    prev:'#sliderPrev'
	});
	
	$("#gridgal").gridalicious({ gutter: 25, width: 300});
	$("#gridgal a").colorbox({
		maxWidth:'90%',
		maxHeight:'90%'
	});

});