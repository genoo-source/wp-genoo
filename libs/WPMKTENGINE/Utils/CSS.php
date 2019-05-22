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

namespace WPMKTENGINE\Utils;

/**
 * Class CSS
 *
 * @package WPMKTENGINE\Utils
 */
class CSS
{
    /** String beginning */
    const START = "<style type='text/css' scoped>";
    /** String End */
    const END = "</style>\n";
    /** @var array Rules */
    public $rules = array();


    /**
     * Constructor
     */
    public function __construct(){}


    /**
     * Add rule
     *
     * @param $rule
     * @return CSSRule
     */


    public function addRule($rule)
    {
        return $this->rules[($this->countRules() + 1)] = new CSSRule($rule);
    }


    /**
     * Count rules
     *
     * @return int
     */

    private function countRules(){ return count($this->rules); }


    /**
     * To string
     *
     * @return string
     */

    public function __toString()
    {
        $r = '';
        if(!empty($this->rules)){
            foreach($this->rules as $rule){
                $r .= $rule->getName() . ' {' . $rule . '} ';
            }
        }
        // Generate CSS
        $cssScoped = self::START . $r . self::END;

        // Add CSS to global generator
        global $GENOO_STYLES;
        if(!empty($GENOO_STYLES)){
            $GENOO_STYLES .= $cssScoped;
        } else {
            $GENOO_STYLES = $cssScoped;
        }

        // Append JS fallback
        $cssJs = '<script type="text/javascript">if(typeof GenooCSS != "undefined"){ GenooCSS.add(' . Json::encode($r) . '); }</script>';
        $cssFinal = $cssScoped . $cssJs;

        // Return
        return $cssFinal;
    }

    /**
     * Append to Global Styles
     */
    public function appendToGlobalStyles()
    {
        // Styles
        $r = $this->getStylesWithoutTags();
        // Generate CSS
        $cssScoped = self::START . $r . self::END;
        // Append
        // Add CSS to global generator
        global $GENOO_STYLES;
        if(!empty($GENOO_STYLES)){
            $GENOO_STYLES .= $cssScoped;
        } else {
            $GENOO_STYLES = $cssScoped;
        }
    }


    /**
     * @return string
     */
    public function getStylesWithoutTags()
    {
        $r = '';
        if(!empty($this->rules)){
            foreach($this->rules as $rule){
                $r .= $rule->getName() . ' {' . $rule . '} ' ;
            }
        }
        return $r;
    }
}


class CSSRule
{
    /** @var select */
    private $selector;
    /** @var array rules */
    private $rules = array();
    /** @type array properties */
    private $properties = array();
    /** @type array property valules */
    private $propertiesValues = array();

    /**
     * Constructor
     *
     * @param string $selector
     */

    public function __construct($selector = ''){ $this->selector = $selector; }

    /**
     * @param $key
     * @param $value
     */

    public function add($key, $value)
    {
        // Add whole rule
        $this->rules[] = ' ' . $key . ': ' . $value . '; ';
        // Add used property
        $this->properties[$key] = $key;
        $this->propertiesValues[$key] = $value;
        return $this;
    }


    /**
     * Get name
     *
     * @return select|string
     */

    public function getName(){ return $this->selector; }

    /**
     * @param string $property
     * @return bool
     */
    public function hasProperty($property = '')
    {
        return in_array($property, $this->properties);
    }

    /**
     * @param string $property
     * @return bool|mixed
     */
    public function getProperty($property = '')
    {
        if($this->hasProperty($property)){
            return $this->propertiesValues[$property];
        }
        return FALSE;
    }

    /**
     * To string
     *
     * @return string
     */

    public function __toString()
    {
        $r = '';
        if(!empty($this->rules)){
            foreach($this->rules as $rule){ $r .= $rule;  }
        }
        return $r;
    }
}