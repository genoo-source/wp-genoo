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

use WPMKTENGINE\Utils\CSS;
use WPMKTENGINE\Utils\Json;
use WPMKTENGINE\Utils\Strings;
use WPMKTENGINE\RepositoryThemes;
use WPMKTENGINE\RepositoryPages;

/**
 * Class TemplateRenderer
 * @package WPMKTENGINE
 */
class TemplateRenderer
{
    /** @var */
    var $json;
    /** @var */
    var $jsonData;
    /** @var */
    var $jsonLayout;
    /** @var string */
    var $buffer = '';
    /** @var object */
    var $definitions;
    /** @var array */
    var $forms = array();
    /** @var array */
    var $ctas = array();
    /** @type array */
    var $ctasTimes = array();
    /** @var array  */
    var $ctasAlign = array();
    /** @var array */
    var $ctasReplace = array();
    var $bodyClass = '';
    /** @var array */
    var $ids = array();
    /** @var */
    var $css;
    /** @var array Declared fotns */
    var $fonts = array(
        'AbrilFatface' => 'Abril Fatface',
        'Abril Fatface' => 'Abril Fatface',
        'AnonymousPro' => 'Anonymous Pro', // Anonymous+Pro:700,400,400italic,700italic
        'Anonymous Pro' => 'Anonymous Pro', // Anonymous+Pro:700,400,400italic,700italic
        'ArchitectsDaughter' => 'Architects Daughter',
        'Architects Daughter' => 'Architects Daughter',
        'Bangers' => 'Bangers',
        'Chewy' => 'Chewy',
        'Cookie' => 'Cookie',
        'CoveredByYourGrace' => 'Covered By Your Grace',
        'Covered By Your Grace' => 'Covered By Your Grace',
        'Lato' => 'Lato', // Lato:400,100,100italic,300,300italic,400italic,700,700italic,900,900italic
        'Lobster' => 'Lobster',
        'Merriweather' => 'Merriweather', // Merriweather:400,300italic,300,400italic,700,700italic,900,900italic
        'OpenSans' => 'Open Sans', // Open+Sans:400,300,300italic,400italic,600,600italic,700,700italic,800,800italic
        'Open Sans' => 'Open Sans',
        'Oswald' => 'Oswald', // Oswald:400,300,700
        'Pacifico' => 'Pacifico',
        'Permanent' => 'Permanent Marker',
        'Permanent Marker' => 'Permanent Marker',
        'PlayfairDisplay' => 'Playfair Display', // Playfair+Display:400,700,400italic,700italic,900,900italic
        'Playfair Display' => 'Playfair Display',
        'PoiretOne' => 'Poiret One',
        'Poiret One' => 'Poiret One',
        'PTMono' => 'PT Mono',
        'PT Mono' => 'PT Mono',
        'Raleway' => 'Raleway', // Raleway:400,100italic,100,200italic,200,300,300italic,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic
        // Added 2016-04-03
        'Roboto' => 'Roboto',
        'Slabo' => 'Slabo 27px',
        'JosefinSlab' => 'Josefin Slab', // Josefin+Slab:400,100,100italic,300,300italic,400italic,600,700,700italic,600italic
        'Josefin Slab' => 'Josefin Slab',
        'Special Elite' => 'Special Elite',
        'SpecialElite' => 'Special Elite',
        'Source Code Pro' => 'Source Code Pro',
        'SourceCodePro' => 'Source Code Proo',
    );
    /** @var array */
    var $fontsAppended = array('Material Icons'); // We load these too
    /** @var array */
    var $images = array();
    /** @var array */
    var $imagesReplace = array();
    /** @var array Form ids with palceholders to be replaced */
    VAR $placeholders = array();
    /** @var \DOMDocument */
    var $dom;
    /** @var array */
    var $counters = array();

