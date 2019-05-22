/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 *
 * Genoo TinyMCE plugin - Lumens
 *
 * @version 1
 * @author latorante.name
 */

(function(){
    var lumens = new GenooTinyMCE.addPlugin(
        tinymce.majorVersion + '.' + tinymce.minorVersion,
        'GenooTinyMCELumens.php',
        'genooLumens',
        'bgTinyMCELumens.png?v=2',
        'Genoo Lumens',
        false,  // Aligned?
        'Are you sure? Please confirm to delete the Lumen classlist.',
        {
            height: 180
        }
    );
})();