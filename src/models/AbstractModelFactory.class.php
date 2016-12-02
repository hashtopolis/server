<?php

/**
 * Abstraction of all ModelFactories.
 * A ModelFactory is used to get all
 * models from Database. It handels the DB calling and caching of objects.
 */
abstract
class AbstractModelFactory {
  private static $dbh = null;
  
  /**
   * Return the Models name
   *
   * @return string The name of the model associated with this factory
   */
  abstract function getModelName();
  
  /**
   * Return the Models associated table
   *
   * This function defines table associated with this model and will be
   * used by the database abstraction to save your model in.
   *
   * @return string The name of the table associated with this factory
   */
  abstract function getModelTable();
  
  /**
   * Returns weither the associated model is cachable or not
   *
   * @return boolean True, if the object might be cached, False if not
   */
  abstract function isCachable();
  
  /**
   * Returns weither the models valid time on cache.
   *
   * Returns the time in seconds a object might life on the cache.
   * If the model shouldn't be cachable -1 shall be returned
   *
   * @return int valid time in seconds, -1 if model shouldn't be cached
   */
  abstract function getCacheValidTime();
  
  /**
   * Returns an empty instance of the associated object
   *
   * This empty object is used to get all the object properties for
   * different queries such as the get queries, where no actual object
   * is given
   */
  abstract function getNullObject();
  
  /**
   * This function inits, an objects values from a dict and returns it;
   *
   * This function is used to get objects from a certain type from db resourcebundle_get_error_message
   *
   * @return s An object of the factories type
   */
  abstract function createObjectFromDict($pk, $dict);
  
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
   */
  public function save($model) {
    $dict = $model->getKeyValueDict();
    
    $query = "INSERT INTO " . $this->getModelTable() . "(";
    $keys = array_keys($dict);
    $vals = array_values($dict);
    
    $placeHolder = "(";
    for ($i = 0; $i < count($keys); $i++) {
      if ($i != count($keys) - 1) {
        $query = $query . $keys[$i] . ",";
        $placeHolder = $placeHolder . "?,";
      }
      else {
        $query = $query . $keys[$i];
        $placeHolder = $placeHolder . "?";
      }
    }
    $query = $query . ")";
    $placeHolder = $placeHolder . ")";
    
    $query = $query . " VALUES " . $placeHolder;
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $result = $stmt->execute($vals);
    
    $id = $dbh->lastInsertId();
    if ($id != 0) {
      $model->setId($id);
      return $model;
    }
    else {
      return null;
    }
  }
  
