<?php

namespace Eshop\Model;

use Eshop\Logging as Logger;

abstract class ActiveRecord
{
    const HAS_ONE = 'has one';
    const HAS_MANY = 'has many';

    /** @var \PDO */
    protected static $pdo = null;
    /** @var string */
    protected static $pk = null;
    /** @var string */
    protected static $table = null;
    /** @var array */
    protected static $relations = [];

    /**
     * Bind PDO connection
     *
     * @param \PDO $pdo
     */
    public static function setDb(\PDO $pdo)
    {
        self::$pdo = $pdo;
    }

    /**
     * Get table name
     *
     * @return null|string
     */
    public static function getTable()
    {
        if (static::$table) {
            return strtolower(static::$table);
        }

        return strtolower(preg_replace('/^.*\\\/', '', static::class));
    }

    /**
     * Get primary key
     *
     * @return null|string
     */
    public static function getPk()
    {
        if (static::$pk) {
            return static::$pk;
        }

        return 'id';
    }

    /**
     * Get primary key value
     *
     * @return mixed
     * @throws \Exception
     */
    public function getPkVal()
    {
        $getter = 'get'.ucfirst(self::getPk());
        Logger::info('Trying to get PK value from '.static::class.'::'.$getter.'()');
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }

        static::error('Cannot get PK value');

