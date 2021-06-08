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

namespace WPME\Extensions;

/**
 * Class CTAs
 *
 * @package WPME\Extensions
 */
class CTAs
{

    /**
     * Register post type
     */
    public static function registerCTAPostType()
    {
        // Which page
        $subPage = class_exists('Genoo\Api') ? 'Genoo' : 'WPMKTENGINELogin';
        // Post Type
        new \WPMKTENGINE\Wordpress\PostType('cta',
            array(
                'supports' => array('title'),
                'label' => __('CTAs', 'wpmktengine'),
                'labels' => array(
                    'add_new' => __('New CTA', 'wpmktengine'),
                    'not_found' => __('No CTA\'s found', 'wpmktengine'),
                    'not_found_in_trash' => __('No CTA\'s found in Trash', 'wpmktengine'),
                    'edit_item' => __('Edit CTA', 'wpmktengine'),
                    'add_new_item' => __('Add Call-to-Action (CTA)', 'wpmktengine'),
                ),
                'public' => false,
                'exclude_from_search' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_nav_menus' => false,
                'show_in_menu' => $subPage,
                'show_in_admin_bar' => false,
            )
        );
    }

    public static function registerCTAPostTypeMeta($data, $post, $context)
    {
        $data->data['meta'] = self::cleanseData(get_post_meta($post->ID));
        return $data;
    }

    /**
     * Clean data
     *
     * @param $data
     * @return array
     */
    public static function cleanseData($data)
    {
        if(empty($data)){
            return $data;
        }
        if(!is_array($data)){
            return $data;
        }
        foreach($data as $key => $dataItem){
            if(is_array($dataItem) && count($dataItem) === 1){
                $dtaa = self::isSerialised($dataItem[0]) ? unserialize($dataItem[0]) : $dataItem[0];
                $data[$key] = $dtaa;
            }
        }
        return $data;
    }

    /**
     * Is Serialised
     *
     * @param $string
     * @return bool
     */
    public static function isSerialised($string) {
        return (@unserialize($string) !== false || $string == 'b:0;');
    }