  public function massSave($models) {
    if(sizeof($models) == 0){
      return false;
    }
    $dict = $models[0]->getKeyValueDict();
    
    $query = "INSERT INTO " . $this->getModelTable() . "(";
    $keys = array_keys($dict);
    
    $placeHolder = "(";
    for ($i = 0; $i < count($keys); $i++) {
      if ($i != count($keys) - 1) {
        $query = $query . $keys[$i] . ",";
        $placeHolder = $placeHolder . "?,";
      }
      else {
        $query = $query . $keys[$i];
        $placeHolder = $placeHolder . "?";
      }
    }
    $query = $query . ")";
    $placeHolder = $placeHolder . ")";
    
    $query = $query . " VALUES ";
    $vals = array();
    for($x=0;$x<sizeof($models);$x++){
      $query .= $placeHolder;
      if($x < sizeof($models) - 1){
        $query .= ", ";
      }
      if($models[$x]->getId() == 0){
        $models[$x]->setId(null);
      }
      $dict = $models[$x]->getKeyValueDict();
      foreach(array_values($dict) as $val){
        $vals[] = $val;
      }
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    return $stmt;
  }
  
  /**
   * Updates the database entry for the model
   *
   * This function updates the database entry for the given model
   * based on it's primary key.
   * Returns the return of PDO::execute()
   */
  public function update($model) {
    $dict = $model->getKeyValueDict();
    
    $query = "UPDATE " . $this->getModelTable() . " SET ";
    
    $keys = array_keys($dict);
    $vals = array();
    
    for ($i = 0; $i < count($keys); $i++) {
      if ($i != count($keys) - 1) {
        $query = $query . $keys[$i] . "=?,";
        array_push($vals, $dict[$keys[$i]]);
      }
      else {
        $query = $query . $keys[$i] . "=?";
        array_push($vals, $dict[$keys[$i]]);
      }
    }
    
    $query = $query . " WHERE " . $model->getPrimaryKey() . "=?";
    array_push($vals, $model->getPrimaryKeyValue());
    
    $stmt = self::getDB()->prepare($query);
    return $stmt->execute($vals);
  }
  
  /**
   * Get's a model from it's primary key.
   *
   * This function returns the model with the given primary key or null.
   * If the model is specified to be non-cached, this function will call
   * the getFromDB() function and retrn it's result. It's therefor recommended
   * to use this function
   *
   * @return Object the with pk associated model or Null
   *
   */
  public function get($pk) {
    if (!$this->isCachable()) {
      return $this->getFromDB($pk);
    }
    else {
      // ToDo: Implement some memecached magic shit
      return $this->getFromDB($pk);
    }
  }
  
  /**
   * Get's a model by it's primary key directly going to the database
   *
   * This function returns the model with the given primary key or null.
   * This function will go to the database directly neglecting the cache.
   * If the model is set to be cachable, the cache will also be updated
   *
   * @return Object the with pk associated model or Null
   */
  public function getFromDB($pk) {
    $query = "SELECT ";
    
    $keys = array_keys($this->getNullObject()->getKeyValueDict());
    
    for ($i = 0; $i < count($keys); $i++) {
      if ($i != count($keys) - 1) {
        $query = $query . $keys[$i] . ",";
      }
      else {
        $query = $query . $keys[$i];
      }
    }
    $query = $query . " FROM " . $this->getModelTable();
    
    $query = $query . " WHERE " . $this->getNullObject()->getPrimaryKey() . "=?";
    
    $stmt = self::getDB()->prepare($query);
      $stmt->execute(array(
        $pk
      )
    );
    if ($stmt->rowCount() != 0) {
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      return $this->createObjectFromDict($pk, $row);
    }
    else {
      return null;
    }
  }
  
  private function applyFilters(&$vals, $filters){
    $parts = array();
    if(!is_array($filters)){
      $filters = array($filters);
    }
  
    foreach($filters as $filter) {
      $parts[] = $filter->getQueryString();
      if(!$filter->getHasValue()){
        continue;
      }
      $v = $filter->getValue();
      if(is_array($v)){
        foreach($v as $val){
          array_push($vals, $val);
        }
      }
      else {
        array_push($vals, $v);
      }
    }
    return " WHERE ".implode(" AND ", $parts);
  }
  
  private function applyOrder($orders){
    $orderQueries = array();
    if(!is_array($orders)){
      $orders = array($orders);
    }
    foreach($orders as $order){
      $orderQueries[] = $order->getQueryString($this->getModelTable());
    }
    return " ORDER BY " . implode(", ", $orderQueries);
  }
  
  private function filterWithJoin($options) {
    $joins = $options['join'];
    if(!is_array($joins)){
      $joins = array($joins);
    }
    $keys = array_keys($this->getNullObject()->getKeyValueDict());
    $prefixedKeys = array();
    $factories = array($this);
    foreach($keys as $key){
      $prefixedKeys[] = $this->getModelTable().".".$key;
      $tables[] = $this->getModelTable();
    }
    $query = "SELECT ".Util::createPrefixedString($this->getModelTable(), $this->getNullObject()->getKeyValueDict());
    $vals = array();
    foreach($joins as $join){
      $joinFactory = $join->getOtherFactory();
      $factories[] = $joinFactory;
      $query .= ", ".Util::createPrefixedString($joinFactory->getModelTable(), $joinFactory->getNullObject()->getKeyValueDict());
    }
    $query .= " FROM " . $this->getModelTable();
    
    foreach($joins as $join){
      $joinFactory = $join->getOtherFactory();
      $localFactory = $this;
      if($join->getOverrideOwnFactory() != null){
        $localFactory = $join->getOverrideOwnFactory();
      }
      $match1 = $join->getMatch1();
      $match2 = $join->getMatch2();
      $query .= " INNER JOIN ".$joinFactory->getModelTable()." ON ".$localFactory->getModelTable().".".$match1."=".$joinFactory->getModelTable().".".$match2." ";
    }
    
    // Apply all normal filter to this query
    $vals = array();
    if (array_key_exists("filter", $options)) {
      $query .= $this->applyFilters($vals, $options['filter']);
    }
    
    // Apply order filter
    if (!array_key_exists("order", $options)) {
      // Add a asc order on the primary keys as a standard
      $oF = new OrderFilter($this->getNullObject()->getPrimaryKey(), "ASC");
      $orderOptions = array(
        $oF
      );
      $options['order'] = $orderOptions;
    }
    $query .= $this->applyOrder($options['order']);
      
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    $res = array();
    $primaryKey = array();
    $values = array();
    foreach($factories as $factory){
      $res[$factory->getModelTable()] = array();
      $values[$factory->getModelTable()] = array();
    }
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      foreach ($row as $k => $v) {
        foreach($factories as $factory){
          if(Util::startsWith($k, $factory->getModelTable())){
            $column = str_replace($factory->getModelTable().".", "", $k);
            if($column != $factory->getNullObject()->getPrimaryKey()){
              $values[$factory->getModelTable()][$column] = $v;
            }
            else{
              $primaryKey[$factory->getModelTable()] = $v;
            }
          }
        }
      }
      
      foreach($factories as $factory){
        $model = $factory->createObjectFromDict($primaryKey[$factory->getModelTable()], $values[$factory->getModelTable()]);
        array_push($res[$factory->getModelTable()], $model);
      }
    }
    
    return $res;
  }
  
