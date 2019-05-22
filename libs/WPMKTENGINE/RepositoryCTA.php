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

class RepositoryCTA extends Repository
{
    /** 3600 seconds = hour */
    const REPO_TIMER = '3600';
    /** cache namespace */
    const REPO_NAMESPACE = 'cta';


    /**
     * Get array of ctas for JS
     *
     * @return array
     */

    public function getArray()
    {
        $r = array();
        $ctas = get_posts(array('post_type' => 'cta', 'posts_per_page' => -1));
        if($ctas){
            foreach($ctas as $cta){
                // Get CTA id
                if($apiId = \WPME\ApiExtension\CTA::inApi($cta->ID)){
                    $r[$apiId] = $cta->post_title;
                } else {
                    $r[$cta->ID] = $cta->post_title;
                }
                unset($apiId);
            }
        }
        return $r;
    }

    /**
     * TinyMCE array
     *
     * @return array
     */
    public function getArrayTinyMCE()
    {
        $r = array();
        $r[] = array(
            'text' => __('Select a CTA'),
            'value' => '',
        );
        $ctas = get_posts(array('post_type' => 'cta', 'posts_per_page' => -1));
        if($ctas){
            foreach($ctas as $cta){
                // Get CTA id
                if($apiId = \WPME\ApiExtension\CTA::inApi($cta->ID)){
                    $r[] = array(
                        'text' => $cta->post_title,
                        'value' => (string)$apiId,
                    );
                } else {
                    $r[] = array(
                        'text' => $cta->post_title,
                        'value' => (string)$cta->ID,
                    );
                }
                unset($apiId);
            }
        }
        return $r;
    }

    /**
     * Run dry IMPORT
     *
     * @return bool
     */
    public function runImportDry()
    {
        global $WPME_API;
        // Get ctas
        $ctasInApi = \WPME\Extensions\CTAs::getCTAsNotInWordPress();
        // Do!
        try {
            if($ctasInApi && !empty($ctasInApi)){
                $ctasApi = new \WPME\ApiExtension\CTA($WPME_API->settingsRepo);
                foreach($ctasInApi as $cta){;
                    \WPME\Extensions\CTAs::importCTA($ctasApi->get($cta->id));
                }
                return TRUE;
            }
            return NULL;
        } catch (\Exception $e){
            return FALSE;
        }
    }

    /**
     * Flush CTA's
     */
    public function flush()
    {
        // Get db
        global $wpdb;
        // Table names
        $posts = $wpdb->posts;
        $postsMeta = $wpdb->postmeta;
        $postsRelationships = $wpdb->term_relationships;
        // Queries
        $wpdb->query("DELETE FROM $posts WHERE post_type='cta'");
        $wpdb->query("DELETE FROM $postsMeta WHERE post_id NOT IN (SELECT id FROM $posts)");
        $wpdb->query("DELETE FROM $postsRelationships WHERE object_id NOT IN (SELECT id FROM $posts)");
        // Done
    }

    /**
     * Flush posts that are in API and
     * not owned by us.
     *
     * @return bool
     */
    public function flushApis()
    {
        // Get posts
        $query = new \WP_Query(
            array(
                'post_type' => 'cta',
                'post_status' => 'any',
                'posts_per_page' => -1,
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => \WPME\ApiExtension\CTA::IDENTIFICAOR,
                        'value' => '',
                        'compare' => '!=',
                    ),
                    array(
                        'key' => \WPME\ApiExtension\CTA::IDENTIFICAOR_OWNER,
                        'value' => \WPME\Extensions\CTAs::getCurrentOwner(),
                        'compare' => '!=',
                    )

                )
            )
        );
        // If we have posts, go through and delete
        if($query->have_posts()){
            $queryPosts = $query->posts;
            foreach($queryPosts as $post){
                wp_delete_post($post->ID, true);
            }
        }
        return true;
    }
}