<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 */

namespace Genoo;

use WPME\RepositorySettingsFactory;
use WPMKTENGINE\Wordpress\Http;
use WPMKTENGINE\Wordpress\Utils;
use WPMKTENGINE\Utils\Strings;
use WPMKTENGINE\Utils\Url;
use WPMKTENGINE\Utils\Json;

/**
 * Class Api
 *
 * @package Genoo
 */
class Api implements \WPME\ApiInterface
{
    /**
     * API's
     */

    const DEV = false;
    const URL = 'https://api.genoo.com/api/rest';
    const URL_DEV = 'https://apidev.odportals.com/api/rest';
    const TIMEZONE = 'America/Chicago';

    /** @var API key */
    var $key;
    /** @var \WPME\RepositorySettingsFactory */
    var $settingsRepo;
    /** @var \WPMKTENGINE\Wordpress\Http */
    var $http;
    /** @var last called queryy */
    var $lastQuery;

    /**
     * API GET
     */

    const VALIDATE = '/validatekey';
    const GET_LEAD = '/leads[S]'; //
    const GET_LEADS_BY_EMAIL = '/leadbyemail[S]'; //
    const GET_LEADS_TYPES = '/leadtypes';
    const GET_LEADS_ALL = '/qualifiedleads[A]'; //
    const GET_LEADS_BY = '/leadtype[A]'; //
    const GET_USERS_BY = '/users[S]'; //
    const GET_USERS = '/users';
    const GET_USER_INFO = '/wpmeaccountinfo';
    const GET_EMAILS = '/emails';
    const GET_FORMS = '/forms';
    const GET_FORM = '/forms[S]'; //
    const GET_LUMENS = '/lumens';
    const GET_LUMENS_CLASSLIST = '/lumens/classlist';
    const GET_LUMENS_CLASS = '/lumens/classlist[S]';
    const GET_STREAM_TYPES = '/activitystreamtypes';
    const GET_ACTIVE_FEATURES = '/wpmeactivefeatures';
    const GET_PACKAGE_ECOMMERCE = '/wpmefeaturecheck/ecommerce';
    const GET_PACKAGE_LMS = '/wpmefeaturecheck/lifterlms';
    const GET_PACKAGE_FORUMS = '/wpmefeaturecheck/forums';
    const GET_PAGES = '/pagelablayoutdata';
    const GET_PAGE = '/pagelablayoutdata[S]';

    /**
     * API POST
     */

    const POST_LEADS = '/leads'; //
    const POST_PRODUCTS = '/wpmeproducts'; //
    const POST_USERS = '/users[P]'; //
    const POST_EMAILS = '/emails/recipients[P]'; //
    const POST_SEQUENCES = '/nurturingsequences[P]'; //
    const POST_ACTIVITIES = '/activitystreams[P]'; //
    const POST_STREAM_TYPES = '/activitystreamtypes[P]'; //
    const POST_ORDER = '/wpmeorders';

    /**
     * API PUT
     */

    const PUT_ACTIVITY = '/activitystream[S]'; //id
    const PUT_LEADTYPE_MEMBERS = '/leadtypes/members';
    const PUT_PAGES = '/pagelablayoutdata[P]'; // id
    const PUT_PRODUCT = '/wpmeproducts[S]'; //
    const PUT_ORDER = '/wpmeorders[S]';

    /**
     * Api DELETE
     */

    const DELETE_PAGE = '/pagelablayoutdata[D]';


    /** @var array Messages */
    private static $messages = array(
        'The server has not found anything matching the request URI',
        'form not found',
        'System unable to authenticate the request.',
        'System unable to verify lumens customer.',
        'not authenticated',
        'There was no data in the request to process.',
        'The method specified in the request is not allowed for the resource identified by the request URI',
        'lead not found',
        'No data in request'
    );

    /**
     * @type array
     */
    public $callstack = array();

    /**
     * Constructor
     *
     * @param RepositorySettings $settings
     */

    public function __construct(\WPME\RepositorySettingsFactory $settingsRepo)
    {
        // assign API key
        $this->key = $settingsRepo->getApiKey();
        // settings repository
        $this->settingsRepo = $settingsRepo;
        // http wrapper
        $this->http = new Http();
    }

    /**
     * Way to reset API key
     *
     * @param $key
     */

    public function setApiKey($key){ $this->key = $key; }


    /** ----------------------------------------------------- */
    /**                 Getters, setters                      */
    /** ----------------------------------------------------- */

