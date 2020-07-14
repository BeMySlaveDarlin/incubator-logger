<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Incubator\Logger\Adapter;

use Phalcon\Db\Adapter\Pdo\AbstractPdo;
use Phalcon\Db\Column;
use Phalcon\Logger\Adapter\AbstractAdapter;
use Phalcon\Logger\Adapter\AdapterInterface;
use Phalcon\Logger\Item;

/**
 * Database Logger
 *
 * Adapter to store logs in a database table
 */
class Database extends AbstractAdapter
{
    /**
     * Name
     * @var string
     */
    protected $name;

    /**
     * @var AbstractPdo
     */
    protected $db;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * Class constructor.
     *
     * @param string $name
     * @param AbstractPdo $db
     * @param string $tableName
     */
    public function __construct(string $name, AbstractPdo $db, string $tableName)
    {
        $this->db = $db;
        $this->name = $name;
        $this->tableName = $tableName;
    }

    /**
     * Closes DB connection
     *
     * @return bool
     */
    public function close(): bool
    {
        if ($this->db->isUnderTransaction()) {
            $this->db->commit();
        }

        return $this->db->close();
    }

    /**
     * Opens DB Transaction
     *
     * @return AdapterInterface
     */
    public function begin(): AdapterInterface
    {
        $this->db->begin();

        return $this;
    }

    /**
     * Commit transaction
     *
     * @return AdapterInterface
     */
    public function commit(): AdapterInterface
    {
        $this->db->commit();

        return $this;
    }

    /**
     * Rollback transaction
     * (happens automatically if commit never reached)
     *
     * @return AdapterInterface
     */
    public function rollback(): AdapterInterface
    {
        $this->db->rollback();

        return $this;
    }

    /**
     * Writes the log into DB table
     *
     * @param Item $item
     */
    public function process(Item $item): void
    {
        $this->db->execute(
            'INSERT INTO ' . $this->tableName . ' VALUES (null, ?, ?, ?, ?)',
            [
                $this->name,
                $item->getType(),
                $this->getFormatter()->format($item),
                $item->getTime(),
            ],
            [
                Column::BIND_PARAM_STR,
                Column::BIND_PARAM_INT,
                Column::BIND_PARAM_STR,
                Column::BIND_PARAM_INT,
            ]
        );
    }
}
