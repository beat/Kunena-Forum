<?php
/**
 * Kunena Component
 *
 * @package       Kunena.Framework
 * @subpackage    Tables
 *
 * @copyright     Copyright (C) 2008 - 2021 Kunena Team. All rights reserved.
 * @license       https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link          https://www.kunena.org
 **/

namespace Kunena\Forum\Libraries\Tables;

use Exception;
use InvalidArgumentException;
use Joomla\CMS\Table\Table;
use Kunena\Forum\Libraries\Error\KunenaError;
use Kunena\Forum\Libraries\Exception\KunenaException;
use RuntimeException;
use UnexpectedValueException;
use function defined;

defined('_JEXEC') or die();

/**
 * Class KunenaTable
 *
 * @since   Kunena 6.0
 */
abstract class KunenaTable extends Table
{
	/**
	 * @var     boolean
	 * @since   Kunena 6.0
	 */
	protected $_exists = false;

	/**
	 * @param   null  $keys   keys
	 * @param   bool  $reset  reset
	 *
	 * @return  boolean
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function load($keys = null, $reset = true): bool
	{
		// Implement JObservableInterface: Pre-processing by observers
		// TODO: remove if when we're only supporting J!3.5+.
		if (isset($this->_observers))
		{
			$this->_observers->update('onBeforeLoad', [$keys, $reset]);
		}

		// Workaround Joomla 3.2 change.
		// TODO: remove check when we're only supporting J!3.5+.
		$tbl_keys = isset($this->_tbl_keys) ? $this->_tbl_keys : (array) $this->_tbl_key;

		if (empty($keys))
		{
			$empty = true;
			$keys  = [];

			// If empty, use the value of the current key
			foreach ($tbl_keys as $key)
			{
				$empty      = $empty && empty($this->$key);
				$keys[$key] = $this->$key;
			}

			// If empty primary key there's is no need to load anything
			if ($empty)
			{
				return false;
			}
		}
		elseif (!is_array($keys))
		{
			// Load by primary key.
			$keyCount = count($tbl_keys);

			if (!$keyCount)
			{
				throw new RuntimeException('No table keys defined.');
			}

			if ($keyCount > 1)
			{
				throw new InvalidArgumentException('Table has multiple primary keys specified, only one primary key value provided.');
			}

			$keys = [$this->getKeyName() => $keys];
		}

		if ($reset)
		{
			$this->reset();
		}

		try
		{
			$this->_db->transactionStart();

			// Initialise the query.
			$query  = $this->_db->getQuery(true)
				->select('*')
				->from($this->_db->quoteName($this->_tbl));
			$fields = array_keys($this->getProperties());

			foreach ($keys as $field => $value)
			{
				// Check that $field is in the table.
				if (!in_array($field, $fields))
				{
					throw new UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.', get_class($this), $field));
				}

				// Add the search tuple to the query.
				$query->Where($this->_db->quoteName($field) . ' = ' . $this->_db->quote($value));
			}

			$this->_db->setQuery($query);

			$row = $this->_db->loadAssoc();

			$this->_db->transactionCommit();
		}
		catch (Exception $e)
		{
			// Catch any database errors.
			$this->_db->transactionRollback();

			KunenaError::displayDatabaseError($e);

			return false;
		}

		if (empty($row))
		{
			// Check that we have a result.
			$result = false;
		}
		else
		{
			// Bind the object with the row and return.
			$result = $this->_exists = $this->bind($row);
		}

		// Implement JObservableInterface: Post-processing by observers
		// TODO: remove if when we're only supporting J!3.5+.
		if (isset($this->_observers))
		{
			$this->_observers->update('onAfterLoad', [&$result, $row]);
		}

		// Bind the object with the row and return.
		return $result;
	}

	/**
	 * @param   bool  $updateNulls  update
	 *
	 * @return  boolean
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function store($updateNulls = false): bool
	{
		// Workaround Joomla 3.2 change.
		// TODO: remove check when we're only supporting J!3.5+.
		$k = isset($this->_tbl_keys) ? $this->_tbl_keys : (array) $this->_tbl_key;

		// Implement JObservableInterface: Pre-processing by observers
		// TODO: remove if when we're only supporting J!3.5+.
		if (isset($this->_observers))
		{
			$this->_observers->update('onBeforeStore', [$updateNulls, $k]);
		}

		if ($this->exists())
		{
			try
			{
				$result = $this->updateObject($updateNulls);
			}
			catch (Exception $e)
			{
				throw new KunenaException($e->getMessage());
			}
		}
		else
		{
			try
			{
				$result = $this->insertObject();
			}
			catch (Exception $e)
			{
				throw new KunenaException($e->getMessage());
			}
		}

		if (!$result)
		{
			return false;
		}

		$this->_exists = true;

		// Implement JObservableInterface: Post-processing by observers
		// TODO: remove if when we're only supporting J!3.5+.
		if (isset($this->_observers))
		{
			$this->_observers->update('onAfterStore', [&$result]);
		}

		return true;
	}

	/**
	 * @param   null  $exists  exists
	 *
	 * @return  boolean
	 *
	 * @since   Kunena 6.0
	 */
	public function exists($exists = null): bool
	{
		$return = $this->_exists;

		if ($exists !== null)
		{
			$this->_exists = $exists;
		}

		return $return;
	}

