// Simply treat all direct clicks on any element with a data-href attribute as 
// if it was a link (whether it is an actual <a> element or not).
$(document).on('click', '[data-href]', function(event) {
	window.location = $(this).data('href');
});
