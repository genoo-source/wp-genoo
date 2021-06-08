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

namespace WPME\ApiExtension;

use WPME\ApiFactory;
use WPME\Extensions\CTAs;

/**
 * Class CTA
 *
 * @package WPME\ApiExtension
 */
class CTA extends ApiFactory
{
    /** Idenetificator used in post meta */
    const IDENTIFICAOR = '_wpme_cta_id';
    /** Owner of the cta */
    const IDENTIFICAOR_OWNER = '_wpme_cta_owner';

    /**
     * Get all CTAs
     *
     * @return object|string
     */
    public function getAll()
    {
        return $this->callCustom(
            '/ctas',
            'GET',
            NULL
        );
    }

    /**
     * @return array
     */
    public function getAllWordPress()
    {
        // Prep
        $r = array();
        // All cta's
        $posts = get_posts(array(
            'post_type' => 'cta',
            'posts_per_page' => -1,
            'post_status' => array(
                'publish',
                'pending',
                'future',
                'private',
                'inherit',
                'trash'
            )
        ));
        if($posts){
            foreach($posts as $post){
                $r[$post->ID] = $post;
                $r[$post->ID]->inApi = self::inApi($post->ID);
                if($r[$post->ID]->inApi){
                    $r[$post->ID]->{self::IDENTIFICAOR} = get_post_meta(self::IDENTIFICAOR, $post->ID, true);
                    $owner = get_post_meta(self::IDENTIFICAOR_OWNER, $post->ID, true);
                    if(is_string($owner) && !empty($owner)){
                        $r[$post->ID]->owner = $owner;
                    }
                }
            }
        }
        return $r;
    }

    /**
     * Get CTA
     *
     * @param $ctaId
     * @return object|string
     */
    public function get($ctaId)
    {
        if(!$ctaId){
            throw new InvalidArgumentException('No CTA id provided.');
        }
        return $this->callCustom(
            '/ctas[S]',
            'GET',
            $ctaId
        );
    }

    /**
     * Save
     *
     * @param $name
     * @param $data
     * @return object|string
     */
    public function save($name, $data)
    {
        if(empty($data)){
            throw new InvalidArgumentException('No data provided.');
        }
        if(empty($name)){
            throw new InvalidArgumentException('No name provided.');
        }
        $owner = CTAs::getCurrentOwner();
        try {
            $result = $this->callCustom(
                '/ctas',
                'POST',
                array(
                    'name' => $name,
                    'owner' => $owner,
                    'data' => $data
                )
            );
            return $result->id;
        } catch (\Exception $e){
            return FALSE;
        }
    }

    /**
     * Save
     *
     * @param $data
     * @return object|string
     */
    public function saveDirect($data)
    {
        if(empty($data)){
            throw new InvalidArgumentException('No data provided.');
        }
        try {
            $result = $this->callCustom(
                '/ctas',
                'POST',
                $data
            );
            return $result->id;
        } catch (\Exception $e){
            return FALSE;
        }
    }

    /**
     * Upate CTA in the APi
     *
     * @param $ctaId
     * @param $data
     * @return object|string
     */
    public function update($ctaId, $data)
    {
        if(!$ctaId){
            throw new InvalidArgumentException('No CTA id provided.');
        }
        return $this->callCustom(
            '/ctas[S]',
            'PUT',
            array(
                'data' => $data
            ),
            $this->buildQuery('/ctas[S]', $ctaId)
        );
    }

    /**
     * Upate CTA in the APi
     *
     * @param $ctaId
     * @param $data
     * @return object|string
     */
    public function updateDirect($ctaId, $data)
    {
        if(!$ctaId){
            throw new InvalidArgumentException('No CTA id provided.');
        }
        return $this->callCustom(
            '/ctas[S]',
            'PUT',
            $data,
            $this->buildQuery('/ctas[S]', $ctaId)
        );
    }

    /**
     * Remove CTA
     *
     * @param $ctaId
     * @return object|string
     */
    public function remove($ctaId)
    {
        if(!$ctaId){
            throw new \InvalidArgumentException('No CTA id provided.');
        }
        return $this->callCustom(
            '/ctas[S]',
            'DELETE',
            $ctaId
        );
    }

    /**
     * In APi?
     *
     * @param $post_id
     * @return bool|string
     */
    public static function inApi($post_id)
    {
        $inApi = get_post_meta($post_id, \WPME\ApiExtension\CTA::IDENTIFICAOR, true);
        return is_string($inApi) && !empty($inApi) ? $inApi : false;
    }

    /**
     * Is current owner?
     *
     * @param $owner
     * @param null $current
     * @return bool
     */
    public static function isOwner($owner, $current = null)
    {
        if($current == null || !is_string($current)){
            $current = \WPME\Extensions\CTAs::getCurrentOwner();
        }
        return $owner == $current;
    }

    /**
     * Am I current owner of this CTA?
     *
     * @param $post_id
     * @return bool
     */
    public static function amIOwner($post_id)
    {
        $ownerCurrent = \WPME\Extensions\CTAs::getCurrentOwner();
        $owner = self::getOwner($post_id);
        $owner = is_string($owner) && !empty($owner) ? $owner : false;
        if($owner){
            return self::isOwner($owner, $ownerCurrent);
        }
        // Not in APi, so you're owner, yes
        return true;
    }

    /**
     * @param $post_id
     * @return mixed
     */
    public static function getOwner($post_id)
    {
        return genoo_wpme_get_domain(get_post_meta($post_id, \WPME\ApiExtension\CTA::IDENTIFICAOR_OWNER, true));
    }


    /**
     * Get by API ID
     * - gets first post ID
     *
     * @param $api_id
     * @return bool
     */
    public static function getByAPIId($api_id)
    {
        $args = array(
            'post_type' => 'cta',
            'meta_query' => array(
                array(
                    'key' => self::IDENTIFICAOR,
                    'value' => $api_id,
                    'compare' => '=',
                )
            )
        );
        $query = new \WP_Query($args);
        if($query->have_posts()){
            $posts = $query->get_posts();
            return $posts[0]->ID;
        }
        return FALSE;
    }
}