  public function filter($options, $single = false) {
    // Check if we need to join and if so pass on to internal Function
    if (array_key_exists('join', $options)) {
      return $this->filterWithJoin($options);
    }
    
    $keys = array_keys($this->getNullObject()->getKeyValueDict());
    $query = "SELECT ".implode(", ", $keys)." FROM " . $this->getModelTable();
    $vals = array();
    
    if (array_key_exists("filter", $options)) {
      $query .= $this->applyFilters($vals, $options['filter']);
    }
    
    if (!array_key_exists("order", $options)) {
      // Add a asc order on the primary keys as a standard
      $oF = new OrderFilter($this->getNullObject()->getPrimaryKey(), "ASC");
      $orderOptions = array($oF);
      $options['order'] = $orderOptions;
    }
    $query .= $this->applyOrder($options['order']);
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    $objects = array();
    
    // Loop over all entries and create an object from dict for each
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $pkName = $this->getNullObject()->getPrimaryKey();
      
      $pk = $row[$pkName];
      $model = $this->createObjectFromDict($pk, $row);
      array_push($objects, $model);
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
   * @return array Returns a list of matching objects or Null
   */
  public function filterOld($options, $single = false) {
    // Check if we need to join and if so pass on to internal Function
    if (array_key_exists('join', $options)) {
      return $this->filterWithJoin($options);
    }
    else {
      $query = "SELECT ";
      $query = $query . $this->getNullObject()->getPrimaryKey() . ",";
      $keys = array_keys($this->getNullObject()->getKeyValueDict());
      
      for ($i = 0; $i < count($keys); $i++) {
        if ($i != count($keys) - 1) {
          $query = $query . $keys[$i] . ",";
        }
        else {
          $query = $query . $keys[$i];
        }
      }
      $query = $query . " FROM " . $this->getModelTable();
      
      $vals = array();
      
      if (array_key_exists("filter", $options)) {
        $query = $query . " WHERE ";
        
        
        $filterOptions = $options['filter'];
        
        for ($i = 0; $i < count($filterOptions); $i++) {
          $option = $filterOptions[$i];
          if ($option->getValue() != null) {
            $v = $option->getValue();
            if(is_array($v)){
              foreach($v as $val){
                array_push($vals, $val);
              }
            }
            else {
              array_push($vals, $v);
            }
          }
          
          if ($i != count($filterOptions) - 1) {
            $query = $query . $option->getQueryString() . " AND ";
          }
          else {
            $query = $query . $option->getQueryString();
          }
        }
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
        $query = $query . " ORDER BY ";
        $orderOptions = $options['order'];
        
        for ($i = 0; $i < count($orderOptions); $i++) {
          if ($i != count($orderOptions) - 1) {
            $order = $orderOptions[$i];
            $query = $query . $order->getQueryString() . ",";
          }
          else {
            $order = $orderOptions[$i];
            $query = $query . $order->getQueryString();
          }
        }
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
        array_push($objects, $model);
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
  }
  
  public function sumFilter($options, $sumColumn){
    $query = "SELECT SUM($sumColumn) AS sum ";
    $query = $query . " FROM " . $this->getModelTable();
  
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
      $query .= $this->applyOrder($options['order']);
    }
  
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
  
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['sum'];
  }
  
  public function countFilter($options) {
    $query = "SELECT COUNT(*) AS count ";
    $query = $query . " FROM " . $this->getModelTable();
  
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
      $query .= $this->applyOrder($options['order']);
    }
  
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
  
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row['count'];
  }
  
  /**
   * Private function used to compute a filter when a join is used.
   * This is put here to avoid putting too much stuff in the filter
   * method. It should only be called from filter()
   */
  private function filterWithJoinOld($options) {
    $joinOptions = $options['join'];
    
    $jO = $joinOptions[0];
    // Get own tables with prefixes
    $ownTable = $this->getModelTable();
    $ownTablePrefixed = Util::createPrefixedString($ownTable, $this->getPrefixedKeys($ownTable));
    
    // Get other tables prefixes and information
    $otherFactory = $jO->getOtherFactory();
    $otherTable = $jO->getOtherFactory()->getModelTable();
    $otherTablePrefixed = Util::createPrefixedString($otherTable, $this->getPrefixedKeys($otherTable));
    
    // Get matching colums
    $match1 = $jO->getMatch1();
    $match2 = $jO->getMatch2();
    $query = "SELECT " . $ownTablePrefixed . " , " . $otherTablePrefixed . " FROM " . $ownTable . " AS " . $ownTable . " INNER JOIN " . $otherTable . " ON " . $ownTable . "." . $match1 . "=" . $otherTable . "." . $match2;
    
    // Apply all normal filter to this query
    $vals = array();
    if (array_key_exists("filter", $options)) {
      $query = $query . " WHERE ";
      $filterOptions = $options['filter'];
      
      for ($i = 0; $i < count($filterOptions); $i++) {
        $option = $filterOptions[$i];
        array_push($vals, $option->getValue());
        
        $table = $ownTable;
        if($option->getFactory() != null){
          $table = $option->getFactory()->getModelTable();
        }
        
        if ($i != count($filterOptions) - 1) {
          $query = $query . $option->getQueryString($table) . " AND ";
        }
        else {
          $query = $query . $option->getQueryString($table);
        }
      }
    }
    
    // Apply order filter
    if (!array_key_exists("order", $options)) {
      // Add a asc order on the primary keys as a standard
      $oF = new OrderFilter($this->getNullObject()->getPrimaryKey(), "ASC");
      $orderOptions = array(
        $oF
      );
      $options['order'] = $orderOptions;
    }
    if (count($options['order']) != 0) {
      $query = $query . " ORDER BY ";
      $orderOptions = $options['order'];
      
      for ($i = 0; $i < count($orderOptions); $i++) {
        if ($i != count($orderOptions) - 1) {
          $order = $orderOptions[$i];
          $query = $query . $order->getQueryString($ownTable) . ",";
        }
        else {
          $order = $orderOptions[$i];
          $query = $query . $order->getQueryString($ownTable);
        }
      }
    }
    
    $dbh = self::getDB();
    echo $query;
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    
    // Seperate each table into two dict to create the corresponding OBJECTS
    $ownTablePref = $ownTable . ".";
    $otherTablePref = $otherTable . ".";
    
    $res = array();
    $res[$ownTable] = array();
    $res[$otherTable] = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $ownKeys = array();
      $ownPk = null;
      
      $otherKeys = array();
      $otherPk = null;
      
      foreach ($row as $k => $v) {
        if (Util::startsWith($k, $ownTablePref)) {
          $nK = str_replace($ownTablePref, "", $k);
          if ($nK != $this->getNullObject()->getPrimaryKey()) {
            $ownKeys[$nK] = $v;
          }
          else {
            $ownPk = $v;
          }
        }
        elseif (Util::startsWith($k, $otherTablePref)) {
          $nK = str_replace($otherTablePref, "", $k);
          if ($nK != $otherFactory->getNullObject()->getPrimaryKey()) {
            $otherKeys[$nK] = $v;
          }
          else {
            $otherPk = $v;
          }
        }
      }
      
      $ownModel = $this->createObjectFromDict($ownPk, $ownKeys);
      $otherModel = $otherFactory->createObjectFromDict($otherPk, $otherKeys);
      
      array_push($res[$ownTable], $ownModel);
      array_push($res[$otherTable], $otherModel);
    }
    
    return $res;
  }
  
