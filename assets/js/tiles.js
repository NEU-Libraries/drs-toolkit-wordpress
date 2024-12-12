jQuery(document).ready(function($) {
  $(".freewall").each(function(){
    var type = $(this).data("type");
    var cell_height = $(this).data("cell-height");
    var cell_width = $(this).data("cell-width");
    var text_align = $(this).data("text-align");
    if (type == 'pinterest-below' || type == 'pinterest' || type == 'pinterest-hover'){
      var wall = new freewall(this);
      if (cell_width == "100%"){
        $(this).find(".brick").css("width", cell_width);
      }
      wall.reset({
        selector: ".brick",
        animate: true,
        cellW: cell_width,
        cellH: "auto",
        onResize: function() {
          wall.fitWidth();
        }
      });
      $(window).on("load", function() {//this wont fire until all the images are loaded
        wall.fitWidth();
      });
      $(this).find(".brick").css("text-align", text_align);
    }
    if (type == 'even-row'){
      var w = 1, html = '', i = 0;
      $(this).find(".cell").each( function(){
        var temp = "<div class='cell' style='width:{width}px; height: {height}px; background-image: url("+$(this).data("thumbnail")+")'><div class='info'><div>"+$(this).children(".info").html()+"</div></div></div>";
        w = cell_width + cell_height * Math.random() << 0;
        html += temp.replace(/\{height\}/g, cell_height).replace(/\{width\}/g, w);
        i++;
      });
  		$(this).html(html);

  		var wall = new freewall(this);
  		wall.reset({
  			selector: '.cell',
  			animate: true,
  			cellW: 20,
  			cellH: cell_height,
  			onResize: function() {
  				wall.fitWidth();
  			}
  		});
  		wall.fitWidth();
  		// for scroll bar appear;
  		$(window).trigger("resize");
      $(this).find(".cell").css("text-align", text_align);
    }
    if (type == 'square'){
      var w = cell_width, h = cell_height, html = '', i = 0;
      $(this).find(".cell").each( function(){
        var temp = "<div class='cell' style='width:{width}px; height: {height}px; background-image: url("+$(this).data("thumbnail")+")'><div class='info'><div>"+$(this).children(".info").html()+"</div></div></div>";
        html += temp.replace(/\{height\}/g, h).replace(/\{width\}/g, w);
        i++;
      });
  		$(this).html(html);

  		var wall = new freewall(this);
  		wall.reset({
  			selector: '.cell',
  			animate: true,
  			cellW: cell_width,
  			cellH: cell_height,
  			onResize: function() {
  				wall.refresh();
  			}
  		});
  		wall.fitWidth();
  		// for scroll bar appear;
  		$(window).trigger("resize");
      $(this).find(".cell").css("text-align", text_align);
    }
  });
  $(".hidden").each(function(){
    $(this).appendTo("body");
  });
});//end doc ready
