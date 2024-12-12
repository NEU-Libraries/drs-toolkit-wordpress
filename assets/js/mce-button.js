/* global tinymce */
( function() {
	tinymce.PluginManager.add( 'drstkshortcodes', function( editor ) {
    editor.on('DblClick',function(e) {
      shortcode = e.srcElement.innerText;
      shortcode = String(shortcode);
      old_shortcode = shortcode;
      if (shortcode.charAt(0) == "[" && shortcode.charAt(shortcode.length-1) == "]"){
        type = shortcode.split("id=")[0].trim();
				type = type.split("collection")[0].trim();
        type = type.split("_")[1].trim();
				if (type == 'tiles'){type = 'tile'}
				if (type == 'item'){type = 'single'}
				if (type == 'gallery'){type = 'slider'}
				if (type == 'collection'){type = 'media'}
        ids = [];
        params = getShortcodeParams(shortcode);
				if (params.metadata) {
					params.metadata = params.metadata.split(",");
					for (var i = 0; i < params.metadata.length; i++) {
						params.metadata[i] = params.metadata[i].trim();
					}
				}
				if (params.id){
					params.id = params.id.split(",");
	        var items = [];
	        jQuery.each(params.id, function(key, item){
	          item = item.trim();
	          this_item = new drstk.Item;
	          repo = item.split(":")[0];
	          if (repo == "wp"){ repo = "local";}
	          if (repo == "neu"){ repo = "drs";} else { item = item.split(":")[1]; } //non drs pids don't need prefix
	          this_item.set("pid", item).set("repo", repo);
	          items.push(this_item);
	        });
	        delete params.id;
					drstk.backbone_modal.__instance = new drstk.backbone_modal.Application({current_tab:type, items: items, old_shortcode:old_shortcode, settings:params});
				} else if (params.collection_id){
					drstk.backbone_modal.__instance = new drstk.backbone_modal.Application({current_tab:type, collection_id: params.collection_id, old_shortcode:old_shortcode, settings:params});
				}
				editor.dom.remove(e.srcElement);
      }
    });
	});

  function getShortcodeParams(shortcode) {
    var re = /([a-zA-Z-_0-9]{1,})="(.*?)"/g,
        match, params = {},
        decode = function (s) {return s};

    while (match = re.exec(shortcode)) {
      params[decode(match[1])] = decode(match[2]);
    }
    return params;
  }
})();
