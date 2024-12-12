jQuery(document).ready(function($) {

	if($().carousel) {
		$(".carousel").each(function(){
			var this_carousel = $(this);
			$(this).carousel();

			$(window).on('load', function(){
				fix_dimensions(this_carousel);
				fix_caption($('.carousel .item:first-of-type img'));
			});

			var cHeight = 0;
			$(this).on('slide.bs.carousel', function(e) {
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
					$nextImage.on('load', function(){
						fix_caption($nextImage);
						fix_dimensions(this_carousel);
					});
				}
			});
		});
	}


	function fix_dimensions(carousel){
		if (carousel.data("max-height") > 0){
			carousel.css("max-height", carousel.data("max-height"));
			carousel.find(".carousel-inner, .item").css("max-height", carousel.data("max-height"));
			if (carousel.find(".carousel-caption").css("position") == 'initial'){
				var img_height = carousel.data("max-height")-carousel.find(".carousel-caption").outerHeight();
				carousel.find("img").css("max-height", img_height);
			} else {
				carousel.find("img").css("max-height", carousel.data("max-height"));
			}
		} else {//no max-height is set
			var height = 0;
			var caption_height = 0;
				carousel.find("img").each(function(){
					if ($(this).data("src") != undefined){
						this_height = $(this).data("height");
					} else  {
						this_height = $(this).prop('naturalHeight');
					}
					this_caption_height = $(this).parents(".item").find(".carousel-caption").outerHeight();
					if (this_height > height) {
						height = this_height;
					}
					if (this_caption_height > caption_height){
						caption_height = this_caption_height;
					}
				});
			carousel.find("img").css("max-height", height);
			if (carousel.find(".carousel-caption").css("position") == 'initial'){
				carousel.find(".carousel-caption").css("min-height", caption_height);
			}
		}
		if (carousel.data("max-width") > 0){
			carousel.css("max-width", carousel.data("max-width"));
			carousel.find(".carousel-inner, img").css("max-width", carousel.data("max-width"));
		}
	}

	function fix_caption(image){
		var caption = image.parents(".item").find(".carousel-caption");
		if (typeof caption.attr('data-caption-width') !== typeof undefined && caption.attr('data-caption-width') !== false && caption.attr('data-caption-width') == 'image'){
			caption.css("width", image.innerWidth());
		}
	}

	$(".hidden").each(function(){
		$(this).appendTo("body");
	});
});
