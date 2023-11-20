<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2023 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

namespace Glpi\Asset\Capacity;

use CommonGLPI;
use Glpi\Asset\Asset;
use Log;

class HasHistoryCapacity extends AbstractCapacity
{
    public function getLabel(): string
    {
        return Log::getTypeName();
    }

    public function onClassBootstrap(string $classname): void
    {
        CommonGLPI::registerStandardTab($classname, Log::class, PHP_INT_MAX); // PHP_INT_MAX to ensure that tab is always the latest
    }

    public function onObjectInstanciation(Asset $object): void
    {
        $object->dohistory = true;
    }

    public function onCapacityDisabled(string $classname): void
    {
        /** @var \DBmysql $DB */
        global $DB;

        // Do not use `CommonDBTM::deleteByCriteria()` to prevent performances issues
        $DB->delete(Log::getTable(), ['itemtype' => $classname]);
    }
}
