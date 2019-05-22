<?php
/**
 * This file is part of the WPMKTGENGINE plugin.
 *
 * Copyright (c) 2014 WPMKTGENGINE, LLC (http://wpmktgengine.com/)
 *
 * For the full copyright and license information, please view
 * the WPMKTENGINE.php file in root directory of this plugin.
 */

namespace WPMKTENGINE\Wordpress\UI;


class PostLike
{
    /** @var string */
    var $title = '';


    /**
     * Render
     */

    public function render(){ echo self::__toString(); }


    public function __toString()
    {
        return '
            <h2>Add New Thing</h2>
            <form action="" method="POST">

            </form>
        ';
    }
}