    /**
     * This is where the dynamic CTA magic
     * happens.
     */
    public static function register()
    {
        // Register post type
        add_action('init', array('\WPME\Extensions\CTAs', 'registerCTAPostType'));
        add_filter('rest_prepare_cta', array('\WPME\Extensions\CTAs', 'registerCTAPostTypeMeta'), 10, 3);

        /**
         * Upon saving post, save CTA accross
         */
        add_action('save_post', function($post_id, $post, $update){
            // Get post type
            $post_type = get_post_type($post_id);
            // Run only for cta
            if("cta" != $post_type) return;
            // Only update (no auto-draft)
            if($update && (!defined('DOING_AJAX') || !DOING_AJAX)){
                global $WPME_API;
                if($WPME_API && \WPME\ApiExtension\CTA::amIOwner($post_id)){
                    $ctas = new \WPME\ApiExtension\CTA($WPME_API->settingsRepo);
                    $data = \WPME\Extensions\CTAs::convertForApi($post);
                    $dataInApi = \WPME\ApiExtension\CTA::inApi($post_id);
                    if($dataInApi == false){
                        // not in api, we create
                        $id = $ctas->saveDirect($data);
                        if($id){
                            update_post_meta($post_id, \WPME\ApiExtension\CTA::IDENTIFICAOR, $id);
                            update_post_meta($post_id, \WPME\ApiExtension\CTA::IDENTIFICAOR_OWNER, \WPME\Extensions\CTAs::getCurrentOwner());
                        }
                    } else {
                        // in api, we update
                        $ctas->updateDirect(
                            $dataInApi,
                            $data
                        );
                    }
                }
            }
        }, 200, 3);

        /**
         * CTA new columns
         */
        add_filter('manage_edit-cta_columns', function($columns){
            if(class_exists('\WPMKTENGINE\Utils\ArrayObject')){
                $class = new \WPMKTENGINE\Utils\ArrayObject();
            } elseif (class_exists('\Genoo\Utils\ArrayObject')){
                $class = new \WPMKTENGINE\Utils\ArrayObject();
            }
            $columns = $class->appendAfter($columns, 'cta_type', 'in_api', 'In APi');
            $columns = $class->appendAfter($columns, 'cta_type', 'owner', 'Owner');
            $columns = $class->appendAfter($columns, 'cta_type', 'id', 'ID');
            return $columns;
        }, 30, 1);


        /**
         * CTA new columns content
         */
        add_action('manage_cta_posts_custom_column', function($column, $post_id){
            switch($column){
                case 'in_api':
                    if($id = \WPME\ApiExtension\CTA::inApi($post_id)){
                        echo '<span class="genooTick active">&nbsp;</span>';
                    } else {
                        echo '<span class="genooCross">&times;</span>';
                    }
                    break;
                case 'owner':
                    $owner = \WPME\ApiExtension\CTA::getOwner($post_id);
                    $owner = is_string($owner) && !empty($owner) ? $owner : false;
                    if($owner){
                        echo $owner;
                    } else {
                        echo \WPME\Extensions\CTAs::getCurrentOwner();
                    }
                    break;
                case 'id':
                    $owner = get_post_meta($post_id, \WPME\ApiExtension\CTA::IDENTIFICAOR, true);
                    $owner = is_string($owner) && !empty($owner) ? $owner : false;
                    if($owner){
                        echo $owner;
                    } else {
                        echo $post_id;
                    }
                    break;
            }
        }, 10, 2);

        /**
         * Confirm before deleting CTA and
         * more suiting messages for locally stored CTA's
         */
        add_filter('post_row_actions', function($actions, $post){
            // check for your post type
            if('cta' == $post->post_type){
                // Add confirmation before delete
                if(isset($actions['delete'])){
                    $actions['delete'] = str_replace(
                        'a href',
                        'a onclick="javascript:return confirm(\'Are you sure you want to delete this CTA?\');" href',
                        $actions['delete']
                    );
                    // Delete locally?
                    if(!\WPME\ApiExtension\CTA::amIOwner($post->ID)){
                        $actions['delete'] = str_replace(
                            __('Delete Permanently'),
                            'Delete Locally',
                            $actions['delete']
                        );
                    }
                }
                // Trash locally?
                if(isset($actions['trash']) && !\WPME\ApiExtension\CTA::amIOwner($post->ID)){
                    $actions['trash'] = str_replace(
                        __('Trash'),
                        'Trash Locally',
                        $actions['trash']
                    );
                }
                // Remove edit link, if not owners
                if(!\WPME\ApiExtension\CTA::amIOwner($post->ID)){
                    if(isset($actions['edit'])){
                        unset($actions['edit']);
                    }
                    if(isset($actions['inline hide-if-no-js'])){
                        unset($actions['inline hide-if-no-js']);
                    }
                }
            }
            return $actions;
        }, 10, 2);

        /**
         * Before we delete, we delete from the
         * api as well (if we are the owners)
         */
        add_action('before_delete_post', function($post_id){
            // Get post type
            $post_type = get_post_type($post_id);
            // Run only for cta
            if("cta" != $post_type) return;
            // delete cta from database
            if($id = \WPME\ApiExtension\CTA::inApi($post_id)){
                // remove from api
                if(\WPME\ApiExtension\CTA::amIOwner($post_id)){
                    global $WPME_API;
                    $ctas = new \WPME\ApiExtension\CTA(new \WPME\RepositorySettingsFactory());
                    try {
                        $ctas->remove($id);
                    } catch(\Exception $e){
                        global $WPME_ADMIN;
                        if(isset($WPME_ADMIN)){
                            $WPME_ADMIN->addSavedNotice('error', $e->getMessage());
                        }
                    }
                } else {
                    // We don't remove CTA from the API,
                    // if we're not the owners of it.
                }
            }
        }, 90, 1);

        /**
         * Remove all metaboxes from CTA except ours,
         * yup, you heard that right. We don't want anything
         * interfering.
         */
        add_action('add_meta_boxes', function(){
            // Get metaboxes
            global $wp_meta_boxes;
            // Allowed metaboxes
            $allowed = array(
                'submitdiv',
                'slugdiv',
                'genoo-cta-info',
                'wpmktgengine-cta-info',
                'builder_pop-up-builder',
                'cta-info-box',
                'popup-style'
            );
            // Go through
            if(isset($wp_meta_boxes['cta']) && is_array($wp_meta_boxes['cta']) && !empty($wp_meta_boxes['cta'])){
                foreach($wp_meta_boxes['cta'] as $position => $priority){
                    if(is_array($priority) && !empty($priority)){
                        foreach($priority as $priorityKey => $metaboxes){
                            if(is_array($metaboxes) && !empty($metaboxes)){
                                foreach($metaboxes as $metaboxId => $metaboxInfo){
                                    if(!in_array($metaboxId, $allowed)){
                                        remove_meta_box($metaboxId, 'cta', $position);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }, 100, 1);

        /**
         * Cta needed actions.
         *  - Flush API ctas
         *  - Sync API Ctas
         */
        add_action('manage_posts_extra_tablenav', function($which){
            if(isset($_GET['post_type']) && $_GET['post_type'] == 'cta'){
                ?>
                <div class="alignleft actions">
                    <button class="button button-primary" type="submit" name="genooSyncCtas" id="submit" style="float: left;" value="true">Sync CTAs</button>
                    <button class="button button-primary" type="submit" name="genooRemoveCtas" id="submit" style="float: left; margin-left: 10px;" value="true">Remove API CTAs</button>
                </div>
                <?php
                if($which == 'top'){
                    do_action('wpme_doing_top_table_action');
                } else {
                    do_action('wpme_doing_bottom_table_action');
                }
            }
        });

        /**
         * CTA info metabox, letting us know, wheter it is
         * in API or not.
         */
        add_action('add_meta_boxes', function(){
            if(!isset($_GET['debug'])) return;
            add_meta_box('cta-info-box', 'Info', function($post){
                // Remove api link?
                if(isset($_GET['removeApiLink'])){
                    delete_post_meta($post->ID, \WPME\ApiExtension\CTA::IDENTIFICAOR);
                    delete_post_meta($post->ID, \WPME\ApiExtension\CTA::IDENTIFICAOR_OWNER);
                }
                // Table
                echo '<table><tbody>';
                if($id = \WPME\ApiExtension\CTA::inApi($post->ID)){
                    // Owner
                    $owner = \WPME\ApiExtension\CTA::amIOwner($post->ID);
                    $owner =
                        $owner ?
                            "<span style=\"color: green; font-weight: bold;\">This site is the owner of this CTA.</span>"
                            :
                            "<span style=\"color: red; font-weight: bold;\">". \WPME\ApiExtension\CTA::getOwner($post->ID) ."</span>";
                    // Is in API
                    echo "<tr><td>Synced:</td><td><span style=\"color: green; font-weight: bold;\">Synced in APi</span></td></tr>";
                    echo "<tr><td>Owner:</td><td><span style=\"color: green; font-weight: bold;\">$owner</span></td></tr>";
                    echo "<tr><td>ID:</td><td><span style=\"color: green; font-weight: bold;\">". $id ."</span></td></tr>";
                    $currentUrl = \WPMKTENGINE\Wordpress\Utils::getRealUrl();
                    $currentUrl = add_query_arg('removeApiLink', '1', $currentUrl);
                    echo "<tr><td>Remove:</td><td><span style=\"color: green; font-weight: bold;\"><a href='$currentUrl'>Remove API Link</a></span></td></tr>";
                } else {
                    echo "<tr><td>Synced:</td><td><span style=\"color: red; font-weight: bold;\">Not synced in APi</span></td></tr>";
                    echo "<tr><td colspan='2'><small>This copy of your CTA is only local copy not saved in your API and accessible throughout all your sites.</small></td></tr>";
                }
                echo '</tbody></table>';
            }, 'cta', 'side');
        }, 10);

        /**
         * Block editing CTA's that are not current owners.
         * This one is tricky one, but we've manged to pull that off.
         */
        add_filter('user_has_cap', function($allcaps, $caps, $args, $that){
            // Let's see if we're ediing
            global $current_screen;
            global $post;
            if(is_admin()
                && isset($current_screen)
                && isset($post)
                && $current_screen instanceof \WP_Screen
                && $post instanceof \WP_Post
                && $current_screen->post_type == 'cta'
                && !\WPME\ApiExtension\CTA::amIOwner($post->ID)
                && did_action('wpme_doing_top_table_action')     // only for this table
                && !did_action('wpme_doing_bottom_table_action') // only for cta table
            ){
                $allcaps['publish_posts'] = false;
                $allcaps['edit_posts'] = false;
                $allcaps['edit_others_posts'] = false;
                $allcaps['edit_published_posts'] = false;
                $allcaps['edit_pages'] = false;
            }
            return $allcaps;
        }, 30, 4);

        /**
         * Add widgets to Tools Page
         * that imports CTA's, yay!
         */
        add_filter('wpmktengine_tools_widgets', function($page){
            $pageImport = '<p>Note: This will import any CTAs in your API that are not synced with your WordPress installation.<p>';
            $pageImport .= '<p><a onclick="Genoo.startCTAsImport(event)" class="button button-primary">Import CTA\'s</a><p>';
            $page->widgets = array_merge(array((object)array('title' => 'CTA Import', 'guts' => $pageImport)), $page->widgets);
            return $page;
        }, 10, 1);

        /**
         * Sync CTA's and flush CTA's actions
         */
        add_action('wp', function(){
            if(is_admin() && (!defined( 'DOING_AJAX' ) || ! DOING_AJAX )){
                if(isset($_GET['post_type']) && $_GET['post_type'] == 'cta'){
                    // Sync CTAs
                    if(isset($_REQUEST['genooSyncCtas']) && $_REQUEST['genooSyncCtas'] == 'true'){
                        // Admin
                        global $WPME_ADMIN;
                        // API
                        $repositaryCTAs = new \WPMKTENGINE\RepositoryCTA();
                        $repositaryCTAs->flushApis();
                        $result = $repositaryCTAs->runImportDry();
                        if($result){
                            $WPME_ADMIN->addSavedNotice('updated', __('All CTA\'s imported.', 'wpmktengine'));
                        } elseif($result === FALSE) {
                            $WPME_ADMIN->addSavedNotice('error', __('There was an error importing CTA\'s.', 'wpmktengine'));
                            //TODO: Notify
                        } elseif($result === NULL){
                            $WPME_ADMIN->addSavedNotice('updated', __('No CTA\'s to be imported.', 'wpmktengine'));
                        }
                        wp_redirect(admin_url('edit.php?post_type=cta'));
                        exit;
                    }
                    // Remove CTA's from api
                    if(isset($_REQUEST['genooRemoveCtas']) && $_REQUEST['genooRemoveCtas'] == 'true') {
                        // Admin
                        global $WPME_ADMIN;
                        // API
                        $repositaryCTAs = new \WPMKTENGINE\RepositoryCTA();
                        $repositaryCTAs->flushApis();
                        $WPME_ADMIN->addSavedNotice('updated', __('All API CTA\'s removed.', 'wpmktengine'));
                        wp_redirect(admin_url('edit.php?post_type=cta'));
                        exit;
                    }
                }
            }
        });


        /**
         * AJAX: Start products import
         */
        add_action('wp_ajax_wpme_import_cta_count', function(){
            $count = \WPME\Extensions\CTAs::getCTAsNotInWordPress();
            if(count($count) > 0){
                genoo_wpme_on_return(array('found' => count($count)));
            }
            genoo_wpme_on_return(array('error' => 'No CTAs found to be imported.'));
        }, 10);

        /**
         * AJAX: Import of the products
         */
        add_action('wp_ajax_wpme_import_ctas', function(){
            // Things
            global $WPME_API;
            $offest = $_REQUEST['offest'];
            $per = $_REQUEST['per'] === NULL ? 0 : $_REQUEST['per'];
            // Api?
            if(isset($WPME_API)){
                // Get products
                $ctas = \WPME\Extensions\CTAs::getCTAsNotInWordPress();
                $ctasSlice = array_slice($ctas, $offest, $per);
                if(!empty($ctasSlice)){
                    $ctasApi = new \WPME\ApiExtension\CTA($WPME_API->settingsRepo);
                    foreach($ctasSlice as $cta){
                        // Go through each CTA and import
                        $newId = \WPME\Extensions\CTAs::importCTA($ctasApi->get($cta->id));
                        $messages[] = 'CTA imported: ' . $cta->name . ' under ID: ' . $newId;
                    }
                } else {
                    $messages[] = 'Nothing to import.';
                }
                genoo_wpme_on_return(array('messages' => $messages));
            } else {
                genoo_wpme_on_return(array('messages' => 'Error: API not found.'));
            }
        }, 10);
    }

    /**
     * Convert for API
     *
     * @param \WP_Post $post
     * @return array
     */
    public static function convertForApi(\WP_Post $post)
    {
        // Prepare array
        $r = array();
        $r['name'] = $post->post_title;
        $r['owner'] = self::getCurrentOwner();
        $r['data'] = array();
        // Convert post meta
        $post_meta = get_post_meta($post->ID);
        self::cleanupPostMeta($post_meta);
        $r['data'] = $post_meta;
        $r['data']['__post_id'] = $post->ID;
        $r['data']['__name'] = $post->post_title;
        return $r;
    }

    /**
     * Get current owner
     *
     * @return string
     */
    public static function getCurrentOwner()
    {
        return genoo_wpme_get_domain($_SERVER['SERVER_NAME']);
    }

    /**
     * Cleans up post meta
     *
     * @param $meta
     */
    public static function cleanupPostMeta(&$meta)
    {
        // Clear up meta
        if(is_array($meta) && !empty($meta)){
            foreach($meta as $id => $value){
                if(substr($id, 0, strlen('_')) !== '_'){
                    // don't start with _
                    $value = $value[0];
                    $isSerialized = @unserialize($value);
                    if($isSerialized !== false){
                        // serialized data append
                        $meta[$id] = $isSerialized;
                    } else {
                        $meta[$id] = $value;
                    }
                } else {
                    unset($meta[$id]);
                }
            }
        }
        // Add images
        self::cleanUpPostMetaAddImages($meta);
    }

    /**
     * Cleans up post meta and adds images
     *
     * @param $meta
     */
    public static function cleanUpPostMetaAddImages(&$meta)
    {
        if(isset($meta['button_image']) && is_numeric($meta['button_image'])){
            $id = $meta['button_image'];
            $meta['button_image'] = array();
            $meta['button_image']['__src'] = wp_get_attachment_url($id);
            $meta['button_image']['__id'] = $id;
        }
        if(isset($meta['button_hover_image']) && is_numeric($meta['button_hover_image'])){
            $id = $meta['button_hover_image'];
            $meta['button_hover_image'] = array();
            $meta['button_hover_image']['__src'] = wp_get_attachment_url($id);
            $meta['button_hover_image']['__id'] = $id;
        }
        if(isset($meta['formpop']['image']) && is_numeric($meta['formpop']['image'])){
            $id = $meta['formpop']['image'];
            $meta['formpop']['image'] = array();
            $meta['formpop']['image']['__src'] = wp_get_attachment_url($id);
            $meta['formpop']['image']['__id'] = $id;
        }
    }

    /**
     * Revetert images
     *
     * @param $meta
     * @param $owner
     */
    public static function cleanUpPostMetaAddImagesRevert(&$meta, $owner)
    {
        $isOwner = \WPME\ApiExtension\CTA::isOwner($owner);
        if(isset($meta['button_image']) && is_array($meta['button_image'])){
            $id = $meta['button_image']['__id'];
            $src = $meta['button_image']['__src'];
            $meta['button_image'] = '';
            if($isOwner){
                $meta['button_image'] = $id;
            } else {
                $meta['button_image'] = $src;
            }
        }
        if(isset($meta['button_hover_image']) && is_array($meta['button_hover_image'])){
            $id = $meta['button_hover_image']['__id'];
            $src = $meta['button_hover_image']['__src'];
            $meta['button_hover_image'] = '';
            if($isOwner){
                $meta['button_hover_image'] = $id;
            } else {
                $meta['button_hover_image'] = $src;
            }
        }
        if(isset($meta['formpop']['image']) && is_array($meta['formpop']['image'])){
            $id = $meta['formpop']['image']['__id'];
            $src = $meta['formpop']['image']['__src'];
            $meta['formpop']['image'] = '';
            if($isOwner){
                $meta['formpop']['image'] = $id;
            } else {
                $meta['formpop']['image'] = $src;
            }

        }
    }


    /**
     * Imports CTA and returns post ID
     *
     * @param $cta
     * @return mixed
     */
    public static function importCTA($cta)
    {
        // Prep post
        $owner = $cta->owner;
        $post_title = $cta->name;
        $id = $cta->id;
        // Meta data
        $metas = (array)$cta->data;
        $metas[\WPME\ApiExtension\CTA::IDENTIFICAOR_OWNER] = $owner;
        $metas[\WPME\ApiExtension\CTA::IDENTIFICAOR] = $id;
        // Convert metas to array
        // Revert image ids to ids or source URLS
        if(isset($metas['formpop']) && is_object($metas['formpop'])){
            $metas['formpop'] = (array)$metas['formpop'];
            if(isset($metas['formpop']['countdown']) && is_object($metas['formpop']['countdown'])){
                $metas['formpop']['countdown'] = (array)$metas['formpop']['countdown'];
            }
        }
        // Clean up
        self::cleanUpPostMetaAddImagesRevert($metas, $owner);
        // Seari
        // Insert post & post_meta
        $post_id = wp_insert_post(
            array(
                'post_title' => $post_title,
                'post_type' => 'cta',
                'post_status' => 'publish',
                'meta_input' => $metas
            )
        );
        // Add formpop
        if(is_numeric($post_id) && isset($metas['formpop'])){
            update_post_meta($post_id, 'formpop', $metas['formpop']);
        }
        return $post_id;
    }


    public static function onActivate()
    {
        try {
            // Get libs
            $settings = new \WPME\RepositorySettingsFactory();
            $ctas = new \WPME\ApiExtension\CTA($settings);
            // Prep arryas
            $ctasNotInApi = array();
            $ctasNotInWordPress = array();
            // Get data
            $ctasInApi = $ctas->getAll();
            $ctasInWordPress = $ctas->getAllWordPress();
            // Prepare arrays to crawl through later
            $ctasInApiArrayIds = array();
            $ctasInApiArrayTitles = array();
            // Crawl through
            foreach($ctasInApi as $cta){
                $ctasInApiArrayIds[$cta->id] = $cta;
                $ctasInApiArrayTitles[$cta->name] = $cta;
            }
            $ctasInWordPressArrayIds = array();
            $ctasInWordPressArrayTitles = array();
            foreach($ctasInWordPress as $cta){
                if($cta->inApi){
                    $ctasInWordPressArrayIds[$cta->{\WPME\ApiExtension\CTA::IDENTIFICAOR}] = $cta;
                    $ctasInWordPressArrayTitles[$cta->post_title] = $cta;
                } else {
                    $ctasNotInApi[$cta->ID] = $cta;
                }
            }
            // Go through each and found what's missing in API, and what's missing in WordPress
            foreach($ctasInApi as $ctaId => $ctaInApi)
            {
                if(!array_key_exists($ctaId, $ctasInWordPressArrayIds)){
                    $ctasNotInWordPress[$ctaId] = $ctaInApi;
                }
            }
            // Do some magic
            if(count($ctasNotInWordPress) > 0){
                // We have found CTA's not in WordPress
                $settings->addSavedNotice(
                        'updated',
                        __('We have found CTA\'s stored in your API that are not imported in your WordPress installation.&nbsp;'
                            .
                            '<a class="button button-primary" href="">Run the import.</a>',
                        'wpmktengine'
                    )
                );
            }
            if(count($ctasNotInApi) > 0){
                // We have found CTA's not in Api
                $settings->addSavedNotice(
                    'updated',
                    __('We have found CTA\'s stored in your WordPress that are not imported in your API.&nbsp;'
                        .
                        '<a class="button button-primary" href="">Run the import.</a>',
                        'wpmktengine'
                    )
                );
            }
            // Compare
        } catch (\Exception $e){

        }
    }


    /**
     * CTA's not in WordPress
     *
     * @param null $settings
     * @param null $ctas
     * @return mixed
     */
    public static function getCTAsNotInWordPress($settings = null, $ctas = null)
    {
        return self::getCTAsNotIn('wordpress', $settings, $ctas);
    }

    /**
     * CTA's not in API
     *
     * @param null $settings
     * @param null $ctas
     * @return mixed
     */
    public static function getCTAsNotInApi($settings = null, $ctas = null)
    {
        return self::getCTAsNotIn('api', $settings, $ctas);
    }

    /**
     * CTA's not in?
     *
     * @param string $where
     * @param null $settings
     * @param null $ctas
     * @return mixed
     */
    public static function getCTAsNotIn($where = 'wordpress', $settings = null, $ctas = null)
    {
        if($settings == null){
            $settings = new \WPME\RepositorySettingsFactory();
        }
        if($ctas == null){
            $ctas = new \WPME\ApiExtension\CTA($settings);
        }
        // Get data
        $ctasInApi = $ctas->getAll();
        $ctasInWordPress = $ctas->getAllWordPress();
        // Prepare arrays to crawl through later
        $ctasInApiArrayIds = array();
        $ctasInApiArrayTitles = array();
        $ctasNotInWordPress = array();
        // Crawl through
        foreach($ctasInApi as $cta){
            $ctasInApiArrayIds[$cta->id] = $cta;
            $ctasInApiArrayTitles[$cta->name] = $cta;
        }
        $ctasInWordPressArrayIds = array();
        $ctasInWordPressArrayTitles = array();
        foreach($ctasInWordPress as $cta2){
            if($cta2->inApi){
                $ctasInWordPressArrayIds[$cta2->inApi] = $cta2;
                $ctasInWordPressArrayTitles[$cta2->post_title] = $cta2;
            } else {
                $ctasNotInApi[$cta2->ID] = $cta2;
            }
        }
        // Go through each and found what's missing in API, and what's missing in WordPress
        foreach($ctasInApiArrayIds as $ctaId => $ctaInApi)
        {
            if(!array_key_exists($ctaId, $ctasInWordPressArrayIds)){
                $ctasNotInWordPress[$ctaId] = $ctaInApi;
            }
        }
        if($where == 'wordpress'){
            return $ctasNotInWordPress;
        }
        return $ctasNotInApi;
    }


    /**
     * Updat post meta - with arrays
     *
     * @param $post_id
     * @param $metas
     */
    public static function update_post_metas($post_id, $metas)
    {
        if($metas && !empty($metas)){
            foreach($metas as $meta_key => $meta_value){
                update_post_meta($post_id, $meta_key, $meta_value);
            }
        }
    }
}
