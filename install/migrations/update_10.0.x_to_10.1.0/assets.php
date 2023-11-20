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

/**
 * @var DB $DB
 * @var Migration $migration
 */

$default_charset = DBConnection::getDefaultCharset();
$default_collation = DBConnection::getDefaultCollation();
$default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

if (!$DB->tableExists('glpi_assets_assetdefinitions')) {
    $query = <<<SQL
        CREATE TABLE `glpi_assets_assetdefinitions` (
            `id` int {$default_key_sign} NOT NULL AUTO_INCREMENT,
            `name` varchar(255) DEFAULT NULL,
            `icon` varchar(255) DEFAULT NULL,
            `comment` text,
            `is_active` tinyint NOT NULL DEFAULT '0',
            `capacities` JSON,
            `date_creation` timestamp NULL DEFAULT NULL,
            `date_mod` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `name` (`name`),
            KEY `is_active` (`is_active`),
            KEY `date_creation` (`date_creation`),
            KEY `date_mod` (`date_mod`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;
SQL;
    $DB->doQueryOrDie($query);
}

if (!$DB->tableExists('glpi_assets_assets')) {
    $query = <<<SQL
        CREATE TABLE `glpi_assets_assets` (
            `id` int {$default_key_sign} NOT NULL AUTO_INCREMENT,
            `assets_assetdefinitions_id` int {$default_key_sign} NOT NULL,
            `name` varchar(255) DEFAULT NULL,
            `comment` text,
            `serial` varchar(255) DEFAULT NULL,
            `otherserial` varchar(255) DEFAULT NULL,
            `contact` varchar(255) DEFAULT NULL,
            `contact_num` varchar(255) DEFAULT NULL,
            `users_id` int {$default_key_sign} NOT NULL DEFAULT '0',
            `groups_id` int {$default_key_sign} NOT NULL DEFAULT '0',
            `users_id_tech` int {$default_key_sign} NOT NULL DEFAULT '0',
            `groups_id_tech` int {$default_key_sign} NOT NULL DEFAULT '0',
            `locations_id` int {$default_key_sign} NOT NULL DEFAULT '0',
            `manufacturers_id` int {$default_key_sign} NOT NULL DEFAULT '0',
            `states_id` int {$default_key_sign} NOT NULL DEFAULT '0',
            `entities_id` int {$default_key_sign} NOT NULL DEFAULT '0',
            `is_recursive` tinyint NOT NULL DEFAULT '0',
            `is_deleted` tinyint NOT NULL DEFAULT '0',
            `date_creation` timestamp NULL DEFAULT NULL,
            `date_mod` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `assets_assetdefinitions_id` (`assets_assetdefinitions_id`),
            KEY `name` (`name`),
            KEY `users_id` (`users_id`),
            KEY `groups_id` (`groups_id`),
            KEY `users_id_tech` (`users_id_tech`),
            KEY `groups_id_tech` (`groups_id_tech`),
            KEY `locations_id` (`locations_id`),
            KEY `manufacturers_id` (`manufacturers_id`),
            KEY `states_id` (`states_id`),
            KEY `entities_id` (`entities_id`),
            KEY `is_recursive` (`is_recursive`),
            KEY `is_deleted` (`is_deleted`),
            KEY `date_creation` (`date_creation`),
            KEY `date_mod` (`date_mod`)
        ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;
SQL;
    $DB->doQueryOrDie($query);
}
