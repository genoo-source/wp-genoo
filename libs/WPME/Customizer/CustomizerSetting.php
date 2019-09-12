<?php
/**
 * This file is part of the WPMKTGENGINE plugin.
 *
 * Copyright 2016 Genoo, LLC. All rights reserved worldwide.  (web: http://www.wpmktgengine.com/)
 * GPL Version 2 Licensing:
 *  PHP code is licensed under the GNU General public static License Ver. 2 (GPL)
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

namespace WPME\Customizer;

/**
 * Fix missing class
 */
if(!class_exists('WP_Customize_Setting')){
    require_once( ABSPATH . WPINC . '/class-wp-customize-setting.php' );
}

/**
 * Class CustomizerSetting
 * - post_meta storage for customizer
 *
 * @package WPME\Customizer
 */
class CustomizerSetting extends \WP_Customize_Setting
{
    /** @var */
    public $post_id;

    /** @var */
    public $theme_id;

    /**
     * Supported types
     *
     * @var array
     */
    public $types = array(
        'post_meta',
        'post_object',
        'post_meta_single'
    );

    /**
     * CustomizerSetting constructor.
     *
     * @param \WP_Customize_Manager $manager
     * @param string $id
     * @param array $args
     * @throws \InvalidArgumentException
     */
    public function __construct(\WP_Customize_Manager $manager, $id, array $args)
    {
        // Construct parent
        parent::__construct($manager, $id, $args);
        // Set post_id
        $this->setPostId();
        $this->setThemeId();
        // Agregate multidimensional array
        $this->aggregate_multidimensional();
    }

    /**
     * Set Post ID
     *
     * @throws \InvalidArgumentException
     */
    public function setPostId()
    {
        // Only if exists, set post_id
        if(isset($_GET['gn-cust']) && is_numeric($_GET['gn-cust'])){
            $this->post_id = (int)$_GET['gn-cust'];
        } else {
            throw new \InvalidArgumentException('No CTA id set for Customizer Setting to be loaded');
        }
    }

    /**
     * Set Theme ID
     *
     * @throws \InvalidArgumentException
     */
    public function setThemeId()
    {
        // Only if exists, set post_id
        if(isset($_GET['gn-thm'])){
            $this->theme_id = sanitize_text_field($_GET['gn-thm']);
        } else {
            throw new \InvalidArgumentException('No Theme id set for Customizer Setting to be loaded');
        }
    }

    /**
     * Get unique key
     *
     * @return string
     */
    protected function get_unique_key()
    {
        return 'gnmdl_' . $this->id_data['base'];
    }

    /**
     * Get value from post_meta
     *
     * @param null $default
     * @return mixed|null|string|void
     */
    protected function get_root_value($default = null)
    {
        $id_base = $this->get_unique_key();
        if ('post_meta' === $this->type){
            return get_post_meta($this->post_id, $id_base, true);
        }
        if ('post_meta_single' === $this->type){
            $id_data = $this->id_data();
            return get_post_meta($this->post_id, $id_data['keys'][0], true);
        }
        if ('post_object' === $this->type){
            $post = get_post($this->post_id);
            $id_data = $this->id_data();
            $id_data_key = $id_data['keys'][0];
            return $post->{$id_data_key};
        }
        return parent::get_root_value($default);
    }

    /**
     * Set the root value for a setting, especially for multidimensional ones.
     *
     * @since 4.4.0
     * @access protected
     *
     * @param mixed $value Value to set as root of multidimensional setting.
     * @return bool Whether the multidimensional root was updated successfully.
     */
    protected function set_root_value($value)
    {
        $id_base = $this->get_unique_key();
        if ('post_meta' === $this->type){
            // Merge with original value
            $original_value = get_post_meta($this->post_id, $id_base, true);
            $original_value = is_array($original_value) ? $original_value : (array)$original_value;
            return update_post_meta(
                $this->post_id,
                $id_base,
                array_merge($original_value, $value)
            );
        }
        if ('post_meta_single' === $this->type){
            $id_data = $this->id_data();
            return update_post_meta($this->post_id, $id_data['keys'][0], $value);
        }
        if ('post_object' === $this->type){
            $id_data = $this->id_data();
            $id_data_key = $id_data['keys'][0];
            $postArray = array(
                'ID'         => $this->post_id,
                $id_data_key => $value
            );
            return wp_update_post($postArray);
        }
        return parent::set_root_value($value);
    }

    /**
     * Save the value of the setting, using the related API.
     *
     * @since 3.4.0
     *
     * @param mixed $value The value to update.
     * @return bool The result of saving the value.
     */
    protected function update($value)
    {
        $id_base = $this->get_unique_key();
        if ('post_meta' === $this->type){
            if ( ! $this->is_multidimensional_aggregated ) {
                return $this->set_root_value( $value );
            } else {
                $root = self::$aggregated_multidimensionals[ $this->type ][ $id_base ]['root_value'];
                $root = $this->multidimensional_replace( $root, $this->id_data['keys'], $value );
                self::$aggregated_multidimensionals[ $this->type ][ $id_base ]['root_value'] = $root;
                return $this->set_root_value( $root );
            }
        }
        if ('post_meta_single' === $this->type){
            return $this->set_root_value( $value );
        }
        if ('post_object' === $this->type){
            return $this->set_root_value( $value );
        }
        // Return original
        return parent::update($value);
    }
}