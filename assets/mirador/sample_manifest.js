// this is a sample JS file for calling a manifest - it must be modified for each collection and correspond to a manifest.json file existing on this server
$(function() {
 Mirador({
   "id": "mirador_viewer",
   "layout": "1x1",
   "data": [
      { "manifestUri": "[wordpress_url]/manifest.json", "location":"Northeastern University Digital Repository Service (DRS)"},
   ],
   "windowObjects": [
          {
          "loadedManifest" : "[wordpress_url]/manifest.json",
          "viewType" : "BookView"}
   ],
   'buildPath' : '/wp-content/plugins/drs-tk/assets/mirador/',
 });
});
