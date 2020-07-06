<?php
/**
 * WPME Plugin
 *
 * PHP version 5.5
 *
 * @category WPMKTGENGINE
 * @package WPMKTGENGINE
 * @author  Genoo, LLC <info@genoo.com>
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License, version 2
 * @link    https://profiles.wordpress.org/genoo#content-about
 */
/**
 * This file is part of the WPMKTGENGINE plugin.
 *
 * Copyright 2016 Genoo, LLC. All rights reserved worldwide.
 * (web: http://www.wpmktgengine.com/)
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

use WPMKTENGINE\Wordpress\Utils;

/**
 * Class RepositoryLandingPages
 *
 * @package WPMKTENGINE
 */
class RepositoryLandingPages
{
    /**
     *
     *
     * @var array
     */
    public $pages;
    /**
     *
     *
     * @var array
     */
    public $templates;
    /**
     *
     *
     * @var bool
     */
    public $has = false;
    /**
     *
     *
     * @type \WP_Query
     */
    public $homepage;
    /**
     *
     *
     * @type bool
     */
    public $isHomepage;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pages = get_posts(
            array(
                'posts_per_page'   => -1,
                'post_type'        => 'wpme-landing-pages',
                'post_status'      => 'publish',
            )
        );
        $this->iterate();
        $this->check();
        $this->homepage = new \WP_Query(array('meta_key' => 'wpmktengine_landing_homepage', 'meta_value' => 'true', 'posts_per_page' => -1, 'post_type' => 'wpme-landing-pages'));
    }

    /**
     * Append meta keys etc.
     */
    public function iterate()
    {
        if (!empty($this->pages) && is_array($this->pages)) {
            foreach ($this->pages as $key => $page) {
                $meta = get_post_meta($page->ID);
                if (is_array($meta) && !empty($meta)) {
                    foreach ($meta as $key2 => $value) {
                        if (is_array($value)) {
                            $meta[$key2] = $meta[$key2][0];
                        }
                    }
                }
                $this->pages[$page->ID] = $page;
                $this->pages[$page->ID]->id = $key;
                $this->pages[$page->ID]->meta = (object)$meta;
            }
        }
    }

    /**
     * Check
     */
    public function check()
    {
        if (!empty($this->pages) && is_array($this->pages)) {
            foreach ($this->pages as $page) {
                if ((isset($page->meta->wpmktengine_landing_url)
                    && !empty($page->meta->wpmktengine_landing_url))
                    && (isset($page->meta->wpmktengine_landing_template)
                    && !empty($page->meta->wpmktengine_landing_template))
                ) {
                    $this->has = true;
                    $this->templates[$page->id] = (object)array(
                            'url' => $page->meta->wpmktengine_landing_url,
                            'page' => $page
                    );
                }
            }
        } else {
            $this->has = false;
        }
    }

    /**
     * @return bool
     */
    public function has()
    {
        return $this->has;
    }

    /**
     * @return bool
     */
    public function hasHomepage()
    {
        return $this->homepage->post_count > 0;
    }

    /**
     * @return mixed
     */
    public function getHomepage()
    {
        return $this->homepage->post;
    }

    /**
     * @return mixed
     */
    public function getHomepageID()
    {
        return $this->homepage->post->ID;
    }

    /**
     * @param bool $aff
     * @return bool
     */
    public function isHomepageWP($aff = false)
    {
        $home = get_home_url();
        $home = rtrim($home, '/');
        $current = Utils::getRealUrl();
        if(function_exists('affiliate_wp')){
          try {
            $refName = affiliate_wp()->settings->get('referral_var', 'ref');
            $refName = $refName . '/';
            if (strpos($current, $refName) !== false) {
                $current = substr($current, 0, strpos($current, $refName));
            }
          } catch (\Exception $e){
          }
        }
        $current = strtok($current, '?');
        $current = rtrim($current, '/');
        return $home == $current;
    }

    /**
     * Fits this url?
     *
     * @param  $urlSample
     * @return mixed
     */
    public function fitsUrl($urlSample)
    {
        // If we have a HOMEPAGE object, and this is indeed Homepage
        if ($this->hasHomepage() && $this->isHomepageWP()) {
            $obj = new \stdClass();
            $obj->url = '';
            $obj->page = $this->pages[$this->getHomepageID()];
            return $obj;
        } else {
            $urlSample = $this->cleanseUrl($urlSample);
            $urlSample = $this->cleansAffiliates($urlSample);
            foreach ($this->templates as $id => $url) {
                // Base
                $base = $this->base();
                $baseFull = $base . $url->url;
                $baseFull = $this->cleanseUrl($baseFull);
                // Fix / added by WP and in URL
                if (strtolower($urlSample) == strtolower($baseFull)) {
                    return $url;
                } else {
                    $baseFull = str_replace('/?', '?', $baseFull);
                    $urlSample = str_replace('/?', '?', $urlSample);
                    // relative url
                    $baseFull = str_replace('http://', '//', $baseFull);
                    $urlSample = str_replace('http://', '//', $urlSample);
                    $baseFull = str_replace('https://', '//', $baseFull);
                    $urlSample = str_replace('https://', '//', $urlSample);
                    if (strtolower($urlSample) == strtolower($baseFull)) {
                        return $url;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param $url
     * @return string
     */
    public function cleanseUrl($url)
    {
        if (!empty($url) && is_string($url)) {
            $url = strtok($url, '?');
            $url = rtrim($url, '/');
        }
        return $url;
    }

    /**
     * Checks whether we should check for affialite URL
     *
     * @return bool
     */
    public static function checkAffiliate()
    {
        return function_exists('affiliate_wp');
    }

    /**
     * @param $url
     * @return mixed
     */
    public function cleansAffiliates($url)
    {
        if (!empty($url) && is_string($url)) {
            // Affilaite WP?
            if (function_exists('affiliate_wp')) {
                $affilaiteVar = affiliate_wp()->settings->get('referral_var', 'ref');
                $affilaiteVar = apply_filters('affwp_referral_var', $affilaiteVar);
                // Remove query ref
                $url = remove_query_arg($affilaiteVar, $url);
                // Regex for : www.something.com/ref/%username-or-id%
                // \/ref\/[a-zA-Z0-9]+([_ -]?[a-zA-Z0-9])*$
                // Regex for : www.something.com?ref=111
                // [\?|\&]ref=[a-zA-Z0-9]+([_ -]?[a-zA-Z0-9])*
                // Replace with: $1
                $url = preg_replace('/\/' . $affilaiteVar .'\/[a-zA-Z0-9]+([_ -]?[a-zA-Z0-9])*$/', '$1', $url);
            }
            return $url;
        }
        return $url;
    }


    /**
     * Find dependencies
     *
     * @return array
     */
    public static function findDependencies()
    {
        global $wpdb;
        $r = array();
        if (isset($wpdb)) {
            $results = $wpdb->get_results(
              'SELECT 
                *
              FROM '. $wpdb->prefix .'postmeta pm JOIN '. $wpdb->prefix .'posts p ON pm.post_id = p.ID WHERE pm.meta_key = "wpmktengine_landing_template" ORDER BY p.post_title ASC', OBJECT);
            if (is_array($results)) {
                // Iterate through
                foreach ($results as $result) {
                    $r[$result->meta_value][] = $result->post_id;
                }
                return $r;
            }
            return $r;
        }
        return $r;
    }

    public static function findDrafts(){
      return  get_posts(array(
        'post_type' => 'wpme-landing-pages',
        'numberposts' => -1,
        'orderby'=> 'title',
        'order' => 'ASC',
        'meta_query' => array(
          'relation' => 'OR',
          array(
            'key' => 'wpmktengine_landing_template',
            'value' => '',
            'compare' => '='
          ),
          array(
            'key' => 'wpmktengine_landing_template',
            'compare' => 'NOT EXISTS'
          ),
        )
      ));
    }


    /**
     * Dependencies for each template
     *
     * @param  $template_id
     * @return null
     */
    public static function findDependenciesForTemplate($template_id)
    {
        $depenedencies = self::findDependencies();
        if (Utils::isIterable($depenedencies) && array_key_exists($template_id, $depenedencies)) {
            return $depenedencies[$template_id];
        }
        return null;
    }


    /**
     * Dependencies for template with post data
     *
     * @param  $template_id
     * @return null|array
     */
    public static function findDependenciesForTemplateWithPost($template_id)
    {
        $depenedencies = self::findDependencies($template_id);
        if (Utils::isIterable($depenedencies)) {
            foreach ($depenedencies as $key => $dependency) {
                $depenedencies[$key] = get_post($depenedency);
            }
            return $depenedencies;
        }
        return null;
    }


    /**
     * Dependencies for all templates with posts
     *
     * @return null|array
     */
    public static function findDependenciesForTemplateWithPosts()
    {
        $depenedencies = self::findDependencies();
        $r = array();
        if (Utils::isIterable($depenedencies)) {
            foreach ($depenedencies as $key => $dependency) {
                if (Utils::isIterable($dependency)) {
                    foreach ($dependency as $key2 => $post) {
                        $r[$key][$key2] = get_post($post);
                    }
                }
            }
        }
        return $r;
    }


    /**
     * Dependency for a Post
     *
     * @param  $post_id
     * @return bool|int|string
     */
    public static function findDependenciesForPost($post_id)
    {
        $meta = get_post_meta($post_id, 'wpmktengine_landing_template', true);
        return is_numeric($meta) ? $meta : false;
    }

    /**
     * @return bool
     */
    public static function removeHomepages()
    {
        global $wpdb;
        $r = false;
        if (isset($wpdb)) {
            $delete = $wpdb->delete($wpdb->prefix . 'postmeta', array('meta_key' => 'wpmktengine_landing_homepage'));
            return true;
        }
        return $r;
    }

    /**
     * @param $page_id
     */
    public static function makePageHomepage($page_id)
    {
        // Just to make sure
        self::removeHomepages();
        // Just one can rule!
        \update_post_meta($page_id, 'wpmktengine_landing_homepage', 'true');
    }

    /**
     * Find How many have homepage as default value
     *
     * @return int
     */
    public function findHomepagesCount()
    {
        return $this->homepage->post_count;
    }


    /**
     * @return string
     */
    public static function base()
    {
        return rtrim(get_home_url(), '/') . '/';
    }
}
