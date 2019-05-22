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

use WPMKTENGINE\RepositoryThemes;

/**
 * Class TemplateRenderer
 *
 * @package WPME\Extensions
 */
class TemplateRenderer extends \WPMKTENGINE\TemplateRenderer
{
    /**
     * TemplateRenderer constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        // dom
        // suppress warnings of invalid html
        libxml_use_internal_errors(TRUE);
        $this->dom = new \DOMDocument();
        // Buffer
        $this->buffer = $data;
    }


    /**
     * Prepare HTML
     */
    public function prepare()
    {
        // prep
        $this->dom->loadHTML('<?xml encoding="utf-8" ?>' . $this->buffer);
        $this->dom->preserveWhiteSpace = FALSE;
        // Go through forms if needed
        // CTA'S Need a global of \WP_Post to work with
        global $post;
        $postPrep = new \stdClass();
        $postPrep->ID = 1;
        $post = new \WP_Post($postPrep);
        // This is needed for Frontend class to react and append modal windows
        $post->post_type = 'wpme-landing-pages';
        $post->post_content = '';
        // Image sources
        $this->appendGlobalImageSources();
        // Just the body please
        // UTF8 not needed anymore
        $this->buffer = str_replace('<?xml encoding="utf-8" ?>', '', $this->buffer);
        // Remove the silly handles
        $this->buffer = str_replace('class="handle"', '', $this->buffer);
        $this->buffer = $this->appendGlobalImageSourcesReplace($this->buffer);
        $this->buffer = str_replace('src="data/', 'src="'. WPMKTENGINE_BUILDER . 'data/', $this->buffer);
        // Clean errors
        libxml_clear_errors();
        // Remove this please, it's just terrible and causes issue.
        remove_filter('the_content', 'wpautop');
        // Last step, apply WordPress shortcodes etc., only if ctas or forms
        $this->buffer = apply_filters('the_content', $this->buffer);
        $this->buffer = str_replace(']]>', ']]>', $this->buffer);
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
        $GLOBALS['post']->post_content = $this->buffer;
        $GLOBALS['post_shortcodes'] = $shortcodeArray;
    }

    /**
     * Renderer
     *
     * @param string $title
     * @param string $additionalHeader
     * @param string $additionalFooter
     */
    public function render($title = '', $additionalHeader = '', $additionalFooter = '')
    {
        // Get header styles
        $repositoryThemes = new RepositoryThemes();
        $css = $repositoryThemes->getAllThemesStyles();
        $cssStyles = (isset($WPME_STYLES) && !empty($WPME_STYLES)) ? $WPME_STYLES : '';
        // Header
        $header = '
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1" />
                <title>'. $title .'</title>
                <link rel="stylesheet" href="'. WPMKTENGINE_BUILDER . 'stylesheets/render.css" charset="utf-8" />
                <style type="text/css">
                '.  $this->appendInline('bootstrap') .'
                '.  $this->appendInline('frontend.css') .'
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
                .table-handle { display: none !important; }
                .pane img { max-width: 100%; }
                '. $css .'
                </style>
                '. $this->css .'
                '. $additionalHeader .'
                '. \WPMKTENGINE\Utils\CSS::START . $cssStyles . \WPMKTENGINE\Utils\CSS::END .'
                '. \WPME\RepositorySettingsFactory::getLandingPagesGlobal('header') .'
        ';
        // Footer
        $footer = $additionalFooter;
        $footer .= \WPME\RepositorySettingsFactory::getLandingPagesGlobal('footer');
        \WPMKTENGINE\Wordpress\Filter::removeFrom('wp_footer')->everythingThatStartsWith('et_');
        \add_filter('genoo_tracking_in_header', '__return_false');
        // Footer: Buffer
        ob_start();
        wp_footer();
        $this->renderFooterScripts();
        $footerSecond = ob_get_contents();
        ob_end_clean();
        $footer .= $footerSecond;
        // Append
        $this->buffer = str_replace(
            array(
                '<!-- header_data -->',
                '<!-- footer_data -->'
            ),
            array(
                $header,
                $footer
            ),
            $this->buffer
        );
        // Render
        echo $this->buffer;
        die;
    }

}
