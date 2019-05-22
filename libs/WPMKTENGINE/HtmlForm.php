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

use WPME\Crypto\SimpleCrypt;
use WPME\Ecommerce\Utils;

class HtmlForm
{
    /** @var string */
    private $html;
    /** @var \DOMDocument */
    private $dom;
    /** @var */
    private $form;
    /** @var */
    private $form_key;
    /** @var  */
    private $form_return = NULL;
    /** @var */
    private $form_id;
    /** @var string */
    private $unique;
    /** @var string */
    private $validator;
    /** @var int */
    static $count = 1;
    /** Form key */
    const FORM_KEY = '{{FORM_KEY}}';
    const FORM_RETURN = '{{FORM_RETURN}}';


    /**
     * Constructor
     *
     * @param $html
     */

    public function __construct($html)
    {
        $html = str_replace('%action-url%', 'https://api.genoo.com/servlet/PostForm', $html);
        // suppress warnings of invalid  html
        libxml_use_internal_errors(true);
        // prep
        $this->html = $html;
        $this->dom = new \DOMDocument;
        $this->dom->loadHTML('<?xml encoding="utf-8" ?>' .$this->html);
        $this->dom->preserveWhiteSpace = false;
        // html
        $this->form = $this->dom->getElementsByTagName("form")->item(0);
        $this->msg = $this->dom->getElementById("genooMsg");
        $this->unique = $this->getUniqueId();
        $this->appendClasses();
        $this->appendHTTPSIP();
    }

    /**
     * Append crypted IP if HTTPS
     */
    public function appendHTTPSIP()
    {
        if(is_ssl()){
            $this->appendHiddenInputs(
                array(
                    'form_version' =>
                        SimpleCrypt::encrypt(
                            Utils::getIpAddress()
                        )
                )
            );
        }
    }