    /**
     * Get leads, by genoo_id
     *
     * @param $key
     * @return mixed
     * @throws ApiException
     */

    public function getLead($key)
    {
        if($key){
            return $this->call(self::GET_LEAD, $key);
        }
        throw new \InvalidArgumentException(__('Genoo Lead Type id was not provided.', 'genoo'));
    }


    /**
     * Get lead, by email_address
     *
     * @param $key
     * @return null|object|string
     * @throws \InvalidArgumentException
     */

    public function getLeadByEmail($key)
    {
        if($key){
            try{
                $lead = $this->call(self::GET_LEADS_BY_EMAIL, $key);
                return $lead;
            } catch (\Exception $e){
                return null;
            }
        }
        throw new \InvalidArgumentException(__('E-mail address required to get Genoo Lead.', 'genoo'));
    }


    /**
     * Returns all leads matching input parameters.  Leads will be returned in create_date desc order and a
     * maximum of 250 records returned in a single query.  The query will identify total leads matching the
     * query so subsequent queries can be made, adjusting the {starting_record} input parameter.
     *
     * {qualified_status}/{time_period}/{starting_record}
     *
     * @param $status
     * @param $period
     * @param int $offset
     * @return object|string
     * @throws \InvalidArgumentException
     */

    public function getAllLeads($status, $period, $offset = 0)
    {
        $statuses = array(
            'MQL',  // marketing qualified lead
            'SAL',  // sales accepted lead
            'SQL'   // sales qualified lead
        );
        $periods = array(
            1,  // Today
            2,  // Yesterday
            3,  // This Week
            4,  // Last 7 Days
            5,  // Last Week
            6,  // This Month
            7,  // Last 30 Days
            8,  // Last Month
            9,  // This Quarter
            10, // This year
        );
        if(in_array($status, $statuses) && in_array($period, $periods)){
            return $this->call(self::GET_LEADS_ALL, array($status, $period, $offset));
        }
        throw new \InvalidArgumentException(__('Restrictions not met to get all CRM leads.', 'genoo'));
    }


    /**
     * Returns a list of leads associated with a lead type.  A maximum of 250 records will be returned for any
     * given query. By setting the starting_record, the client API may paginate through the result set.
     * The result set will contain the lead_type_id, the total number of leads associated with the lead type as
     * well as the number_returned, and the starting_record used for the query, and an array of “members”
     * that contains the join_date and join_source that identifies the date the lead was associated with the
     * lead type.
     *
     * The leads will be ordered by join_date desc (join_date is the date the lead was associated with the lead type).
     *
     * leadtype/{id}/members/{starting_record}
     *
     * @param $leadTypeId
     * @param int $offset
     * @return string
     * @throws ApiException
     */


    public function getLeadsBy($leadTypeId, $offset = 0)
    {
        if($leadTypeId && is_numeric($offset)){
            return $this->call(self::GET_LEADS_BY, array($leadTypeId, 'members', $offset));
        }
        throw new \InvalidArgumentException(__('Lead type id not provided.', 'genoo'));
    }


    /**
     * Get user by genoo_id
     *
     * @param $key
     * @return mixed
     * @throws ApiException
     */

    public function getUsersBy($key)
    {
        if($key){
            return $this->call(self::GET_USERS_BY, $key);
        }
        throw new \InvalidArgumentException(__('Genoo id required to get users.', 'genoo'));
    }


    /**
     * Get form by id
     *
     * @param $key
     * @return mixed
     * @throws ApiException
     */

    public function getForm($key)
    {
        if($key){
            return $this->call(self::GET_FORM, $key);
        }
        throw new \InvalidArgumentException(__('Form id required to get Genoo form.', 'genoo'));
    }


    /**
     * Verify Lumens Account
     *
     * @return bool
     */

    public function isLumens()
    {
        $lumens = $this->call(self::GET_LUMENS);
        if($lumens->result == 'false'){
            return false;
        }
        return true;
    }


    /**
     * Is lumens account - initial setup value
     *
     * @return bool
     */

    public function isLumensSetup()
    {
        if(GENOO_SETUP){
            $option = \get_option(RepositorySettingsFactory::KEY_LUMENS, null);
            if(is_null($option)){
                try{
                    // is lumens or not?
                    $value = $this->isLumens();
                    $value = $value == false ? 0 : 1;
                    $did = \update_option(
                        RepositorySettingsFactory::KEY_LUMENS,
                        $value,
                        true
                    );
                    return $value;
                } catch (\Exception $e){
                    // we don't catch exception in init setup
                    return false;
                }
            } else {
                return (bool)$option;
            }
        }
        return false;
    }


