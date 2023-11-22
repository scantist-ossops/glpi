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

namespace Glpi\Asset;

use CommonDBTM;
use CommonGLPI;
use Glpi\Application\View\TemplateRenderer;
use Glpi\Asset\Capacity\CapacityInterface;
use Glpi\DBAL\QueryExpression;
use Glpi\DBAL\QueryFunction;
use Profile;
use ProfileRight;
use Session;

final class AssetDefinition extends CommonDBTM
{
    public static $rightname = 'config';

    public static function getTypeName($nb = 0)
    {
        return _n('Asset definition', 'Asset definitions', $nb);
    }

    public static function getIcon()
    {
        return 'ti ti-database-cog';
    }

    public static function canCreate()
    {
        // required due to usage of `config` rightname
        return static::canUpdate();
    }

    public static function canPurge()
    {
        // required due to usage of `config` rightname
        return static::canUpdate();
    }

    public function defineTabs($options = [])
    {
        $tabs = [];

        $this->addDefaultFormTab($tabs);
        $this->addStandardTab(self::class, $tabs, $options);

        return $tabs;
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item instanceof self) {
            return [
                1 => self::createTabEntry(__('Capacities'), 0, self::class, 'ti ti-adjustments'),
                // 2 is reserved for "Fields"
                3 => self::createTabEntry(_n('Profile', 'Profiles', Session::getPluralNumber()), 0, self::class, 'ti ti-user-check'),
            ];
        }

        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item instanceof self) {
            switch ($tabnum) {
                case 1:
                    $item->showCapacitiesForm();
                    break;
                case 2:
                    // 2 is reserved for "Fields" form
                    break;
                case 3:
                    $item->showProfilesForm();
                    break;
            }
        }
        return true;
    }

    public function showForm($ID, array $options = [])
    {
        $this->initForm($ID, $options);
        TemplateRenderer::getInstance()->display('pages/admin/assetdefinition/main.html.twig', [
            'item'   => $this,
            'params' => $options,
        ]);
        return true;
    }

    /**
     * Display capacities form.
     *
     * @return void
     */
    private function showCapacitiesForm(): void
    {
        $capacities = AssetDefinitionManager::getInstance()->getAvailableCapacities();

        TemplateRenderer::getInstance()->display('pages/admin/assetdefinition/capacities.html.twig', [
            'item' => $this,
            'capacities' => $capacities,
        ]);
    }

    /**
     * Display profiles form.
     *
     * @return void
     */
    private function showProfilesForm(): void
    {
        /** @var \DBmysql $DB */
        global $DB;

        $rightname       = $this->getAssetRightname();
        $possible_rights = $this->getPossibleAssetRights();

        $profiles_data   = iterator_to_array(
            $DB->request([
                'SELECT' => ['id', 'name'],
                'FROM'   => Profile::getTable(),
            ])
        );

        $nb_cb_per_col = array_fill_keys(
            array_keys($possible_rights),
            [
                'checked' => 0,
                'total' => count($profiles_data),
            ]
        );
        $nb_cb_per_row = [];

        $matrix_rows = [];
        foreach ($profiles_data as $profile_data) {
            $profile_id = $profile_data['id'];
            $profile_rights = ProfileRight::getProfileRights($profile_id, [$rightname])[$rightname] ?? 0;

            $checkbox_key = sprintf('_profiles[%d]', $profile_id);

            $nb_cb_per_row[$checkbox_key] = [
                'checked' => 0,
                'total' => count($possible_rights),
            ];

            $row = [
                'label' => $profile_data['name'],
                'columns' => []
            ];
            foreach (array_keys($possible_rights) as $right_value) {
                $checked = $profile_rights & $right_value;
                $row['columns'][$right_value] = [
                    'checked' => $checked,
                ];

                if ($checked) {
                    $nb_cb_per_row[$checkbox_key]['checked']++;
                    $nb_cb_per_col[$right_value]['checked']++;
                }
            }
            $matrix_rows[$checkbox_key] = $row;
        }

        TemplateRenderer::getInstance()->display('pages/admin/assetdefinition/profiles.html.twig', [
            'item'           => $this,
            'matrix_columns' => $possible_rights,
            'matrix_rows'    => $matrix_rows,
            'nb_cb_per_col'  => $nb_cb_per_col,
            'nb_cb_per_row'  => $nb_cb_per_row,
        ]);
    }

    public function prepareInputForAdd($input)
    {
        if (!array_key_exists('capacities', $input)) {
            $input['capacities'] = []; // ensure default capacities value will be a valid array
        }
        return $this->prepareInput($input);
    }

    public function prepareInputForUpdate($input)
    {
        return $this->prepareInput($input);
    }

    /**
     * Prepare common input for and an update.
     *
     * @param array $input
     * @return array|bool
     */
    private function prepareInput(array $input): array|bool
    {
        /** @var \DBmysql $DB */
        global $DB;

        if (array_key_exists('capacities', $input)) {
            if (!$this->validateCapacityArray($input['capacities'])) {
                Session::addMessageAfterRedirect(
                    sprintf(
                        __('The following field has an incorrect value: "%s".'),
                        __('Capacities')
                    ),
                    false,
                    ERROR
                );
                return false;
            }
            $input['capacities'] = json_encode($input['capacities']);
        }

        if (array_key_exists('_profiles', $input)) {
            $is_valid = true;
            if (!is_array($input['_profiles'])) {
                $is_valid = false;
            } else {
                $profiles_iterator = $DB->request([
                    'SELECT' => ['id'],
                    'FROM'   => Profile::getTable(),
                ]);
                $available_profiles = array_column(iterator_to_array($profiles_iterator), 'id');
                foreach ($input['_profiles'] as $profile_id => $rights) {
                    if (!in_array($profile_id, $available_profiles)) {
                        $is_valid = false;
                        break;
                    }
                    foreach ($rights as $right_value => $is_enabled) {
                        if (
                            !filter_var($right_value, FILTER_VALIDATE_INT)
                            || filter_var($is_enabled, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) === null
                        ) {
                            $is_valid = false;
                            break;
                        }
                    }
                }
            }
            if (!$is_valid) {
                Session::addMessageAfterRedirect(
                    sprintf(
                        __('The following field has an incorrect value: "%s".'),
                        _n('Profile', 'Profiles', Session::getPluralNumber())
                    ),
                    false,
                    ERROR
                );
                return false;
            }
        }

        return $input;
    }

    public function post_updateItem($history = true)
    {
        if (in_array('capacities', $this->updates)) {
            // When capabilities are removed, trigger the cleaning of data related to this capacity.
            $new_capacities = @json_decode($this->fields['capacities']);
            $old_capacities = @json_decode($this->oldvalues['capacities']);

            if (!is_array($new_capacities)) {
                // should not happen, do not trigger cleaning to prevent unexpected mass deletion of data
                trigger_error(sprintf('Invalid `capacities` value `%s`.', $this->fields['capacities']), E_USER_WARNING);
                return;
            }
            if (!is_array($old_capacities)) {
                // should not happen, do not trigger cleaning to prevent unexpected mass deletion of data
                trigger_error(sprintf('Invalid `capacities` value `%s`.', $this->oldvalues['capacities']), E_USER_WARNING);
                return;
            }

            $removed_capacities = array_diff($old_capacities, $new_capacities);
            foreach ($removed_capacities as $capacity_classname) {
                $capacity = AssetDefinitionManager::getInstance()->getCapacity($capacity_classname);
                if ($capacity === null) {
                    // can be null if provided by a plugin that is no longer active
                    continue;
                }
                $capacity->onCapacityDisabled($this->getConcreteClassName());
            }
        }

        if (array_key_exists('_profiles', $this->input)) {
            $rightname = $this->getAssetRightname();

            foreach ($this->input['_profiles'] as $profile_id => $right_inputs) {
                $rights = 0;
                foreach ($right_inputs as $right_value => $is_enabled) {
                    if ($is_enabled) {
                        $rights += (int)$right_value;
                    }
                }

                ProfileRight::updateProfileRights((int)$profile_id, [$rightname => $rights]);
            }
        }
    }

    /**
     * Return translated name.
     *
     * @param int $count
     * @return string
     */
    public function getTranslatedName(int $count = 1): string
    {
        // TODO Return translated plural form.
        return $this->fields['name'];
    }

    /**
     * Return icon to use for assets.
     *
     * @return string
     */
    public function getAssetsIcon(): string
    {
        return $this->fields['icon'] ?: 'ti ti-box';
    }

    public function rawSearchOptions()
    {
        $search_options = parent::rawSearchOptions();

        $i = 1000;
        $search_options[] = [
            'id'   => 'capacities',
            'name' => __('Capacities')
        ];
        foreach (AssetDefinitionManager::getInstance()->getAvailableCapacities() as $capacity) {
            $i++;

            // capacity is stored in a JSON array, so entry is surrounded by double quotes
            $search_string = json_encode($capacity::class);
            // Backslashes must be doubled in LIKE clause, according to MySQL documentation:
            // > To search for \, specify it as \\\\; this is because the backslashes are stripped
            // > once by the parser and again when the pattern match is made,
            // > leaving a single backslash to be matched against.
            $search_string = str_replace('\\', '\\\\', $search_string);

            $search_options[] = [
                'id'            => $i,
                'table'         => self::getTable(),
                'field'         => sprintf('_capacities_%s', $capacity::class),
                'name'          => $capacity->getLabel(),
                'computation'   => QueryFunction::if(
                    condition: ['capacities' => ['LIKE', '%' . $search_string . '%']],
                    true_expression: new QueryExpression('1'),
                    false_expression: new QueryExpression('0')
                ),
                'datatype'      => 'bool'
            ];
        }

        return $search_options;
    }

    /**
     * Get the definition's concrete asset class name.
     *
     * @param bool $with_namespace
     * @return string|null
     */
    public function getConcreteClassName(bool $with_namespace = true): string
    {
        return sprintf(
            ($with_namespace ? 'Glpi\\Asset\\' : '') . 'Asset%s',
            $this->getID()
        );
    }

    /**
     * Get the definition's concrete asset rightname.
     *
     * @param AssetDefinition $definition
     * @return string
     */
    public function getAssetRightname(): string
    {
        return sprintf('asset_%d', $this->getID());
    }

    /**
     * Indicates whether the given capacity is enabled.
     *
     * @param CapacityInterface $capacity
     * @return bool
     */
    public function hasCapacityEnabled(CapacityInterface $capacity): bool
    {
        $enabled_capacities = $this->getDecodedJsonField('capacities', [$this, 'validateCapacityArray']);
        return in_array($capacity::class, $enabled_capacities);
    }

    /**
     * Get the list of enabled capacities.
     *
     * @return CapacityInterface[]
     */
    public function getEnabledCapacities(): array
    {
        $capacities = [];
        foreach (AssetDefinitionManager::getInstance()->getAvailableCapacities() as $capacity) {
            if ($this->hasCapacityEnabled($capacity)) {
                $capacities[] = $capacity;
            }
        }
        return $capacities;
    }

    /**
     * Get the list of possible rights for the assets.
     * @return array
     */
    private function getPossibleAssetRights(): array
    {
        $class = $this->getConcreteClassName();
        $object = new $class();
        return $object->getRights();
    }

    /**
     * Return the decoded value of a JSON field.
     *
     * @param string $field_name
     * @param callable $validator
     * @return array
     */
    private function getDecodedJsonField(string $field_name, ?callable $validator = null): array
    {
        $values = @json_decode($this->fields[$field_name], associative: true);
        if ($validator !== null && !call_user_func($validator, $values)) {
            trigger_error(sprintf('Invalid `%s` value (`%s`).', $field_name, $this->fields[$field_name]), E_USER_WARNING);
            $this->fields[$field_name] = '[]'; // prevent warning to be triggered on each method call
            $values = [];
        }
        return $values;
    }

    /**
     * Validate that the given capacities array contains valid values.
     *
     * @param mixed $capacities
     * @return bool
     */
    private function validateCapacityArray(mixed $capacities): bool
    {
        if (!is_array($capacities)) {
            return false;
        }

        $is_valid = true;

        $available_capacities = array_map(
            fn ($capacity) => $capacity::class,
            AssetDefinitionManager::getInstance()->getAvailableCapacities()
        );
        foreach ($capacities as $classname) {
            if (!in_array($classname, $available_capacities)) {
                $is_valid = false;
                break;
            }
        }

        return $is_valid;
    }
}
