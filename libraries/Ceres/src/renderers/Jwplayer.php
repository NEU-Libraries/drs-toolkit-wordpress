<?php 

namespace Ceres\Renderer;

class Jwplayer extends Html {
  
  public function render(): string {
    
    $jwplayerData = $this->fetcher->parseJwPlayerData($this->resourceId);
    list($plainMediaUrl, $playlistMediaUrl, $type, $imageUrl) = $jwplayerData;
    
    switch ($type) {
      case 'mp3':
        $avProvider = 'sound';
        
        //height below 40 puts jwplayer into audio mode -- no image is shown
        //but the aspect ratio has to be empty, otherwise it overrides and back to video mode
        $playerHeight = '30';
        if ($this->getOption('audioPoster')) {
          $aspectRatio = '16:9';
        } else {
          $aspectRatio = '';
        }
        break;
        
      case 'mp4':
        $avProvider = 'video';
        $aspectRatio = '16:9';
        $playerHeight = '400';
        break;
    }
    
    $playerWidth = $this->getOption('playerWidth', '50%');

    $numericPid = str_replace(":", "-", $this->resourceId);
    $imgId = 'ceres-item-img-' . $numericPid;
    $mediaId = 'ceres-item-media-' . $numericPid;
    
    $html = "<img id='$imgId' src='$imageUrl' class='replace_thumbs'/>";
    $html .= "<div id='$mediaId'></div>";
    
    $scriptHtml = "
      <script type='text/javascript'>
        
      jwplayer.key = '" . JWPLAYER_KEY . "',
      
      jQuery(document).ready(function ($) {
          $('#$imgId').hide();
          jwplayer('$mediaId').setup(
            {
              width: '$playerWidth',
              height: '$playerHeight',
              rtmp: {
                  bufferlength: 5
              },
              image: '$imageUrl',
              provider: '$avProvider',
              androidhls: true,
              primary: 'html5',
              hlshtml: true,
              aspectratio: '$aspectRatio',
              sources:[ {
                  file: '$plainMediaUrl', type: '$type'
              },
              {
                  file: '$playlistMediaUrl'
              }]
            }
          );
          
          jwplayer('$mediaId').on('ready', function () {
                  // Set poster image for video element to avoid black background for audio-only programs.
                  $('$mediaId video').attr('poster', '$imageUrl');
          });
          function errorMessage() {
              $('#$imgId').before('<div>There was a problem playing the media. Refresh the page and try again.</div>');
              $('#$imgId').show();
              $('#$mediaId').hide();
          }
          jwplayer('$mediaId').on('error', function () {
              errorMessage();
          });
          jwplayer('$mediaId').on('setupError', function () {
              errorMessage();
          });
          jwplayer('$mediaId').on('buffer', function () {
              theTimeout = setTimeout(function (e) {
                  errorMessage(e);
              },
              5000);
          });
          jwplayer('$mediaId').on('play', function () {
              clearTimeout(theTimeout);
          });
          $('.replace_thumbs').click(function () {
              jwplayer('$mediaId').play()
          })
      });
      </script>
    ";
    $html .= $scriptHtml;
    return $html;
  }
}