    /**
     * Get Lumens Class List
     *
     * @return object|string
     */

    public function getLumensClassList(){ return $this->call(self::GET_LUMENS_CLASSLIST); }


    /**
     * Get Lumen classlist
     *
     * @param $key
     * @return object|string
     * @throws \InvalidArgumentException
     */

    public function getLumen($key)
    {
        if($key){
            return $this->call(self::GET_LUMENS_CLASS, $key);
        }
        throw new \InvalidArgumentException(__('Lumen Class List id was not provided.', 'genoo'));
    }


    /**
     * Get Users
     *
     * @return object|string
     */

    public function getUsers(){ return $this->call(self::GET_USERS); }


    /**
     * Get Emails
     *
     * @return object|string
     */

    public function getEmails(){ return $this->call(self::GET_EMAILS); }


    /**
     * Get Forms
     *
     * @return object|string
     */

    public function getForms(){ return $this->call(self::GET_FORMS); }


    /**
     * Get Lead Types
     *
     * @return object|string
     */

    public function getLeadTypes(){
	    return $this->call(self::GET_LEADS_TYPES);
        global $WPME_CACHE;
        try{
            if (!$prepLeadTypes = $WPME_CACHE->get('leadtypes', 'settings')){
                $prepLeadTypes = $this->call(self::GET_LEADS_TYPES);
                $WPME_CACHE->set(
                    'leadtypes',
                    $prepLeadTypes,
                    60 * 60 * 12,
                    'settings'
                );
            }
        } catch(\Exception $e){
            return $this->call(self::GET_LEADS_TYPES);
        }
        // We have data from cache
        $prepLeadTypes = (array)$prepLeadTypes;
        if(!empty($prepLeadTypes)){
            foreach($prepLeadTypes as $key => $value){
                $prepLeadTypes[$key] = (object)$value;
            }
        }
        return $prepLeadTypes;
    }


    /**
     * Get User info (only wpme)
     *
     * @return bool
     * @throws ApiException
     */

    public function getUserInfo(){ return $this->call(self::GET_USER_INFO); }


    /**
     * Get Stream Activity Types
     *
     * @return object|string
     */

    public function getStreamTypes(){ return $this->call(self::GET_STREAM_TYPES); }


    /**
     * Get user account features
     *
     * @return object|string
     */

    public function getActiveFeatures(){ return $this->call(self::GET_ACTIVE_FEATURES); }