    /**
     * @param $data
     */
    public function __construct($data)
    {
        // dom
        // suppress warnings of invalid html
        libxml_use_internal_errors(TRUE);
        $this->dom = new \DOMDocument();
        // json data
        $this->json = $data;
        $this->css = new CSS();
        // object definition
        // 19.2.2016
        $this->definitions = (object)(array(
            'image' =>
                array (
                    0 =>
                        (object)(array(
                            'title' => 'Image',
                            'options' =>
                                array (
                                    0 =>
                                        array (
                                            0 => 'swap_vert',
                                            1 => 'change_spacing',
                                        ),
                                    1 =>
                                        array (
                                            0 => '%image%',
                                            1 => 'set_image',
                                        ),
                                ),
                            'style' =>
                                (object)(array(
                                    'display' => 'block',
                                    'margin' => '0 auto',
                                )),
                            'meta' =>
                                (object)(array(
                                    'id' => '#image-wrapper',
                                    'type' => 'image',
                                    'img-object' => '#image-wrapper img',
                                    'img-src' => 'default_picture.png',
                                )),
                        )),
                    1 => '<div id="%meta.id%" class="handle"><img style="%style%" src="%meta.img-src%" /></div>',
                ),
            'text' =>
                array (
                    0 =>
                        (object)(array(
                            'title' => 'Text',
                            'options' =>
                                array (
                                    0 =>
                                        array (
                                            0 => 'list',
                                            1 => 'text_settings',
                                        ),
                                    1 =>
                                        array (
                                            0 => 'swap_vert',
                                            1 => 'change_spacing',
                                        ),
                                    2 =>
                                        array (
                                            0 => 'edit',
                                            1 => 'edit_text',
                                        ),
                                ),
                            'style' =>
                                (object)(array(
                                )),
                            'meta' =>
                                (object)(array(
                                    'id' => '#text-wrapper',
                                    'type' => 'text',
                                    'text-object' => '#text-wrapper',
                                    'list-is-fancy' => true,
                                    'list-icon' => 'label',
                                    'list-color' => '#123456',
                                    'list-margin' => '10px',
                                    'text' => '<p><strong><span style="font-size: 32px;">Hey there world!</span></strong></p>',
                                )),
                        )),
                    1 => '<div id="%meta.id%" data-fancy-list="%meta.list-is-fancy%" data-fancy-icon="%meta.list-icon%" data-fancy-color="%meta.list-color%" class="handle" data-fancy-margin="%meta.list-margin%" style="%style%;line-height:initial!important;">%meta.text%</div>',
                ),
            'countdown' =>
                array (
                    0 =>
                        (object)(array(
                            'title' => 'Countdown',
                            'options' =>
                                array (
                                    0 =>
                                        array (
                                            0 => 'swap_vert',
                                            1 => 'change_spacing',
                                        ),
                                    1 =>
                                        array (
                                            0 => 'edit',
                                            1 => 'Counter.toggle',
                                        ),
                                ),
                            'style' => (object)(array()),
                            'meta' => (object)(array(
                                'id' => '',
                                'type' => 'countdown',
                                'endDate' => '',
                                'styles' =>
                                    (object)(array(
                                        'counter' =>
                                            (object)(array(
                                                'background-color' => '#000000',
                                                'color' => '#ffffff',
                                                'font-family' => 'monospace',
                                                'font-size' => '18px',
                                            )),
                                        'label' =>
                                            (object)(array(
                                                'color' => '#ffffff',
                                                'font-family' => 'monospace',
                                                'font-size' => '18px',
                                            )),
                                    )),
                            )),
                        )),
                    1 => "<div class=\"handle countdown-wrapper cf\" id=\"%meta.id%\" data-countdown-date=\"%meta.endDate%\"><div class=\"timing days\"><span class=\"counter\" style=\"%meta.styles.counter%\">0</span><span class=\"label\" style=\"%meta.styles.label%\">Days</span></div><div class=\"timing hours\"><span class=\"counter\" style=\"%meta.styles.counter%\">0</span><span class=\"label\" style=\"%meta.styles.label%\">Hours</span></div><div class=\"timing minutes\"><span class=\"counter\" style=\"%meta.styles.counter%\">0</span><span class=\"label\" style=\"%meta.styles.label%\">Min</span></div><div class=\"timing seconds\"><span class=\"counter\" style=\"%meta.styles.counter%\">0</span><span class=\"label\" style=\"%meta.styles.label%\">Sec</span></div></div>"
                ),
            'video' =>
                array (
                    0 =>
                        (object)(array(
                            'title' => 'Video',
                            'options' =>
                                array (
                                    0 =>
                                        array (
                                            0 => 'swap_vert',
                                            1 => 'change_spacing',
                                        ),
                                    1 =>
                                        array (
                                            0 => 'videocam',
                                            1 => 'video_embed_editor',
                                        ),
                                ),
                            'style' =>
                                (object)(array(
                                    'display' => 'block',
                                )),
                            'meta' =>
                                (object)(array(
                                    'type' => 'video',
                                    'embed-code' => '<div class="fluidVids" style="width: 100%; position: relative; padding-top: 56.25%;"><iframe width="100%" height="100%" src="https://www.youtube.com/embed/kfvxmEuC7bU" frameborder="0" allowfullscreen="" style="position: absolute; top: 0px; left: 0px;"></iframe></div>',
                                )),
                        )),
                    1 => '<div id="%meta.id%" class="handle" style="%style%">%meta.embed-code%</div>',
                ),
            'button' =>
                array (
                    0 =>
                        (object)(array(
                            'title' => 'Button',
                            'meta' =>
                                (object)(array(
                                    'link' => 'javascript:void',
                                    'text' => 'submit',
                                    'type' => 'button',
                                    'img-object' => '#button-added-1-wrapper',
                                    'header-style' =>
                                        (object)(array(
                                        )),
                                )),
                            'options' =>
                                array (
                                    0 =>
                                        array (
                                            0 => 'swap_vert',
                                            1 => 'change_spacing',
                                        ),
                                    1 =>
                                        array (
                                            0 => 'link',
                                            1 => 'set_href',
                                        ),
                                    2 =>
                                        array (
                                            0 => '%image%',
                                            1 => 'set_image',
                                        ),
                                    3 =>
                                        array (
                                            0 => 'edit',
                                            1 => 'edit_plain_text',
                                        ),
                                    4 =>
                                        array (
                                            0 => 'style',
                                            1 => 'edit_cta',
                                        ),
                                ),
                            'style' =>
                                (object)(array(
                                    'display' => 'inline-block',
                                    'border-style' => 'solid',
                                    'color' => '#ffffff',
                                    'font-family' => 'Arial',
                                    'font-weight' => 'Normal',
                                    'font-size' => '18px',
                                    'background-color' => '#016699',
                                    'padding' => '10px 40px',
                                    'border-color' => '#EBEBEB',
                                    'border-width' => '0px',
                                    'float' => 'left',
                                    'border-top-left-radius' => '1px',
                                    'border-top-right-radius' => '1px',
                                    'border-bottom-left-radius' => '1px',
                                    'border-bottom-right-radius' => '1px',
                                    'border-top-width' => '0px',
                                    'border-right-width' => '0px',
                                    'border-left-width' => '0px',
                                    'border-bottom-width' => '0px',
                                )),
                        )),
                    1 => '<div id="%meta.id%" class="handle" style="%style%"><a style="color:inherit;font-size:inherit;" href="%meta.link%">%meta.text%</a></div>',
                ),
            'form' =>
                array (
                    0 =>
                        (object)(array(
                            'title' => 'Form',
                            'options' =>
                                array (
                                    0 =>
                                        array (
                                            0 => 'swap_vert',
                                            1 => 'change_spacing',
                                        ),
                                    1 =>
                                        array (
                                            0 => 'settings',
                                            1 => 'edit_form',
                                        ),
                                ),
                            'style' =>
                                (object)(array(
                                )),
                            'meta' =>
                                (object)(array(
                                    'id' => 'form-wrappper',
                                    'type' => 'form',
                                    'text-object' => '#form-wrapper input',
                                    'form-id' => '123456',
                                    'styles' =>
                                        (object)(array(
                                            'label' =>
                                                (object)(array(
                                                )),
                                            'input' =>
                                                (object)(array(
                                                )),
                                            'input-placeholder' =>
                                                (object)(array(
                                                )),
                                            'dropdown' =>
                                                (object)(array(
                                                )),
                                            'button' =>
                                                (object)(array(
                                                )),
                                            'button-hover' =>
                                                (object)(array(
                                                )),
                                        )),
                                )),
                        )),
                    1 => '<div id="%meta.id%" data-form-id="%meta.form-id%" class="handle"></div>',
                ),
            'survey' =>
                array (
                    0 =>
                        (object)(array(
                            'title' => 'Survey',
                            'style' =>
                                (object)(array(
                                )),
                            'meta' =>
                                (object)(array(
                                    'id' => '#survey-wrapper',
                                    'type' => 'survey',
                                    'text-object' => '#survey-wrapper',
                                    'text' => '<p><strong><span style="font-size: 32px;">THIS IS A SURVEY</span></strong></p>',
                                )),
                        )),
                    1 => '<div id="%meta.id%" class="handle">%meta.text%</div>',
                ),
        )
        );
    }

