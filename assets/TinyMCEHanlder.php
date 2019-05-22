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
 * Class TinyMCEHanlder
 */
abstract class TinyMCEHanlder
{
    /** @var string */
    public $shortcode;
    /** @var string */
    public $url;
    /** @var string */
    public $edit;
    /** @var string */
    public $selected;
    /** @var string */
    public $version;
    /** @var string */
    public $title = '';
    /** @var string */
    public $name = '';
    /** @var string */
    public $visible;
    /** @var string */
    public $refresh;
    /** @var array|null */
    public $postypes;
    /** @var array */
    public $ctas;
    /** @var array */
    public $forms;
    /** @var array */
    public $themes;
    /** @var \Genoo\RepositorySettings */
    public $repositarySettings;
    /** @var \Genoo\RepositoryForms */
    public $repositaryForms;
    /** @var \Genoo\RepositoryCTA */
    public $repositaryCTAs;



    /**
     * @param $shortcode
     */
    public function __construct($shortcode)
    {
        // Construct first vars
        $urlPrep = "http" . (($_SERVER['SERVER_PORT']==443) ? "s://" : "://") . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $this->url = substr($urlPrep, 0, strpos($urlPrep , "wp-content"));
        $this->shortcode = $shortcode;
        // Set values
        $this->repositarySettings = new \Genoo\RepositorySettings();
        $api = new \Genoo\Api($this->repositarySettings);
        $cache = new \Genoo\Cache(GENOO_CACHE);
        $this->repositaryForms = new \Genoo\RepositoryForms($cache, $api);
        $this->repositaryCTAs = new \Genoo\RepositoryCTA($cache);
        $this->themes = $this->repositarySettings->getSettingsThemes();
        $this->forms = $this->repositaryForms->getFormsArray();
        $this->ctas = $this->repositaryCTAs->getArray();
        $this->postypes = $this->repositarySettings->getCTAPostTypes();
        if(GENOO_LUMENS){
            $this->repositaryLumens = new \Genoo\RepositoryLumens($cache, $api);
            $this->lumens = $this->repositaryLumens->getLumensArray();
        }
        // Resovle
        $this->resolve();
        // Render
        $this->render();
    }


    /**
     * Resolve GET parameters
     */
    public function resolve()
    {
        $this->edit = (isset($_GET['edit']) && $_GET['edit'] == '1') ? true : false;
        $this->selected = (isset($_GET['selected']) ? self::parseAtts($_GET['selected']) : array());
        $this->selectedRaw = (isset($_GET['selected']) ? $_GET['selected'] : array());
        $this->version = isset($_GET['version']) ? $_GET['version'] : 4;
        $this->title = $this->edit ? 'Update' : 'Insert';
        $this->visible = isset($this->selected['msgsuccess']) && isset($this->selected['msgfail']) ? true : false;
        $this->refresh = isset($_GET['commandRefresh']) ? $_GET['commandRefresh'] : null;
        $this->resolveSecond();
    }


    /**
     * Parse shortcode attributes
     *
     * @param $text
     * @return array|string
     */
    public static function parseAtts($text)
    {
        // We now include the file, so we can use WordPress inner Parse Attributes
        $text = str_replace("\'", "'", $text);
        $atts = shortcode_parse_atts($text);
        return $atts;
    }


    /**
     * Shortcode regex
     *
     * @return string
     */
    public function shortcodeRegex()
    {
        // Tag regex
        $tagregexp = join('|', array_map('preg_quote', $this->shortcode));
        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        return
            '\\['                              // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            .     '(?:'
            .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
            .     ')*?'
            . ')'
            . '(?:'
            .     '(\\/)'                        // 4: Self closing tag ...
            .     '\\]'                          // ... and closing bracket
            . '|'
            .     '\\]'                          // Closing bracket
            .     '(?:'
            .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            .             '[^\\[]*+'             // Not an opening bracket
            .             '(?:'
            .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            .                 '[^\\[]*+'         // Not an opening bracket
            .             ')*+'
            .         ')'
            .         '\\[\\/\\2\\]'             // Closing shortcode tag
            .     ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }


