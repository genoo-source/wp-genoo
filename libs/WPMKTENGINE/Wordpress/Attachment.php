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

use WPMKTENGINE\Utils\CSS;


class Attachment
{
    /**
     * Genreate CSS
     *
     * @param $img
     * @param $imgHover
     * @param $id
     * @param string $size
     * @param bool $ratio
     * @return CSS
     */

    public static function generateCss($img, $imgHover, $id, $size = 'full', $ratio = TRUE)
    {
        $r = '';
        $size = !is_string($size) ? 'full' : $size;
        $experimental = TRUE;
        if(!is_null($img)){
            if(is_object($img)){
                $img = $img->__src;
            }
            if(is_numeric($img)){
                $src = wp_get_attachment_image_src($img, $size);
            } else {
                $image = self::getRemoteImageSize($img);
                $src = array(
                    0 => $img,
                    1 => $image['width'],
                    2 => $image['height'],
                );
            }
        }
        if(!is_null($imgHover)){
            if(is_object($imgHover)){
                $imgHover = $imgHover->__src;
            }
            if(is_numeric($imgHover)){
                $srcHover = wp_get_attachment_image_src($imgHover, $size);
            } else {
                $image = self::getRemoteImageSize($imgHover);
                $srcHover = array(
                    0 => $imgHover,
                    1 => $image['width'],
                    2 => $image['height'],
                );
            }

        }
        $css = new CSS();
        // Image preload for hover
        if(!is_null($imgHover)){
            $css->addRule('body #' . $id.  ' input:after')
                ->add('content', 'url("'. $srcHover[0] .'")')
                ->add('display', 'none !important');
        }
        // Image
        if(!is_null($img)){
            $css->addRule('body #' . $id.  '')
                ->add('display', 'inline-block')
                ->add('width', 'auto')
                ->add('height', 'auto')
                ->add('width', $src[1] . 'px')
                ->add('height', $src[2] . 'px')
                ->add('min-height', $src[2] . 'px')
                ->add('max-width', '100%');
        }
        // Image ratio protection (this is experimental)
        if($experimental){
            // Width / height only if both are the same size
            //img-height / img-width * container-width * 10000
            if(!is_null($img)){
                $ratio = (($src[2] / ($src[1] * 100))) * 10000;
                $css->addRule('body #' . $id.  ' input')
                    ->add('background', 'url(\'' . $src[0] . '\') top left no-repeat transparent !important')
                    ->add('background-size', 'contain !important')
                    ->add('background-repeat', 'no-repeat !important')
                    ->add('width', '100% !important')
                    ->add('height', '0 !important')
                    ->add('padding-top', $ratio . '% !important')
                    ->add('display', 'inline-block !important')
                    ->add('min-height', '0 !important')
                    ->add('cursor', 'pointer')
                    ->add('max-width', $src[1] . 'px !important');
            }
            if(!is_null($imgHover)){
                $ratio = (($srcHover[2] / ($srcHover[1] * 100))) * 10000;
                $css->addRule('body #' . $id . ' input:hover, ' . '#' . $id . ' input:focus, ' . '#' . $id . ' input:active')
                    ->add('background', 'url(\'' . $srcHover[0] . '\') top left no-repeat transparent !important')
                    ->add('background-size', 'contain !important')
                    ->add('background-repeat', 'no-repeat !important')
                    ->add('width', '100% !important')
                    ->add('height', '0 !important')
                    ->add('padding-top', $ratio . '% !important')
                    ->add('display', 'inline-block !important')
                    ->add('min-height', '0 !important')
                    ->add('cursor', 'pointer')
                    ->add('max-width', $src[1] . 'px !important');
            }
            if(!is_null($img) || !is_null($imgHover)){
                $css->addRule('body #' . $id)
                    ->add('display', 'block !important')
                    ->add('max-height', $src[2] . 'px !important');
            }
        }
        // clean up theme styles
        $css->addRule('body #' . $id.  ' input')
            ->add('box-shadow', 'none !important')
            ->add('border', 'none !important')
            ->add('border-radius', '0 !important');

        return $css;
    }


    /**
     * @param $url
     * @return array
     */
    public static function getRemoteImageSize($url)
    {
        // Image
        // If valid url
        if(is_string($url) && filter_var($url, FILTER_VALIDATE_URL) !== FALSE){
            $image = new \WPME\Utils\FastImage($url);
        } elseif(is_object($url) && isset($url->__src)) {
            $image = new \WPME\Utils\FastImage($url->__src);
        } else {
            return false;
        }
        // Size
        list($width, $height) = $image->getSize();
        // Return
        return array(
            'width' => $width,
            'height' => $height,
        );
    }
}