jQuery(document).ready(function($) {
	$('.carousel').carousel({
		interval: false
	});
  var cHeight = 0;
  $('.carousel').on('slide.bs.carousel', function(e) {
    var $nextImage = $(e.relatedTarget).find('img');
    $activeItem = $('.active.item', this);

    // prevents the slide decrease in height
    if (cHeight == 0) {
      cHeight = $(this).height();
      $activeItem.next('.item').find("img").height(cHeight);
    }

    // prevents the loaded image if it is already loaded
    var src = $nextImage.data('src');

    if (typeof src !== "undefined" && src != "") {
      $nextImage.attr('src', src)
      $nextImage.data('src', '');
    }
  });
	$('.drs_page_image').click(function(){
		if ($(this).hasClass("next")){
			var next = $("#drs_item_modal .modal-body .pagination .next").attr('data-ordinal_value');
			$('#drs_item_modal .modal-body .body').html("<img src='"+$("#drs_item_modal .modal-body .pagination .next").attr('data-img')+"' /><br/><h4>Page "+next+"</h4>");
			$("#drs_item_modal .modal-body .pagination li").removeClass("active");
			next = parseInt(next)+1;
			$("#drs_item_modal .modal-body .pagination").find("li:nth-of-type("+next+")").addClass("active");
			reset_pagination();
		} else if ($(this).hasClass("prev")){
			var prev = $("#drs_item_modal .modal-body .pagination .prev").attr('data-ordinal_value');
			$('#drs_item_modal .modal-body .body').html("<img src='"+$("#drs_item_modal .modal-body .pagination .prev").attr('data-img')+"' /><br/><h4>Page "+prev+"</h4>");
			$("#drs_item_modal .modal-body .pagination li").removeClass("active");
			prev = parseInt(prev)+1;
			$("#drs_item_modal .modal-body .pagination").find("li:nth-of-type("+prev+")").addClass("active");
			reset_pagination();
		} else {
			$('#drs_item_modal .modal-body .body').html("<img src='"+$(this).data('img')+"' /><br/><h4>Page "+$(this).attr("data-ordinal_value")+"</h4>");
			$('#drs_item_modal .modal-body').css("height", $(window).height() - 190);
			$("#drs_item_modal .modal-body .pagination li").removeClass("active");
			$("#drs_item_modal .modal-body .pagination").find("li:nth-of-type("+(parseInt($(this).attr("data-ordinal_value"))+1)+")").addClass("active");
			reset_pagination();
		}
	});
	function reset_pagination(){
		var prev = $("#drs_item_modal .modal-body .pagination .active").prev("li").find("a");
		var next = $("#drs_item_modal .modal-body .pagination .active").next("li").find("a");
		if (prev.attr("class").indexOf("prev") >= 0){
			$("#drs_item_modal .modal-body .pagination li:nth-of-type(1)").addClass("disabled");
			$("#drs_item_modal .modal-body .pagination .prev").data("img","").attr("data-ordinal_value","");
		} else {
			$("#drs_item_modal .modal-body .pagination .prev").attr("data-img",prev.data("img")).attr("data-ordinal_value",prev.attr("data-ordinal_value"));
			$("#drs_item_modal .modal-body .pagination li:nth-of-type(1) ").removeClass("disabled");
		}
		if (next.attr("class").indexOf("next") >= 0){
			$("#drs_item_modal .modal-body .pagination li:last").addClass("disabled");
			$("#drs_item_modal .modal-body .pagination .next").data("img","").attr("data-ordinal_value","");
		} else {
			$("#drs_item_modal .modal-body .pagination .next").attr("data-img",next.data("img")).attr("data-ordinal_value",next.attr("data-ordinal_value"));
			$("#drs_item_modal .modal-body .pagination li:last").removeClass("disabled");
		}
	}
});
