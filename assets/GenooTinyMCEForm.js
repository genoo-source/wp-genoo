/**
 * This file is part of the Genoo plugin.
 *
 * Copyright (c) 2014 Genoo, LLC (http://www.genoo.com/)
 *
 * For the full copyright and license information, please view
 * the Genoo.php file in root directory of this plugin.
 *
 * Genoo TinyMCE plugin - Form
 *
 * @version 1
 * @author latorante.name
 */

(function(){
    var forms = new GenooTinyMCE.addPlugin(
        tinymce.majorVersion + '.' + tinymce.minorVersion,
        'GenooTinyMCEForm.php',
        'genooForm',
        'bgTinyMCE.png?v=2',
        'Add a default Genoo Form',
        false,  // Aligned?
        'Are you sure? Please confirm to delete the form.',
        {
            height: 250
        }
    );
})();

