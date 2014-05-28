$(document).ready(function(){
	// Slider
	/*
	$('div#block-wisski_pathbuilder-2 ul').addClass('bjqs');
	$('div#block-wisski_pathbuilder-2 div.content').bjqs({
		'height': 180,		
		'width': 180
	});
	*/
	// Colorbox
	$('div#block-wisski_images-0 a.wisski-image').attr('rel', 'group1');
	$('div#block-wisski_images-0 a.wisski-image').colorbox({
		rel:"group1",
		transition:"elastic",
		width:"75%",
		height:"75%"
	});
});

