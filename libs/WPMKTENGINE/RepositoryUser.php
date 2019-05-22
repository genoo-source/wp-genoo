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


class RepositoryUser
{
    /** @var  */
    var $user;

    /**
     * Constructor
     */

    public function __construct(){ $this->user = wp_get_current_user(); }


    /**
     * Hide nag?
     *
     * @return bool
     */

    public function hideNag($option = '')
    {
        $nag = $this->getOption($option);
        if(isset($nag) && ($nag == 1)){
            return true;
        }
        return false;
    }


    /**
     * Get user Name
     *
     * @return mixed
     */

    public function getName(){ return $this->user->display_name; }

    /**
     * Get user Login
     *
     * @return mixed
     */

    public function getLogin(){ return $this->user->user_login; }


    /**
     * Get user Email
     *
     * @return mixed
     */

    public function getEmail(){ return $this->user->user_email; }


    /**
     * Get user ID
     *
     * @return mixed
     */

    public function getId(){ return $this->user->ID; }


    /**
     * Update meta
     *
     * @param $key
     * @param $value
     * @return mixed
     */

    public function updateMeta($key, $value){ return update_user_meta($this->user->ID, $key, $value); }


    /**
     * Get meta value
     *
     * @param $key
     * @return mixed
     */

    public function getMeta($key){ return update_user_meta($this->user->ID, $key, true); }


    /**
     * Insert meta
     *
     * @param $key
     * @param $value
     * @return mixed
     */

    public function insertMeta($key, $value){ return add_user_meta($this->user->ID, $key, $value); }


    /**
     * Delete meta
     *
     * @param $key
     * @return mixed
     */

    public function deleteMeta($key){ return delete_user_meta($this->user->ID, $key); }


    /**
     * Upadate option
     *
     * @param $key
     * @param $value
     * @return mixed
     */

    public function updateOption($key, $value){ return update_user_option($this->user->ID, $key, $value); }


    /**
     * Get option
     *
     * @param $key
     * @return mixed
     */

    public function getOption($key){ return get_user_option($key, $this->user->ID); }


    /**
     * Delete option
     *
     * @param $key
     * @return mixed
     */

    public function deleteOption($key){ return delete_user_option($this->user->ID, $key); }


    /**
     * Current user can
     *
     * @param null $what
     * @return mixed
     */

    public function userCan($what = null){ return current_user_can($what); }
}