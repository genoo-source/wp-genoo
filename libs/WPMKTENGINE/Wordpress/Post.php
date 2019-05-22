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

use WPMKTENGINE\CTA;


class Post
{
    /** @var */
    public $id;
    /** @var \WP_Post object */
    public $post;

    /**
     * Set post
     *
     * @param $id
     * @return static
     * @throws \InvalidArgumentException
     */

    public function __construct($id)
    {
        if (is_numeric($id) || is_string($id)){
            $i = $id;
            $post = get_post($id);
        } elseif (is_object($id) && ($id instanceof \WP_Post)) {
            $i = $id->ID;
            $post = $id;
        } else {
            throw new \InvalidArgumentException('ID or Post object needs to be provided.');
        }
        $this->id = $i;
        $this->post = $post;
    }

    /**
     * Is Post Id?
     *
     * @param $postId
     * @return mixed
     */

    public static function is($postId)
    {
        return is_post($postId);
    }


    /**
     * Is single
     *
     * @return mixed
     */

    public static function isSingle()
    {
        return is_single();
    }


    /**
     * Is Page
     *
     * @return mixed
     */

    public static function isPage()
    {
        return is_page();
    }


    /**
     * Is post type "this" type?
     *
     * @param \WP_Post $post
     * @param $type
     * @return bool
     */

    public static function isPostType(\WP_Post $post, $type)
    {
        if (is_string($type) && !empty($type)) {
            return $post->post_type == $type;
        } elseif (is_array($type)) {
            return in_array($post->post_type, $type);
        }
        return false;
    }


    /**
     * Set post
     *
     * @param $id
     * @return static
     * @throws \InvalidArgumentException
     */

    public static function set($id)
    {
        return new Post($id);
    }


    /**
     * Returns post
     *
     * @return \WP_Post
     */

    public function getPost()
    {
        return $this->post;
    }


    /**
     * Check post exists
     *
     * @param $postId
     * @return bool
     */

    public static function exists($postId)
    {
        $post = get_post($postId);
        if (!empty($post)) {
            return true;
        }
        return false;
    }


    /**
     * Get post types
     *
     * @param array $args
     * @return mixe
     */

    public static function getTypes($args = array())
    {
        return get_post_types(array_merge(array('public' => true, 'show_ui' => true), $args), 'objects');
    }


    /**
     * Get meta
     *
     * @param $name
     * @return \InvalidArgumentException
     */

    public function getMeta($name)
    {
        if (empty($this->id)) {
            return new \InvalidArgumentException('No post ID specified. Used method set first.');
        }
        return get_post_meta($this->id, $name, true);
    }


    /**
     * Gettitle
     *
     * @return \InvalidArgumentException
     */

    public function getTitle()
    {
        if (empty($this->id)) {
            return new \InvalidArgumentException('No post ID specified. Used method set first.');
        }
        return get_the_title($this->id);
    }


    /**
     * Set meta
     *
     * @param $name
     * @param $value
     * @return \InvalidArgumentException
     */

    public function setMeta($name, $value)
    {
        if (empty($this->id)) {
            return new \InvalidArgumentException('No post ID specified. Used method set first.');
        }
        return pdate_post_meta($this->id, $name, $value);
    }
}