    /**
     * @return bool
     */
    public function getPackageEcommerce()
    {
        $data = $this->call(self::GET_PACKAGE_ECOMMERCE);
        if((is_object($data)) && (isset($data->active)) && ($data->active == TRUE)){
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @return bool
     */
    public function getPackageLMS()
    {
        $data = $this->call(self::GET_PACKAGE_LMS);
        if((is_object($data)) && (isset($data->active)) && ($data->active == TRUE)){
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @return bool
     */
    public function getPackageForums()
    {
        $data = $this->call(self::GET_PACKAGE_FORUMS);
        if((is_object($data)) && (isset($data->active)) && ($data->active == TRUE)){
            return TRUE;
        }
        return FALSE;
    }

    /**
     * @return bool
     * @throws ApiException
     */
    public function getPages()
    {
        return $this->call(self::GET_PAGES);
    }


    /**
     * Get old page
     *
     * @param $id
     * @return bool
     * @throws \InvalidArgumentException
     * @throws \WPMKTENGINE\ApiException
     */
    public function getPageOld($id)
    {
        if($id){
            return $this->call(self::GET_PAGE, $id);
        }
        throw new \InvalidArgumentException(__('Page id required..', 'wpmktengine'));
    }


    /**
     * Get page
     *
     * @param $id
     * @return bool
     * @throws ApiException
     */
    public function getPage($id)
    {
        if($id){
            try {
                // Get page?
                $page = $this->callCustom('/pagelablayouthtml[S]', 'GET', $id);
                $code = $this->http->response['response']['code'];
                if($code === 200){
                    // Well hello, it's the HTML of the page
                    // Recreate previous format, cause, you know
                    $headers = $this->http->response['headers']->getAll();
                    return (object)array(
                        'name'          => $headers['x-page-name'],
                        'id'            => $headers['x-page-id'],
                        'version'       => $headers['x-version'],
                        'create_date'   => $headers['x-created-at'],
                        'htmlonly'      => true,
                        'page_data'     => $page,
                    );
                } else {
                    return $this->getPageOld($id);
                }
                return $page;
            } catch (\Exception $e){
                return $this->getPageOld($id);
            }
        }
        throw new \InvalidArgumentException(__('Page id required..', 'wpmktengine'));
    }


    /**
     * Rename Page
     *
     * @param $id
     * @param $name
     * @return bool
     * @throws ApiException
     */
    public function renamePage($id, $name)
    {
        // check first, trust later
        if(empty($id)){
            throw new \InvalidArgumentException(__('Invalid ID provided for page ID.', 'wpmktengine'));
        }
        if(empty($name)){
            throw new \InvalidArgumentException(__('Name is a required update page field!', 'wpmktengine'));
        }
        // call me maybe
        return $this->call(self::PUT_PAGES, array(
            'id' => (string)$id,
            'params' => array(
                'name' => $name
            )
        ));
    }


    /**
     * Set stream type
     *
     * @param $name
     * @param string $description
     * @return bool
     * @throws ApiException
     */
    public function setStreamType($name, $description = '')
    {
        if(empty($name)){
            throw new \InvalidArgumentException(__('Name is a required parameter for Activity Stream.', 'genoo'));
        }
        // leads
        $result = $this->call(self::POST_STREAM_TYPES, array(
            array(
                'name' => (string)$name,
                'description' => $description,
            )
        ));
        // return
        if(is_array($result) && (isset($result[0]))){
            if(is_object($result[0]) && (isset($result[0]->result)) && ($result[0]->result == 'success')){
                return TRUE;
            } else {
                return FALSE;
            }
        };
        return FALSE;
    }


    /**
     * Stream type full as an array
     *
     * @param array $array
     * @return bool
     * @throws ApiException
     */

    public function setStreamTypes($array = array())
    {
        if(empty($array) || !is_array($array)){
            throw new \InvalidArgumentException(__('setStreamTypes accepts only non-empty array as an argument.', 'genoo'));
        }
        return $this->call(self::POST_STREAM_TYPES, $array);
    }


    /**
     * Creates or updates lead records with the information supplied in a JSON request and returns
     * JSON data with the results of the lead creation. Adds all successfully created leads into the
     * supplied lead type. (lead type needs to be existing within your Genoo account) It can be the ID
     * of the lead type or the lead type’s name (case-sensitive).
     *
     * The request body is a formatted JSON string.  The required fields are “leadtype” and “leads”.
     * the “leads” field is an array of objects containing the fields and data for each lead to be created.
     * This array can be from one to 250 records.  A status of 401 is return if request exceeds maximum records.
     *
     * @param $leadType
     * @param array $leads
     * @param bool $update
     * @return object|string
     * @throws \InvalidArgumentException
     * @throws \LengthException
     */

    public function setLeads($leadType, array $leads = array(), $update = false)
    {
        //standard lead, example
        $stdLead = array('email' => '', 'first_name' => '', 'last_name' => '', 'web_site_url' => '');

        // check first, trust later
        if(empty($leadType) || !is_numeric($leadType) || !is_array($leads)){
           throw new \InvalidArgumentException(__('Error when posting leads, invalid LeadType | Leads', 'genoo'));
        }

        // check length
        $leadsCount = count($leads);
        if($leadsCount > 250){
            throw new \LengthException(__('Genoo API takes only 250 leads per call. Consider using a batch system.', 'genoo'));
        }

        // leads
        return $this->call(self::POST_LEADS, array(
            'leadtype' => (string)$leadType,
            'updateadd' => $update,
            'leads' => $leads
        ));
    }

    /**
     * @param array $products
     *
     * @return bool
     * @throws \WPMKTENGINE\ApiException
     */
    public function setProducts(array $products = array())
    {
        // check first, trust later
        if(empty($products) || !is_array($products)){
            throw new \InvalidArgumentException(__('Error when posting products. Products empty, or not array.', 'wpmktengine'));
        }
        // leads
        return $this->call(self::POST_PRODUCTS, $products);
    }


    /**
     * @param array $cart
     *
     * @return bool
     * @throws \WPMKTENGINE\ApiException
     */
    public function setCart(array $cart = array())
    {
        // check first, trust later
        if(empty($cart) || !is_array($cart)){
            throw new \InvalidArgumentException(__('Error when posting cart. Cart empty, or not array.', 'wpmktengine'));
        }
        // leads
        return $this->call(self::POST_ORDER, $cart);
    }

    /**
     * @param array $product
     *
     * @return bool
     * @throws \WPMKTENGINE\ApiException
     */
    public function setProduct(array $product = array())
    {
        // check first, trust later
        if(empty($product) || !is_array($product)){
            throw new \InvalidArgumentException(__('Error when posting products. Products empty, or not array.', 'wpmktengine'));
        }
        // leads
        return $this->call(self::POST_PRODUCTS, array($product));
    }

    /**
     * @param       $productId
     * @param array $params
     *
     * @return bool
     * @throws \WPMKTENGINE\ApiException
     */
    public function updateProduct($productId, $params = array())
    {
        $data['id'] = $productId;
        $data['params'] = $params;
        return $this->call(self::PUT_PRODUCT, $data);
    }

    /**
     * @param       $cartId
     * @param array $params
     *
     * @return object|string
     * @throws \WPMKTENGINE\ApiException
     */
    public function updateCart($cartId, $params = array())
    {
        $data['id'] = $cartId;
        $data['params'] = $params;
        $result = $this->call(self::PUT_ORDER, $data);
        return $this->onReturnHeader() === 200;
    }

    /**
     * Set single lead
     *
     * @param $leadType
     * @param $email
     * @param string $first_name
     * @param string $last_name
     * @param string $web_site_url
     * @param bool $update
     * @param array $additional
     * @return null
     */

    public function setLead($leadType, $email, $first_name = '', $last_name = '', $web_site_url = '', $update = false, $additional = array())
    {
        $lead = $this->setLeads($leadType, array(
            array(
                'email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'web_site_url' => $web_site_url,
            )
            +
            $additional
        ), $update);
        if($lead->result == 'processed'){
            return isset($lead->process_results[0]->genoo_id) ? $lead->process_results[0]->genoo_id : null;
        }
        return null;
    }


    /**
     * Set single lead - update
     *
     * @param $genooId
     * @param $leadType
     * @param $email
     * @param string $first_name
     * @param string $last_name
     * @param string $web_site_url
     * @return null
     */

    public function setLeadUpdate($genooId, $leadType, $email, $first_name = '', $last_name = '', $web_site_url = '')
    {
        $lead = $this->setLeads($leadType, array(
            array(
                'genoo_id' => $genooId,
                'email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'web_site_url' => $web_site_url,
            )
        ), true);
        if($lead->result == 'processed'){
            return isset($lead->process_results[0]->genoo_id) ? $lead->process_results[0]->genoo_id : null;
        }
        return null;
    }


    /**
     * Remove lead from Leadtype(s)
     *
     * @param $genooId
     * @param $leadtypes
     * @throws ApiException
     * @throws \Exception
     */

    public function removeLeadFromLeadtype($genooId, $leadtypes)
    {
        if(is_string($leadtypes) || is_numeric($leadtypes)){
            return $this->call(self::PUT_LEADTYPE_MEMBERS, array(
                'leadtype' => (string)$leadtypes,
                'action' => 'remove',
                'genoo_ids' => array(
                    (string)$genooId
                )
            ));
        } elseif(is_array($leadtypes)){
            foreach($leadtypes as $leadtype){
                $this->call(self::PUT_LEADTYPE_MEMBERS, array(
                    'leadtype' => (string)$leadtype,
                    'action' => 'remove',
                    'genoo_ids' => array(
                        (string)$genooId
                    )
                ));
            }
        } else {
            throw new \Exception(__('Leadtype must be either of string | numeric | array type.', 'genoo'));
        }
    }


    public function setUsers(array $array = array()){}
    public function setEmails(array $array = array()){}
    public function setSequences(array $array = array()){}


    /**
     * Post Activities
     *
     * Adds activity stream records for leads. Assumes custom activity stream type will be used, rather than
     * Existing Genoo-specific activity stream types.
     * Data in request should be a JSON formatted array of activity streams. To match a record to a lead each input must
     * contain either an email address or the genoo_id. It will return a result string that will contain any errors that may
     * have occurred in and array.
     *
     * @param array $array
     * @return object|string
     * @throws \InvalidArgumentException
     */

    public function postActivities($array = array())
    {
        // std activity
        $stdActivity = array(
            "genoo_id" => "123456",
            "activity_name" => "test activity",
            "activity_stream_type" => "example",
            "activity_date" => "2014-02-24T03:34:00-06:00",
            'activity_description' => '',
            "url" => "http:/www.genoo.com/page1"
        );

        // we don't want you
        if(!is_array($array)){
            throw new \InvalidArgumentException(__('Invalid activity stream type, array excpected.', 'genoo'));
        }

        return $this->call(self::POST_ACTIVITIES, $array);
    }


    /**
     * Adds activity stream entry for a lead.  Assumes custom Activity Stream Type will be used, rather than
     * existing Genoo-specific activity types.
     *
     * Note: To view or filter on custom Activity Stream Types, you must create them within the Genoo admin,
     * when logged in as the Account Manager user.  Be sure to create them EXACTLY as you are passing them
     * in through the API.
     *
     * Request object:
     *
     * array(
     *  'activity_date' => '2012-12-12T09:30:00-06:00',
     *  'activity_stream_type' => 'Your Custom Type String',
     *  'activity_name' => 'short description',
     *  'activity_description' => 'longer description text',
     *  'url'=> 'http://www.yourdomain.com/page-or-resource'
     * );
     *
     * @param $id
     * @param $date
     * @param string $type
     * @param null $name
     * @param null $desc
     * @param null $url
     * @return object|string
     * @throws \InvalidArgumentException
     */

    public function putActivity($id, $date, $type, $name, $desc = '', $url = '')
    {
        // check first, trust later
        if(empty($id)){
            return;
            // throw new \InvalidArgumentException(__('Invalid ID provided for Genoo activity stream.', 'genoo'));
        }
        if(empty($date)){
            return;
            // throw new \InvalidArgumentException(__('Date is a required activity field!', 'genoo'));
        }
        if(empty($type)){
            return;
            // throw new \InvalidArgumentException(__('Type is a required activity field!', 'genoo'));
        }
        if(empty($name)){
            return;
            // throw new \InvalidArgumentException(__('Name is a required activity field!', 'genoo'));
        }

        // prep
        $activityDateTime = new \DateTime($date);
        $activityDate = $activityDateTime->format('c');
        $activityType = !empty($type) ? $type : '';
        $activityName = $name;
        $activityDesc = $desc;
        $activityUrl = $url;

        // standard activity
        $stdActivity = array(
            'activity_date' => $activityDate, // Dates should be in the format that the field is set to or ISO 8601 format.
            'activity_stream_type' => $activityType, // posted comment
            'activity_name' => $activityName, // title of post
            'activity_description' => $activityDesc, // comment itself
            'url' => $activityUrl, // url of post,
            'ebid' => '',
            'ebslid' => ''
        );

        // call me maybe
        return $this->call(self::PUT_ACTIVITY, array(
            'id' => (string)$id,
            'params' => $stdActivity
        ));
    }


    /**
     * Put comment activity
     *
     * @param $id
     * @param $comment
     * @return object|string
     */

    public function putCommentActivity($id,$comment)
    {
        return $this->putActivity(
            $id,
            $comment->comment_date_gmt,
            'posted comment',
            get_the_title($comment->comment_post_ID),
            $comment->comment_content,
            get_permalink($comment->comment_post_ID)
        );
    }

    /**
     * This is used by extensions to esaily insert activities
     *
     * @param $email
     * @param $activityType
     * @param $activityName
     * @param string $description
     * @param string $url
     * @return null
     */
    public function putActivityByMail($email, $activityType, $activityName, $description = '', $url = '')
    {
        try {
            $lead = $this->getLeadByEmail($email);
            if(is_array(($lead)) && isset($lead[0])){
                // Get Lead Id
                $leadid = $lead[0]->genoo_id;
                // Set timezone
                $dt = new \DateTime("now", new \DateTimeZone(self::TIMEZONE));
                $dt->setTimestamp(time()); //adjust the object to correct timestamp
                $this->putActivity($leadid, $dt->format('Y-m-d H:i:s'), $activityType, $activityName, $description, $url);
                return $leadid;
            } else {
                //logError('Warning: lead' . $email . ' does not exist.');
            }
        } catch (\Exception $e){
            //logError('Error during: ' . $activityType . ', Error: ' . $e->getMessage());
        }
        return NULL;
    }

    /**
     * @param $id
     * @return bool
     * @throws ApiException
     */
    public function deletePage($id)
    {
        if($id){
            return $this->call(self::DELETE_PAGE, $id);
        }
        throw new \InvalidArgumentException(__('Page id required..', 'wpmktengine'));
    }

    /** ----------------------------------------------------- */
    /**                          Guts                         */
    /** ----------------------------------------------------- */

    /**
     * Is installation set up?
     *
     * @return bool
     */

    public function isSetup()
    {
        $genooApiKey = $this->settingsRepo->getApiKey();
        $genooTrackingCode = $this->settingsRepo->getTrackingCode();
        if((!empty($genooApiKey) && !empty($genooTrackingCode))){
            return true;
        }
        return false;
    }


    /**
     * Is set up fully?
     *
     * @return bool
     */

    public function isSetupFull()
    {
        $genooLeadType = $this->settingsRepo->getLeadType();
        if((!empty($genooLeadType) && is_numeric($genooLeadType))){
            return true;
        }
        return false;
    }


    /**
     * Returns a status and result on the validation of the api_key parameter value.
     *
     * @param $key
     * @return bool
     * @throws ApiException
     */

    public function validate($key = null)
    {
        // set key if set
        if($key){ $this->key = $key; }
        // actual code
        $call = $this->call(self::VALIDATE);
        if(isset($call->result) && $call->result == 'valid'){
            return true;
        }
        throw new ApiException(__('Your Genoo API key is not valid, please generate a new one in your Genoo Administration account.', 'genoo'));
    }


    /**
     * Call API
     *
     * @param $action
     * @param null $params
     * @return bool
     * @throws ApiException
     */

    private function call($action, $params = null)
    {
        // Filters
        $action = apply_filters('genoo_wpme_api_action', $action, $params);
        $params = apply_filters('genoo_wpme_api_params', $params, $action);
	    // Callstack
	    $this->callstack[][$action] = $params;
        try{
            switch($action){
                case self::VALIDATE:
                case self::GET_USERS:
                case self::GET_EMAILS:
                case self::GET_FORMS:
                case self::GET_LUMENS:
                case self::GET_LUMENS_CLASSLIST:
                case self::GET_LUMENS_CLASS:
                case self::GET_LEAD:
                case self::GET_LEADS_BY_EMAIL:
                case self::GET_LEADS_TYPES:
                case self::GET_LEADS_ALL:
                case self::GET_LEADS_BY:
                case self::GET_USERS_BY:
                case self::GET_USER_INFO:
                case self::GET_STREAM_TYPES:
                case self::GET_ACTIVE_FEATURES:
                case self::GET_PACKAGE_ECOMMERCE:
                case self::GET_PACKAGE_LMS:
                case self::GET_PACKAGE_FORUMS:
                case self::GET_PAGES:
                case self::GET_PAGE:
                    $url = $this->buildQuery($action, $params);
                    // Append all from forms
                    if(self::GET_FORMS === $action){
                        $url = $url . '&all=true';
                    }
                    $this->http->setUrl($url);
                    $this->http->get();
                    // return decoded json, if not json, actuall body
                    return $this->onReturn(Json::isJson($this->http->getBody()) ? Json::decode($this->http->getBody()) : $this->http->getBody());
                    break;
                case self::GET_FORM:   // Form returns JSON with HTML and Stylef
                    $url = $this->buildQuery($action, $params);
                    $url = str_replace('?api_key=' . $this->key, '/all?api_key=' . $this->key, $url);
                    $this->http->setUrl($url);
                    $this->http->get();
                    return $this->onReturn(Json::isJson($this->http->getBody()) ? Json::decode($this->http->getBody()) : $this->http->getBody());
                    break;
                case self::POST_LEADS:
                case self::POST_PRODUCTS:
                case self::POST_USERS:
                case self::POST_EMAILS:
                case self::POST_SEQUENCES:
                case self::POST_ACTIVITIES:
                case self::POST_STREAM_TYPES:
                case self::POST_ORDER:
                    $leadType = array_key_exists('leadtype', $params) ? $params['leadtype'] : null;
                    $this->http->setUrl($this->buildQuery($action, $leadType));
                    $this->http->post(Json::encode($params));
                    return $this->onReturn(Json::isJson($this->http->getBody()) ? Json::decode($this->http->getBody()) : $this->http->getBody());
                    // post
                    break;
                // put activity stream
                case self::PUT_ACTIVITY:
                case self::PUT_PAGES:
                case self::PUT_PRODUCT;
                case self::PUT_ORDER;
                    $this->http->setUrl($this->buildQuery($action, $params['id']));
                    $this->http->put(Json::encode($params['params']));
                    return $this->onReturn(Json::isJson($this->http->getBody()) ? Json::decode($this->http->getBody()) : $this->http->getBody());
                    break;
                // put leadtype members
                case self::PUT_LEADTYPE_MEMBERS:
                    $this->http->setUrl($this->buildQuery($action));
                    $this->http->put(Json::encode($params));
                    return $this->onReturn(Json::isJson($this->http->getBody()) ? Json::decode($this->http->getBody()) : $this->http->getBody());
                    break;
                case self::DELETE_PAGE:
                    $this->http->setUrl($this->buildQuery($action, $params));
                    $this->http->delete();
                    if($this->http->getResponseCode() == 204){
                        return TRUE;
                    }
                    return FALSE;
                    break;
            }
        } catch (Wordpress\HttpException $e){
            throw new ApiException('Wordpress HTTP Api: ' . $e->getMessage());
        } catch (JsonException $e){
            throw new ApiException('JSON parsing: ' . $e->getMessage());
        } catch (\Exception $e){
            throw new ApiException($e->getMessage());
        }
        return true;
    }


    /**
     * Call Custom
     *
     * @param        $action
     * @param string $method
     * @param null   $params
     *
     * @return object|string
     * @throws \Genoo\ApiException
     * @throws \Genoo\Utils\JsonException
     * @throws \Genoo\Wordpress\HttpException
     */

    public function callCustom($action, $method = 'GET', $params = NULL, $url = NULL)
    {
        // Filters
        $action = apply_filters('genoo_wpme_api_action', $action, $params);
        $params = apply_filters('genoo_wpme_api_params', $params, $action);
        // Callstack
        $this->callstack[][$action] = $params;
        $this->http->setUrl($this->buildQuery($action, $params));
        if(!is_null($url) && is_string($url)){
            $this->http->setUrl($url);
        }
        switch($method){
            case 'POST':
                $this->http->post(Json::encode($params));
                break;
            case 'PUT':
                $this->http->put(Json::encode($params));
                break;
            case 'DELETE':
                $this->http->delete(Json::encode($params));
                break;
            case 'PATCH':
                break;
            case 'GET':
            default:
                $this->http->get();
                break;
        }
        return $this->onReturn(Json::isJson($this->http->getBody()) ? Json::decode($this->http->getBody()) : $this->http->getBody());
    }

    /**
     * Builds query out of param(s) in their order in array,
     * to the current API rul
     *
     * @param $action
     * @param null $params
     * @return mixed|null
     */

    public function buildQuery($action, $params = null)
    {
        if(!empty($action)){
            // prep action and lastQuery
            $prepAction = $action;
            $prepUrl = (GENOO_DEV ? self::URL_DEV : self::URL) . str_replace(array('[A]','[S]', '[P]', '[D]'), '', $action);
            // build query arguments
            if(Strings::endsWith($prepAction, "[S]") || Strings::endsWith($prepAction, "[D]") || Strings::endsWith($prepAction, "[P]")){
                // GET STRING
                if(!is_array($params)){
                    $prepUrl .= '/' . $params;
                }
            } elseif(Strings::endsWith($prepAction, "[A]") && is_array($params)){
                // GET ARRAY
                foreach($params as $param){
                    $prepUrl .= '/' . $param;
                }
            }
            // lastQuery
            return $this->lastQuery = Utils::addQueryParam($prepUrl, 'api_key', $this->key);
        }
        return null;
    }


    /**
     * On return, checks if it's an error, or wrong result.
     *
     * @param $object
     * @return object|string
     * @throws ApiException
     */

    public function onReturn($object)
    {
        if(is_string($object) && $this->isApiError($object)){
            throw new ApiException($object);
        } elseif(is_object($object)){
            if(isset($object->result) && ($object->result == 'failed' || $object->result == 'false') && (isset($object->error_message))){
                throw new ApiException($object->error_message);
            }
            return $object;
        }
        return $object;
    }

    /**
     * @return mixed
     */
    private function onReturnHeader()
    {
        return $this->http->getResponseCode();
    }

    /**
     * Is api error message?
     *
     * @param $error
     * @return bool
     */

    private function isApiError($error){ return in_array($error, self::$messages); }


    /**
     * Get's back API error, translated
     *
     * @param $error
     * @return mixed
     */

    private function getApiError($error){ return self::$messages[array_search($error, self::$messages)]; }

}

class ApiException extends \Exception{}
class ApiNoteException extends \Exception{}