    /**
     * The main renderer, decides version at some points
     * and renders accordingly. Inner parts are rendered
     * from extending classes.
     */
    public function render()
    {
        ?>
        <!DOCTYPE html>
        <head>
            <title><?php echo $this->title; ?> <?php echo $this->name; ?></title>
            <script type="text/javascript" src="<?php echo GENOO_HOME_URL; ?>/wp-includes/js/jquery/jquery.js"></script>
            <script type="text/javascript" src="<?php echo GENOO_HOME_URL; ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
            <style type="text/css">
                html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{display:block}body{line-height:1}html,body{overflow-x:hidden}ol,ul{list-style:none}blockquote,q{quotes:none}blockquote:before,blockquote:after,q:before,q:after{content:'';content:none}table{border-collapse:collapse;border-spacing:0}*:focus{outline:0}textarea{resize:none !important}input[type="search"]::-webkit-search-decoration,input[type="search"]::-webkit-search-cancel-button{display:none}input[type="search"]:focus,input[type="text"]:focus{cursor:text}img{border:0;-ms-interpolation-mode:bicubic;vertical-align:middle}label{cursor:pointer}input:invalid,input:-moz-ui-invalid{border:0 !important;outline:0;box-shadow:none;-moz-box-shadow:none;-webkit-box-shadow:none}input[type="text"],button,select,select option,textarea{-moz-box-sizing:border-box;-webkit-box-sizing:border-box;-o-box-sizing:border-box;-ms-box-sizing:border-box;box-sizing:border-box;background:0;border:0;outline:0;box-shadow:none;-moz-box-shadow:none;-webkit-box-shadow:none;font:inherit;cursor:pointer;font-family:inherit;font-weight:normal;font-size:inherit;}select::-ms-expand{display:none !important}input[type="checkbox"]{-webkit-appearance:checkbox;-moz-appearance:checkbox;appearance:checkbox}input[type="submit"]:hover{cursor:pointer}
                a:active{ background-color: transparent; }
                body
                {
                    background: <?php echo $this->version >= 4 ? '#fff' : '#f1f1f1'; ?>;
                    font-family: 'Helvetice', 'Arial', 'Tahoma', sans-serif;
                    font-size: 13px;
                    line-height: 1.2em;
                    padding: <?php echo $this->version >= 4 ? '16px' : '10px 0'; ?>;
                }
                .submit { display: inline-block; float: right; }
                .submit
                {
                    background: #2ea2cc;
                    background: -webkit-gradient(linear, left top, left bottom, from(#2ea2cc), to(#1e8cbe));
                    background: -webkit-linear-gradient(top, #2ea2cc 0%,#1e8cbe 100%);
                    background: linear-gradient(top, #2ea2cc 0%,#1e8cbe 100%);
                    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#2ea2cc', endColorstr='#1e8cbe',GradientType=0 );
                    border-color: #0074a2;
                    -webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,0.5);
                    box-shadow: inset 0 1px 0 rgba(120,200,230,0.5);
                    color: #fff !important;
                    text-decoration: none;
                    text-shadow: 0 1px 0 rgba(0,86,132,0.7);
                    padding: 0 10px 1px;
                    font-size: 13px;
                    height: 24px;
                    line-height: 26px;
                    cursor: pointer;
                    -webkit-border-radius: 3px;
                    -webkit-appearance: none;
                    border-radius: 3px;
                    white-space: nowrap;
                    border: 1px solid #adadad;
                    font-weight: bold;
                    margin-top: 15px;
                }
                input.text,
                select
                {
                    padding: 6px 4px;
                    margin: 10px 0;
                    border: 1px solid #b5b5b5;
                    background: #fafafa;
                    -webkit-border-radius: 3px;
                    border-radius: 3px;
                    width: 99%;
                }
                .hidden { display: none; }
            </style>
            <script type="text/javascript">

                /**
                 * Toggle Class
                 *
                 * @param el
                 * @param className
                 */

                function toggleClass(el, className)
                {
                    if (el.classList) {
                        el.classList.toggle(className);
                    } else {
                        var classes = el.className.split(' ');
                        var existingIndex = -1;
                        for (var i = classes.length; i--;) {
                            if (classes[i] === className)
                                existingIndex = i;
                        }
                        if (existingIndex >= 0)
                            classes.splice(existingIndex, 1);
                        else
                            classes.push(className);
                        el.className = classes.join(' ');
                    }
                }

                /**
                 * Checked?
                 *
                 * @param that
                 */

                function checkChecked(that)
                {
                    var element = document.getElementById('formHidden');
                    toggleClass(element, 'hidden');
                }


                /**
                 * Add Slashes
                 *
                 * @param str
                 * @returns {string}
                 */

                function addSlashes(str)
                {
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&quot;');
                }


                /**
                 * Main function handeling the
                 * pop up and insertion of shortcode
                 *
                 * @type {{e: string, init: Function, insert: Function}}
                 */

                var Popup = {
                    e: '',
                    init: function(e){
                        Popup.e = e;
                        tinyMCEPopup.resizeToInnerSize();

                    },
                    data: function(){
                        return tinyMCEPopup.getWindowArg('query');
                    },
                    insert: function createShortcode(e){
                        <?php $this->renderJavascript(); ?>
                    }
                };

                // On Init
                tinyMCEPopup.onInit.add(Popup.init, Popup);
            </script>

        </head>
        <html>
        <body>
        <form id="formShortcode">
            <?php $this->renderForm(); ?>
            <p><a class="submit" href="javascript:Popup.insert(Popup.e)"><?php echo $this->title; ?></a></p>
        </form>
        </body>
        </html>
    <?php
    }

    /**
     * Renders Javasript function to insert a shortcode,
     * this will be overwritten by each extending class.
     */
    public function renderJavascript(){}


    /**
     * Renders the form, again this will be replaced by
     * the extending class.
     */
    public function renderForm(){}


    /**
     * To be overwritten by extending class.
     */
    public function resolveSecond(){}
}
