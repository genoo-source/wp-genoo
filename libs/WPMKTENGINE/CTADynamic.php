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
use WPMKTENGINE\Wordpress\Post;
use WPMKTENGINE\Wordpress\Sidebars;
use WPMKTENGINE\RepositorySettings;
use WPMKTENGINE\CTA;


class CTADynamic extends CTA
{
    /** @var RepositorySettings */
    public $repositarySettings;
    /** @var array */
    public $ctas = array();
    /** @var array */
    public $ctasRegister = array();
    /** @var static */
    public $postOrg;

    /**
     * Constructor
     *
     * @param \WP_Post $post
     */

    public function __construct(\WP_Post $post)
    {
        $this->postOrg = $post;
        $this->post = Post::set($post->ID);
        $this->repositarySettings = new RepositorySettingsFactory();
        $this->postTypes = $this->repositarySettings->getCTAPostTypes();
        $this->postObject = $this->post->getPost();
        if($this->has()){
            $this->resolve();
        }
    }


    /**
     * Has dynamic CTA's?
     *
     * @return bool
     */

    public function has()
    {
        $meta = $this->post->getMeta('enable_cta_for_this_post_repeat');
        if(!empty($this->postTypes) && (is_array($this->postTypes)) && (in_array($this->postObject->post_type, $this->postTypes)) && $meta){
            $ctas = $this->post->getMeta(apply_filters('genoo_wpme_repeatable_key', 'repeatable_wpmktgengine-dynamic-cta'));
            if(!empty($ctas)){
                return true;
            }
            return false;
        }
        return false;
    }


    /**
     * Has multiple CTA's?
     *
     * @return bool
     */

    public function hasMultiple()
    {

        return !empty($this->ctas);
    }


    /**
     * Resolve
     */

    public function resolve()
    {
        $ctas = $this->post->getMeta(apply_filters('genoo_wpme_repeatable_key', 'repeatable_wpmktgengine-dynamic-cta'));
        foreach($ctas as $ct){
            // Does CTA and sidebar Exists?
            if(Post::exists($ct['cta']) && Sidebars::exists($ct['sidebar'])){
                $objCta = new CTA();
                $obj = (object)$ct;
                $obj->cta = $objCta->setCta($obj->cta);
                unset($obj->sidebar);
                $this->ctas[$ct['sidebar']][] = $obj;
                // Inject position before adding
                $obj->cta->position = (int)$ct['position'];
                $obj->cta->sidebar = $ct['sidebar'];
                $this->ctasRegister[] = $obj->cta;
            }
        }
        $this->has = $this->hasMultiple();
        $this->hasMultiple = $this->hasMultiple();
    }


    /**
     * Get CTAs
     *
     * @return array
     */

    public function getCtas()
    {
        return $this->ctas;
    }


    /**
     * Get ctas for Widgets::injectRegisterWidgets()
     *
     * @return array
     */

    public function getCtasRegister()
    {
        return $this->ctasRegister;
    }
}