        return null; // never happen :)
    }

    /**
     * Log error and throw it as a exception
     *
     * @param $msg
     * @throws \Exception
     */
    protected static function error($msg)
    {
        Logger::error(static::class.': '.$msg);
        throw new \Exception(static::class.': '.$msg);
    }

    /**
     * Get instance properties and values
     *
     * @return array
     * @throws \Exception
     * @throws \ReflectionException
     */
    protected function getProps()
    {
        $result = [];
        $reflect = new \ReflectionClass($this);

        $props = $reflect->getProperties();
        foreach ($props as $prop) {
            $propName = $prop->getName();
            $getter = 'get'.ucfirst($propName);
            if (!($prop->getModifiers() & \ReflectionProperty::IS_STATIC)) {
                Logger::info('Trying to get value from '.$propName.' using '.static::class.'::'.$getter.'()');
                if (method_exists($this, $getter)) {
                    $key = $prop->getName();
                    $value = $this->$getter();

                    if (
                        $value instanceof ActiveRecord ||
                        (array_key_exists($key, static::$relations) && static::$relations[$key][0] === self::HAS_ONE)
                    ) {
                        $key .= '_'.$value::getPk();
                        $value = $value->getPkVal();
                    }

                    if ($value instanceof \DateTimeInterface) {
                        $value = $value->format('r');
                    }

                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Get simple instance properties (w/o relations)
     *
     * @param $props
     * @return array
     */
    protected static function getSimpleProps($props)
    {
        return array_filter($props, function ($key) {
            return !(array_key_exists($key, static::$relations) && static::$relations[$key][0] === self::HAS_MANY);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get instance properties representing HAS MANY relations
     *
     * @param $props
     * @return array
     */
    protected static function getHasManyProps($props)
    {
        return array_filter($props, function ($key) {
            return array_key_exists($key, static::$relations) && static::$relations[$key][0] === self::HAS_MANY;
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Execute query, returning statement on success or false on fail
     *
     * @param $query
     * @param array $params
     * @return bool
     */
    public static function execute($query, array $params = [])
    {
        Logger::info('Query: '.trim($query).' with params: '.implode(', ', $params));

        if (!($stmt = self::$pdo->prepare($query))) {
            Logger::error(print_r('Prepare error: '.implode(', ', self::$pdo->errorInfo()), true));

            return false;
        }

        if (!$stmt->execute($params)) {
            Logger::error(print_r('Execute error: '.implode(', ', self::$pdo->errorInfo()), true));

            return false;
        }

        return $stmt;
    }

    /**
     * Find item by primary key value
     *
     * @param $pkVal
     * @return bool
     */
    public static function find($pkVal)
    {
        $pk = static::getPk();
        $query = 'SELECT * FROM `'.self::getTable().'`'.
            " WHERE $pk = :$pk";
        /** @var \PDOStatement $result */
        if ($result = self::execute($query, [$pk => $pkVal])) {
            $result->setFetchMode(\PDO::FETCH_CLASS, static::class);
            if ($result = $result->fetch()) {
                $result->fetchRelations();
            }

            return $result;
        }

        return false;
    }

    /**
     * Find item by conditions - [ key1 => value1, key2 => value2, ... ]
     *
     * @param $cond
     * @return array|\PDOStatement
     * @throws \Exception
     */
    public static function findBy($cond)
    {
        $keys = array_keys($cond);
        $ands = array_map(function ($key) {
            return $key.' = :'.$key;
        }, $keys);

        $query = 'SELECT * FROM `'.self::getTable().'`'.
            ' WHERE '.implode(' AND ', $ands);
        /** @var \PDOStatement $result */
        if ($result = self::execute($query, $cond)) {
            $result = $result->fetchAll(\PDO::FETCH_CLASS, static::class);

            /** @var ActiveRecord $row */
            foreach ($result as $row) {
                $row->fetchRelations();
            }

            return $result;
        }

        return [];
    }

    /**
     * Return all items
     *
     * @return array|\PDOStatement
     * @throws \Exception
     */
    public static function all()
    {
        $query = 'SELECT * FROM `'.self::getTable().'`';
        /** @var \PDOStatement $result */
        if ($result = self::execute($query)) {
            $result = $result->fetchAll(\PDO::FETCH_CLASS, static::class);

            /** @var ActiveRecord $row */
            foreach ($result as $row) {
                $row->fetchRelations();
            }

            return $result;
        }

        return [];
    }

    /**
     * Insert instance to DB
     *
     * @return bool
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function insert()
    {
        if (!($props = $this->getProps())) {
            return false;
        }

        $directProps = self::getSimpleProps($props);
        $keys = array_keys($directProps);

        $query = 'INSERT INTO `'.static::getTable().'` ('.implode(',', $keys).') VALUES (:'.implode(',:', $keys).')';

        $this->updateHasMany($props);

        return self::execute($query, $directProps);
    }

    /**
     * Updates instance to DB
     *
     * @return bool
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function update()
    {
        if (!($props = $this->getProps())) {
            return false;
        }

        $directProps = self::getSimpleProps($props);
        $keys = array_keys($directProps);

        $sets = array_map(function ($key) {
            return $key.' = :'.$key;
        }, $keys);

        $query = 'UPDATE `'.static::getTable().'` SET '.implode(',', $sets).
            ' WHERE '.static::getPk().' = :'.static::getPk();

        $this->updateHasMany($props);

        return self::execute($query, $directProps);
    }

    /**
     * Fetch related properties (HAS ONE or HAS MANY)
     *
     * @throws \Exception
     */
    protected function fetchRelations()
    {
        foreach (static::$relations as $prop => $relation) {
            list($relType, $class) = $relation;
            $setter = 'set'.ucfirst($prop);
            if ($relType === self::HAS_ONE) {
                /** @var ActiveRecord $class */
                $key = $class::getTable().'_'.$class::getPk();
                Logger::info('Trying to set value to '.$prop.' using '.static::class.'::'.$setter.'()');
                if (method_exists($this, $setter)) {
                    $this->$setter($class::find($this->$key));
                }
                unset($this->$key);
            }

            if ($relType === self::HAS_MANY) {
                $relTable = static::getTable().'_'.$class::getTable();
                $firstKey = static::getTable().'_'.static::getPk();
                $firstPk = $this->getPkVal();
                $secondKey = $class::getTable().'_'.$class::getPk();

                Logger::info('Trying to set value to '.$prop.' using '.static::class.'::'.$setter.'()');
                if (method_exists($this, $setter)) {
                    $query = "SELECT $secondKey FROM `".$relTable.'`'.
                        " WHERE $firstKey = :$firstKey";
                    $relResult = [];
                    if ($relKeys = self::execute($query, [$firstKey => $firstPk])) {
                        /** @var \PDOStatement $relKeys */
                        $relKeys = $relKeys->fetchAll(\PDO::FETCH_ASSOC);
                        foreach ($relKeys as $relKey) {
                            if ($relItem = $class::find($relKey[$secondKey])) {
                                $relResult[] = $relItem;
                            }
                        }
                        if ($relResult) {
                            $this->$setter($relResult);
                        }
                    }
                }
            }
        }
    }

    /**
     * Update HAS MANY relation
     *
     * @param $props
     * @throws \Exception
     */
    protected function updateHasMany($props)
    {
        $hasManyProps = self::getHasManyProps($props);

        foreach ($hasManyProps as $key => $value) {
            $class = static::$relations[$key][1];
            /** @var ActiveRecord $class */
            $relTable = static::getTable().'_'.$class::getTable();
            $firstKey = static::getTable().'_'.static::getPk();
            $firstPk = $this->getPkVal();
            $secondKey = $class::getTable().'_'.$class::getPk();
            $tuples = array_map(function (ActiveRecord $related) use ($firstPk, $class) {
                $secondPk = $related->getPkVal();

                return '('.((int)$firstPk).','.((int)$secondPk).')';
            }, $value);

            $query = 'DELETE FROM `'.$relTable."` WHERE $firstKey = :$firstKey";
            self::execute($query, [$firstKey => $firstPk]);

            $query = 'INSERT INTO `'.$relTable."` ($firstKey,$secondKey) VALUES ".implode(',', $tuples);
            self::execute($query);
        }
    }
}
