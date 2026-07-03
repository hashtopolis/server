<?php

namespace Hashtopolis\dba;

use Exception;
use PDO, PDOStatement, PDOException;
use Hashtopolis\inc\StartupConfig;

/**
 * Abstraction of all ModelFactories.
 * A ModelFactory is used to get all
 * models from Database. It handles the DB calling and caching of objects.
 *
 * @template TModel of AbstractModel
 */
abstract class AbstractModelFactory {
  private const MAPPING_PREFIX = "htp_";
  
  /**
   * @var PDO|null
   */
  private static ?PDO $dbh = null;
  
  /**
   * Return the Models name
   *
   * @return string The name of the model associated with this factory
   */
  abstract function getModelName(): string;
  
  /**
   * Return the Models associated table
   *
   * This function defines table associated with this model and will be
   * used by the database abstraction to save your model in.
   *
   * @return string The name of the table associated with this factory
   */
  abstract function getModelTable(): string;
  
  /**
   * @return bool
   */
  abstract function isMapping(): bool;
  
  /**
   * Returns whether the associated model is able to be cached or not
   *
   * @return boolean True, if the object might be cached, False if not
   */
  abstract function isCachable(): bool;
  
  /**
   * Returns weather the models valid time on cache.
   *
   * Returns the time in seconds a object might life on the cache.
   * If the model should not be cachable -1 shall be returned
   *
   * @return int valid time in seconds, -1 if model shouldn't be cached
   */
  abstract function getCacheValidTime(): int;
  
  /**
   * Returns an empty instance of the associated object
   *
   * This empty object is used to get all the object properties for
   * different queries such as the get queries, where no actual object
   * is given
   *
   * @return TModel
   */
  abstract function getNullObject(): AbstractModel;
  
  /**
   * This function inits, an objects values from a dict and returns it;
   *
   * This function is used to get objects from a certain type from db resourcebundle_get_error_message
   *
   * @param $pk string primary key
   * @param $dict array dict of values and keys
   * @return TModel An object of the factories type
   */
  abstract function createObjectFromDict(string $pk, array $dict): AbstractModel;
  
  /**
   * Return the model name in the table, which is the same normally, unless mapping is required. In a mapping case,
   * the configured prefix is added.
   *
   * @return string
   */
  public function getMappedModelTable(): string {
    if ($this->isMapping()) {
      return self::MAPPING_PREFIX . $this->getModelName();
    }
    return $this->getModelName();
  }
  
  /**
   * @param AbstractModel $model
   * @param string $key unmapped column name
   * @return bool
   */
  private static function isBinaryColumn(AbstractModel $model, string $key): bool {
    $features = $model->getFeatures();
    return isset($features[$key]['type']) && $features[$key]['type'] === 'binary';
  }
  
  /**
   * @param AbstractModel $model
   * @param string $key unmapped column name
   * @return string placeholder SQL fragment ("?" or db-specific hex-to-binary function)
   */
  private static function binaryPlaceholder(AbstractModel $model, string $key): string {
    if (!self::isBinaryColumn($model, $key)) {
      return "?";
    }
    $dbType = StartupConfig::getInstance()->getDatabaseType();
    return $dbType === 'mysql' ? "UNHEX(?)" : "decode(?, 'hex')";
  }
  
  /**
   * Get all the attribute keys of a model prepared with the mapping prefix where needed. The returned keys are then named
   * exactly how they are present in the database.
   *
   * @param AbstractModel $model
   * @return array list of keys of the model (mapped where needed)
   */
  public static function getMappedModelKeys(AbstractModel $model): array {
    // check the keys of the table for required mapping from features
    $keys = [];
    $features = $model->getFeatures();
    foreach (array_keys($model->getKeyValueDict()) as $key) {
      if ($features[$key]["dba_mapping"]) {
        $keys[] = self::MAPPING_PREFIX . $key;
      }
      else {
        $keys[] = $key;
      }
    }
    return $keys;
  }
  
  /**
   * Get the key for a model how it's represented in the database itself. For non-mapped keys the value just remains.
   *
   * @param AbstractModel $model
   * @param string $key
   * @return string
   */
  public static function getMappedModelKey(AbstractModel $model, string $key): string {
    $features = $model->getFeatures();
    if ($features[$key]["dba_mapping"]) {
      return self::MAPPING_PREFIX . $key;
    }
    return $key;
  }
  
