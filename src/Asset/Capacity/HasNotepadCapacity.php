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
use Notepad;
use ReflectionClass;
use Session;

class HasNotepadCapacity extends AbstractCapacity
{
    public function getLabel(): string
    {
        return Notepad::getTypeName(Session::getPluralNumber());
    }

    public function getSearchOptions(string $classname): array
    {
        return Notepad::rawSearchOptionsToAdd($classname);
    }

    public function onClassBootstrap(string $classname): void
    {
        CommonGLPI::registerStandardTab($classname, Notepad::class, 80);
    }

    public function onObjectInstanciation(Asset $object): void
    {
        $reflected_class = new ReflectionClass($object);
        $reflected_property = $reflected_class->getProperty('usenotepad');
        $reflected_property->setValue($object, true);
    }

    public function onCapacityDisabled(string $classname): void
    {
        // Delete related infocom data
        $notepad = new Notepad();
        $notepad->deleteByCriteria(['itemtype' => $classname], force: true, history: false);

        $notepad_search_options = Notepad::rawSearchOptionsToAdd($classname);

        // Clean history related to notepad
        $this->deleteFieldsLogs($classname, $notepad_search_options);

        // Clean display preferences
        $this->deleteDisplayPreferences($classname, $notepad_search_options);

        // Clean rights
        $this->removeRights($classname::$rightname, [READNOTE, UPDATENOTE]);
    }
}
