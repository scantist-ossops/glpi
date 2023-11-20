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

use Glpi\Asset\Asset;

interface CapacityInterface
{
    /**
     * Get the capacity label.
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Method executed during asset classes bootstraping.
     *
     * @param string $classname
     * @return void
     */
    public function onClassBootstrap(string $classname): void;

    /**
     * Method executed when capacity is disabled on given asset class.
     *
     * @param string $classname
     * @return void
     */
    public function onCapacityDisabled(string $classname): void;

    /**
     * Method executed during creation of an object instance (i.e. during `__construct()` method execution).
     *
     * @param Asset $object
     * @return void
     */
    public function onObjectInstanciation(Asset $object): void;
}
