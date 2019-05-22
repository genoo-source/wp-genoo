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


use WPMKTENGINE\Utils\Strings;

class Http
{
    /** @var  */
    var $response;
    /** @var array */
    var $args = array('sslverify' => false, 'timeout' => 120);
    /** @var  */
    var $url;
    var $requestBody;
    /** @var array  */
    var $headers = array();


    /**
     * Concstructor
     *
     * @param null $url
     */
    public function __construct($url = null)
    {
        $this->url = $url;
        $this->apikeySetup();
        return $this;
    }

    /**
     * Auto set api key
     */
    public function apikeySetup()
    {
        if(!empty($this->url)){
            $query = parse_url($this->url, PHP_URL_QUERY);
            if(!empty($query)){
                parse_str($query, $queryParams);
                if(!empty($queryParams['api_key'])){
                    $this->headers['X-API-KEY'] = $queryParams['api_key'];
                    $this->url = remove_query_arg('api_key', $this->url);
                }
            }
            $this->url = str_replace('/?', '?', $this->url);
            $this->url = rtrim($this->url, '/');
        }
    }


    /**
     * @param array $args
     * @return $this
     */
    public function setArgs(array $args = array())
    {
        $this->args = $args;
        return $this;
    }


    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url = '')
    {
        $this->url = $url;
        $this->apikeySetup();
        return $this;
    }


    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body = '')
    {
        $this->args['body'] = $body;
        $this->requestBody = $body;
        return $this;
    }

    /**
     * @return $this
     * @throws HttpException
     */
    public function get()
    {
        // content type need for correct API resopnse
        $defaults = array('headers' => array('Accept' => 'application/json') + $this->headers);
        $this->response = \wp_remote_get($this->url, array_merge($defaults, $this->args));
        $this->check();
        return $this;
    }

    /**
     * @param null $body
     * @param string $method
     * @throws HttpException
     */
    public function post($body = null, $method = 'POST')
    {
        // content type need for correct API resopnse
        $defaults = array(
            'method' => $method,
            'timeout' => 120,
            'body'   => $body,
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Content-Length' => strlen($body)
            ) + $this->headers,
        );
        $this->requestBody = $body;
        // go my man
        $this->response = \wp_remote_post($this->url, array_merge($defaults, $this->args));
        $this->check();
    }


    /**
     * @param null $body
     */
    public function put($body = null)
    {
        $this->post($body, 'PUT');
    }

    /**
     * Couldn't get working with WP_Http,
     * so changed to work with curl
     *
     * @throws HttpException
     */
    public function delete($body = '')
    {
        $headers = $this->headers;
        $headersNew = array("Content-Type: application/json");
        if(!empty($headers)){
            foreach ($headers as $key => $value){
                $headersNew[] = $key . ": " . $value;
            }
        }
        // Defaults
        // Get cURL resource
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headersNew);
        // Set body
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        // Send the request & save response to $resp
        $resp = curl_exec($ch);
        if(!$resp){
            // This is good!
            if(curl_getinfo($ch, CURLINFO_HTTP_CODE) == 204){
                $this->response['response'] = array();
                $this->response['response']['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $this->response['response']['body'] = '';
                // Unathorised
                // $this->checkUnathorizedApiCall();
            } else {
                throw new HttpException('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch) . ' Code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE));
            }
        } else {
            $this->response['response'] = array();
            $this->response['response']['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $this->response['response']['body'] = $resp;
            // Unathorised
            // $this->checkUnathorizedApiCall();
        }
        curl_close($ch);
        $this->check();
    }


    /**
     * @throws HttpException
     */
    public function head()
    {
        // content type need for correct API resopnse
        $defaults = array('headers' => array('Accept' => 'application/json') + $this->headers);
        $this->response = \wp_remote_head($this->url, array_merge($defaults, $this->args));
        $this->check();
    }


    /**
     * Check's response after operation
     *
     * @throws HttpException
     */
    private function check()
    {
        return;
    }


    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response['response'];
    }


    /**
     * Response code
     *
     * @return mixed
     */
    public function getResponseCode()
    {
        if(is_wp_error($this->response)){
          return 403;
        }
        return $this->response['response']['code'];
    }


    /**
     * Get response body
     *
     * @return mixed
     */
    public function getBody()
    {
        if(is_wp_error($this->response)){
            return '';
        }
        return isset($this->response['body']) ? $this->response['body'] : '';
    }


    /**
     * Reset
     */
    public function reset()
    {
        $this->response = '';
        $this->args = array();
        $this->url= '';
    }

    /**
     * Returns whether to continue with exception?
     *
     * @return bool
     */
    public function checkUnathorizedApiCall()
    {
        if(
            ($this->response['response']['code'] === 401 && Strings::contains($this->url, WPMKTENGINE_DOMAIN))
            ||
            ($this->response['response']['code'] === 400 && Strings::contains($this->url, WPMKTENGINE_DOMAIN) && strtolower($this->response['response']['body']) == 'not authenticated')
        ){
            //\WPMKTENGINE::unauthorized();
            return false;
        }
        return true;
    }
}


class HttpException extends \Exception{}