	/**
	 * Updates a row in a table based on an object's properties.
	 *
	 * @param   boolean  $nulls  True to update null fields or false to ignore them.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 */
	public function updateObject($nulls = false): bool
	{
		$fields = [];
		$where  = [];

		// Workaround Joomla 3.2 change.
		// TODO: remove check when we're only supporting J!3.5+.
		$tbl_keys = isset($this->_tbl_keys) ? $this->_tbl_keys : (array) $this->_tbl_key;

		// Create the base update statement.
		$statement = 'UPDATE ' . $this->_db->quoteName($this->_tbl) . ' SET %s WHERE %s';

		// Iterate over the object variables to build the query fields/value pairs.
		foreach (get_object_vars($this) as $k => $v)
		{
			// Only process scalars that are not internal fields.
			if (is_array($v) || is_object($v) || $k[0] == '_')
			{
				continue;
			}

			// Set the primary key to the WHERE clause instead of a field to update.
			if (in_array($k, $tbl_keys))
			{
				$where[] = $this->_db->quoteName($k) . '=' . $this->_db->quote($v);
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			if ($v === null)
			{
				// If the value is null and we want to update nulls then set it.
				if ($nulls)
				{
					$val = 'NULL';
				}
				// If the value is null and we do not want to update nulls then ignore this field.
				else
				{
					continue;
				}
			}
			// The field is not null so we prep it for update.
			else
			{
				$val = $this->_db->quote($v);
			}

			// Add the field to be updated.
			$fields[] = $this->_db->quoteName($k) . '=' . $val;
		}

		// We don't have any fields to update.
		if (empty($fields))
		{
			return true;
		}

		try
		{
			$this->_db->transactionStart();

			// Set the query and execute the update.
			$this->_db->setQuery(sprintf($statement, implode(",", $fields), implode(' AND ', $where)));

			$this->_db->execute();

			$this->_db->transactionCommit();
		}
		catch (Exception $e)
		{
			// Catch any database errors.
			$this->_db->transactionRollback();

			throw new KunenaException($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Inserts a row into a table based on an object's properties.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 * @throws  RuntimeException
	 */
	protected function insertObject(): bool
	{
		$fields = [];
		$values = [];

		// Workaround Joomla 3.2 change.
		// TODO: remove check when we're only supporting J!3.5+.
		$tbl_keys = isset($this->_tbl_keys) ? $this->_tbl_keys : (array) $this->_tbl_key;

		// Iterate over the object variables to build the query fields and values.
		foreach (get_object_vars($this) as $k => $v)
		{
			// Only process non-null scalars.
			if (is_array($v) || is_object($v) || $v === null)
			{
				continue;
			}

			// Ignore any internal fields.
			if ($k[0] == '_')
			{
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			$fields[] = $this->_db->quoteName($k);
			$values[] = $this->_db->quote($v);
		}

		try
		{
			$this->_db->transactionStart();

			// Create the base insert statement.
			$query = $this->_db->getQuery(true)
				->insert($this->_db->quoteName($this->_tbl))
				->columns($fields)
				->values(implode(',', $values));

			// Set the query and execute the insert.
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				return false;
			}

			// Update the primary key if it exists.
			$id = $this->_db->insertid();

			$this->_db->transactionCommit();
		}
		catch (Exception $e)
		{
			// Catch any database errors.
			$this->_db->transactionRollback();

			throw new KunenaException($e->getMessage());

			return false;
		}

		if (count($tbl_keys) == 1 && $id)
		{
			$key        = reset($tbl_keys);
			$this->$key = $id;
		}

		return true;
	}

	/**
	 * @param   null  $pk  pk
	 *
	 * @return  boolean
	 *
	 * @since   Kunena 6.0
	 *
	 * @throws  Exception
	 */
	public function delete($pk = null): bool
	{
		// Workaround Joomla 3.2 change.
		// TODO: remove check when we're only supporting J!3.5+.
		$tbl_keys = isset($this->_tbl_keys) ? $this->_tbl_keys : (array) $this->_tbl_key;

		if (is_null($pk))
		{
			$pk = [];

			foreach ($tbl_keys as $key)
			{
				$pk[$key] = $this->$key;
			}
		}
		elseif (!is_array($pk))
		{
			$key = reset($tbl_keys);
			$pk  = [$key => $pk];
		}

		foreach ($tbl_keys as $key)
		{
			$pk[$key] = is_null($pk[$key]) ? $this->$key : $pk[$key];

			if ($pk[$key] === null)
			{
				throw new UnexpectedValueException('Null primary key not allowed.');
			}

			$this->$key = $pk[$key];
		}

		// Implement JObservableInterface: Pre-processing by observers
		// TODO: remove if when we're only supporting J!3.5+.
		if (isset($this->_observers))
		{
			$this->_observers->update('onBeforeDelete', [$pk]);
		}

		// Check for a database error.
		try
		{
			$this->_db->transactionStart();

			// Delete the row by primary key.
			$query = $this->_db->getQuery(true)
				->delete($this->_db->quoteName($this->_tbl));

			foreach ($pk as $key => $value)
			{
				$query->where("{$this->_db->quoteName($key)} = {$this->_db->quote($value)}");
			}

			$this->_db->setQuery($query);

			$this->_db->execute();

			$this->_db->transactionCommit();
		}
		catch (Exception $e)
		{
			// Catch any database errors.
			$this->_db->transactionRollback();

			throw new KunenaException($e->getMessage());

			return false;
		}

		// Implement JObservableInterface: Post-processing by observers
		// TODO: remove if when we're only supporting J!3.5+.
		if (isset($this->_observers))
		{
			$this->_observers->update('onAfterDelete', [$pk]);
		}

		return true;
	}
}
