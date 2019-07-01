<?php
/**
 * This file is part of the WPMKTGENGINE plugin.
 *
 * Copyright 2016 Genoo, LLC. All rights reserved worldwide.
 * (web: http://www.wpmktgengine.com/)
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

add_action('enqueue_block_editor_assets', function(){
    // Current DIR
    $dir  = dirname( __FILE__ );
    // Widgets
    $block = 'cta';
    // Add it in
    $block_js   = 'index.js';
    $editor_css = 'editor.css';
    wp_enqueue_script(
        'wpme-'. $block .'-block',
        plugins_url( $block_js, __FILE__ ),
        array(
            'wp-blocks',
            'wp-i18n',
            'wp-element',
        ),
        filemtime( "$dir/$block_js" ),
        true
    );
    wp_enqueue_style(
        'wpme-'. $block .'-block',
        plugins_url( $editor_css, __FILE__ ),
        array(
            'wp-blocks',
        ),
        filemtime( "$dir/$editor_css" ) 
    );
}, 100, 1);

// Register our block, and explicitly define the attributes we accept.
register_block_type( 'wpme/wpme-cta-block', array(
    'attributes' => array(
        'id' => array(
            'type' => 'string'
        ),
        'align' => array(
            'type' => 'string'
        ),
        'hasTime' => array(
            'type' => 'bool'
        ),
        'time' => array(
            'type' => 'integer'
        )
    ),
    'editor_script'   => 'wpme-cta-block', // The script name we gave in the wp_register_script() call.
    'render_callback' => 'wpme_cta_block_render',
) );

/**
 * Our combined block and shortcode renderer.
 *
 * For more complex shortcodes, this would naturally be a much bigger function, but
 * I've kept it brief for the sake of focussing on how to use it for block rendering.
 *
 * @param array $attributes The attributes that were set on the block or shortcode.
 */
function wpme_cta_block_render($attributes) {
    $default = array(
        'id' => '',
        'align' => '',
        'hasTime' => '',
        'time' => '',
    );
    if(is_object($attributes)){
        $values = array_merge(
            $default,
            get_object_vars($attributes)
        );
    } else if(is_array($attributes)){
        $values = array_merge(
            $default,
            $attributes
        );
    }
    $shortcodeString = '';
    foreach($values as $attribute => $value){
        $shortcodeString .= ' ' . $attribute . '="' . $value . '"';
    }
    // This is guttenberg call, asking for render of the CTA
    if(defined('REST_REQUEST') && REST_REQUEST){
        return "<div class=\"loading-placeholder gn-sc-plc placeholder-cta\" title=\"CTA\"><div class=\"dashicons dashicons-migrate migrate\"></div></div>";
    } else {
        // This is regular page
        $shortcodeName = apply_filters('genoo_wpme_cta_shortcode', 'WPMKTENGINECTA');
        return do_shortcode('['. $shortcodeName .' ' . $shortcodeString . ']');
    }
}
