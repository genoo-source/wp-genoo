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

use WPME\RepositorySettingsFactory;
use WPMKTENGINE\Api,
    WPMKTENGINE\RepositorySettings;


class Import
{
    /** @var \WPMKTENGINE\Api */
    private $api;
    /** @var \WPMKTENGINE\RepositorySettings */
    private $settings;
    /** @var string */
    public $leadType;


    /**
     * Import constructor
     */

    public function __construct()
    {
        $this->settings = new RepositorySettingsFactory();
        $this->api = new \WPME\ApiFactory($this->settings);
        $this->leadType = $this->settings->getLeadType();
    }


    /**
     * Import commnets
     *
     * @param $comments
     * @return array
     */

    public function importComments($comments)
    {
        // don't break us down lad
        @error_reporting(0);
        @ini_set('display_errors', 0);
        // return arra\`
        $arr = array();
        // emails array, for no double entries
        $emails = array();
        // leads to post
        $leads = array();
        // activities to post
        $activities = array();
        // pre activites
        $leadsActivities = array();
        // if we have comments
        if(!empty($comments)){
            foreach($comments as $comment){
                // leads
                if(!in_array($comment->comment_author_email, $emails)){
                    $leads[] = array(
                        'email' => $comment->comment_author_email,
                        'first_name' => $comment->comment_author,
                        'web_site_url' => $comment->comment_author_url
                    );
                    $emails[] = $comment->comment_author_email;
                }
                // lead activites
                $activityDateTime = new \DateTime($comment->comment_date_gmt);
                $activityDate = $activityDateTime->format('c');
                $leadsActivities[$comment->comment_author_email][] = array(
                    'email' => $comment->comment_author_email, // email
                    'activity_date' => $activityDate, // Dates should be in the format that the field is set to or ISO 8601 format.
                    'activity_stream_type' => 'posted comment',
                    'activity_name' => get_the_title($comment->comment_post_ID), // title of post
                    'activity_description' => $comment->comment_content, // comment itself
                    'url' => get_permalink($comment->comment_post_ID) // url of post
                );
            }
            if(!empty($comment->comment_author_email) && !empty($this->leadType)){
                try {
                    // set leads
                    $apiResult = $this->api->setLeads($this->leadType, $leads);
                    // get processed and set activity
                    if(!empty($apiResult->process_results)){
                        foreach($apiResult->process_results as $result){
                            if($result->result == 'success'){
                                if(isset($leadsActivities[$result->email]) && !empty($leadsActivities[$result->email])){
                                    $activities = array_merge($activities,$leadsActivities[$result->email]);
                                }
                                $arr[] = __('Comment lead imported', 'wpmktengine') . ' email: '. $result->email;
                            }
                        }
                    }
                    // set activities
                    $apiResultActivities = $this->api->postActivities($activities);
                    // return info
                    return $arr;
                } catch(\Exception $e){
                    return array(__('Error while importing lead: ', 'wpmktengine'). $e->getMessage());
                }
            } else {
                if(empty($comment->comment_author_email)){
                    return array(__('Lead not imported, it has no email address.', 'wpmktengine'));
                }
                return array(__('Error while importing lead, no lead type set. Your account <a href="'. admin_url('admin.php?page=WPMKTENGINELogin&reset=true') .'">may need resetting.</a>', 'wpmktengine'));
            }
        }
        return array(__('No comments provided.', 'wpmktengine'));
    }


    /**
     * Import subscribers
     *
     * @param $subscribers
     * @return array
     */

    public function importSubscribers($subscribers, $leadType)
    {
        // don't break us down lad
        @error_reporting(0);
        @ini_set('display_errors', 0);
        // return array
        $arr = array();
        // leads to post

        $leads = array();
        // leadtype check / fill
        $importLeadType = (isset($leadType) && is_numeric($leadType)) ? $leadType : $this->leadType;
        // if we have subscribers
        if(!empty($subscribers)){
            if(!empty($importLeadType)){
                return array(__('Error while importing lead, no correct lead type found. Please try resetting your account here or contacting our support.', 'wpmktengine'));
            }
            foreach($subscribers as $subscriber){
                // leads
                $leads[] = array(
                    'email' => $subscriber->data->user_email,
                    'first_name' => $subscriber->data->user_nicename,
                    'web_site_url' => $subscriber->data->user_url
                );
            }
            try {
                // set leads
                $apiResult = $this->api->setLeads($importLeadType, $leads);
                // get processed
                if(!empty($apiResult->process_results)){
                    foreach($apiResult->process_results as $result){
                        if($result->result == 'success'){
                            $arr[] = __('Subscriber lead imported', 'wpmktengine') . ' email: '. $result->email;
                        }
                    }
                }
                // return info
                return $arr;
            } catch(\Exception $e){
                return array(__('Error while importing lead: ', 'wpmktengine'). $e->getMessage());
            }
        }
        return array(__('No subscribers provided.', 'wpmktengine'));
    }


    /**
     * Import single comment
     *
     * @param $comment
     */

    public function importComment($comment)
    {
        try {
            $apiEmail = $this->api->getLeadByEmail($comment->comment_author_email);
            if(!empty($apiEmail)){
                $this->api->putCommentActivity($apiEmail[0]->genoo_id, $comment);
            } else {
                $apiResult = $this->api->setLead(
                    $this->leadType,
                    $comment->comment_author_email,
                    $comment->comment_author,
                    '',
                    $comment->comment_author_url
                );
                $this->api->putCommentActivity($apiResult, $comment);
            }
            // we good?
        } catch(\Exception $e){
            // oops, just show it in admin i guess
            $settins = new RepositorySettings();
            $settins->addSavedNotice('error', __('Error while importing a lead:', 'wpmktengine') . ' ' . $e->getMessage());
        }
    }
}