    /**
     * Prepare data,
     * put's layout in one, and data in two.
     */
    public function prapare()
    {
        // Convert to object again please
        $this->json = Json::decode(Json::encode($this->json));
        // Differntiate
        if(!empty($this->json)){
            if(isset($this->json->{0}) && !empty($this->json->{0})){
                $this->jsonData = $this->json->{0};
            } elseif(isset($this->json[0]) && !empty($this->json[0])){
                $this->jsonData = $this->json[0];
            }
            if(isset($this->json->{1}) && !empty($this->json->{1})){
                $this->jsonLayout = $this->json->{1};
            } elseif(isset($this->json[1]) && !empty($this->json[1])){
                $this->jsonLayout = $this->json[1];
            }
        }
    }

    /**
     * @return string
     */
    public function getBodyStyle()
    {
        $r = '';
        if(isset($this->jsonData->{'#body'})){
            // Inline Styles
            $inlineStyles = $this->iterateRowInlineStyle($this->jsonData->{'#body'}->style);
            // All except
            $meta = $this->jsonData->{'#body'}->meta;
            $meta = $this->removePropertiesExcept($meta, array(''));
            $r = $inlineStyles;
        }
        return $r;
    }

    /**
     * @param $content
     */
    public function setBodyClass($content)
    {
        global $post;
        // Old
        $old = $post->post_content;
        // Set new
        $post->post_content = $content;
        $this->bodyClass = $this->getBodyClass();
        $post->post_content = $old;
    }

    /**
     * @return string
     */
    public function getBodyClass()
    {
        return join(' ', get_body_class());
    }

    /**
     * Iterates through the JSON
     */
    public function iterate()
    {
        // Itereate through, if not empty
        if(is_array($this->jsonLayout) && !empty($this->jsonLayout)){
            foreach($this->jsonLayout as $key => $layout){
                $this->iteratorRow($layout);
            }
        }
    }


    /**
     * Assings output to renderer, for each row
     *
     * @param $layout
     */
    public function iteratorRow($layout)
    {
        // Render
        $r = '';
        // Layout in array

        if(is_array($layout) && !empty($layout)){
            // Basics
            $renderInlineStyle = isset($layout[0]->style) ? $this->iterateRowInlineStyle($layout[0]->style) : '';
            $renderContainerClass = (isset($layout[0]->full_width) && $layout[0]->full_width == TRUE) ? '' : 'container';
            // Another Row
            $renderAnotherRow = isset($layout[0]->{'background-style'}) ? $layout[0]->{'background-style'} : FALSE;
            $renderAnotherRowStyled = $renderAnotherRow;
            $r .= '<div class="container-fluid" style="' . $renderInlineStyle . '"><div class="'. $renderContainerClass .'">';
            // Go through
            if(count($layout) > 1){
                // Go through rows
                for($counter = 1; $counter < count($layout); $counter++){
                    $current =  $layout[$counter];
                    $width = (isset($layout[$counter][0]) && ($layout[$counter][0] instanceof \stdClass)) ? $layout[$counter][0]->width : 1;
                    $r .= '<div class="col-md-'. $width .' pane sortable-column">';
                    // Go through columns
                    if(count($layout[$counter]) > 1){
                        for($counter_col = 1; $counter_col < count($layout[$counter]); $counter_col++){
                            $current_col =  $layout[$counter][$counter_col];
                            if(is_string($current_col)){
                                if(isset($this->jsonData->{$current_col})){
                                    $r .= '<div>'. $this->iterateRowInlineObject($this->jsonData->{$current_col}) .'</div>';
                                } elseif($this->jsonData[$current_col]){
                                    $r .= '<div>'. $this->iterateRowInlineObject($this->jsonData[$current_col]) .'</div>';
                                }
                            } elseif(is_array($current_col)){
                                // We've got a new thing
                                // This seems to be a "partition"
                                if(isset($current_col[0]) && $current_col[0] instanceof \stdClass){
                                    $styles = $current_col[0]->style;
                                    $styles_2 = clone $styles;
                                    $partitionBackgroundStyle = $this->iterateRowInlineStyleFor($styles, 'background');
                                    $partitionBox = $this->iterateRowInlineStyleFor($styles_2, 'box');
                                } else {
                                    $partitionBackgroundStyle = '';
                                    $partitionBox = '';
                                }
                                // Buffer
                                $r .= '<div class="partition-wrapper">';
                                $r .= '<div class="partition" style="'. $partitionBox .'"">';

                                // TODO: Check partition layout type

                                // $r .= '<div class="pane sortable-column ui-sortable">';
                                // Iterate through inner elements of partition
                                if(count($current_col) > 1){
                                    // There are two possibilities here ( an extra for backwards
                                    // compatablity. )
                                    // 1 - this is a list of IDs {String} (old way)
                                    // 2 - this is a list of partition columns {Array}

                                    // To have backwards compatablity, we need to convert the list
                                    // of ids to one partition column
                                    if ( gettype( $current_col[1] ) == 'string' || gettype( $current_col[1] ) == 'undefined') {
                                        $partition_to_generate = array(
                                            0 => array(
                                                'width' => "1_1"
                                            ),
                                        );
                                        for ( $lonely_item = 1; $lonely_item < count( $current_col ); $lonely_item++ ) {
                                            $partition_to_generate[ $lonely_item ] =
                                                $current_col[ $lonely_item ];
                                            // Delete this old record
                                            unset( $current_col[ $lonely_item ] );
                                        }
                                        // Finally, we add this information back into the column
                                        $current_col[1] = $partition_to_generate;
                                    }

                                    for ( $partition_column = 1; $partition_column < count( $current_col ); $partition_column++ ) {
                                        $partition_column_width =
                                            $current_col[$partition_column][0]->width;

                                        $r .= '<div class="pane sortable-column ui-sortable partition-column__'.$partition_column_width.'">';

                                        // Loop through all of the items in this column in the
                                        // partition
                                        for($counter_col_partition = 1; $counter_col_partition < count($current_col[$partition_column]); $counter_col_partition++){

                                            $current_col_partition_id =
                                                $current_col[$partition_column]
                                                [$counter_col_partition];

                                            if(isset($this->jsonData->{$current_col_partition_id})){
                                                $r .=
                                                    $this->iterateRowInlineObject(
                                                        $this->
                                                        jsonData->
                                                        {$current_col_partition_id
                                                        }
                                                    );
                                            } elseif($this->jsonData[$current_col_partition_id]){
                                                $r .= $this->iterateRowInlineObject($this->jsonData[$current_col_partition_id ]);
                                            }
                                        }

                                        $r .= '</div>';
                                    }

                                }
                                // $r .= '</div>';

                                $r .= '</div>';
                                $r .= '<div class="partition-background" style="'. $partitionBackgroundStyle .'"></div>';
                                $r .= '</div>';
                            }
                        }
                    }
                    $r .= '</div>';
                }
            }
            // Another Row added as transparency background image?
            if($renderAnotherRow !== FALSE){
                // Get styles and append
                $renderAnotherRowStyle = $this->iterateRowInlineStyle($renderAnotherRow);
                $renderAnotherRowStyled = '<div class="row-background" style="'. $renderAnotherRowStyle .'"></div>';
            }
            $r .= '</div>'. $renderAnotherRowStyled .'</div></div>';
        } elseif($layout instanceof \stdClass){
            // Not sure Joshua?
        }
        $this->buffer .= $r;
    }

