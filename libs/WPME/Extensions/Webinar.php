<?php
/**
 * This file is part of the WPMKTGENGINE plugin.
 *
 * Copyright 2016 Genoo, LLC. All rights reserved worldwide.  (web: http://www.wpmktgengine.com/)
 * GPL Version 2 Licensing:
 *  PHP code is licensed under the GNU General Public License Ver. 2 (GPL)
 *  Licensed "As-Is"; all warranties are disclaimed.
 *  HTML: http://www.gnu.org/copyleft/gpl.html
 *  Text: http://www.gnu.org/copyleft/gpl.txt
 *
 * Proprietary Licensing:
 *  Remaining code elements, including without limitation:
 *  images, cascading style sheets, and JavaScript elements
 *  are licensed under restricted license.
 *  http://www.wpmktgengine.com/terms-of-service
 *  Copyright 2016 Genoo LLC. All rights reserved worldwide.
 */

add_action('wp_footer', function(){
    ?>
    <script type="text/javascript">
      var isWebinarScheduleFieldLoaded = function() {
        var field = document.querySelector('[name="scheduleid"]');
        if ( !field || field.options.length <= 1 ){
          setTimeout( isWebinarScheduleFieldLoaded, 1000 );
          return;
        };
        var dates = Object.assign( [], field.options ).slice(1).map(option => option.innerHTML);
        var webinarDateShortcodes = Object.assign([],
          document.querySelectorAll( 'span.gn-webinar-date' )
        );
        webinarDateShortcodes.forEach(function( webinarDate ){
          var format = webinarDate.getAttribute( "data-format" );
          var index = webinarDate.getAttribute( "data-index" ) * 1;
          if ( dates.length < index ) return;

          var formatArray = "day date time".split(" ").map(function( formatType ){
            return format.indexOf( formatType ) > -1;
          });
          var dateToPrint = dates[index].split(",").filter(function(text, i){
            return formatArray[i] === true;
          }).join(",");
          webinarDate.innerHTML = dateToPrint;
        });
      };
      isWebinarScheduleFieldLoaded();
    </script>
    <?php
}, 10, 1);

/**
 * @param $atts
 * @return string
 */
function wpme_webinar_date( $atts ) {
    $atts = shortcode_atts( array(
        'index' => "1",
        'format' => 'day date time'
    ), $atts, 'wpme_webinar_date' );
    return "<span class=\"gn-webinar-date\" data-index=\"" . $atts['index'] ."\" data-format=\"" . $atts['format'] . "\"></span>";
}
add_shortcode("wpme_webinar_date", "wpme_webinar_date");
