<?php
/**
 * Kunena Component
 *
 * @package        Kunena.Installer
 *
 * @copyright      Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license        https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link           https://www.kunena.org
 **/
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// Kunena 1.6.0: Migrate polls data from K1.5
/**
 * @param   string  $parent parent
 *
 * @return  array
 *
 * @since   Kunena 6.0
 *
 * @throws  KunenaInstallerException
 */
function kunena_160_2010_05_30_polls($parent)
{
	$db = Factory::getDbo();

	$upgraded = false;

	// Convert all old polls tables to new structure
	$tablelist = $db->getTableList();

	foreach ($tablelist as $table)
	{
		if ($table == $db->getPrefix() . 'kunena_polls')
		{
			$fields = $db->getTableColumns($db->getPrefix() . 'kunena_polls');

			if (isset($fields ['catid']) && isset($fields ['polltimetolive']))
			{
				$query = "ALTER TABLE `{$db->quoteName($db->getPrefix() . 'kunena_polls')}` DROP COLUMN catid, MODIFY title varchar(50)";
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $e)
				{
					throw new KunenaInstallerException($e->getMessage(), $e->getCode());
				}

				$upgraded = true;
			}

			if (isset($fields ['catid']) && !isset($fields ['polltimetolive']))
			{
				$query = "ALTER TABLE `{$db->quoteName($db->getPrefix() . 'kunena_polls')}` DROP COLUMN catid, MODIFY title varchar(50),ADD `polltimetolive` timestamp";
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $e)
				{
					throw new KunenaInstallerException($e->getMessage(), $e->getCode());
				}

				$upgraded = true;
			}

			if (isset($fields ['topicid']) && isset($fields ['voters']) && isset($fields ['options']))
			{
				$query = "ALTER TABLE `{$db->quoteName($db->getPrefix() . 'kunena_polls')}` DROP COLUMN voters, DROP COLUMN options,CHANGE topicid threadid int(11), ADD polltimetolive timestamp";
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $e)
				{
					throw new KunenaInstallerException($e->getMessage(), $e->getCode());
				}

				$upgraded = true;
			}
		}

		if ($table == $db->getPrefix() . 'kunena_polls_datas')
		{
			$query = "DROP TABLE `{$db->quoteName($db->getPrefix() . 'kunena_polls_options')}`";
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				throw new KunenaInstallerException($e->getMessage(), $e->getCode());
			}

			$query = "ALTER TABLE `{$db->quoteName($db->getPrefix() . 'kunena_polls_datas')}` MODIFY `id` int(11) AUTO_INCREMENT, MODIFY `text` varchar(100), CHANGE `hits` `votes` int(11)";
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				throw new KunenaInstallerException($e->getMessage(), $e->getCode());
			}

			$query = "ALTER TABLE `{$db->quoteName($db->getPrefix() . 'kunena_polls_users')}` DROP COLUMN `id`, ADD `votes` int(11), ADD `lasttime` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, ADD `lastvote` int(11), ADD UNIQUE KEY `pollid` (pollid,userid)";
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				throw new KunenaInstallerException($e->getMessage(), $e->getCode());
			}

			$query = "ALTER TABLE `{$db->quoteName($db->getPrefix() . 'kunena_polls_datas')}` RENAME TO `{$db->quoteName($db->getPrefix() . 'kunena_polls_options')}`";
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				throw new KunenaInstallerException($e->getMessage(), $e->getCode());
			}

			$upgraded = true;
		}

		if ($table == $db->getPrefix() . 'kunena_polls_options')
		{
			$fields = $db->getTableColumns($db->getPrefix() . 'kunena_polls_options');
			$query  = "ALTER TABLE `{$db->quoteName($db->getPrefix() . 'kunena_polls_options')}` MODIFY text varchar(50)";
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				throw new KunenaInstallerException($e->getMessage(), $e->getCode());
			}
		}

		if ($table == $db->getPrefix() . 'kunena_polls_users')
		{
			$fields = $db->getTableColumns($db->getPrefix() . 'kunena_polls_users');

			if (!isset($fields ['id']) && !isset($fields ['lastvote']))
			{
				$query = "ALTER TABLE `{$db->quoteName($db->getPrefix() . 'kunena_polls_users')}` MODIFY votes int(11), ADD lastvote int(11)";
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (Exception $e)
				{
					throw new KunenaInstallerException($e->getMessage(), $e->getCode());
				}

				$upgraded = true;
			}
		}
	}

	if ($upgraded)
	{
		return ['action' => '', 'name' => Text::_('COM_KUNENA_INSTALL_160_POLLS'), 'success' => true];
	}

	return;
}