    /**
     * Interate Row, get inline style
     *
     * @param $style
     * @return string
     */
    public function iterateRowInlineStyle($style)
    {
        $r = '';
        if($style instanceof \stdClass){
            $styles = get_object_vars($style);
            if(!empty($styles)){
                foreach($styles as $property => $value){
                    if($property == 'font-family'){
                        $this->appendFontFamily($value);
                    }
                    if($property == 'background-style'){
                        continue;
                    }
                    // Cleanse
                    $value = str_replace('"', '\'', $value);
                    if(Strings::contains($value, 'url(') && Strings::contains($value, 'data/')){
                        $value = str_replace('\'data/', '\''. WPMKTENGINE_BUILDER . 'data/', $value);
                    }
                    // Might have a wrong protocol still
                    $protocolLess = str_replace('http://', '', WPMKTENGINE_BUILDER);
                    $protocolLess = str_replace('https://', '', $protocolLess);
                    if(Strings::contains($value, $protocolLess)){
                        $value = str_replace('http://', 'https://', $value);
                    }
                    if(!empty($value)){
                        $r .= ' ' . $property . ': ' . $value . ' ;';
                    }
                }
            }
        }
        return $r;
    }


    /**
     * @param $style
     * @param string $type
     * @return string
     */
    public function iterateRowInlineStyleFor($style, $type = 'background')
    {
        $r = '';
        if($style instanceof \stdClass){
            $styles = get_object_vars($style);
            if(!empty($styles)){
                switch($type){
                    case 'background':
                        // Remove properties?
                        foreach($styles as $property => $value){
                            $remove = TRUE;
                            if(Strings::contains($property, 'background-color')){
                                $remove = FALSE;
                            }
                            if(Strings::contains($property, 'opacity')){
                                $remove = FALSE;
                            }
                            if(Strings::contains($property, 'border') && Strings::contains($property, 'radius')){
                                $remove = FALSE;
                            }
                            if($remove == TRUE){
                                unset($style->{$property});
                            }
                        }
                        break;
                    case 'box':
                        // Remove properties?
                        foreach($styles as $property => $value){
                            $remove = FALSE;
                            if(Strings::contains($property, 'background-color')){
                                $remove = TRUE;
                            }
                            if(Strings::contains($property, 'opacity')){
                                $remove = TRUE;
                            }
                            if(Strings::contains($property, 'border') && Strings::contains($property, 'radius')){
                                $remove = TRUE;
                            }
                            if($remove == TRUE){
                                unset($style->{$property});
                            }
                        }
                        break;
                }
                // Return styles
                return $this->iterateRowInlineStyle($style);
            }
        }
        return $r;
    }


    /**
     * Remove all properties except
     *
     * @param $object
     * @param array $properties
     * @return mixed
     */
    public function removePropertiesExcept($object, $properties = array())
    {
        $vars = get_object_vars($object);
        if(!empty($vars)){
            foreach($vars as $porperty => $value){
                if(!in_array($porperty, $properties)){
                    unset($object->{$porperty});
                }
            }
        }
        return $object;
    }


