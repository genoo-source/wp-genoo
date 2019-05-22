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

namespace WPME\Extensions\LandingPages;

use WPME\Ecommerce\Utils;
use WPMKTENGINE\Utils\Json;
use WPMKTENGINE\Wordpress\Action;
use WPMKTENGINE\Wordpress\Filter;
use WPMKTENGINE\Wordpress\Http;

/**
 * Class Metabox
 *
 * @package WPME\Extensions\LandingPages
 */
class Metabox extends \WPMKTENGINE\Wordpress\Metabox
{
    /** @var */
    var $title;
    /** @var string  */
    var $id;
    /** @var */
    var $postType;
    /** @var string  */
    var $nonceKey;
    /** @var  */
    var $ctas;


    /**
     * Constructor
     *
     * @param $title
     * @param $postType
     */

    function __construct($postType)
    {
        // assign
        $this->title = apply_filters(
            'genoo_wpme_metabox_landing_page_styles',
            'Enable third party Styles and Scripts'
        );
        $this->id = 'repeatable_' . \WPMKTENGINE\Utils\Strings::webalize($this->title);
        $this->postType = $postType;
        $this->nonceKey =  $this->id . 'Nonce';
        $this->context = 'side';
        Action::add('add_meta_boxes',    array($this, 'register'));
        Action::add('save_post',         array($this, 'save'));
    }

    /**
     * Render
     *
     * @param $post
     */

    public function render($post)
    {
        // Retrieve from itself, yay!
        $http = new Http();
        $http->setUrl(esc_url(home_url('/')) . "?genooScriptsQueue=true");
        $http->get();
        if($http->getResponseCode() === 200){
            $scripts = (array) get_post_meta($post->ID, '_scripts', true);
            $styles = (array) get_post_meta($post->ID, '_styles', true);
            try {
                $object = Json::decode( $http->getBody());
                $objectStyles = $object->styles;
                $objectScripts = $object->scripts;
                // Iterate
                // Scripts
                echo "<p><strong>Scripts:</strong></p>";
                if(!empty($objectScripts)){
                    echo "<ul>";
                    foreach($objectScripts as $script){
                        $checked = in_array($script, $scripts) ? 'checked="1"' : '';
                        echo "<li><label>";
                        echo "<input type=\"checkbox\" $checked name=\"scripts[$script]\" value=\"$script\" /> ";
                        echo $script;
                        echo "</label></li>";
                    }
                    echo "</ul>";
                } else {
                    echo "None.";
                }
                echo "<p><strong>Styles:</strong></p>";
                if(!empty($objectStyles)){
                    echo "<ul>";
                    foreach($objectStyles as $script){
                        $checked = in_array($script, $styles) ? 'checked="1"' : '';
                        echo "<li><label>";
                        echo "<input type=\"checkbox\" $checked name=\"styles[$script]\" value=\"$script\" /> ";
                        echo $script;
                        echo "</label></li>";
                    }
                    echo "</ul>";
                } else {
                    echo "None.";
                }
                // Styles
            } catch (\Exception $e){
                echo "<p><strong>Could not get frontend script and style queue.</strong></p>";
            }
        } else {
            echo "<p><strong>Could not get frontend script and style queue.</strong></p>";
        }
    }


    /**
     * Save
     *
     * @param $post_id
     * @return mixed|void
     */

    public function save($post_id)
    {
        if (!current_user_can('edit_post', $post_id)) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        $scripts = array();
        if(isset($_POST['scripts'])){
          $scripts = array_keys($_POST['scripts']);
        }
        $styles = array();
        if(isset($_POST['styles'])){
            $styles = array_keys($_POST['styles']);
        }
        update_post_meta($post_id, '_scripts', $scripts);
        update_post_meta($post_id, '_styles', $styles);
    }
}