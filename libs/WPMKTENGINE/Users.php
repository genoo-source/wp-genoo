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

use WPMKTENGINE\RepositorySettings,
    WPMKTENGINE\Wordpress\Action,
    WPMKTENGINE\Tracer,
    WPMKTENGINE\Api;

class Users
{
    /**
     * Add newly registered users to WPMKTENGINE as a lead
     */

    public static function register(\WPME\RepositorySettingsFactory $repositorySettings, \WPME\ApiFactory $api)
    {
        // User Registration
        Action::add('user_register', function($user_id) use ($repositorySettings, $api){
            // Get user data
            $roles = $repositorySettings->getSavedRolesGuide();
            $user = get_userdata($user_id);

            // Check user role and add
            if($roles){
                foreach($roles as $key => $leadId){
                    if(Users::checkRole($key, $user_id)){
                        $firstName = empty($user->first_name) ? self::getFirstNameFromRequest() : $user->first_name;
                        $lastName = empty($user->last_name) ? self::getLastNameFromRequest() : $user->last_name;
                        try{
                            $api->setLead(
                                $leadId,
                                $user->user_email,
                                $firstName,
                                $lastName,
                                $user->user_url
                            );
                        } catch (\Exception $e){
                            //$repositorySettings->addSavedNotice('error', __('Error adding Genoo lead while registering a new user: ', 'wpmktengine') . $e->getMessage());
                        }
                        break;
                    }
                }
            }
        }, 999, 1);

        // User role change
        Action::add('set_user_role', function($user_id, $role, $old_roles) use ($repositorySettings, $api){
            // Get user data
            $roles = $repositorySettings->getSavedRolesGuide();
            $user = get_userdata($user_id);
            // WP higher then 3.6
            if(isset($old_roles) && !empty($old_roles) && is_array($old_roles)){
                $leadtypes = array();
                // Do we have leadtypes to remove?
                foreach($old_roles as $roling){
                    if(is_array($roles)){
                        if(array_key_exists($roling, $roles)){
                            $leadtypes[] = $roles[$roling];
                        }
                    }
                }
            }

            if(!empty($roles)){
                // Let's try this
                try {
                    // Data
                    $userEmail = $user->user_email;
                    $userExisting = $api->getLeadByEmail($userEmail);
                    $userNewLead = isset($roles[$role]) ? $roles[$role] : null;
                    $userGenoo = $api->getLeadByEmail($userEmail);
                    $userGenoo = Users::getUserFromLead($userGenoo);
                    // Update
                    if(!is_null($userGenoo) && !empty($leadtypes)){
                        // Leads, one or more?
                        $leadtypesFinal = count($leadtypes) == 1 ? $leadtypes[0] : $leadtypes;
                        // Existing User, remove from Leadtype
                        $api->removeLeadFromLeadtype($userGenoo->genoo_id, $leadtypesFinal);
                        // Add to leadtype
                        $api->setLeadUpdate($userGenoo->genoo_id, $userNewLead, $userEmail, $user->first_name, $user->last_name);
                    } elseif(!is_null($userGenoo)){
                        // Update lead
                        $api->setLeadUpdate($userGenoo->genoo_id, $userNewLead, $userEmail, $user->first_name, $user->last_name);
                    } else {
                        // set lead
                        $firstName = empty($user->first_name) ? self::getFirstNameFromRequest() : $user->first_name;
                        $lastName = empty($user->last_name) ? self::getLastNameFromRequest() : $user->last_name;
                        $result = $api->setLead($userNewLead, $userEmail, $firstName, $lastName);
                    }
                } catch (\Exception $e){
                    //$repositorySettings->addSavedNotice('error', __('Error changing Genoo user lead: ', 'wpmktengine') . $e->getMessage());
                }
            }
        }, 10, 3);
    }

    /**
     * Get First name from request
     *
     * @return null|string
     */
    public static function getFirstNameFromRequest()
    {
        if(isset($_POST)){
            @$first = isset($_POST['billing_first_name']) ? $_POST['billing_first_name'] : null;
            if($first === null){
                @$first = isset($_POST['shipping_first_name']) ? $_POST['shipping_first_name'] : null;
                if($first === null){
                    @$first = isset($_POST['first_name']) ? $_POST['first_name'] : null;
                }
            }
            return $first === null ? '' : $first;
        }
        return '';
    }

    /**
     * Get Last name from request
     *
     * @return null|string
     */
    public static function getLastNameFromRequest()
    {
        if(isset($_POST)){
            @$first = isset($_POST['billing_last_name']) ? $_POST['billing_last_name'] : null;
            if($first === null){
                @$first = isset($_POST['shipping_last_name']) ? $_POST['shipping_last_name'] : null;
                if($first === null){
                    @$first = isset($_POST['last_name']) ? $_POST['last_name'] : null;
                }
            }
            return $first === null ? '' : $first;
        }
        return '';
    }

    /**
     * Get count
     *
     * @param string $role
     * @return int
     */

    public static function getCount($role = 'subscriber')
    {
        return count(get_users(array('role' => $role)));
    }


    /**
     * Get users
     *
     * @param array $arr
     * @return mixed
     */

    public static function get($arr = array())
    {
        return get_users(array_merge(array('role' => 'subscriber'), $arr));
    }


    /**
     * Get User from WPMKTENGINE Lead
     *
     * @param $lead
     * @return null
     */

    public static function getUserFromLead($lead)
    {
        if(is_array($lead)){
            return $lead[0];
        }
        return null;
    }


    /**
     * Get ajax users
     *
     * @param $per
     * @param $offest
     * @return mixed
     */

    public static function getAjaxUsers($per, $offest)
    {
        return self::get(array(
            'offset' => (int)$offest,
            'number' => (int)$per,
        ));
    }


    /**
     * Checks if a particular user has a role.
     * Returns true if a match was found.
     *
     * @param string $role Role name.
     * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
     * @return bool
     */

    public static function checkRole($role, $user_id = null)
    {
        if (is_numeric($user_id))
            $user = get_userdata($user_id);
        else
            $user = wp_get_current_user();
        if (empty($user))
            return false;
        return in_array($role, (array)$user->roles);
    }
}