  /**
   * Saves the passed model in database, and returns it with the real id
   * in the database.
   *
   * The function saves the passed model in the database and updates the
   * cache, if the model shall be cached. The primary key of this object
   * MUST be -1
   *
   * The Function returns null if the object could not be placed into the
   * database
   * @param TModel $model model to save
   * @return TModel|null
   * @throws Exception
   */
  public function save(AbstractModel $model): ?AbstractModel {
    $dict = $model->getKeyValueDict();
    
    $query = "INSERT INTO " . $this->getMappedModelTable();
    $origKeys = array_keys($dict);
    $vals = array_values($dict);
    
    $keys = self::getMappedModelKeys($model);
    
    if ($vals[0] === -1 || $vals[0] === null) {
      array_splice($vals, 0, 1);
      array_splice($keys, 0, 1);
      array_splice($origKeys, 0, 1);
    }
    
    $query .= " (" . implode(",", $keys) . ") ";
    $placeholders = [];
    foreach ($origKeys as $k) {
      $placeholders[] = self::binaryPlaceholder($model, $k);
    }
    $query = $query . " VALUES (" . implode(",", $placeholders) . ")";
    
    $dbh = $this->getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    if ($model->getId() === null || $model->getId() === -1) {
      $id = intval($dbh->lastInsertId());
      if ($id != 0) {
        $model->setId($id);
        return $model;
      }
      else if ($model->getId() != 0) {
        return $model;
      }
      else {
        return null;
      }
    }
    else {
      return $model;
    }
  }
  
  /**
   * @param $arr array
   * @return Filter[]
   */
  private function getFilters(array $arr): array {
    if (!is_array($arr[Factory::FILTER])) {
      $arr[Factory::FILTER] = array($arr[Factory::FILTER]);
    }
    if (isset($arr[Factory::FILTER])) {
      return $arr[Factory::FILTER];
    }
    return array();
  }
  
  /**
   * @param $arr array
   * @return Join[]
   */
  private function getJoins(array $arr): array {
    if (!is_array($arr[Factory::JOIN])) {
      $arr[Factory::JOIN] = array($arr[Factory::JOIN]);
    }
    if (isset($arr[Factory::JOIN])) {
      return $arr[Factory::JOIN];
    }
    return array();
  }
  
  /**
   * Updates the database entry for the model
   *
   * This function updates the database entry for the given model
   * based on it's primary key.
   * Returns the return of PDO::execute()
   * @param TModel $model model to update
   * @return PDOStatement
   * @throws Exception
   */
  public function update(AbstractModel $model): PDOStatement {
    $dict = $model->getKeyValueDict();
    
    $query = "UPDATE " . $this->getMappedModelTable() . " SET ";
    
    $origKeys = array_keys($dict);
    $values = array_values($dict);
    $mappedKeys = self::getMappedModelKeys($model);
    
    for ($i = 0; $i < count($mappedKeys); $i++) {
      $query .= $mappedKeys[$i] . "=" . self::binaryPlaceholder($model, $origKeys[$i]);
      if ($i < count($mappedKeys) - 1) {
        $query .= ", ";
      }
    }
    
    $query .= " WHERE " . $model->getPrimaryKey() . "=?";
    $values[] = $model->getPrimaryKeyValue();
    
    $stmt = $this->getDB()->prepare($query);
    $stmt->execute($values);
    return $stmt;
  }
  
  /**
   * Atomically sets the given keys of this model to the given values without setting all other values (like ->update() does)
   *
   * Returns the return of PDO::execute() or null if nothing was executed
   * @param TModel $model primary key of model
   * @param array $arr key-value associations for update
   * @return TModel updated model
   * @throws Exception
   */
  public function mset(AbstractModel $model, array $arr): AbstractModel {
    $query = "UPDATE " . $this->getMappedModelTable() . " SET ";
    $elements = [];
    $values = [];
    foreach ($arr as $key => $val) {
      $elements[] = self::getMappedModelKey($model, $key) . "=" . self::binaryPlaceholder($model, $key) . " ";
      $values[] = $val;
    }
    $query .= implode(", ", $elements);
    
    $query = $query . " WHERE " . $model->getPrimaryKey() . "=?";
    $values[] = $model->getPrimaryKeyValue();
    
    $stmt = $this->getDB()->prepare($query);
    $stmt->execute($values);
    
    $model = $this->get($model->getPrimaryKeyValue());
    assert($model !== null); // assert as on this update we should not get null back (unless race-condition)
    return $model;
  }
  
