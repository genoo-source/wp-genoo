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

namespace WPME\Extensions\Clever;

use WPME\Nag\Nag;

/**
 * Class Plugins
 *
 * @package WPME\Extensions\Clever
 */
class Plugins
{
    /** @var array */
    var $notifications = array();

    /** @var array|mixed */
    var $supportedPlugins = array();

    /** @var array  */
    var $installedPlugins = array();

    /** @var array */
    var $remotePlugins = array(
      'wp-gravity-forms-extension-master',
      'wp-genoo-elementor-addon-master',
      'wp-genoo-auto-segmentation-master'
    );

    /**
     * Plugins constructor.
     */
    public function __construct()
    {
        $this->supportedPlugins = $this->getSupportedPlugins();
        if (!function_exists( 'get_plugins')){
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $this->installedPlugins = \get_plugins();
        $this->nag = new Nag();
    }

    /**
     * Register
     */
    public function register()
    {
        add_action('plugins_loaded', array($this, 'pluginsLoaded'), 345);
        add_action('admin_notices', array($this, 'pluginNotices'), 999);
        add_action('admin_enqueue_scripts', function(){
            wp_enqueue_script('updates');
        }, 999);
        add_action('admin_head', function(){
            ?>
    <style>
    .notice.custom-notice-bar {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    height: 50px;
    padding: 0;
border-left-width: 2px;
}
.notice.custom-notice-bar .notice-right {
    width: 100px;
    margin-right: 5px;
    height: 50px;
    display: flex;
    align-items: center;
    border-right: solid 3px #14da6a;
    padding: 0 15px;
    background-color: #eee;
}
</style>
            <script type="text/javascript">
                jQuery(function() {
                    // On install
                    jQuery(document).on('wp-plugin-install-success', function(event, data){
                        
                            // Get container
                            var url = ajaxurl.replace(/[^\/]*$/, '');
                            var append = jQuery('.wpme-plugin-notice.plugin-card-' + data.slug);
                            // Append activate message
                                append.find('.notice-left').html(
                                    '<p>Plugin installed successfully, you can proceed to ' +
                                    '<a href="'+ url +'plugins.php">activate it here.</a>' +
                                    '</p>'
                                );
                                append.find('a.install-now')
                                    .attr('href', '')
                                    .removeClass('thickbox')
                                    .removeClass('open-plugin-details-modal');
                        return;
                    });
                });
            </script>
            <?php
        }, 999);
        // Activated plugin
        add_action('activated_plugin', array($this, 'reactivate'), 10, 2);
        add_filter('plugins_api_result', array($this, 'remotePluginInfo'), 10, 3);
        add_filter('upgrader_source_selection', array( $this, 'rename_github_zip' ), 1, 3 );

    }

    /**
     * This gets called after clicking "Check It Out" button in the plugins page,
     * and generates plugin info for plugins, that are hosted remotely.
     */
    public function remotePluginInfo($res, $action, $args){
      // If it's a plugin, that is not our own, and remote
      // one hosted in a repo, return regular response.
      if(!in_array($args->slug, $this->remotePlugins)){
        return $res;
      }
      // Find plugin data
      $plugins = $this->getSupportedPlugins();
      $plugin = array_filter($plugins, function($name) use($args){
        return $name['slug'] == $args->slug;
      });
      $plugin = current($plugin);
      // If no plugin found, return original response
      if (!array_key_exists('slug', $plugin)) {
          return $res;
      }
      // Populate modal window
      $resClone = new \stdClass();
      $resClone->name = $plugin['name'];
      $resClone->version = "latest";
      $resClone->rating = 100;
      $resClone->num_ratings = rand(123, 132);
      $resClone->homepage = 'https://genoo.com/';
      $resClone->author_profile = 'https://genoo.com/';
      $resClone->requires = $args->wp_version;
      $resClone->slug = $args->slug;
      $resClone->sections = ["description" => $plugin['desc']];
      $resClone->download_link = $plugin['url'];
      $resClone->banners = ['low' => 'https://ps.w.org/genoo/assets/banner-772x250.jpg?rev=874378'];
      return $resClone;
    }

    /**
     * Reactivate?
     *
     * @param $plugin
     * @param $reactivate
     */
    public function reactivate($plugin, $reactivate)
    {
        $plugins = $this->getSupportedPlugins();
        if(array_key_exists($plugin, $plugins)){
            $key = crc32($plugin);
            $keyUser = wp_get_current_user();
            \delete_user_meta($keyUser->ID, 'wp_hide-' . $key);
        }
    }

    /**
     * Check plugins
     */
    public function pluginsLoaded()
    {
        // Search for plugins
        $plugins = $this->getActivePlugins();
        if($plugins && !empty($plugins)){
            foreach($plugins as $plugin){
                // Check if we support plugin
                if($this->isSupportedPlugin($plugin) && !$this->isExtensionInstalled($plugin)){
                    $this->addNotificationFor($plugin);
                }
            }
        }
    }

    /**
     * Render Admin notices
     */
    public function pluginNotices()
    {
        // Add modal
        add_thickbox();
        // Render notices
        foreach($this->notifications as $notification){
            // Plugin definition
            $pluginDefinition = $this->supportedPlugins[$notification];
            // Get plugin message
            // Render message
            $this->nag->renderNotice(
                $this->generateInstallMessage($pluginDefinition),
                crc32($notification),
                true
            );
        }
    }
    
    public function rename_github_zip( $source, $remote_source, $thiz )
    {
    $path_parts = pathinfo($source);

    if(strpos($path_parts['filename'],'wp-genoo-auto-segmentation-master') !== false){

          $repo_slug = 'wp-genoo-auto-segmentation-master';
    $newsource = trailingslashit($path_parts['dirname']). trailingslashit($repo_slug);
    rename($source, $newsource);
    
    return $newsource;
     }
     else
     {
        return $source; 
     }
}

    /**
     * Generate message
     *
     * @param $pluginDefinition
     * @return string
     */
    public function generateInstallMessage($pluginDefinition)
    {
        $pluginOwner = apply_filters('genoo_wpme_clever_plugins_owner', '<strong>WPMKTGENGINE: </strong>');
        echo "
            <div class='wpme-plugin-notice plugin-card-{$pluginDefinition['slug']} notice custom-notice-bar'>
                <div class='notice-right'>
                   <a 
                    data-slug='{$pluginDefinition['slug']}'
                    data-title='{$pluginDefinition['name']}' 
                    href='{$this->getPluginInfoUrl($pluginDefinition['slug'])}' 
                    class='install-now button button-primary thickbox open-plugin-details-modal'>Check it out!</a>
                </div>
                <div class='notice-left'><p>{$pluginOwner}{$pluginDefinition['message']}</p></div>
                <div class='clear cls cf'></div>
            </div>
        ";
    }

    /**
     * @param $plugin
     * @return string
     */
    public function getPluginInfoUrl($plugin)
    {
        return admin_url("/plugin-install.php?tab=plugin-information&plugin=$plugin&TB_iframe=true&width=600&height=550");
    }

    /**
     * Get's plugin link
     *
     * @param $plugin
     * @return string
     */
    public function getPluginInfoLink($plugin)
    {
        $link = $this->getPluginInfoUrl($plugin);
        return "<a href=\"$link\" class=\"thickbox open-plugin-details-modal\" aria-label=\"More information about $plugin\" data-title=\"$plugin\">More Details</a>";
    }

    /***
     * @param $plugin
     * @return bool
     */
    public function isSupportedPlugin($plugin)
    {
        return array_key_exists($plugin, $this->supportedPlugins);
    }

    /**
     * Is extension installed?
     *
     * @param $plugin
     * @return bool
     */
    public function isExtensionInstalled($plugin)
    {
        $supportedPlugin = $this->supportedPlugins[$plugin];
        return array_key_exists($supportedPlugin['file'], $this->installedPlugins);
    }

    /**
     * @param $plugin
     */
    public function addNotificationFor($plugin)
    {
        $this->notifications[] = $plugin;
    }

    /**
     * @return mixed
     */
    public function getSupportedPlugins()
    {
        // Array
        $plugins = array();
        // Add supported plugins
        $plugins['bbpress/bbpress.php'] = array(
            'connection' => 'https://wordpress.org/plugins/wpmktgengine-extension-bbpress/',
            'slug' => 'wpmktgengine-extension-bbpress',
            'message' => 'Hey there, I see that you are using bbPress. We have an integration with bbPress and can get that working by installing our plugin extension.',
            'name' => '',
            'file' => 'wpmktgengine-extension-bbpress/wpmktgengine-bbpress.php'
        );
        $plugins['buddypress/bp-loader.php'] = array(
            'connection' => 'https://wordpress.org/plugins/wpmktgengine-extension-buddypress/',
            'slug' => 'wpmktgengine-extension-buddypress',
            'message' => 'Hey there, I see that you are using BuddyPress. We have an integration with BuddyPress and can get that working by installing our plugin extension.',
            'name' => '',
            'file' => 'wpmktgengine-extension-buddypress/wpmktgengine-buddypress.php'
        );
        $plugins['lifterlms/lifterlms.php'] = array(
            'connection' => 'https://wordpress.org/plugins/lifterlms-wpmktgengine-extension/',
            'slug' => 'lifterlms-wpmktgengine-extension',
            'message' => 'Hey there, I see that you are using LifterLMS. We have an integration with LifterLMS and can get that working by installing our plugin extension.',
            'name' => '',
            'file' => 'lifterlms-wpmktgengine-extension/wpmktgengine-lifter-lms.php'
        );
        $plugins['woocommerce/woocommerce.php'] = array(
            'connection' => 'https://wordpress.org/plugins/wpmktgengine-extension-woocommerce/',
            'slug' => 'wpmktgengine-extension-woocommerce',
            'message' => 'Hey there, I see that you are using WooCommerce. We have an integration with WooCommerce and can get that working by installing our plugin extension.',
            'name' => '',
            'file' => 'wpmktgengine-extension-woocommerce/wpmktgengine-woocommerce.php'
        );
        $plugins['gravityforms/gravityforms.php'] = array(
            'connection' => '',
            'slug' => 'wp-gravity-forms-extension-master',
            'message' => 'Hey there, I see that you are using Gravityforms. We have an integration with Gravityforms and can get that working by installing our plugin extension.',
            'desc' => 'Integrate your Gravity Forms directly into Genoo/WPMktgEngine - and place into Lead Type, specify which email to sent upon submit, or register the lead into a webinar automatically.',
            'name' => '',
            'file' => 'wp-gravity-forms-extension-master/wp-starter.php',
            'url'  => 'https://github.com/genoo-source/wp-gravity-forms-extension/archive/master.zip',
            'name' => 'Gravity Forms WPMktgEngine Extension Plugin'
        );
        $plugins['elementor/elementor.php'] = array(
            'connection' => '',
            'slug' => 'wp-genoo-elementor-addon-master',
            'message' => 'Hey there, I see that you are using Elementor. We have an integration with Elementor and can get that working by installing our plugin extension.',
            'desc' => 'To have CTAs, Genoo Forms, and Surveys easily appear on Elementor pages, install this plugin.  Or if you want to integrate Genoo/WPMktgEngine into Elementor Forms, this adds those customizations to Elementor.',
            'name' => '',
            'file' => 'wp-genoo-elementor-addon-master/Genno_Elementor_Extension.php',
            'url'  => 'https://github.com/genoo-source/wp-genoo-elementor-addon/archive/master.zip',
            'name' => 'Genoo Elementor Extension Plugin'
        );
        $plugins['genoo/Genoo.php'] = array(
            'connection' => '',
            'slug' => 'wp-genoo-auto-segmentation-master',
            'message' => 'Since you are using Genoo/WPMktgEngine plugin, we have an extension that will automatically segment your leads based upon their views of your blog posts.  You set a lead type by category and everything else is taken care of.',
            'desc' => 'Easily segment your leads by their behavior.  This plugin allows you to identify Lead Types associated with Blog Categories, so as leads visit your blog pages, automatically segment them.',
            'name' => '',
            'file' => 'wp-genoo-auto-segmentation-master/wp-genoo-auto-segmentation.php',
            'url'  => 'https://genoolabs.com/plugins/wp-genoo-auto-segmentation/wp-genoo-auto-segmentation-master.zip',
            'name' => 'Auto Segment Your Leads - Genoo'
        );
        // Return
        return $plugins;
    }

    /**
     * @return mixed
     */
    public function getPlugins(){ return get_plugins(); }

    /**
     * @return mixed
     */
    public function getActivePlugins(){ return get_option('active_plugins', array()); }

    /**
     * Install plugin
     * http://stackoverflow.com/questions/10353859/is-it-possible-to-programmatically-install-plugins-from-wordpress-theme
     */
    public function installPlugin($plugin)
    {
        $api = plugins_api('plugin_information', array(
            'slug' => $plugin,
            'fields' => array(
                'short_description' => false,
                'sections' => false,
                'requires' => false,
                'rating' => false,
                'ratings' => false,
                'downloaded' => false,
                'last_updated' => false,
                'added' => false,
                'tags' => false,
                'compatibility' => false,
                'homepage' => false,
                'donate_link' => false,
            ),
        ));
        include_once(ABSPATH . 'wp-admin/includes/file.php');
        include_once(ABSPATH . 'wp-admin/includes/misc.php');
        include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
        $upgrader = new \Plugin_Upgrader(
            new \Plugin_Installer_Skin(
                compact('title', 'url', 'nonce', 'plugin', 'api')
            )
        );
        $upgrader->install($api->download_link);
    }
    


    /**
     * @param $notification
     */
    public function addNotification($notification)
    {
        $this->notifications[] = $notification;
    }
}
