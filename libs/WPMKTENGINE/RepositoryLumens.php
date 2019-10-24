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
 * @category WPME
 * @package RepositoryLumens
 * @author Genoo, LLC <info@genoo.com>
 * @license https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html GNU General Public License, version 2
 * @link self
 */
class RepositoryLumens extends Repository
{
    /**
     * @var \WPMKTENGINE\Cache
     */
    private $cache;
    /**
     * @var \WPMKTENGINE\Api
     */
    private $api;

    const REPO_TIMER = '3600';
    const REPO_NAMESPACE = 'lumens';

    /**
     * @param Cache $cache
     */
    public function __construct(\WPMKTENGINE\Cache $cache, \WPME\ApiFactory $api, $empty = false)
    {
        $this->cache = $cache;
        $this->api = $api;
        $this->empty = false;
        parent::__construct();
    }



    /**
     * Get Lumens class lists
     *
     * @return object|string
     */
    public function getLumens()
    {
        // Empty? return empty
        if ($this->empty) {
            return array();
        }
        $prepLumens = '';
        try {
            if (!$prepLumens = $this->cache->get(self::REPO_NAMESPACE, self::REPO_NAMESPACE)) {
                $prepLumens = $this->api->getLumensClassList();
                $this->cache->set(self::REPO_NAMESPACE, $prepLumens, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch (\Exception $e) {
        }
        return $prepLumens;
    }


    /**
     * Get Lumen class list
     *
     * @param  $id
     * @return bool|mixed
     */
    public function getLumen($id)
    {
        // Empty? return empty
        if ($this->empty) {
            return '';
        }
        $prepLumen = '';
        try {
            if (!$prepLumen = $this->cache->get((string)$id, self::REPO_NAMESPACE)) {
                $prepLumen = $this->api->getLumen($id);
                $this->cache->set((string)$id, $prepLumen, self::REPO_TIMER, self::REPO_NAMESPACE);
            }
        } catch (\Exception $e) {
        }
        // Get Lumen class without "http://" and "https://" here already
        return Utils::nonProtocolUrl($prepLumen);
    }


    /**
     * Get Forms Array
     *
     * @return array
     */
    public function getLumensArray()
    {
        // Empty? return empty
        if ($this->empty) {
            return array();
        }
        $lumensVars = array();
        try {
            $lumens = $this->getLumens();
            if (!empty($lumens)) {
                foreach ($lumens as $lumen) {
                    if (is_array($lumen) && !empty($lumen)) {
                        $lumensVars[$lumen['id']] = $lumen['name'];
                    }
                }
            }
        } catch (\Exception $e) {
        }
        return $lumensVars;
    }

    /**
     * @return array
     */
    public function getLumensArrayTinyMCE()
    {
        // Empty? return empty
        if ($this->empty) {
            return array(
            array(
                'text' => __('Select a Lumens Class'),
                'value' => '',
            )
            );
        }
        $lumensVars = array();
        $lumensVars[] = array(
            'text' => __('Select a Lumens Class'),
            'value' => '',
        );
        try {
            $lumens = $this->getLumens();
            if (!empty($lumens) && is_array($lumens)) {
                foreach ($lumens as $lumen) {
                    if (is_array($lumen) && !empty($lumen)) {
                        $lumensVars[] = array(
                            'text' => $lumen['name'],
                            'value' => (string)$lumen['id']
                        );
                    }
                }
            }
        } catch (\Exception $e) {
        }
        return $lumensVars;
    }


    /**
     * Get forms for listing table
     *
     * @return array
     */
    public function getLumensTable()
    {
        // Empty? return empty
        if ($this->empty) {
            return array();
        }
        $forms = array();
        $lumens = $this->getLumens();
        if (!empty($lumens)) {
            foreach ($lumens as $form) {
                $form = (object)$form;
                $forms[] = array(
                    'id' => $form->id,
                    'name' => $form->name,
                );
            }
        }
        return $forms;
    }


    /**
     * @return bool
     */
    public function flush()
    {
        return $this->cache->flush(self::REPO_NAMESPACE);
    }
}