  /**
   * Atomically sets the given key of this model to the given value without altering other values
   *
   * Returns the return of PDO::execute()
   * @param TModel $model primary key of model
   * @param string $key key of the column to update
   * @param $value
   * @return TModel
   * @throws Exception
   */
  public function set(AbstractModel $model, string $key, $value): AbstractModel {
    $query = "UPDATE " . $this->getMappedModelTable() . " SET " . self::getMappedModelKey($model, $key) . "=" . self::binaryPlaceholder($model, $key);
    
    $values = [];
    $query = $query . " WHERE " . $model->getPrimaryKey() . "=?";
    $values[] = $value;
    $values[] = $model->getPrimaryKeyValue();
    
    $stmt = $this->getDB()->prepare($query);
    $stmt->execute($values);
    
    $model = $this->get($model->getPrimaryKeyValue());
    assert($model !== null); // assert as on this update we should not get null back (unless race-condition)
    return $model;
  }
  
  /**
   * Increments the given key of this model by the given value atomically
   *
   * Returns the return of PDO::execute()
   * @param TModel &$model primary key of model
   * @param $key string key of the column to update
   * @param $value int amount of increment
   * @return PDOStatement
   * @throws Exception
   */
  public function inc(AbstractModel &$model, string $key, int $value = 1): PDOStatement {
    if ($value <= 0) {
      throw new Exception("Cannot increment by zero or negative values!");
    }
    
    $mapped_key = self::getMappedModelKey($model, $key);
    $query = "UPDATE " . $this->getMappedModelTable() . " SET " . $mapped_key . "=" . $mapped_key . "+?";
    
    $values = [];
    $query = $query . " WHERE " . $model->getPrimaryKey() . "=?";
    $values[] = $value;
    $values[] = $model->getPrimaryKeyValue();
    
    $stmt = $this->getDB()->prepare($query);
    $stmt->execute($values);
    
    $refreshed = $this->get($model->getPrimaryKeyValue());
    assert($refreshed !== null);
    $model = $refreshed;
    return $stmt;
  }
  
  /**
   * Decrements the given key of this model by the given value
   *
   * Returns the return of PDO::execute()
   * @param TModel &$model primary key of model
   * @param $key string key of the column to update
   * @param $value int amount of increment
   * @return PDOStatement
   * @throws Exception
   */
  public function dec(AbstractModel &$model, string $key, int $value = 1): PDOStatement {
    if ($value <= 0) {
      throw new Exception("Cannot decrement by zero or negative values!");
    }
    
    $mapped_key = self::getMappedModelKey($model, $key);
    $query = "UPDATE " . $this->getMappedModelTable() . " SET " . $mapped_key . "=" . $mapped_key . "-?";
    
    $values = [];
    $query = $query . " WHERE " . $model->getPrimaryKey() . "=?";
    $values[] = $value;
    $values[] = $model->getPrimaryKeyValue();
    
    $stmt = $this->getDB()->prepare($query);
    $stmt->execute($values);
    
    $refreshed = $this->get($model->getPrimaryKeyValue());
    assert($refreshed !== null);
    $model = $refreshed;
    return $stmt;
  }
  