  /**
   * This function gives back a dict with all colums in the table
   * and their prefixed equivalent.
   *
   * As an example, the column "name" in "user" becomes "name" => "user.name"
   */
  private function getPrefixedKeys($table) {
    $dbh = self::getDB();
    
    $query = "DESCRIBE `$table`"; // For whatever reason, prepared statements are not working on this one. Or i'm to stupid.
    
    $stmt = $dbh->prepare($query);
    $stmt->execute(array(
      $table
    )
    );
    
    $dict = array();
    $fields = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($fields as $f) {
      $dict[$f] = "`" . $table . "." . $f . "`";
    }
    
    return $dict;
  }
  
  /**
   * Deletes the given model
   *
   * This function deletes the given and also cleanes the cache from it.
   * It returns the return of the execute query.
   */
  public function delete($model) {
    if ($model != null) {
      $query = "DELETE FROM " . $this->getModelTable() . " WHERE " . $model->getPrimaryKey() . " = ?";
      $stmt = self::getDB()->prepare($query);
      return $stmt->execute(array(
        $model->getPrimaryKeyValue()
      )
      );
    }
  }
  
  /**
   * Deletes all matching objects of the model. Matching objects can be given by query filters
   *
   * @param $options
   * @return bool
   */
  public function massDeletion($options) {
    $query = "DELETE FROM " . $this->getModelTable();
    
    $vals = array();
    
    if (array_key_exists("filter", $options)) {
      $query .= $this->applyFilters($vals, $options['filter']);
    }
    
    $dbh = self::getDB();
    $stmt = $dbh->prepare($query);
    $stmt->execute($vals);
    return $stmt;
  }
  
