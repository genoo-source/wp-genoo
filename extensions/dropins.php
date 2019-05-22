<?php
use \WPMKTENGINE\Wordpress\Utils;

// 2018-12-30 - FB Comment Shortcode
remove_shortcode('facebook_comments');
add_shortcode('facebook_comments', function($atts){
    extract(shortcode_atts(
            array(
                'shareurl' => '',
            ), $atts)
    );
    $url = \WPMKTENGINE\Wordpress\Utils::getRealUrl();
    $r = '<div id="fb-root"></div>
    <script type="text/javascript">(function(d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) return;
      js = d.createElement(s); js.id = id;
      js.src = "//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.5";
      fjs.parentNode.insertBefore(js, fjs);
        }(document, \'script\', \'facebook-jssdk\'));</script>';
    $r .= '<br />';
    $r .= '<div class="fb-like" data-href="'. $url .'" data-layout="standard" data-action="like" data-show-faces="false" data-share="false"></div>';
    $r .= '<div class="fb-share-button" data-href="'. $url .'" data-layout="button_count"></div>';
    $r .= '<div class="clear"></div>';
    $r .= '<br />';
    if(isset($shareurl) && !empty($shareurl)){
        $url = $shareurl;
    }
    $r .= '<div class="fb-comments" data-href="'. $url .'" data-numposts="5" data-order-by="reverse_time"></div>';
    return $r;
});
