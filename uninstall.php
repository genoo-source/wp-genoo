<?php

/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 */

/**
 * Wordpress core & uninstall check
 */

if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')){ exit(); }

/**
 * Genoo Uninstall function
 */

function genooUninstall()
{
    global $wpdb;

    /**
     * 1. Delete genoo options in worpdress database,
     *    clean after us.
     */

    delete_option('genooApiSettings');
    delete_option('genooApiGeneral');
    delete_option('genooThemeSettings');
    delete_option('genooFormMessages');
    delete_option('genooDebug');
    delete_option('genooDebugCheck');


    /**
     * 2. Delete saved Widget settings
     */

    delete_option('widget_genoocta');
    delete_option('widget_genooform');
    delete_option('widget_genoolumen');


    /**
     * 3. Go through users, and delete user nag meta
     */

    $users = get_users(array('who' => array('administrator')));
    if(!empty($users)){
        foreach($users as $user){
            delete_user_option($user->ID, 'hideGenooNag');
            delete_user_option($user->ID, 'hideGenooApi');
            delete_user_option($user->ID, 'hideGenooSidebar');
        }
    }

    /**
     * 4. Delete widgets backup
     */

    delete_option('genoo_sidebars_widgets');
}


/**
 * Go Go Go!
 */

if (function_exists('is_multisite') && is_multisite()){
    global $wpdb;
    $blogs = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
    foreach ($blogs as $blog){
        switch_to_blog($blog);
        genooUninstall();
        restore_current_blog();
    }
} else {
    genooUninstall();
}


