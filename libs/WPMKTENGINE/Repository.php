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

use WPMKTENGINE\Utils\Strings,
    WPMKTENGINE\Wordpress\Utils;


abstract class Repository
{
    /** @var string */
    var $tableName;
    /** @var string */
    var $tableSingleName;


    /**
     * Constructor extracst name of Repository
     */

    public function __construct()
    {
        preg_match('#Repository(\w+)$#', get_class($this), $class);
        $this->tableName = Utils::camelCaseToUnderscore($class[1]);
        $this->tableSingleName = Strings::firstUpper($class[1]);
    }
}