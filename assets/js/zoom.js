jQuery(document).ready(function($) {
  $(".drstk-caption").each(function(){
    if($(this).data('caption-align')){
      $(this).css("text-align", $(this).data('caption-align'));
    }
    if($(this).data('caption-position') == 'hover'){
      $(this).parents(".drs-item").addClass("hover");
    }
  });
  $("img.drs-item-img").each(function(){
    if ($(this).data('align')){
      $(this).parent('a').css("text-align", $(this).data('align'));
    }
    if ($(this).data('zoom') == 'on'){
      if($(this).attr('data-zoom-position') == 'inner'){
        $(this).elevateZoom({
          zoomType	: "inner",
          cursor: "crosshair"
        });
      } else if ($.isNumeric($(this).attr('data-zoom-position'))){
        var position = parseInt($(this).attr('data-zoom-position'));
        $(this).elevateZoom({ zoomWindowPosition: position});
      } else {
        $(this).elevateZoom();
      }
    }
    if ($(this).parents(".drs-item").hasClass("hover")){
      width = $(this).innerWidth() - 30;
      height = $(this).innerHeight() - 30;
      $(this).siblings(".drstk-caption").width(width);
      $(this).siblings(".drstk-caption").height(height);
    }
  });
  $(".hidden").each(function(){
    $(this).appendTo("body");
  });
});//end doc ready
