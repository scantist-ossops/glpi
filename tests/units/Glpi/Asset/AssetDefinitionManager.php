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

namespace tests\units\Glpi\Asset;

use Glpi\Asset\AssetDefinition;

class AssetDefinitionManager extends \GLPITestCase
{
    public function testLoadConcreteClass(): void
    {
        // use a loop to simulate multiple classes
        $mapping = [];
        for ($i = 0; $i < 5; $i++) {
            $definition = new AssetDefinition();
            $definition->fields = [
                'id'    => mt_rand(),
                'name'  => __METHOD__ . $i,
            ];

            $expected_classname = 'Glpi\\Asset\\Asset' . $definition->fields['id'];

            $instance = \Glpi\Asset\AssetDefinitionManager::getInstance();
            $this->callPrivateMethod($instance, 'loadConcreteClass', $definition);

            $mapping[$expected_classname] = $definition;
        }

        foreach ($mapping as $expected_classname => $definition) {
            $this->boolean(class_exists($expected_classname))->isTrue();
            $this->object($expected_classname::getDefinition())->isEqualTo($definition);
        }
    }
}