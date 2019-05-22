/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 *
 * Genoo TinyMCE plugin - CTA
 *
 * @version 1
 * @author latorante.name
 */

(function(){
    var ctas = new GenooTinyMCE.addPlugin(
        tinymce.majorVersion + '.' + tinymce.minorVersion,
        'GenooTinyMCECTA.php',
        'genooCTA',
        'bgTinyMCECTA.png?v=2',
        'Genoo CTA',
        true,  // Aligned?
        'Are you sure? Please confirm to delete the cta.'
    );
})();