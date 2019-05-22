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

/**
 * This small lib is only for dev puproses.
 */

namespace WPMKTENGINE\Wordpress;

use WPMKTENGINE\Utils\Strings,
    WPMKTENGINE\Wordpress\Post;

class Dummy
{

    /**
     * Dummy generated comments for pst
     *
     * @param $postId
     * @param int $count
     */

    public static function commentsForPost($postId, $count = 200)
    {
        if(Post::exists($postId)){
            for ($i = 1; $i <= $count; $i++){
                wp_insert_comment(array(
                    'comment_post_ID'		=> $postId,
                    'comment_author'		=> Strings::random(3, 'a-z') . $i . ' ' . $i . uniqid(null, true),
                    'comment_author_email'	=> Strings::random(5, 'a-z') . $i . uniqid(null, true) . '@' . $i . Strings::random(4,'a-z') . '.com',
                    'comment_auhor_url'		=> '',
                    'comment_content'		=> Strings::random(25,'a-z'),
                    'comment_type'			=> '',
                    'comment_date'			=> current_time('mysql'),
                    'comment_approved'		=> '1'
                ));
            }
        }
    }
}