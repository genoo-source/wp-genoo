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

namespace WPME\Nag;

use WPME\Ecommerce\Utils;
use WPME\Nag\Notice;
use WPMKTENGINE\RepositoryUser;
use WPMKTENGINE\Wordpress\Nag as DummyNag;

/**
 * Class Nag
 * Bit more clever nag the ones before
 *
 * @package WPME\Nag
 */
class Nag
{
    /** @var \WPMKTENGINE\Wordpress\Nag  */
    public $dummyNag;

    /**
     * Nag constructor.
     */
    public function __construct()
    {
        $this->dummyNag = new DummyNag();
        $this->userRepository = new RepositoryUser();
    }

    public function renderNotice($message, $uniqueKey = null, $dismissable = false, $dismissableJs = false)
    {
        // If not JS dismissable, dont add class
        if(!$dismissableJs){
            Notice::$noticeDissmisiable = '';
        }
        // Empty unique key? Make one
        if(is_null($uniqueKey)){
            $uniqueKey = crc32($message);
        }
        // Unique key modifier
        $uniqueKey = 'hide-' . $uniqueKey;
        if($dismissable){
            $visible = $this->isVisible($uniqueKey);
            if($visible){
                $message .= "<a href=\"" . $this->getHideLink($uniqueKey) . "\" class=\"notice-dismiss\"><span class=\"screen-reader-text\">Dismiss this notice.</span></a>";
                echo Notice::type('updated wpme-install-notice')->text($message);
                return;
            }
            return;
        }
        echo Notice::type('updated wpme-install-notice')->text($message);
    }

    /**
     * Is nag visible? (if dismissable)
     *
     * @param $key
     * @return bool
     */
    public function isVisible($key)
    {
        $this->dummyNag->check($key);
        $shouldHideNag = $this->dummyNag->visible($key);
        return !$shouldHideNag;
    }

    /**
     * Get Hide Link
     *
     * @param $key
     * @return mixed
     */
    public function getHideLink($key)
    {
        return admin_url(
            \WPMKTENGINE\Wordpress\Utils::addQueryParam(
                basename(
                    \WPMKTENGINE\Wordpress\Utils::getRealUrl()
                ),
                $key,
                1
            )
        );
    }

    /**
     * @param $message
     * @param null $uniqueKey
     */
    public function renderNoticeDismissable($message, $uniqueKey = null)
    {
        return $this->renderNotice($message, $uniqueKey, true);
    }

}