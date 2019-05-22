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

use WPMKTENGINE\Request;
use WPMKTENGINE\Shortcodes;
use WPMKTENGINE\Utils\Strings;
use WPMKTENGINE\Wordpress\Utils;


class ModalWindow
{
    /** modal id */
    const MODAL_ID = 'modalWindow';
    /** modal windows in session */
    const MODAL_SEETION = 'modalWindows';
    /** modal get param */
    const MODAL_GET = 'genooMobileWindow';
    /** overlay id */
    const MODAL_OVERLAY_ID = 'genooOverlay';

    /** @var bool */
    var $visibility = false;
    /** @var  */
    var $modals;
    /** @var array */
    var $modalsSaved;


    /**
     * Constructor
     */

    public function __construct()
    {
        if(!empty($_SESSION[self::MODAL_SEETION])){
            $this->modalsSaved = $_SESSION[self::MODAL_SEETION];
        } else {
            $this->modalsSaved = array();
        }
    }


    /**
     * Add new modal Window
     *
     * @param $id
     * @param null $guts
     * @param bool $visible
     * @param null $class
     * @param false $renderer
     * @throws InvalidArgumentException
     */

    public function addModalWindow($id, $guts = NULL, $visible = FALSE, $class = NULL, $renderer = false)
    {
        // prep modal
        $modalWindow = new \stdClass();
        // fill in data
        $modalWindow->visibility = $visible ? true : $this->isModalVisible($id);
        $modalWindow->aria = $modalWindow->visibility ? '' : 'hidden aria-hidden="true"';
        $modalWindow->id = self::getModalId($id);
        $modalWindow->class = $modalWindow->visibility ? 'visible renderedVisible ' : '';
        $modalWindow->class .= $class;
        $modalWindow->tabIndex = $this->countModals() + 1;
        $modalWindow->guts = $guts;
        $modalWindow->renderer = $renderer;
        // add modal
        if(isset($this->modals[$modalWindow->id])){
            throw new \InvalidArgumentException('Modal Window with id: '. $modalWindow->id .' already exists.');
        }
        $this->modals[$modalWindow->id] = $modalWindow;
    }


    /**
     * Is modal window visible, saved in sessions, or
     * in GET parameter?
     *
     * @param $id
     * @return bool
     */

    private function isModalVisible($id)
    {
        $modalId = self::getModalId($id);
        if((array_key_exists($modalId, $this->modalsSaved)) || (isset($_GET[self::MODAL_ID]) && $_GET[self::MODAL_ID] == $modalId)){
            $this->setVisibility(true);
            return true;
        }
        return false;
    }


    /**
     * Modal form result
     *
     * @param $id
     * @return bool|null
     */

    public static function modalFormResult($id)
    {
        $visible = self::isModalVisibleStatic($id);
        if($visible == true){
            if(isset($_GET['formResult'])){
                if($_GET['formResult'] == 'true'){
                    return true;
                } elseif($_GET['formResult'] == 'false'){
                    return false;
                }
            }
            return null;
        }
        return null;
    }


    /**
     * Is modal visible, static
     *
     * @param $id
     * @return bool
     */

    public static function isModalVisibleStatic($id)
    {
        $modalId = self::getModalId($id);
        if((isset($_GET[self::MODAL_ID]) && $_GET[self::MODAL_ID] == $modalId)){
            return true;
        }
        return false;
    }


    /**
     * Generates modal ID
     *
     * @param $id
     * @return string
     */

    public static function getModalId($id){ return str_replace('-', '', self::MODAL_ID . Strings::firstUpper($id)); }


    /**
     * Button
     *
     * @param $title
     * @param $id
     * @param bool $button
     * @param $class
     * @param $cssid
     * @return string
     */