  /**
   * @param TModel[] $models
   * @return bool|PDOStatement
   * @throws Exception
   */
  public function massSave(array $models): bool|PDOStatement {
    if (sizeof($models) == 0) {
      return false;
    }
    
    $keys = self::getMappedModelKeys($models[0]);
    $origKeys = array_keys($models[0]->getKeyValueDict());
    $query = "INSERT INTO " . $this->getMappedModelTable();
    
    $pkInclude = false;
    if ($models[0]->getId() !== -1 && $models[0]->getId() !== null) {
      $pkInclude = true;
    }
    else {
      array_splice($keys, 0, 1);
      array_splice($origKeys, 0, 1);
    }
    
    $query .= " (" . implode(",", $keys) . ") ";
    $placeholders = [];
    foreach ($origKeys as $k) {
      $placeholders[] = self::binaryPlaceholder($models[0], $k);
    }
    $placeHolderStr = " (" . implode(",", $placeholders) . ")";
    
    $query = $query . " VALUES ";
    $vals = array();
    for ($x = 0; $x < sizeof($models); $x++) {
      $query .= $placeHolderStr;
      if ($x < sizeof($models) - 1) {
        $query .= ", ";
      }
      $dict = $models[$x]->getKeyValueDict();
      if (!$pkInclude) {
        array_splice($dict, 0, 1);
      }
      foreach ($dict as $val) {
        $vals[] = $val;
      }
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    return $stmt;
  }
  
  /**
   * @param $options array filter options
   * @param $sumColumn string column to apply OP to
   * @param $op string either min or max
   * @return mixed
   * @throws Exception
   */
  public function minMaxFilter(array $options, string $sumColumn, string $op): mixed {
    if (strtolower($op) == "min") {
      $op = "MIN";
    }
    else {
      $op = "MAX";
    }
    $query = "SELECT $op(" . self::getMappedModelKey($this->getNullObject(), $sumColumn) . ") AS column_" . strtolower($op) . " ";
    $query = $query . " FROM " . $this->getMappedModelTable();
    
    $vals = array();
    
    if (array_key_exists(Factory::FILTER, $options)) {
      $query .= $this->applyFilters($vals, $options[Factory::FILTER]);
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['column_' . strtolower($op)];
  }
  
  /**
   * @param $options array as usual, to filter and join
   * @param $aggregations array of Aggregation objects
   * @return mixed
   * @throws Exception
   */
  public function multicolAggregationFilter(array $options, array $aggregations): mixed {
    $elements = [];
    foreach ($aggregations as $aggregation) {
      $elements[] = $aggregation->getQueryString($this, isset($options[Factory::JOIN]));
    }
    
    $query = "SELECT " . join(",", $elements);
    
    $query .= " FROM " . $this->getMappedModelTable();
    
    $vals = array();
    
    if (array_key_exists(Factory::JOIN, $options)) {
      $query .= $this->applyJoins($options[Factory::JOIN]);
    }
    if (array_key_exists(Factory::FILTER, $options)) {
      $query .= $this->applyFilters($vals, $options[Factory::FILTER]);
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
  
  /**
   * @param $options array options of query (filters and joins)
   * @param $columns array|string single column key or array of column keys which should be retrieved
   * @return array of the column entries returned from this query
   * @throws Exception
   */
  public function columnFilter(array $options, array|string $columns): array {
    if (!is_array($columns)) {
      $columns = [$columns];
    }
    $elements = [];
    foreach ($columns as $column) {
      $elements[] = Util::createPrefixedString($this->getMappedModelTable(), [self::getMappedModelKey($this->getNullObject(), $column)]);
    }
    $query = "SELECT " . join(",", $elements) . " FROM " . $this->getMappedModelTable();
    
    $vals = array();
    
    if (array_key_exists(Factory::JOIN, $options)) {
      $query .= $this->applyJoins($options[Factory::JOIN]);
    }
    if (array_key_exists(Factory::FILTER, $options)) {
      $query .= $this->applyFilters($vals, $options[Factory::FILTER]);
    }
    if (array_key_exists(Factory::ORDER, $options)) {
      $query .= $this->applyOrder($options[Factory::ORDER]);
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    if (sizeof($elements) == 1) {
      return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    return $stmt->fetchAll(PDO::FETCH_NUM);
  }
  
  /**
   * @param $options array options with filters
   * @param $sumColumn string column to sum up
   * @return int
   * @throws Exception
   */
  public function sumFilter(array $options, string $sumColumn): int {
    $query = "SELECT SUM(" . self::getMappedModelKey($this->getNullObject(), $sumColumn) . ") AS sum ";
    $query = $query . " FROM " . $this->getMappedModelTable();
    
    $vals = array();
    
    if (array_key_exists(Factory::FILTER, $options)) {
      $query .= $this->applyFilters($vals, $options[Factory::FILTER]);
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['sum'] == null) {
      return 0;
    }
    return $row['sum'];
  }
  
  /**
   * Create a timeseries with counts per day for a given table.
   *
   * @param array $options can contain FILTER options to select which entries should match to be counted (e.g. also if the timeseries should only be over a certain amount of day)
   * @param string $timeColumn table column which should be used to be use for the 'day' grouping
   * @return array list of [day => count] entries
   * @throws Exception
   */
  public function columnTimeseriesFilter(array $options, string $timeColumn): array {
    $dbType = StartupConfig::getInstance()->getDatabaseType();
    $to_timestamp = ($dbType == "postgres") ? "TO_TIMESTAMP" : "FROM_UNIXTIME";
    
    $query = "SELECT DATE(" . $to_timestamp . "(" . self::getMappedModelKey($this->getNullObject(), $timeColumn) . ")) AS day, COUNT(*) AS total";
    
    $query .= " FROM " . $this->getMappedModelTable();
    
    $vals = array();
    
    if (array_key_exists(Factory::FILTER, $options)) {
      $query .= $this->applyFilters($vals, $options[Factory::FILTER]);
    }
    
    $query .= " GROUP BY day ORDER BY day";
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    return $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_KEY_PAIR);
  }
  
  /**
   * @param $options array options with filter and join
   * @return int
   * @throws Exception
   */
  public function countFilter(array $options): int {
    $query = "SELECT COUNT(*) AS count ";
    $query = $query . " FROM " . $this->getMappedModelTable();
    
    $vals = array();
    
    if (array_key_exists(Factory::JOIN, $options)) {
      $query .= $this->applyJoins($options[Factory::JOIN]);
    }
    
    if (array_key_exists(Factory::FILTER, $options)) {
      $query .= $this->applyFilters($vals, $options[Factory::FILTER]);
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['count'] === null) {
      return 0;
    }
    return $row['count'];
  }
  
  /**
   * Get's a model from it's primary key.
   *
   * This function returns the model with the given primary key or null.
   * If the model is specified to be non-cached, this function will call
   * the getFromDB() function and return it's result. It's therefor recommended
   * to use this function
   *
   * @param $pk string primary key
   * @return TModel|null the with pk associated model or Null
   * @throws Exception
   */
  public function get($pk): ?AbstractModel {
    return $this->getFromDB($pk);
  }
  
  /**
   * Get's a model by it's primary key directly going to the database
   *
   * This function returns the model with the given primary key or null.
   * This function will go to the database directly neglecting the cache.
   * If the model is set to be cachable, the cache will also be updated
   *
   * @param $pk string primary key
   * @return TModel|null the with pk associated model or Null
   * @throws Exception
   */
  public function getFromDB($pk): ?AbstractModel {
    $keys = self::getMappedModelKeys($this->getNullObject());
    $query = "SELECT " . implode(", ", $keys);
    $query .= " FROM " . $this->getMappedModelTable();
    $query .= " WHERE " . $this->getNullObject()->getPrimaryKey() . "=?";
    
    $stmt = $this->getDB()->prepare($query);
    $stmt->execute(array($pk));
    if ($stmt->rowCount() != 0) {
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      return $this->createObjectFromDict($pk, $row);
    }
    else {
      return null;
    }
  }
  
  /**
   * Filters the database for a set of options
   *
   * This function filters the dataset (think of it as a select) for a set
   * of options.
   * The structure of the options array is a dictionary with the following
   * structure
   *
   * $options = array();
   * $options[Factory::FILTER] is an array of QueryFilter options
   * $options[Factory::ORDER] is an array of OrderFilter options
   * $options[Factory::JOIN] is an array of JoinFilter options
   *
   * @param $options array containing option settings
   * @return array<TModel>|TModel|null Returns an array of matching objects
   * @throws Exception
   */
  private function filterWithJoin(array $options): array {
    $vals = array();
    $joins = $this->getJoins($options);
    $factories = array($this);
    $query = "SELECT " . Util::createPrefixedString($this->getMappedModelTable(), self::getMappedModelKeys($this->getNullObject()));
    foreach ($joins as $join) {
      $joinFactory = $join->getOtherFactory();
      $factories[] = $joinFactory;
      $query .= ", " . Util::createPrefixedString($joinFactory->getMappedModelTable(), self::getMappedModelKeys($joinFactory->getNullObject()));
    }
    $query .= " FROM " . $this->getMappedModelTable();
    
    foreach ($joins as $join) {
      $joinFactory = $join->getOtherFactory();
      $localFactory = $this;
      if ($join->getOverrideOwnFactory() != null) {
        $localFactory = $join->getOverrideOwnFactory();
      }
      $match1 = self::getMappedModelKey($localFactory->getNullObject(), $join->getMatch1());
      $match2 = self::getMappedModelKey($joinFactory->getNullObject(), $join->getMatch2());
      $query .= " " . $join->getJoinType() . " JOIN " . $joinFactory->getMappedModelTable() . " ON " . $localFactory->getMappedModelTable() . "." . $match1 . "=" . $joinFactory->getMappedModelTable() . "." . $match2 . " ";
      $joinQueryFilters = $join->getQueryFilters();
      if (count($joinQueryFilters) > 0) {
        $query .= $this->applyFilters($vals, $joinQueryFilters, true);
      }
    }
    
    // Apply all normal filter to this query
    if (array_key_exists(Factory::FILTER, $options)) {
      $query .= $this->applyFilters($vals, $options[Factory::FILTER]);
    }
    
    // Apply order filter
    if (!array_key_exists(Factory::ORDER, $options)) {
      // Add a asc order on the primary keys as a standard
      $oF = new OrderFilter($this->getNullObject()->getPrimaryKey(), "ASC");
      $orderOptions = array($oF);
      $options[Factory::ORDER] = $orderOptions;
    }
    $query .= $this->applyOrder($options[Factory::ORDER]);
    
    if (array_key_exists(Factory::LIMIT, $options)) {
      $query .= $this->applyLimit($options[Factory::LIMIT]);
    }
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    $res = array();
    $values = array();
    foreach ($factories as $factory) {
      $res[$factory->getModelTable()] = array();
      $values[$factory->getModelTable()] = array();
    }
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      foreach ($row as $k => $v) {
        $k = strtolower($k);
        foreach ($factories as $factory) {
          if (str_starts_with($k, strtolower($factory->getMappedModelTable()))) {
            $column = str_replace(strtolower($factory->getMappedModelTable()) . "_", "", $k);
            $values[$factory->getModelTable()][strtolower($column)] = $v;
          }
        }
      }
      
      foreach ($factories as $factory) {
        $model = $factory->createObjectFromDict($values[$factory->getModelTable()][strtolower($factory->getNullObject()->getPrimaryKey())], $values[$factory->getModelTable()]);
        $res[$factory->getModelTable()][] = $model;
      }
    }
    
    return $res;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return array<TModel>|TModel|null
   * @throws Exception
   */
  public function filter(array $options, bool $single = false): array|AbstractModel|null {
    // Check if we need to join and if so pass on to internal Function
    if (array_key_exists(Factory::JOIN, $options)) {
      return $this->filterWithJoin($options);
    }
    
    $keys = self::getMappedModelKeys($this->getNullObject());
    $query = "SELECT " . implode(", ", $keys) . " FROM " . $this->getMappedModelTable();
    $vals = array();
    
    if (array_key_exists(Factory::FILTER, $options)) {
      $query .= $this->applyFilters($vals, $options[Factory::FILTER]);
    }
    
    if (!array_key_exists(Factory::ORDER, $options)) {
      // Add a asc order on the primary keys as a standard
      $oF = new OrderFilter($this->getNullObject()->getPrimaryKey(), "ASC");
      $orderOptions = array($oF);
      $options[Factory::ORDER] = $orderOptions;
    }
    $query .= $this->applyOrder($options[Factory::ORDER]);
    
    if (array_key_exists(Factory::LIMIT, $options)) {
      $query .= $this->applyLimit($options[Factory::LIMIT]);
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    $objects = array();
    
    // Loop over all entries and create an object from dict for each
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $pkName = $this->getNullObject()->getPrimaryKey();
      
      if (isset($row[strtolower($pkName)])) {
        $pk = $row[strtolower($pkName)];
      }
      else {
        $pk = $row[$pkName];
      }
      $model = $this->createObjectFromDict($pk, $row);
      $objects[] = $model;
    }
    
    if ($single) {
      if (sizeof($objects) == 0) {
        return null;
      }
      else {
        return $objects[0];
      }
    }
    
    return $objects;
  }
  
  /**
   * @param $vals
   * @param $filters Filter|Filter[]
   * @param bool $isJoinFilter
   * @return string
   */
  private function applyFilters(&$vals, Filter|array $filters, bool $isJoinFilter = false): string {
    $parts = array();
    if (!is_array($filters)) {
      $filters = array($filters);
    }
    
    foreach ($filters as $filter) {
      $parts[] = $filter->getQueryString($this, true);
      if (!$filter->getHasValue()) {
        continue;
      }
      $v = $filter->getValue();
      $this->getAllArrayValues($vals, $v);
    }
    if ($isJoinFilter) {
      return " AND " . implode(" AND ", $parts);
    }
    return " WHERE " . implode(" AND ", $parts);
  }
  
  /**
   * @param $vals
   * @param $element
   */
  private function getAllArrayValues(&$vals, $element): void {
    if (!is_array($element)) {
      $vals[] = $element;
      return;
    }
    
    foreach ($element as $v) {
      $this->getAllArrayValues($vals, $v);
    }
  }
  
  /**
   * @param $orders Order|Order[]
   * @return string
   */
  private function applyOrder(Order|array $orders): string {
    $orderQueries = array();
    if (!is_array($orders)) {
      $orders = array($orders);
    }
    foreach ($orders as $order) {
      $orderQueries[] = $order->getQueryString($this, true);
    }
    return " ORDER BY " . implode(", ", $orderQueries);
  }
  
  /**
   * @param array|Join $joins
   * @return string
   */
  private function applyJoins(Join|array $joins): string {
    $query = "";
    if (!is_array($joins)) {
      $joins = array($joins);
    }
    
    foreach ($joins as $join) {
      $joinFactory = $join->getOtherFactory();
      $localFactory = $this;
      if ($join->getOverrideOwnFactory() != null) {
        $localFactory = $join->getOverrideOwnFactory();
      }
      $match1 = self::getMappedModelKey($localFactory->getNullObject(), $join->getMatch1());
      $match2 = self::getMappedModelKey($joinFactory->getNullObject(), $join->getMatch2());
      $query .= " " . $join->getJoinType() . " JOIN " . $joinFactory->getMappedModelTable() . " ON " . $localFactory->getMappedModelTable() . "." . $match1 . "=" . $joinFactory->getMappedModelTable() . "." . $match2 . " ";
    }
    return $query;
  }
  
  /**
   * applylimit is slightly different than the other apply functions, since you can only limit by a single value
   * the $limit argument is a single object LimitFilter object instead of an array of objects.
   *
   * @param $limit
   * @return string
   */
  private function applyLimit($limit): string {
    return " LIMIT " . $limit->getQueryString($this);
  }
  
  /**
   * Deletes the given model
   *
   * This function deletes the given and also cleans the cache from it.
   * It returns the return of the execute query.
   * @param TModel $model
   * @return bool
   * @throws Exception
   */
  public function delete($model): bool {
    if ($model != null) {
      $query = "DELETE FROM " . $this->getMappedModelTable() . " WHERE " . $model->getPrimaryKey() . " = ?";
      $stmt = $this->getDB()->prepare($query);
      return $stmt->execute(array(
          $model->getPrimaryKeyValue()
        )
      );
    }
    return false;
  }
  
  /**
   * @param $options array
   * @return PDOStatement
   * @throws Exception
   */
  public function massDeletion(array $options): PDOStatement {
    $query = "DELETE FROM " . $this->getMappedModelTable();
    
    $vals = array();
    
    if (array_key_exists(Factory::FILTER, $options)) {
      $query .= $this->applyFilters($vals, $this->getFilters($options));
    }
    
    $dbh = $this->getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    return $stmt;
  }
  
  /**
   * @param $matchingColumn
   * @param $updateColumn
   * @param $updates MassUpdateSet[]
   * @return bool|null
   * @throws Exception
   */
  public function massSingleUpdate($matchingColumn, $updateColumn, array $updates): ?bool {
    $query = "UPDATE " . $this->getMappedModelTable();
    
    if (sizeof($updates) == 0) {
      return null;
    }
    $query .= " SET " . self::getMappedModelKey($this->getNullObject(), $updateColumn) . " = ( CASE ";
    
    $vals = array();
    
    foreach ($updates as $update) {
      $query .= $update->getMassQuery(self::getMappedModelKey($this->getNullObject(), $matchingColumn));
      $vals[] = $update->getMatchValue();
      $vals[] = $update->getUpdateValue();
    }
    
    $matchingArr = array();
    foreach ($updates as $update) {
      $vals[] = $update->getMatchValue();
      $matchingArr[] = "?";
    }
    
    // this covers the specific case when integer values are updated and the db system does not know what type the case statements would have
    // mysql does not really care, but postgres does
    // the trick we use here works for both systems (as opposed to cast it to int/bigint in postgres with ::bigint where we would need to branch based on the db)
    if (is_int($updates[0]->getUpdateValue())) {
      $query .= " ELSE 2147483648 "; // 32 bit int max + 1
    }
    
    $query .= "END) WHERE " . self::getMappedModelKey($this->getNullObject(), $matchingColumn) . " IN (" . implode(",", $matchingArr) . ")";
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    return $stmt->execute($vals);
  }
  
  /**
   * @param $options
   * @return bool
   * @throws Exception
   */
  public function massUpdate($options): bool {
    $query = "UPDATE " . $this->getMappedModelTable();
    
    $vals = array();
    
    if (array_key_exists(Factory::UPDATE, $options)) {
      $query = $query . " SET ";
      
      
      $updateOptions = $options[Factory::UPDATE];
      if (!is_array($updateOptions)) {
        $updateOptions = array($updateOptions);
      }
      $vals = array();
      
      for ($i = 0; $i < count($updateOptions); $i++) {
        $option = $updateOptions[$i];
        $vals[] = $option->getValue();
        
        if ($i != count($updateOptions) - 1) {
          $query = $query . $option->getQuery($this) . " , ";
        }
        else {
          $query = $query . $option->getQuery($this);
        }
      }
    }
    
    if (array_key_exists(Factory::FILTER, $options)) {
      $query .= $this->applyFilters($vals, $options[Factory::FILTER]);
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    return $stmt->execute($vals);
  }
  
  /**
   * Returns the DB connection if possible
   * @param bool $test
   * @param array $testProperties
   * @return ?PDO
   * @throws Exception
   */
  public function getDB(bool $test = false, array $testProperties = []): ?PDO {
    if (self::$dbh !== null) {
      return self::$dbh;
    }
    try {
      $dbUser = StartupConfig::getInstance()->getDatabaseUser();
      $dbPass = StartupConfig::getInstance()->getDatabasePassword();
      $dbType = StartupConfig::getInstance()->getDatabaseType();
      $dbHost = StartupConfig::getInstance()->getDatabaseServer();
      $dbPort = StartupConfig::getInstance()->getDatabasePort();
      $dbDB = StartupConfig::getInstance()->getDatabaseDB();
      if ($test && sizeof($testProperties) == 6) { // if the connection is being tested, take credentials from argument properties
        $dbUser = $testProperties['user'];
        $dbPass = $testProperties['pass'];
        $dbType = $testProperties['type'];
        $dbHost = $testProperties['server'];
        $dbPort = $testProperties['port'];
        $dbDB = $testProperties['db'];
      }
      
      if ($dbType == 'mysql') {
        // connect as mysql
        $dsn = "mysql:dbname=$dbDB;host=$dbHost;port=$dbPort;charset=utf8mb4";
        self::$dbh = new PDO($dsn, $dbUser, $dbPass);
      }
      else if ($dbType == 'postgres') {
        // connect as postgres
        $dsn = "pgsql:dbname=$dbDB;host=$dbHost;port=$dbPort;user=$dbUser;password=$dbPass";
        self::$dbh = new PDO($dsn);
      }
      else {
        // unknown type
        if ($test) {
          return null;
        }
        throw new Exception("Fatal Error: Unknown database type specified!");
      }
      
      self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return self::$dbh;
    }
    catch (PDOException $e) {
      if ($test) {
        return null;
      }
      throw new Exception("Fatal Error! Database connection failed: " . $e->getMessage());
    }
  }
}

