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

namespace WPMKTENGINE;

use WPMKTENGINE\Utils\CSS;
use WPMKTENGINE\Utils\Json;
use WPMKTENGINE\Utils\Strings;

/**
 * Class RepositoryThemes
 *
 * @package WPMKTENGINE
 */
class RepositoryThemes extends Repository
{
    /** @type array|null */
    public $themes;
    /** @type array */
    public $js = array();

    /**
     * RepositoryThemes constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->themes = get_posts(
            array(
                'posts_per_page'   => -1,
                'post_type'        => 'wpme-styles',
                'post_status'      => 'publish',
            )
        );
    }

    /**
     * @return array
     */
    public function getDropdownArray()
    {
        $r = array();
        if($this->has()){
            foreach($this->themes as $theme){
                $r[$this->getThemeName($theme)] = $theme->post_title;
            }
        }
        return $r;
    }

    /**
     * @param \WP_Post $post
     * @return string
     */
    public function getThemeName(\WP_Post $post)
    {
        return 'theme' . Strings::firstUpper($post->post_name);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function findByThemeName($name = '')
    {
        if($this->has()){
            foreach($this->themes as $theme){
                if($name === $this->getThemeName($theme)){
                    return $theme;
                }
            }
            return NULL;
        }
        return NULL;
    }

    /**
     * Returns all theme styles, without the <style> tags
     *
     * @return string
     */
    public function getAllThemesStyles()
    {
        // WPME stylees
        global $WPME_STYLES;
        $r = '';
        if($this->has()){
            // If we have some, generate the styles buddy.
            foreach($this->themes as $theme){
                $styles = $this->getThemeStyle($theme);
                $this->penetrateDifferentStyles($styles, $theme);
                if(!is_null($styles) && is_object($styles) && method_exists($styles, 'getStylesWithoutTags')){
                    $r .= $styles->getStylesWithoutTags();
                }
            }
        }
        // Now we append styles from global $WPME_STYLES object
        // if there are any
        if(!empty($WPME_STYLES)){
            $r .= $WPME_STYLES;
        }
        return $r;
    }

    public function getAllThemesJavascript()
    {
        if($this->hasJS()){
        ?>
        <script type="text/javascript">

            /**
             * Forms
             * @type {Array}
             */
            var forms = [];
                forms = <?php echo json_encode($this->js); ?>;

            /**
             * Labels to Placeholders
             * @param formSelector
             */
            function WPMElabelsToPlaceholders(formSelector){
                // Select all of the labels inside of the form
                if(document.querySelector){
                    labelNodeList = document.querySelectorAll(formSelector + ' label' );
                    for(labelNodeIndex=0;labelNodeIndex<labelNodeList.length;labelNodeIndex++){
                        labelNode = labelNodeList[labelNodeIndex];
                        // Hide the label
                        labelNode.style.display = 'none';
                        // Select the input after and set it's placeholder ot the label's text
                        labelNode.nextSibling.placeholder = labelNode.textContent;
                    }
                }
            }

            // Ready?
            if(Document && typeof Document.ready !== "undefined"){
                Document.ready(window, function(e){
                    // If forms
                    if(forms.length > 0){
                        for (var i = 0; i < forms.length; i++){
                            var id = '.' + forms[i];
                            if(document.querySelector){
                                if(document.querySelector(id) !== null){
                                    WPMElabelsToPlaceholders(id);
                                }
                            }
                        }
                    }
                });
            }
        </script>
        <?php
        }
    }

    /**
     * This method is used to go through the styles
     * and if needed, append some differnt styles accordingly to the layout needed
     *
     * @param $styles
     * @param $theme
     */
    public function penetrateDifferentStyles(&$styles, $theme)
    {
        // Get theme name
        $themeName = $this->getThemeName($theme);
        // Go through css rules
        if(isset($styles->rules)){
            $CSS = $styles;
            // Get new rule names
            $CSSNewRule = 'body .genooModal.genooModal' . Strings::firstUpper($themeName);
            $CSSNewRulePreview = '.genooFullPage .genooMobileWindow .genooModal.genooModal' . Strings::firstUpper($themeName);
            $CSSNewRuleInner = $CSSNewRule . ' .genooGuts';
            $CSSNewRuleInner = preg_replace('/\s+/', ' ', $CSSNewRuleInner);
            // REmove padding for system-generated styles
            $CSS->addRule($CSSNewRuleInner)
                ->add('padding-right', '0 !important');
            $CSS->addRule($CSSNewRuleInner)
                ->add('padding-right', '0 !important');
            if(!empty($styles->rules)){
                $CSSRules = $styles->rules;
                foreach($CSSRules as $rule){
                    $CSSRule = $rule;
                    $CSSRuleName = $rule->getName();
                    $CSSRuleName = preg_replace('/\s+/', ' ', $CSSRuleName); // Remove double spaces
                    // Here comes the style switching magic
                    if(Strings::contains($CSSRuleName, '.genooForm.') && Strings::endsWith($CSSRuleName, '.genooPop')){
                        // This is modal style. If it has border-raidous and background, we drop background of overall thing
                        if($rule->hasProperty('border-radius') || $rule->hasProperty('background-color')){
                            // Has background color and border radius
                            // We now need to add canceling styler for the whole modal, to remove the padding and
                            // background
                            $CSS->addRule($CSSNewRule)
                                ->add('background', 'transparent !important');
                            $CSS->addRule($CSSNewRulePreview . ' .genooGuts')
                                ->add('padding', '0 !important');
                        }
                    }
                    // Here comes the styles for modal width
                    if(Strings::contains($CSSRuleName, '.genooPopImage') && !Strings::contains($CSSRuleName, '.genooPopImage img') && $CSSRule->hasProperty('width')){
                        // Get the width
                        $width = $CSSRule->getProperty('width');
                        $width = (int)filter_var($width, FILTER_SANITIZE_NUMBER_INT);
                        // Max data
                        $widthMax = 360;
                        $widthForm = 280;
                        $widthForm = 345;
                        if($width <= $widthMax){
                            // If width is smaller then 360px, add values
                            $CSS->addRule($CSSNewRule)
                                ->add('width', ($width + $widthForm + 5) . 'px !important');
                        }
                    }
                }
            }
        }
    }



    /**
     * Get theme Style
     *
     * @param \WP_Post $post
     * @return string
     */
    public function getThemeStyle(\WP_Post $post)
    {
        $r = NULL;
        $content = $post->post_content;
        try {
            $json = Json::decode($content);
            $themeName = $this->getThemeName($post);
            $css = new CSS();
            if(!empty($json)){
                foreach($json as $selector => $styles){
                    $ruler = $this->explodeRuleIfNeeded($selector, 'body .genooForm.' . $themeName . ' ');
                    //$ruler = 'body .genooForm.' . $themeName . ' ' . $selector;
                    $ruler = str_replace('.genooModal', '', $ruler);
                    $rule = $css->addRule($ruler);
                    if($selector !== '#genooOverlay'){
                        if(!empty($styles)){
                            foreach($styles as $property => $value){
                                if(!Strings::contains($value, '!important')){
                                    $value = $value . ' !important';
                                }
                                $rule->add($property, $value);
                            }
                        }
                    } else {
                        // Overlay has different magic - none.
                    }
                }
            }
            $r = $css;
        } catch (\Exception $e){}
        return $r;
    }

    /**
     * @param $rule
     * @param $prepend
     * @return mixed
     */
    public function explodeRuleIfNeeded($rule, $prepend)
    {
        $ruleOrg = $rule;
        $rulerExploded = explode(', ', $rule);
        if(count($rulerExploded) > 1){
            foreach($rulerExploded as $key => $value){
                $rulerExploded[$key] = $prepend . $value;
            }
            $rule = implode(', ', $rulerExploded);
        } else {
            $rule = $prepend . ' ' .$ruleOrg;
        }
        return $rule;
    }

    /**
     * @return bool
     */
    public function has(){ return !empty($this->themes); }

    /**
     * @return bool
     */
    public function hasJS()
    {
        if($this->has()){
            foreach($this->themes as $theme){
                $name = $this->getThemeName($theme);
                $placeholders = get_post_meta($theme->ID, 'wpmktengine_style_make_placeholders', TRUE);
                if($placeholders === 'true' && !in_array($name, $this->js)){
                    $this->js[] = $name;
                }
            }
            return !empty($this->js);
        }
        return FALSE;
    }

    /**
     * @return array|null
     */
    public function getThemes(){ return $this->themes; }
}