    /**
     * ITterate Row, get inline html for object
     *
     * @param $object
     * @return string
     */
    public function iterateRowInlineObject($object)
    {
        global $WPME_STYLES;
        $r = '';
        if(isset($object->meta->type) && isset($this->definitions->{$object->meta->type})){
            // Html definitinos
            $definition = $this->definitions->{$object->meta->type}[1];
            // Definitions Base
            $definitionBase = $this->definitions->{$object->meta->type}[0];
            // Definitions CSS
            $definitionCSS = $this->iterateRowInlineStyle($object->style);
            // We act a bit different if it's generated CTA, and epseically if HTML
            $isGeneratedCTA = FALSE;
            $isGeneratedCTAHTML = FALSE;
            $object->stylesHover = FALSE;
            if(
                isset($object->meta->{'cta-id'})
                && isset($object->meta->{'cta-type'})
                && (is_numeric($object->meta->{'cta-id'}) && $object->meta->{'cta-id'} > 0)
                && ($object->meta->{'cta-type'} == 'html')
            ){
                $isGeneratedCTA = TRUE;
                $isGeneratedCTAHTML = TRUE;
                if($object->meta->{'hover-style'}){
                    $object->stylesHover = $object->meta->{'hover-style'};
                }
            }
            // Work with it
            // Only if it's not HTML CTA button, becauese that will have CSS in the
            // global object
            $definition = str_replace('%style%', (!$isGeneratedCTAHTML ? $definitionCSS : '') . ' ', $definition);
            // Remove silly metas
            $definition = str_replace('%meta.id%', isset($object->meta->id) ? $object->meta->id : '', $definition);
            $definition = str_replace('%meta.img-src%', isset($object->meta->{'img-src'}) ? $object->meta->{'img-src'} : '', $definition);
            $definition = str_replace('%meta.list-is-fancy%', isset($object->meta->{'list-is-fancy'}) ? $object->meta->{'list-is-fancy'} : '', $definition);
            $definition = str_replace('%meta.list-icon%', isset($object->meta->{''}) ? $object->meta->{'list-icon'} : '', $definition);
            $definition = str_replace('%meta.list-color%', isset($object->meta->{'list-color'}) ? $object->meta->{'list-color'} : '', $definition);
            $definition = str_replace('%meta.link%', isset($object->meta->{'link'}) ? $object->meta->{'link'} : '', $definition);
            $definition = str_replace('%meta.text%', isset($object->meta->{'text'}) ? $object->meta->{'text'} : '', $definition);
            $definition = str_replace('%meta.embed-code%', isset($object->meta->{'embed-code'}) ? $object->meta->{'embed-code'} : '', $definition);
            $definition = str_replace('%meta.form-id%', isset($object->meta->{'form-id'}) ? $object->meta->{'form-id'} : '', $definition);
            $definition = str_replace('%meta.use-placeholders%', isset($object->meta->{'use-placeholders'}) ? $object->meta->{'use-placeholders'} : '', $definition);
            $definition = str_replace('%meta.show-required-text%', isset($object->meta->{'show-required-text'}) ? $object->meta->{'show-required-text'} : '', $definition);
            $definition = str_replace('%meta.list-margin%', isset($object->meta->{'list-margin'}) ? $object->meta->{'list-margin'} : '', $definition);
            // Counter styles
            if($object->meta->type == 'countdown'){
                $definitionCSSCounter = $this->iterateRowInlineStyle($object->meta->styles->counter);
                $definitionCSSLabel = $this->iterateRowInlineStyle($object->meta->styles->label);
                $definition = str_replace('%meta.styles.counter%', $definitionCSSCounter, $definition);
                $definition = str_replace('%meta.styles.label%', $definitionCSSLabel, $definition);
            }
            // Assign forms to objects / ids
            // Assign ctas to objects / ids
            if(isset($object->meta->{'form-id'}) && isset($object->meta->id)){
                $this->forms[$object->meta->id] = $object->meta->{'form-id'};
                // Hide required?
                if(isset($object->meta->{'show-required-text'}) && $object->meta->{'show-required-text'} == FALSE){
                    $rule = $this->css->addRule('#' . $object->meta->id . ' span.req');
                    $rule->add('display', 'none !important');
                }
                // Placeholders (done with JS)
                if(isset($object->meta->{'use-placeholders'}) && $object->meta->{'use-placeholders'} == TRUE){
                    $this->placeholders[] = $object->meta->id;
                    $rule = $this->css->addRule('#' . $object->meta->id . ' label');
                    $rule->add('display', 'none !important');
                }
            }
            // Form styling
            if(isset($object->meta->id) && isset($object->meta->styles)){
                // Ok, we have styles. Let's crawl through them and append them to the CSS
                // Well J. has done some funky things here, let's assing them the way they should be
                $stylePlaceholder = (isset($object->meta->styles->{'input-placeholder'})) && !empty($object->meta->styles->{'input-placeholder'}) ? $object->meta->styles->{'input-placeholder'} : NULL;
                $styleInput = (isset($object->meta->styles->input)) && !empty($object->meta->styles->input) ? $object->meta->styles->input : NULL;
                $styleButton = (isset($object->meta->styles->button)) && !empty($object->meta->styles->button) ? $object->meta->styles->button : NULL;
                $styleButtonHover = (isset($object->meta->styles->{'button-hover'})) && !empty($object->meta->styles->{'button-hover'}) ? $object->meta->styles->{'button-hover'} : NULL;
                $styleSelect = (isset($object->meta->styles->dropdown)) && !empty($object->meta->styles->dropdown) ? $object->meta->styles->dropdown : NULL;
                $styleLabel = (isset($object->meta->styles->label)) && !empty($object->meta->styles->label) ? $object->meta->styles->label : NULL;
                // Style object
                $styleObject = array();
                // Append to it
                $styleObject['input::-webkit-input-placeholder'] = $stylePlaceholder;
                $styleObject['input::-moz-placeholder'] = $stylePlaceholder;
                $styleObject['input:-moz-placeholder'] = $stylePlaceholder;
                $styleObject['input:-ms-input-placeholder'] = $stylePlaceholder;
                $styleObject['input:not([type="submit"])'] = $styleInput;
                $styleObject['input[type="submit"]'] = $styleButton;
                $styleObject['input[type="submit"]:hover'] = $styleButtonHover;
                $styleObject['select'] = $styleSelect;
                $styleObject['label'] = $styleLabel;
                // Add styles
                $this->appendCSSFromRulesArrayObject($object->meta->id, $styleObject);
            }
            // CTA much?
            if(isset($object->meta->{'cta-id'}) && isset($object->meta->id)){
                // Go through CTA's later
                $this->ctas[$object->meta->id] = $object->meta->{'cta-id'};
                if(isset($object->meta->{'cta-time'})){
                    $this->ctasTimes[$object->meta->id] = $object->meta->{'cta-time'};
                }
                if(isset($object->meta->{'cta-align'})){
                    $this->ctasAlign[$object->meta->id] = $object->meta->{'cta-align'};
                }
            }
            // If meta.text, extract inline css, and font-family and image sources
            if(isset($object->meta->{'text'}) && !empty($object->meta->text)){
                // Append Font Families in inline styles to a global object
                $this->appendFontFamilyFromHTML($object->meta->text);
            }
            // Image link?
            if($object->meta->type == 'image' && isset($object->meta->{'link'}) && !empty($object->meta->{'link'})){
                $link = $object->meta->{'link'};
                $tagStart = "<a href=\"$link\">";
                $tagEnd = "</a>";
                $definition = str_replace('<img', $tagStart . '<img', $definition);
                $definition = str_replace('</div>', $tagEnd . '</div>', $definition);
            }
            // Add counters to global
            if($object->meta->type == 'countdown'){
                if(!empty($object->meta->id) && isset($object->meta->endDate) && !empty($object->meta->endDate)){
                    $this->counters[$object->meta->id] = $object->meta->endDate;
                }
            }
            // Add global CSS if any?
            if(isset($object->meta->style_css) && !empty($object->meta->style_css)){
                if(!empty($WPME_STYLES)){
                    $WPME_STYLES .= $object->meta->style_css;
                } else {
                    $WPME_STYLES = $object->meta->style_css;
                }
            }
            // Add CTA HTML button global CSS
            if($isGeneratedCTAHTML){
                $this->appendCSSForCTAHTMLButton(
                    'body #' . $object->meta->id . ' .genooGenrated form .genooButton',
                    $object->style,
                    $object->stylesHover
                );
            }
            // Object
            $r = $definition;
        } else {
            // Probaly won't throw exception in live environment
            //throw \Exception('Type not supported in definitions. Type: ' . $object->meta->type);
        }
        return $r;
    }

    /**
     * @param $font
     */
    public function appendFontFamily($font)
    {
        // Find clear font
        $fontClear = (false !== ($pos = strpos($font, ','))) ? substr($font, 0, $pos) : $font;
        $fontClear = str_replace("'", "", $fontClear);
        $fontClear = str_replace('"', '', $fontClear);
        $fontClearReal = array_key_exists($fontClear, $this->fonts) ? $this->fonts[$fontClear] : NULL;
        // Append if exisits
        if(array_key_exists($fontClearReal, $this->fonts)){
            $this->fontsAppended[$this->fonts[$fontClearReal]] = $this->fonts[$fontClearReal];
        }
    }