    /**
     * Append custom classes to form elements
     */
    public function appendClasses()
    {
        // Get all elements we can
        if(isset($this->form) && method_exists($this->form, 'getElementsByTagName')){
            $formElements['form'] = array($this->form);
            $formElements['wrapper'] = $this->form->getElementsByTagName("p");
            $formElements['label'] = $this->form->getElementsByTagName("label");
            $formElements['input'] = $this->form->getElementsByTagName("input");
            $formElements['select'] = $this->form->getElementsByTagName("select");
            $formElements['textarea'] = $this->form->getElementsByTagName("textarea");
            $formElements['button'] = $this->form->getElementsByTagName("button");
            // Go through each filter type
            foreach($formElements as $filter => $elements)
            {
                // Class name
                $class = apply_filters('wpmktengine_form_element_class_' . $filter, ' ');
                // Only if class assigned
                if(!is_null($class)){
                    // can we go through?
                    if($elements instanceof \DOMNodeList || is_array($elements)){
                        // Can we assign these?
                        foreach($elements as $element){
                            if(
                                method_exists($element, 'getAttribute')
                                &&
                                method_exists($element, 'setAttribute')
                                &&
                                method_exists($element, 'hasAttribute')
                            ){
                                if($filter == 'form'){
                                    // Form has a iD inside we extract
                                    if($element->hasAttribute('onsubmit')){
                                        // Has on submit protection, therefore id
                                        $this->form_id = filter_var($element->getAttribute('onsubmit'), FILTER_SANITIZE_NUMBER_INT);
                                        $this->validator = $element->getAttribute('onsubmit');
                                    } elseif($element->hasAttribute('onSubmit')){
                                        // Has on submit protection, therefore id
                                        $this->form_id = filter_var($element->getAttribute('onSubmit'), FILTER_SANITIZE_NUMBER_INT);
                                        $this->validator = $element->getAttribute('onSubmit');
                                    }
                                }
                                if($filter == 'input' && $element->hasAttribute('type') && $element->getAttribute('type') == 'hidden'){
                                    // if is form_key, save that for later && put a placeholder
                                    if($element->getAttribute('name') == 'form_key'){
                                        // Get the value
                                        $this->form_key = $element->getAttribute('value');
                                        // Set placeholder
                                        $element->setAttribute('value', self::FORM_KEY);
                                    }
                                    // else, continue
                                    continue;
                                }
                                if($filter == 'input' && $element->hasAttribute('type') && $element->getAttribute('type') == 'submit'){
                                    $class = apply_filters('wpmktengine_form_element_class_button', NULL);
                                }
                                if($element->hasAttribute('class')){
                                    $prep = $element->getAttribute('class');
                                    $element->setAttribute('class', $prep . ' ' . $class);
                                } else {
                                    $element->setAttribute('class', $class);
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Append hidden input
     *
     * @param array $array
     * @return Html
     */

    public function appendHiddenInputs(array $array = array())
    {
        if($array){
            foreach($array as $key => $value){
                // This one behaves specially
                if($key == 'returnModalUrl'){
                    // Value
                    $this->form_return = $value;
                    $value = self::FORM_RETURN;
                    // Append id
                    $value = $value . '#f' . $this->unique;
                }
                $node = $this->dom->createElement("input");
                $node->setAttribute("type","hidden");
                $node->setAttribute("name", $key);
                $node->setAttribute("value", $value);
                if(!empty($this->form)){
                    $this->form->insertBefore($node, $this->form->childNodes->item(0));
                }
            }
        }
        return $this;
    }


    /**
     * Append Message
     *
     * @param string $msg
     * @param bool $err
     */

    public function appendMsg($msg = '', $err = false)
    {
        $html = '';
        if(!empty($msg)){
            $strongClass = $err == true ? 'genooSucess' : 'genooError fielderror';
            // remove form if succes
            if($err == true){
                if(!empty($this->form)){
                    if($this->form->parentNode){
                        $this->form->parentNode->removeChild($this->form);
                    } else {
                        // No form parent
                    }
                }
            }
            if(!empty($this->msg)){
                // html
                $html .= '<strong class="'.$strongClass.'">' . strip_tags($msg, '<br><br/>') . '</strong>'; //htmlspecialchars
                $fragment = $this->dom->createDocumentFragment();
                $fragment->appendXML($html);
                $this->msg->appendChild($fragment);
                // Add genoo pop class
                if(is_object($this->msg) && method_exists($this->msg, 'setAttribute')){
                    $this->msg->setAttribute('class', 'genooPop');
                }
            } else {
                // If for some reason it's not to be found and set, try
                // search for it again.
                if(method_exists($this->dom, 'getElementsByTagName')){
                    // Find divs
                    $findElements = $this->dom->getElementsByTagName('div');
                    // If not empty
                    if($findElements instanceof \DOMNodeList || is_array($findElements)){
                        // Go through elemens
                        foreach($findElements as $element){
                            if(
                                is_object($element)
                                &&
                                method_exists($element, 'getAttribute')
                            ){
                                // Ok, lets see if ID matches genooMsg
                                $id = $element->getAttribute('id');
                                if($id == 'genooMsg'){
                                    // Try appending
                                    $html .= '<strong class="'.$strongClass.'">' . strip_tags($msg, '<br><br/>') . '</strong>'; //htmlspecialchars
                                    $fragment = $this->dom->createDocumentFragment();
                                    $fragment->appendXML($html);
                                    $element->appendChild($fragment);
                                    // Append class genooPop
                                    if(is_object($element) && method_exists($element, 'setAttribute')){
                                        $element->setAttribute('class', 'genooPop');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Hide required field
     * after the form was submitted
     */

    public function hideRequired()
    {
        // Find span
        $span = $this->dom->getElementsByTagName("span");
        // Do we have elements?
        if($span instanceof \DOMNodeList){
            foreach($span as $element){
                // Remove if parent
                if($element->parentNode){
                    $element->parentNode->removeChild($element);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        static $counter = 1;
        $data = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $this->dom->saveHTML());
        $unique_id = '_' . $counter . '_' . sha1($data);
        $counter++;
        return $unique_id;
    }

    /**
     * Cleared from doctype, html, body
     *
     * @return mixed
     */

    public function __toString()
    {
        // Get data
        $data = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $this->dom->saveHTML());
        // Add uniqe form identificator
        if(isset($this->form_key) && !empty($this->form_key) && (isset($this->form_id) && (!empty($this->form_id)))){
            // Okay, we have a form key, lets get unique number
            $unique = strstr($this->form_key, 'fpk');
            $unique = substr($unique, 0, strpos($unique, ':'));
            $unique = filter_var($unique, FILTER_SANITIZE_NUMBER_INT);
            // Last check of the same number
            if($unique !== $this->form_id){
                // If they are not equal, we trust the body one
                $unique = $this->form_id;
            }
            $data = str_replace($unique, $this->unique, $data);
            // And then return the form_key
            $data = str_replace(self::FORM_KEY, $this->form_key, $data);
            if($this->form_return !== NULL){
                $data = str_replace(self::FORM_RETURN, $this->form_return, $data);
            }
            // Done
            // Filter to change
            $data = apply_filters('genoo_wpme_form_reducer', $data, $this->form_key, $unique, $this->unique);
        }
        $unique_id = "f" . $this->unique;
        $data = str_replace('%action-url%', 'https://api.genoo.com/servlet/PostForm', $data);
        //$data = str_replace('name="scheduleid"', 'name="scheduleid' . $this->form_id . '"', $data);
        // $data = str_replace("scheduleid" . $this->unique, "scheduleid" . $this->form_id, $data);
        // Return
        return "<div class=\"gn-generated\" id=\"$unique_id\">" . $data . "</div>";
    }


    /**
     * Destructor to clean errors.
     */

    public function __destruct(){ libxml_clear_errors(); }
}