    public static function button($title, $id, $button = true, $class = null, $mobile = false, $cssid = '')
    {
        // prep
        $r = '';

        $linkParams = array();
        $linkParams[self::MODAL_ID] = self::getModalId($id);

        // @deprecated
        // if($mobile == true){ $linkParams[self::MODAL_GET] = '1'; }

        $link = Utils::addQueryParams(Utils::getRealUrl(), $linkParams);
        $linkOnClick = 'onclick="Modal.display(event,\''. self::getModalId($id) . '\');"';
        $formTarget = '';

        if(!$button && $mobile == true){
            $linkOnClick = '';
            $formTarget = 'target="_blank"';
        }

        $t = '';
        $r .= '<form method="POST" id="genooButtonForm" action="'. $link  .'" '.$formTarget.'>';
        $rb = '<input type="submit" id="'. $cssid .'" class="'. $class .'" '. $linkOnClick .' value="'. $title .'">';
        $rb = apply_filters('wpmktengine_modal_form_button', $rb, $id, $class, $linkOnClick, $title);
        $r .= $rb;
        $r .= '</form>';
        $r = apply_filters('wpmktengine_modal_form', $r);

        // return button
        return $r;
    }


    /**
     * Get return URL
     *
     * @param $modalId
     * @return mixed
     */

    public static function getReturnUrl($modalId)
    {
        return Utils::addQueryParams(
            self::closeUrl(false),
            array(self::MODAL_ID => self::getModalId($modalId))
        );
    }


    /**
     * Is mobile window?
     *
     * @return bool
     */

    public static function isMobileWindow(){ return isset($_GET[self::MODAL_GET]); }


    /**
     * Modal close url
     *
     * @param bool $m
     * @return mixed
     */

    public static function closeUrl($m = true)
    {
        return apply_filters(
            'wpmktengine_modal_window_close_url',
            Utils::removeQueryParam(
                Utils::getRealUrl(),
                array(
                    self::MODAL_ID,
                    $m ? self::MODAL_GET : null,
                    Shortcodes::SHORTCODE_ID,
                    'formResult'
                )
            )
        );
    }


    /**
     * Count modal windows
     *
     * @return int
     */

    private function countModals(){ return count($this->modals); }


    /**
     * Get all modals
     *
     * @return mixed
     */

    private function getAllModals(){ return $this->modals; }


    /**
     * Get modal
     *
     * @param $key
     * @return mixed
     */

    private function getModal($key){ return $this->modals[$key]; }


    /**
     * Get overlay class
     *
     * @return string
     */

    private function getOverlayClass(){ return $this->getVisibility() ? 'visible' : ''; }


    /**
     * Set Visibility
     *
     * @param $visibility
     */

    public function setVisibility($visibility){ $this->visibility = $visibility; }


    /**
     * Get visibility
     *
     * @return mixed
     */

    public function getVisibility(){ return $this->visibility; }


    /**
     * Display all modals
     */

    public function __toString()
    {
        // prep
        $r = '';
        $overlayAria = $this->getVisibility() ? 'aria-hidden="false"' : 'hidden aria-hidden="true"';
        $overlayClass = $this->getOverlayClass();
        $overlayModals = $this->getAllModals();

        // fill with data
        $r .= '<div '. $overlayAria .' id="'. self::MODAL_OVERLAY_ID .'" class="'. $overlayClass .'">';
        $r .= '<a id="modalOverlayClose" class="fullLink" onclick="Modal.close(event)" href="'. self::closeUrl() .'"></a>';
        if(!empty($overlayModals)){
            foreach($overlayModals as $modalWindow){
                if(isset($modalWindow->renderer) && is_callable($modalWindow->renderer)){
                    // Renderer to return modalguts
                    $r .= call_user_func_array($modalWindow->renderer, array(
                        $this,
                        $modalWindow,
                    ));
                } else {
                    $r .= '<div id="'. $modalWindow->id .'" tabindex="-' . $modalWindow->tabIndex . '" role="dialog" class="genooModal '. $modalWindow->class .'">';
                    $r .= '<div class="relative gn-modal-background">';
                    $r .= '<a class="genooModalClose gn-modal-close" onclick="Modal.close(event)" href="'. self::closeUrl() .'">x</a>';
                    $r .= $modalWindow->guts;
                    $r .= '</div>';
                    $r .= '</div>';
                }
            }
        }
        $r .= '</div>' . "\n";
        // String
        $r = apply_filters('wpmktengine_modal_window_string', $r, $this);
        // return
        return $r;
    }


    /**
     * Destruct
     */

    function __destruct()
    {
        if(isset($_SESSION[self::MODAL_SEETION])){
            unset($_SESSION[self::MODAL_SEETION]);
        }
    }
}