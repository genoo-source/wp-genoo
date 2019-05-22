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

namespace WPMKTENGINE\Wordpress;

use WPME\RepositorySettingsFactory;
use WPMKTENGINE\Api;

/**
 * Class Cron
 *
 * @package WPMKTENGINE\Wordpress
 */
class Cron
{
    /**
     * Run Cron
     */

    public static function cron($args = null)
    {
        if(!is_null($args) && is_array($args) && isset($args['action'])){
            switch($args['action']){
                case '':
                    break;
            }
        } else {
            try{
                // test Valid key
                $genooSettings = new RepositorySettingsFactory();
                $genooApi = new \WPME\ApiFactory($genooSettings);
                // validate key, throw error in notices
                $genooApi->validate();
            } catch (ApiException $e){
                $genooSettings->addSavedNotice('error', $e->getMessage());
            }
        }
    }


    /**
     * Register cron
     *
     * @param $cron
     */

    public static function register($cron){ Action::add($cron, array(__CLASS__, 'cron')); }


    /**
     * Activate WordPress cron job.
     *
     * @param $cron
     */

    public static function onActivate($cron){ self::schedule('daily', $cron); }


    /**
     * Deactivate next scheduled cron job
     *
     * @param $cron
     */

    public static function onDeactivate($cron){ wp_unschedule_event(wp_next_scheduled($cron), $cron); }


    /**
     * Schedule Cron Event
     *
     * @param $time
     * @param $cron
     * @param array $args
     */

    public static function scheduleSingle($time, $cron, array $args = array()){ wp_schedule_single_event($time, $cron, $args); }


    /**
     * Schedule repeating event
     *
     * @param $timing
     * @param $cron
     * @param array $args
     */

    public static function schedule($timing, $cron, array $args = array())
    {
        $times = array('hourly', 'twicedaily', 'daily');
        wp_schedule_event(time(), $timing, $cron, $args);
    }


    /**
     * Clears out given hook
     *
     * @param string $hookName
     */

    public static function unscheduleCronEvents($hookName)
    {
        $events = self::getEvents();
        error_reporting(0);
        if(!empty($events)){
            foreach($events as $time => $cron){
                if(!empty($cron)){
                    foreach($cron as $hook => $dings){
                        if($hook == $hookName){
                            foreach($dings as $sig => $data){
                                wp_unschedule_event($time, $hook, $data['args']);
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * Get WordPress cron events
     *
     * @return mixed
     */

    public static function getEvents(){ return get_option('cron'); }
}