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

use Computer;
use DbTestCase;
use Glpi\Asset\Capacity\HasDocumentsCapacity;
use Glpi\Asset\Capacity\HasInfocomCapacity;
use Profile;

class AssetDefinition extends DbTestCase
{
    protected function updateInputProvider(): iterable
    {
        yield [
            'input'    => [],
            'output'   => [],
            'messages' => [],
        ];

        // Capacities inputs
        yield [
            'input'    => [
                'capacities' => [
                    HasDocumentsCapacity::class,
                    HasInfocomCapacity::class,
                ],
            ],
            'output'   => [
                'capacities' => json_encode([
                    HasDocumentsCapacity::class,
                    HasInfocomCapacity::class,
                ]),
            ],
            'messages' => [],
        ];

        yield [
            'input'    => [
                'capacities' => [
                    Computer::class, // not a capacity
                    HasInfocomCapacity::class,
                ],
            ],
            'output'   => false,
            'messages' => [
                ERROR => [
                    'The following field has an incorrect value: "Capacities".',
                ],
            ],
        ];

        yield [
            'input'    => [
                'capacities' => 'not a valid capacity input',
            ],
            'output'   => false,
            'messages' => [
                ERROR => [
                    'The following field has an incorrect value: "Capacities".',
                ],
            ],
        ];

        // Profiles input
        $self_service_p_id = getItemByTypeName(Profile::class, 'Self-Service', true);
        $admin_p_id        = getItemByTypeName(Profile::class, 'Admin', true);
        $valid_profiles_input = [
            $self_service_p_id => [
                READ => 1,
                CREATE => 0,
                UPDATE => 0,
                DELETE => 0,
            ],
            $admin_p_id => [
                READ => 1,
                CREATE => 1,
                UPDATE => 1,
                DELETE => 1,
            ],
        ];
        yield [
            'input'    => [
                '_profiles' => $valid_profiles_input,
            ],
            'output'   => [
                '_profiles' => $valid_profiles_input,
            ],
            'messages' => [],
        ];

        yield [
            'input'    => [
                '_profiles' => [
                    999999999 => [ // invalid profile ID
                        READ => 1,
                        CREATE => 0,
                        UPDATE => 0,
                        DELETE => 0,
                    ],
                ],
            ],
            'output'   => false,
            'messages' => [
                ERROR => [
                    'The following field has an incorrect value: "Profiles".',
                ],
            ],
        ];

        yield [
            'input'    => [
                '_profiles' => [
                    $self_service_p_id => [
                        'read' => 1, // invalid right value
                    ],
                ],
            ],
            'output'   => false,
            'messages' => [
                ERROR => [
                    'The following field has an incorrect value: "Profiles".',
                ],
            ],
        ];

        yield [
            'input'    => [
                '_profiles' => [
                    $self_service_p_id => [
                        READ => 'a', // invalid boolean value
                        UPDATE => 0,
                    ],
                ],
            ],
            'output'   => false,
            'messages' => [
                ERROR => [
                    'The following field has an incorrect value: "Profiles".',
                ],
            ],
        ];
    }

    /**
     * @dataProvider updateInputProvider
     */
    public function testPrepareInputForUpdate(array $input, array|false $output, array $messages): void
    {
        $definition = $this->newTestedInstance();

        $this->variable($definition->prepareInputForUpdate($input))->isEqualTo($output);

        foreach ($messages as $level => $level_messages) {
            $this->hasSessionMessages($level, $level_messages);
        }
    }

    protected function addInputProvider(): iterable
    {
        foreach ($this->updateInputProvider() as $data) {
            if (is_array($data['output']) && !array_key_exists('capacities', $data['output'])) {
                $data['output']['capacities'] = '[]';
            }
            yield $data;
        }
    }

    /**
     * @dataProvider addInputProvider
     */
    public function testPrepareInputForAdd(array $input, array|false $output, array $messages): void
    {
        $definition = $this->newTestedInstance();

        $this->variable($definition->prepareInputForAdd($input))->isEqualTo($output);

        foreach ($messages as $level => $level_messages) {
            $this->hasSessionMessages($level, $level_messages);
        }
    }
}