  /**
   * Updates all matching objects of the model. Matching objects can be given by query filters
   *
   * @param $options
   * @return bool
   */
  public function massUpdate($options) {
    $query = "UPDATE " . $this->getModelTable();
    
    $vals = array();
  
    if (array_key_exists("update", $options)) {
      $query = $query . " SET ";
    
    
      $updateOptions = $options['update'];
      if(!is_array($updateOptions)){
        $updateOptions = array($updateOptions);
      }
      $vals = array();
    
      for ($i = 0; $i < count($updateOptions); $i++) {
        $option = $updateOptions[$i];
        array_push($vals, $option->getValue());
      
        if ($i != count($updateOptions) - 1) {
          $query = $query . $option->getQuery() . " , ";
        }
        else {
          $query = $query . $option->getQuery();
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
   */
  public static function getDB($test = false) {
    global $CONN;
    
    $dsn = 'mysql:dbname=' . $CONN['db'] . ";" . "host=" . $CONN['server'];
    $user = $CONN['user'];
    $password = $CONN['pass'];
    
    if (self::$dbh !== null) {
      return self::$dbh;
    }
    
    try {
      self::$dbh= new PDO($dsn, $user, $password);
      self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return self::$dbh;
    }
    catch (PDOException $e) {
      if ($test) {
        return false;
      }
      die("Fatal Error ! Database connection failed");
    }
  }
}

?>
