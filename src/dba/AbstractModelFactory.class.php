<?php

namespace DBA;

// this is needed for tests (not really great, but no other way currently)
use JsonSchema\Constraints\Drafts\Draft06\AnyOfConstraint;

use MassUpdateSet;
use PDO, PDOStatement, PDOException;
use UI;

/**
 * Abstraction of all ModelFactories.
 * A ModelFactory is used to get all
 * models from Database. It handles the DB calling and caching of objects.
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
   * Returns wether the associated model is able to be cached or not
   *
   * @return boolean True, if the object might be cached, False if not
   */
  abstract function isCachable(): bool;
  
  /**
   * Returns wether the models valid time on cache.
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
   * @return AbstractModel
   */
  abstract function getNullObject(): AbstractModel;
  
  /**
   * This function inits, an objects values from a dict and returns it;
   *
   * This function is used to get objects from a certain type from db resourcebundle_get_error_message
   *
   * @param $pk string primary key
   * @param $dict array dict of values and keys
   * @return AbstractModel An object of the factories type
   */
  abstract function createObjectFromDict(string $pk, array $dict): AbstractModel;
  
  public function getMappedModelTable(): string {
    if ($this->isMapping()) {
      return self::MAPPING_PREFIX . $this->getModelName();
    }
    return $this->getModelName();
  }
  
  private static function getMappedModelKeys(AbstractModel $model): array {
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
   * @param $model AbstractModel model to save
   * @return AbstractModel|null
   */
  public function save(AbstractModel $model): ?AbstractModel {
    $dict = $model->getKeyValueDict();
    
    $query = "INSERT INTO " . $this->getMappedModelTable();
    $vals = array_values($dict);
    $keys = self::getMappedModelKeys($model);
    
    $query .= " (" . implode(",", $keys) . ") ";
    $placeHolder = " (" . implode(",", array_fill(0, count($keys), "?")) . ")";
    
    $query = $query . " VALUES " . $placeHolder;
    
    $dbh = $this->getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
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
  
  /**
   * @param $arr array
   * @return Filter[]
   */
  private function getFilters(array $arr): array {
    if (!is_array($arr['filter'])) {
      $arr['filter'] = array($arr['filter']);
    }
    if (isset($arr['filter'])) {
      return $arr['filter'];
    }
    return array();
  }
  
  /**
   * @param $arr array
   * @return Order[]
   */
  private function getOrders(array $arr): array {
    if (!is_array($arr['order'])) {
      $arr['order'] = array($arr['order']);
    }
    if (isset($arr['order'])) {
      return $arr['order'];
    }
    return array();
  }
  
  /**
   * @param $arr array
   * @return Group[]
   */
  private function getGroups(array $arr): array {
    if (!is_array($arr['group'])) {
      $arr['group'] = array($arr['group']);
    }
    if (isset($arr['group'])) {
      return $arr['group'];
    }
    return array();
  }
  
  /**
   * @param $arr array
   * @return Join[]
   */
  private function getJoins(array $arr): array {
    if (!is_array($arr['join'])) {
      $arr['join'] = array($arr['join']);
    }
    if (isset($arr['join'])) {
      return $arr['join'];
    }
    return array();
  }
  
  /**
   * Updates the database entry for the model
   *
   * This function updates the database entry for the given model
   * based on it's primary key.
   * Returns the return of PDO::execute()
   * @param $model AbstractModel model to update
   * @return PDOStatement
   */
  public function update(AbstractModel $model): PDOStatement {
    $dict = $model->getKeyValueDict();
    
    $query = "UPDATE " . $this->getMappedModelTable() . " SET ";
    
    $values = array_values($dict);
    $keys = self::getMappedModelKeys($model);
    
    for ($i = 0; $i < count($keys); $i++) {
      if ($i != count($keys) - 1) {
        $query .= $keys[$i] . "=?, ";
      }
      else {
        $query .= $keys[$i] . "=?";
      }
    }
    
    $query .= " WHERE " . $model->getPrimaryKey() . "=?";
    $values[] = $model->getPrimaryKeyValue();
    
    $stmt = $this->getDB()->prepare($query);
    $stmt->execute($values);
    return $stmt;
  }
  
  /**
   * Atomically sets the given keys of this model to the given values
   *
   * Returns the return of PDO::execute()
   * @param $model AbstractModel primary key of model
   * @param $arr array key-value associations for update
   * @return PDOStatement
   */
  public function mset(AbstractModel &$model, array $arr): PDOStatement {
    $query = "UPDATE " . $this->getMappedModelTable() . " SET ";
    $elements = [];
    $values = [];
    foreach ($arr as $key => $val) {
      $elements[] = self::getMappedModelKey($model, $key) . "=? ";
      $values[] = $val;
    }
    $query .= implode(", ", $elements);
    
    $query = $query . " WHERE " . $model->getPrimaryKey() . "=?";
    $values[] = $model->getPrimaryKeyValue();
    
    $stmt = $this->getDB()->prepare($query);
    $stmt->execute($values);
    
    $model = $this->get($model->getPrimaryKeyValue());
    return $stmt;
  }
  
  /**
   * Atomically sets the given key of this model to the given value
   *
   * Returns the return of PDO::execute()
   * @param $model AbstractModel primary key of model
   * @param $key string key of the column to update
   * @param $value
   * @return PDOStatement
   */
  public function set(AbstractModel &$model, string $key, $value): PDOStatement {
    $query = "UPDATE " . $this->getMappedModelTable() . " SET " . self::getMappedModelKey($model, $key) . "=?";
    
    $values = [];
    $query = $query . " WHERE " . $model->getPrimaryKey() . "=?";
    $values[] = $value;
    $values[] = $model->getPrimaryKeyValue();
    
    $stmt = $this->getDB()->prepare($query);
    $stmt->execute($values);
    
    $model = $this->get($model->getPrimaryKeyValue());
    return $stmt;
  }
  
  /**
   * Increments the given key of this model by the given value
   *
   * Returns the return of PDO::execute()
   * @param $model AbstractModel primary key of model
   * @param $key string key of the column to update
   * @param $value int amount of increment
   * @return PDOStatement
   */
  public function inc(AbstractModel &$model, string $key, int $value = 1): PDOStatement {
    $mapped_key = self::getMappedModelKey($model, $key);
    $query = "UPDATE " . $this->getMappedModelTable() . " SET " . $mapped_key . "=" . $mapped_key . "+?";
    
    $values = [];
    $query = $query . " WHERE " . $model->getPrimaryKey() . "=?";
    $values[] = $value;
    $values[] = $model->getPrimaryKeyValue();
    
    $stmt = $this->getDB()->prepare($query);
    $stmt->execute($values);
    
    $model = $this->get($model->getPrimaryKeyValue());
    return $stmt;
  }
  
  /**
   * Decrements the given key of this model by the given value
   *
   * Returns the return of PDO::execute()
   * @param $model AbstractModel primary key of model
   * @param $key string key of the column to update
   * @param $value int amount of increment
   * @return PDOStatement
   */
  public function dec(AbstractModel &$model, string $key, int $value = 1): PDOStatement {
    $mapped_key = self::getMappedModelKey($model, $key);
    $query = "UPDATE " . $this->getMappedModelTable() . " SET " . $mapped_key . "=" . $mapped_key . "-?";
    
    $values = [];
    $query = $query . " WHERE " . $model->getPrimaryKey() . "=?";
    $values[] = $value;
    $values[] = $model->getPrimaryKeyValue();
    
    $stmt = $this->getDB()->prepare($query);
    $stmt->execute($values);
    
    $model = $this->get($model->getPrimaryKeyValue());
    return $stmt;
  }
  
  /**
   * @param $models AbstractModel[]
   * @return bool|PDOStatement
   */
  public function massSave(array $models): bool|PDOStatement {
    if (sizeof($models) == 0) {
      return false;
    }
    
    $keys = self::getMappedModelKeys($models[0]);
    $query = "INSERT INTO " . $this->getMappedModelTable();
    
    $query .= " (" . implode(",", $keys) . ") ";
    $placeHolder = " (" . implode(",", array_fill(0, count($keys), "?")) . ")";
    
    $query = $query . " VALUES ";
    $vals = array();
    for ($x = 0; $x < sizeof($models); $x++) {
      $query .= $placeHolder;
      if ($x < sizeof($models) - 1) {
        $query .= ", ";
      }
      if ($models[$x]->getId() === 0) {
        $models[$x]->setId(null);
      }
      $dict = $models[$x]->getKeyValueDict();
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
    
    if (array_key_exists("filter", $options)) {
      $query .= $this->applyFilters($vals, $options['filter']);
    }
    
    if (!array_key_exists("order", $options)) {
      // Add a asc order on the primary keys as a standard
      $oF = new OrderFilter($this->getNullObject()->getPrimaryKey(), "ASC");
      $orderOptions = array(
        $oF
      );
      $options['order'] = $orderOptions;
    }
    if (count($options['order']) != 0) {
      $query .= $this->applyOrder($this->getOrders($options));
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['column_' . strtolower($op)];
  }
  
  public function multicolAggregationFilter($options, $aggregations) {
    //$options: as usual
    //$columns: array of Aggregation objects
    
    $elements = [];
    foreach ($aggregations as $aggregation) {
      $elements[] = $aggregation->getQueryString($this);
    }
    
    $query = "SELECT " . join(",", $elements);
    $query = $query . " FROM " . $this->getMappedModelTable();
    
    $vals = array();
    
    if (array_key_exists('join', $options)) {
      $query .= $this->applyJoins($options['join']);
    }
    if (array_key_exists("filter", $options)) {
      $query .= $this->applyFilters($vals, $options['filter']);
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }
  
  public function sumFilter($options, $sumColumn) {
    $query = "SELECT SUM(" . self::getMappedModelKey($this->getNullObject(), $sumColumn) . ") AS sum ";
    $query = $query . " FROM " . $this->getMappedModelTable();
    
    $vals = array();
    
    if (array_key_exists("filter", $options)) {
      $query .= $this->applyFilters($vals, $options['filter']);
    }
    
    if (!array_key_exists("order", $options)) {
      // Add a asc order on the primary keys as a standard
      $oF = new OrderFilter($this->getNullObject()->getPrimaryKey(), "ASC");
      $orderOptions = array(
        $oF
      );
      $options['order'] = $orderOptions;
    }
    if (count($options['order']) != 0) {
      $query .= $this->applyOrder($this->getOrders($options));
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['sum'];
  }
  
  public function countFilter($options) {
    $query = "SELECT COUNT(*) AS count ";
    $query = $query . " FROM " . $this->getMappedModelTable();
    
    $vals = array();
    
    if (array_key_exists('join', $options)) {
      $query .= $this->applyJoins($options['join']);
    }
    
    if (array_key_exists("filter", $options)) {
      $query .= $this->applyFilters($vals, $options['filter']);
    }
    
    if (!array_key_exists("order", $options)) {
      // Add a asc order on the primary keys as a standard
      $oF = new OrderFilter($this->getNullObject()->getPrimaryKey(), "ASC");
      $orderOptions = array(
        $oF
      );
      $options['order'] = $orderOptions;
    }
    if (count($options['order']) != 0) {
      $query .= $this->applyOrder($options['order']);
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
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
   * @return AbstractModel|null the with pk associated model or Null
   */
  public function get($pk) {
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
   * @return AbstractModel|null the with pk associated model or Null
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
   * $options['filter'] is an array of QueryFilter options
   * $options['order'] is an array of OrderFilter options
   * $options['join'] is an array of JoinFilter options
   *
   * @param $options array containing option settings
   * @return AbstractModel[]|AbstractModel Returns a list of matching objects or Null
   */
  private function filterWithJoin(array $options): array|AbstractModel {
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
      $query .= " INNER JOIN " . $joinFactory->getMappedModelTable() . " ON " . $localFactory->getMappedModelTable() . "." . $match1 . "=" . $joinFactory->getMappedModelTable() . "." . $match2 . " ";
    }
    
    // Apply all normal filter to this query
    $vals = array();
    if (array_key_exists("filter", $options)) {
      $query .= $this->applyFilters($vals, $options['filter']);
    }
    
    if (array_key_exists("group", $options)) {
      $query .= $this->applyGroups($this->getGroups($options));
    }
    
    // Apply order filter
    if (!array_key_exists("order", $options)) {
      // Add a asc order on the primary keys as a standard
      $oF = new OrderFilter($this->getNullObject()->getPrimaryKey(), "ASC");
      $orderOptions = array($oF);
      $options['order'] = $orderOptions;
    }
    $query .= $this->applyOrder($options['order']);
    
    if (array_key_exists("limit", $options)) {
      $query .= $this->applyLimit($options['limit']);
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
        foreach ($factories as $factory) {
          if (Util::startsWith($k, $factory->getMappedModelTable())) {
            $column = str_replace($factory->getMappedModelTable() . ".", "", $k);
            $values[$factory->getModelTable()][$column] = $v;
          }
        }
      }
      
      foreach ($factories as $factory) {
        $model = $factory->createObjectFromDict($values[$factory->getModelTable()][$factory->getNullObject()->getPrimaryKey()], $values[$factory->getModelTable()]);
        $res[$factory->getModelTable()][] = $model;
      }
    }
    
    return $res;
  }
  
  /**
   * @param array $options
   * @param bool $single
   * @return array|AbstractModel|null
   */
  public function filter(array $options, bool $single = false) {
    // Check if we need to join and if so pass on to internal Function
    if (array_key_exists('join', $options)) {
      return $this->filterWithJoin($options);
    }
    
    $keys = self::getMappedModelKeys($this->getNullObject());
    $query = "SELECT " . implode(", ", $keys) . " FROM " . $this->getMappedModelTable();
    $vals = array();
    
    if (array_key_exists("filter", $options)) {
      $query .= $this->applyFilters($vals, $options['filter']);
    }
    
    if (array_key_exists("group", $options)) {
      $query .= $this->applyGroups($this->getGroups($options));
    }
    
    if (!array_key_exists("order", $options)) {
      // Add a asc order on the primary keys as a standard
      $oF = new OrderFilter($this->getNullObject()->getPrimaryKey(), "ASC");
      $orderOptions = array($oF);
      $options['order'] = $orderOptions;
    }
    $query .= $this->applyOrder($options['order']);
    
    if (array_key_exists("limit", $options)) {
      $query .= $this->applyLimit($options['limit']);
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    $objects = array();
    
    // Loop over all entries and create an object from dict for each
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $pkName = $this->getNullObject()->getPrimaryKey();
      
      $pk = $row[$pkName];
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
   * @return string
   */
  private function applyFilters(&$vals, Filter|array $filters): string {
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
      if (is_array($v)) {
        foreach ($v as $val) {
          $vals[] = $val;
        }
      }
      else {
        $vals[] = $v;
      }
    }
    return " WHERE " . implode(" AND ", $parts);
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
  
  private function applyJoins($joins): string {
    $query = "";
    foreach ($joins as $join) {
      $joinFactory = $join->getOtherFactory();
      $localFactory = $this;
      if ($join->getOverrideOwnFactory() != null) {
        $localFactory = $join->getOverrideOwnFactory();
      }
      $match1 = self::getMappedModelKey($localFactory->getNullObject(), $join->getMatch1());
      $match2 = self::getMappedModelKey($joinFactory->getNullObject(), $join->getMatch2());
      $query .= " INNER JOIN " . $joinFactory->getMappedModelTable() . " ON " . $localFactory->getMappedModelTable() . "." . $match1 . "=" . $joinFactory->getMappedModelTable() . "." . $match2 . " ";
    }
    return $query;
  }
  
  //applylimit is slightly different than the other apply functions, since you can only limit by a single value
  //the $limit argument is a single object LimitFilter object instead of an array of objects.
  private function applyLimit($limit): string {
    return " LIMIT " . $limit->getQueryString($this);
  }
  
  private function applyGroups($groups): string {
    $groupsQueries = array();
    if (!is_array($groups)) {
      $groups = array($groups);
    }
    foreach ($groups as $group) {
      $groupsQueries[] = $group->getQueryString($this, true);
    }
    return " GROUP BY " . implode(", ", $groupsQueries);
  }
  
  /**
   * Deletes the given model
   *
   * This function deletes the given and also cleans the cache from it.
   * It returns the return of the execute query.
   * @param $model AbstractModel
   * @return bool
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
   */
  public function massDeletion(array $options): PDOStatement {
    $query = "DELETE FROM " . $this->getMappedModelTable();
    
    $vals = array();
    
    if (array_key_exists("filter", $options)) {
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
   * @return null
   */
  public function massSingleUpdate($matchingColumn, $updateColumn, array $updates) {
    $query = "UPDATE " . $this->getMappedModelTable();
    
    if (sizeof($updates) == 0) {
      return null;
    }
    $query .= " SET ".self::getMappedModelKey($this->getNullObject(),$updateColumn)." = ( CASE ";
    
    $vals = array();
    
    foreach ($updates as $update) {
      $query .= $update->getMassQuery(self::getMappedModelKey($this->getNullObject(),$matchingColumn));
      $vals[] = $update->getMatchValue();
      $vals[] = $update->getUpdateValue();
    }
    
    $matchingArr = array();
    foreach ($updates as $update) {
      $vals[] = $update->getMatchValue();
      $matchingArr[] = "?";
    }
    
    $query .= "END) WHERE ".self::getMappedModelKey($this->getNullObject(), $matchingColumn)." IN (" . implode(",", $matchingArr) . ")";
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    return $stmt->execute($vals);
  }
  
  public function massUpdate($options): bool {
    $query = "UPDATE " . $this->getMappedModelTable();
    
    $vals = array();
    
    if (array_key_exists("update", $options)) {
      $query = $query . " SET ";
      
      
      $updateOptions = $options['update'];
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
    
    if (array_key_exists("filter", $options)) {
      $query .= $this->applyFilters($vals, $options['filter']);
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    return $stmt->execute($vals);
  }
  
  /**
   * Returns the DB connection if possible
   * @param bool $test
   * @return PDO
   */
  public function getDB(bool $test = false): ?PDO {
    if (!$test) {
      $dsn = 'mysql:dbname=' . DBA_DB . ";host=" . DBA_SERVER . ";port=" . DBA_PORT;
      $user = DBA_USER;
      $password = DBA_PASS;
    }
    else {
      global $CONN;
      // The utf8mb4 is here to force php to connect with that encoding, so you can save emoji's or other non ascii chars (specifically, unicode characters outside of the BMP) into the database. 
      // If you are running into issues with this line, we could make this configurable.
      $dsn = 'mysql:dbname=' . $CONN['db'] . ";host=" . $CONN['server'] . ";port=" . $CONN['port'] . ";charset=utf8mb4";
      $user = $CONN['user'];
      $password = $CONN['pass'];
    }
    
    if (self::$dbh !== null) {
      return self::$dbh;
    }
    
    try {
      self::$dbh = new PDO($dsn, $user, $password);
      self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return self::$dbh;
    }
    catch (PDOException $e) {
      if ($test) {
        return null;
      }
      UI::printError(UI::ERROR, "Fatal Error! Database connection failed: " . $e->getMessage());
      return null;
    }
  }
}