    /**
     * @param $html
     */
    public function appendGlobalImageSources()
    {
        // Only if DOM present
        if(isset($this->dom) && method_exists($this->dom, 'loadHTML')){
            // Load html
            if(method_exists($this->dom, 'getElementsByTagName')){
                // Get all images
                $imgs = $this->dom->getElementsByTagName('img');
                if(!empty($imgs) && $imgs instanceof \DOMNodeList){
                    foreach($imgs as $img){
                        $this->images[] = $img->getAttribute('src');
                    }
                }
            }
        }
        // If not empty, do the magic please
        if(!empty($this->images)){
            foreach($this->images as $image){
                // Starter
                global $WPME_API;
                if(isset($WPME_API) && $WPME_API instanceof \WPME\ApiFactory){
                    $starter = 'data/' . md5($WPME_API->key) . '/';
                    if(Strings::startsWith($image, 'data/')){
                        $this->imagesReplace[$image] = WPMKTENGINE_BUILDER . $image;
                    }
                }
            }
        }
    }


    /**
     * @param $html
     * @return mixed
     */
    public function appendGlobalImageSourcesReplace($html)
    {
        if(!empty($this->imagesReplace)){
            foreach($this->imagesReplace as $orignal => $new){
                $html = str_replace($orignal, $new, $html);
            }
        }
        return $html;
    }


    /**
     * Extracts inline styles from HTML,
     * finds font-family and appends it nescessary
     *
     * @param $html
     */
    public function appendFontFamilyFromHTML($html)
    {
        // Extract any styles
        preg_match_all(
            '/style="(.*?)"/is',
            $html,
            $styles
        );
        if(is_array($styles) && !empty($styles)){
            // Styles are in second array
            if(isset($styles[1]) && !empty($styles[1])){
                $cssRules = $styles[1];
                foreach($cssRules as $rule){
                    // See if font-family present
                    preg_match('@font-family(\s*):(.*?)(\s?)("|;|$)@i', $rule, $fonts);
                    if(isset($fonts[2]) && !empty($fonts[2])){
                        $fontFamily = $fonts[2];
                        $fontFamily = trim($fontFamily);
                        $this->appendFontFamily($fontFamily);
                    }
                }
            }
        }
    }

    /**
     * Append CSS styles to the global object,
     * used for forms
     *
     * @param $object
     */
    public function appendCSSFromObject($object)
    {
        $ruleIgnore = array('translate');
        $ruleImportant = array('width');
        $ruleAppender = '#' . $object->meta->id . ' ';
        $ruleElements = get_object_vars($object->meta->styles);
        if(is_array($ruleElements) && !empty($ruleElements)){
            foreach($ruleElements as $element => $rules){
                $rulesCSS = get_object_vars($rules);
                if(is_array($rulesCSS) && !empty($rulesCSS)){
                    // Get which object to assign to
                    $currentElement = $ruleAppender . $element;
                    // Button is possilby meant for input = type submit too
                    if($element == 'button'){
                        $currentElement .= ', ' . $ruleAppender . ' input[type="submit"]';
                    }
                    $current = $this->css->addRule($currentElement);
                    foreach($rulesCSS as $property => $value){
                        if($property == 'font-family'){
                            $this->appendFontFamily($value);
                        }
                        if(!in_array($property, $ruleIgnore) && !empty($value)){
                            if(in_array($property, $ruleImportant) && !Strings::contains($value, '!important')){
                                $value = $value . ' !important';
                            }
                            $current->add($property, $value);
                        }
                    }
                }
            }
        }
    }

    /**
     * Append CSS to global if CTA HTML button
     *
     * @param $identificator
     * @param $styles
     * @param bool $stylesHover
     */
    public function appendCSSForCTAHTMLButton($identificator, $styles, $stylesHover = FALSE)
    {
        $ruleIgnore = array('translate');
        $ruleImportant = array('width');
        $rulesCSS = get_object_vars($styles);
        $rulesCSSHover = FALSE;
        if($stylesHover !== FALSE && is_object($stylesHover)){
            $rulesCSSHover = get_object_vars($stylesHover);
        }
        if(is_array($rulesCSS) && !empty($rulesCSS)){
            // Get which object to assign to
            $currentElement = $identificator;
            // Button is possilby meant for input = type submit too
            $current = $this->css->addRule($currentElement);
            foreach($rulesCSS as $property => $value){
                if($property == 'font-family'){
                    $this->appendFontFamily($value);
                }
                if(!in_array($property, $ruleIgnore) && !empty($value)){
                    if(in_array($property, $ruleImportant) && !Strings::contains($value, '!important')){
                        $value = $value . ' !important';
                    }
                    $current->add($property, $value);
                }
            }
        }
        if(is_array($rulesCSSHover) && !empty($rulesCSSHover)){
            // Get which object to assign to
            $currentElement = $identificator . ':hover, ' . $identificator . ':active ';
            // Button is possilby meant for input = type submit too
            $current = $this->css->addRule($currentElement);
            foreach($rulesCSSHover as $property => $value){
                if($property == 'font-family'){
                    $this->appendFontFamily($value);
                }
                if(!in_array($property, $ruleIgnore) && !empty($value)){
                    if(in_array($property, $ruleImportant) && !Strings::contains($value, '!important')){
                        $value = $value . ' !important';
                    }
                    $current->add($property, $value);
                }
            }
        }
    }

    /**
     * Different way of appending
     *
     * @param $appendTo
     * @param $object
     */
    public function appendCSSFromRulesArrayObject($appendTo, $object)
    {
        $ruleIgnore = array('translate');
        $ruleImportant = array('width');
        $ruleAppender = '#' . $appendTo . ' ';
        $ruleElements = $object;
        if(is_array($ruleElements) && !empty($ruleElements)){
            foreach($ruleElements as $element => $rules){
                $rulesCSS = get_object_vars($rules);
                if(is_array($rulesCSS) && !empty($rulesCSS)){
                    // Get which object to assign to
                    $currentElement = $ruleAppender . $element;
                    // Button is possilby meant for input = type submit too
                    $current = $this->css->addRule($currentElement);
                    foreach($rulesCSS as $property => $value){
                        if($property == 'font-family'){
                            $this->appendFontFamily($value);
                        }
                        if(!in_array($property, $ruleIgnore) && !empty($value)){
                            // Force width
                            if(in_array($property, $ruleImportant) && !Strings::contains($value, '!important')){
                                $value = $value . ' !important';
                            }
                            $current->add($property, $value);
                        }
                    }
                }
            }
        }
    }


    /**
     * Before renderer, append shortcode of forms etc.
     */
    public function beforeRenderer()
    {
        $globalPost = $GLOBALS['post'];
        // Initiate HTML
        // prep
        $this->dom->loadHTML('<?xml encoding="utf-8" ?><!DOCTYPE html>' . $this->buffer);
        $this->dom->preserveWhiteSpace = FALSE;
        $shortcodeArray = array();
        // Go through forms if needed
        // CTA'S Need a global of \WP_Post to work with
        global $post;
        $postPrep = new \stdClass();
        $postPrep->ID = 1;
        $post = $globalPost instanceof \WP_Post ? $globalPost : new \WP_Post($postPrep);
        // Template redirect?
        do_action('template_redirect_wpme');
        // This is needed for Frontend class to react and append modal windows
        $post->post_type = 'wpme-landing-pages';
        $post->post_content = '';
        if(!empty($this->forms)){
            // Go through
            foreach($this->forms as $formHtmlId => $formId){
                // Element
                $element = $this->dom->getElementById($formHtmlId);
                // Add shortcode
                $shortcode = '[WPMKTENGINEForm id=\'' . $formId . '\']';
                $shortcodeArray[] = $shortcode;
                $fragment = $this->dom->createDocumentFragment();
                $fragment->appendXML($shortcode);
                if(is_object($element) && method_exists($element, 'appendChild')){
                    $element->appendChild($fragment);
                } else {
                    // TODO: logger
                }
                // Append to post body, so Modal windows can react
                $post->post_content .= $shortcode . ' ';
            }
        }
        // Go through CTAs if needed
        if(!empty($this->ctas)){

            // Go through
            foreach($this->ctas as $formHtmlId => $formId){
                // Element
                $element = $this->dom->getElementById($formHtmlId);
                // Remove children ... (any html insdie)
                while ($element->hasChildNodes()) {
                    $element->removeChild($element->firstChild);
                }
                // Check time
                $timeText = '';
                if(!empty($this->ctasTimes) && array_key_exists($formHtmlId, $this->ctasTimes)){
                    $time = $this->ctasTimes[$formHtmlId];
                    if($time > 0){
                        $timeText = ' time=\''. $time .'\'';
                    }
                }
                $alignText = '';
                if(!empty($this->ctasAlign) && array_key_exists($formHtmlId, $this->ctasAlign)){
                    $align = $this->ctasAlign[$formHtmlId];
                    if($align == 'right' || $align == 'left' || $align == 'center'){
                        $alignText = 'align=\''. $align .'\'';
                    }
                }
                // Add shortcode
                $shortcode = '[WPMKTENGINECTA id=\'' . $formId . '\''. $timeText .' '. $alignText .']';
                $shortcodeArray[] = $shortcode;
                $fragment = $this->dom->createDocumentFragment();
                $fragment->appendXML($shortcode);
                $element->appendChild($fragment);
                // Append to post body, so Modal windows can react
                $post->post_content .= $shortcode . ' ';
            }
        }
        // Image sources
        $this->appendGlobalImageSources();
        // Just the body please
        $this->buffer = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $this->dom->saveHTML());
        // UTF8 not needed anymore
        $this->buffer = str_replace('<?xml encoding="utf-8" ?>', '', $this->buffer);
        // Remove the silly handles
        $this->buffer = str_replace('class="handle"', '', $this->buffer);
        $this->buffer = $this->appendGlobalImageSourcesReplace($this->buffer);
        $this->buffer = str_replace('src="data/', 'src="'. WPMKTENGINE_BUILDER . 'data/', $this->buffer);
        // When we have a buffer, we still need to replace CTA's
        if(!empty($this->ctas)){
            foreach($this->ctas as $formHtmlId => $formId){
                // Check time
                $timeText = '';
                if(!empty($this->ctasTimes) && array_key_exists($formHtmlId, $this->ctasTimes)){
                    $time = $this->ctasTimes[$formHtmlId];
                    if($time > 0){
                        $timeText = ' time=\''. $time .'\'';
                    }
                }
                $alignText = '';
                if(!empty($this->ctasAlign) && array_key_exists($formHtmlId, $this->ctasAlign)){
                    $align = $this->ctasAlign[$formHtmlId];
                    if($align == 'right' || $align == 'left' || $align == 'center'){
                        $alignText = 'align=\''. $align .'\'';
                    }
                }
                $shortcode = '[WPMKTENGINECTA id=\'' . $formId . '\''. $timeText .' '. $alignText .']';
                $shortcodeValue = do_shortcode($shortcode);
                $this->buffer = str_replace($shortcode, $shortcodeValue, $this->buffer);
            }
        }
        $this->setBodyClass($this->buffer);
        // Clean errors
        libxml_clear_errors();
        // Remove this please, it's just terrible and causes issue.
        remove_filter('the_content', 'wpautop');
        // Last step, apply WordPress shortcodes etc., only if ctas or forms
        $this->buffer = apply_filters('the_content', $this->buffer);
        $this->buffer = str_replace(']]>', ']]>', $this->buffer);
        // TODO: Remove in version above 1.7.7
        $this->buffer = str_replace('@AMP', '&', $this->buffer);
        $this->buffer = str_replace(array(
            '%AMP%',
            '%AUTOPLAY%'
        ), array(
            '&',
            'autoplay=1'
        ), $this->buffer);
        // Shortcodes
        $this->buffer = do_shortcode($this->buffer);
        // Add shortcodes to page content for footer to find
        if(isset($GLOBALS) && is_array($GLOBALS) && array_key_exists('post', $GLOBALS) && $GLOBALS['post'] instanceof \WP_Post){
            $GLOBALS['post']->post_content = implode(' ', $shortcodeArray);
        }
        $GLOBALS['post_shortcodes'] = $shortcodeArray;
    }

    /**
     * @param $content
     * @return mixed
     */
    public function renderShortcode($content)
    {
        $content = apply_filters('the_content', $content);
        $content = str_replace(']]>', ']]>', $content);
        return $content;
    }

    /**
     * Renders fonts found in styles and inline styles
     *
     * @return string
     */
    public function renderFonts()
    {
        $r = '';
        if(!empty($this->fontsAppended)){
            foreach($this->fontsAppended as $key => $value){
                $this->fontsAppended[$key] = urlencode($value);
            }
            $r = '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family='. implode('|', $this->fontsAppended) .'" type="text/css" />';
        }
        return $r;
    }

    public function renderWpHead()
    {
        if(isset($GLOBALS['landing_page'])
            && is_object($GLOBALS['landing_page'])
            && isset($GLOBALS['landing_page']->meta))
        {
            $meta = $GLOBALS['landing_page']->meta;
            // Allowed
            $allowedScripts = array();
            $allowedStyles = array();
            if(isset($meta->_scripts)){
                $allowedScripts = unserialize($meta->_scripts);
            }
            if(isset($meta->_styles)){
                $allowedStyles = unserialize($meta->_styles);
            }
            // Check if we have something first
            if(count($allowedScripts) === 0 && count($allowedStyles) === 0){
              return;
            }
            // Get global scripts
            global $wp_scripts;
            global $wp_styles;
            // Remove not allowed
            foreach($wp_styles->queue as $style){
                if(!in_array($style, $allowedStyles)){
                    // Here comes trouble
                    $wp_styles->dequeue($style);
                    $wp_styles->remove($style);
                }
            }
            // Remove not allowed
            foreach($wp_scripts->queue as $scripts){
                if(!in_array($scripts, $allowedScripts)){
                    $wp_scripts->dequeue($scripts);
                    $wp_scripts->remove($scripts);
                }
            }
            ob_start();
            wp_head();
            return ob_get_clean();
        }
        return;
    }

    /**
     * Render footer scripts
     */
    public function renderFooterScripts()
    {
        ?>
        <script type="text/javascript">
            /**
             * Forms
             * @type {Array}
             */
            var forms = [];
            forms = <?php echo json_encode($this->placeholders); ?>;

            /**
             * Labels to placeholders
             * @param formId
             */
            function labelsToPlaceholders(formId){
                // Select all of the labels inside of the form
                labelNodeList = document.querySelectorAll(formId + ' label' );
                for(labelNodeIndex=0;labelNodeIndex<labelNodeList.length;labelNodeIndex++ ){
                    labelNode = labelNodeList[labelNodeIndex];
                    // Hide the label
                    labelNode.style.display = 'none';
                    // Select the input after and set it's placeholder ot the label's text
                    labelNode.nextSibling.placeholder = labelNode.textContent;
                }
            }

            // If forms
            if(forms.length > 0){
                for (var i = 0; i < forms.length; i++){
                    var id = '#' + forms[i];
                    var element =  document.getElementById(forms[i]);
                    if (typeof(element) != 'undefined' && element != null){
                        labelsToPlaceholders(id);
                    }
                }
            }

            <?php
            if(!empty($this->counters)){
                foreach($this->counters as $id => $time){
                    echo "CounterBuilder.attach('$time', '$id');\n";
                }
            }
            ?>
        </script>
        <?php
    }

    /**
     * Renderer
     *
     * @param string $title
     * @param string $additionalHeader
     * @param string $additionalFooter
     */
    public function render($title = '', $additionalHeader = '', $additionalFooter = '', $renderTrackingInHead = false)
    {
        global $WPME_STYLES;
        global $wp_filter;
        global $WPME_FRONTEND;
        // Run before renderer
        $this->beforeRenderer();
        // Get header styles
        $repositoryThemes = new RepositoryThemes();
        $css = $repositoryThemes->getAllThemesStyles();
        $cssStyles = (isset($WPME_STYLES) && !empty($WPME_STYLES)) ? $WPME_STYLES : '';

        // Tracking script manually tracked
        \add_filter('genoo_tracking_is_manually_tracking', '__return_true');
        $repositorySettings = new \WPME\RepositorySettingsFactory();
        $trackingScript = $repositorySettings->getTrackingCode();
        // Tracking script locations
        $trackingScriptHeader = '';
        $trackingScriptFooter = '';
        // Tracking code option
        if($renderTrackingInHead === FALSE){
          // If we're rendering the tracking script in footer, nothing to do
          $trackingScriptFooter = $trackingScript;
        } else {
          $trackingScriptHeader = $trackingScript;
        }

        // Remove wpfooter
        if(isset($wp_filter['wp_footer']) && is_array($wp_filter['wp_footer']->callbacks[1])){ // we assign to first footer
            foreach($wp_filter['wp_footer']->callbacks[1] as $filter => $data){
                if(Strings::endsWith($filter, 'footerFirst')){
                    \remove_action('wp_footer', $filter, 1);
                }
            }
        }
        // Add it manually
        // Render
        echo '<!DOCTYPE html>
        <html lang="en">
          <head>
	              <meta charset="utf-8">
	              <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, width=device-width">
                <title>'. $title .'</title>
                '. $trackingScriptHeader .'
                <link rel="stylesheet" href="'. WPMKTENGINE_BUILDER . 'stylesheets/render.css" charset="utf-8" />
                <style type="text/css">
                '. $this->appendInline('bootstrap') .'
                '. $this->appendInline('frontend.css') .'
                '. $this->renderWpHead() .'
                </style>
                <script type="text/javascript">'.  $this->appendInline('frontend.js') .'</script>
                '. $this->renderFonts() .'
                <style type="text/css">
                .pane:empty { visibility: hidden; }
                iframe { width: 100%; max-width: 100%; display: block; }
                button, input, label { cursor: pointer; }
                body{ padding: 0; }
                .container-fluid { position: relative; z-index: 300; }
                .row-background { position: absolute; width: 100%; top: 0; left: 0; z-index: -100; }
                #body { margin: 0; min-height: 100%; }
                *, *:after, *:before { pointer-events: auto !important; }
                div[id*="added"]{ clear: both; }
                .partition-background { position: absolute; width: 100%; height: 100%; top: 0; z-index: -10; }
                .partition-wrapper { position: relative; }
                .cf:before,
                .cf:after { content: " "; display: table; }
                .cf:after { clear: both; }
                .cf { *zoom: 1; }
                #genoo_pricing_loading_overlay { display: none !important; }
                .pane img { max-width: 100%; }
                '. $css .'
                </style>
                '. $this->css .'
                '. \WPME\RepositorySettingsFactory::getLandingPagesGlobal('header') .'
                '. $additionalHeader .'
                '. \WPMKTENGINE\Utils\CSS::START . $cssStyles . \WPMKTENGINE\Utils\CSS::END .'
            </head>
            <body id="body" class="'. $this->bodyClass .'" style="'. $this->getBodyStyle() .'">
                <div>'. $this->buffer .'</div>
                '. \WPME\RepositorySettingsFactory::getLandingPagesGlobal('footer') .'
                '. $additionalFooter .'
              ';
                // WP_footer for cta modals
                $this->renderFooterScripts();
                wp_footer();
                if(isset($WPME_FRONTEND)){
                    $WPME_FRONTEND->footerFirst();
                }
                echo $trackingScriptFooter;
            echo '</body>';
        echo '</html>';
    }

    public function cache($html)
    {

    }


    /**
     * Append Inline File
     *
     * @param $what
     * @return mixed
     */
    public function appendInline($what)
    {
        // <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.no-icons.min.css" />
        switch($what){
            case 'bootstrap':
                return file_get_contents(WPMKTENGINE_ASSETS_DIR . 'bootstrap' . DIRECTORY_SEPARATOR . 'bootstrap.min.css');
                break;
            case 'frontend.js':
                return file_get_contents(WPMKTENGINE_ASSETS_DIR . 'GenooFrontend.js');
                break;
            case 'frontend.css':
                return file_get_contents(WPMKTENGINE_ASSETS_DIR . 'GenooFrontend.css');
                break;

        }
        return;
    }
}
