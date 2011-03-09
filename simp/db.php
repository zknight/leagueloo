<?
class DB
{
  // constants
  private $db;
  private $required;

  // singleton
  public static function GetDB()
  {
    static $DB;
    if (isset($DB))
    {
      return $DB;
    }

    $DB = new DB("sqlite:db/blokc.db");
    return $DB;
  }

  private function __construct($dbfile)
  {
    $required = array();
    try
    {
      $this->db = new PDO($dbfile);
    }
    catch (PDOException $e)
    {
      die("<p class='warn'>" . $e->getMessage() . "</p>");
    }

  }

  private function GetTable($model)
  {
    global $TABLES;
    return $TABLES[$model]['table'];
  }

  /*
  private function GetClass($model)
  {
    global $TABLES;
    return $TABLES[$model]['class'];
  }
   */

  function Fetch($table, $fields, $cond = array(), $opt = array()/*, $class = ''*/)
  {
    $cols = "";
    if (is_array($fields))
      $cols = implode($fields, ",");
    else
      $cols = $fields;

    $cond_clause = "";

    if (count($cond) > 0)
    {
      $ca = array();
      foreach ($cond as $col => $val)
      {
        $ca[] = "$col = :$col" . "_c";
        $data[":$col" . "_c"] = $val;
      }
      $cstr = implode($ca, " AND ");
      $cond_clause = "WHERE $cstr";
    }
    $q = "SELECT $cols FROM $table $cond_clause";
    $stm = $this->db->prepare($q);
    if ($stm != NULL)
    {
      if ($stm->execute($data))
      {
        //echo '<pre>' . $stm->queryString . "\n";
        //print_r($data);
        $mode = PDO::FETCH_ASSOC;
        $stm->setFetchMode($mode);

        $result = $stm->fetchAll();
        //print_r($result);
        //echo '</pre>';
      }
    }
    else
    {
      $err = $this->db->errorInfo();
      $result = "";
      echo "<p class='warn'>Cannot execute query: " . $q;
      echo "<br />Reason: ";
      foreach ($err as $estr)
      {
        echo "<br />$estr";
      }
      echo "</p>";
    }
    return $result;
  }

  function FetchRow($table, $cond)
  {
    return $this->Fetch($table, '*', $cond);
  }

  function AddRow($table, $data)
  {
    foreach ($data as $col => $val)
    {
      $cols[] = $col;
      $vars[] = ":$col";
    }
    $q = "INSERT INTO $table (" . implode($cols, ',');
    $q .= ") VALUES (" . implode($vars, ',') . ")";
    Puts($q);
    Puts(print_r($data, true));
    $statement = $this->db->prepare($q);
    if ($statement != NULL)
      $statement->execute($data);
    else
    {
      echo "<p class='warn'>Cannot execute query: " . $q . "</p>";
      return false;
    }
    return true;
  }

  function Delete($table, $cond)
  {
    Puts("Deleting where cond = ");
    foreach ($cond as $col => $val)
    {
      $p = ":$col"."_c";
      $ca[] = "$col = $p";
      $v[$p] = $val;
    }
    $cstr = implode($ca, " AND ");
    $q = "DELETE FROM $table WHERE $cstr";
    $stm = $this->db->prepare($q); 
    if ($stm != NULL)
      $stm->execute($v);
    else
    {
      echo "<p class='warn'>Cannot execute query: " . $q . "</p>";
      return false;
    }
    return true;
  }

  function UpdateRow($table, $data, $cond)
  {
    // UPDATE table SET col = :col, col2 = :col2 WHERE col = :col
    foreach ($data as $col => $val)
    {
      $p = ":$col"."_d";
      $da[] = "$col = $p";
      $v[$p] = $val;
    }
    foreach ($cond as $col => $val)
    {
      $p = ":$col"."_c";
      $ca[] = "$col = $p";
      $v[$p] = $val;
    }

    $cstr = implode($ca, " AND ");
    $dstr = implode($da, ", ");
    $q = "UPDATE $table SET $dstr WHERE $cstr";
    //echo $q . "\n";
    $stm = $this->db->prepare($q); 
    if ($stm != NULL)
      $stm->execute($v);
    else
    {
      echo "<p class='warn'>Cannot execute query: " . $q . "</p>";
      return false;
    }
    return true;
  }

  /* Find Multiple Models */
  /*
  function FindAll($model, $opt = array())
  {
    if (!$this->required[$model])
    {
      global $APP_PATH;
      //$table = $this->GetTable($model);
      require_once($APP_PATH . "models/$model.php");
      $this->required[$model] = true;
    }
    $table = $this->GetTable($model);
    $models = $this->Fetch($table, "*", $opt, ClassCase($model));
    return $models;
  }
   */

  /* Find First model */
  /*
  function Find($model, $opt = array())
  {
    $models = $this->FindAll($model, $opt);
    //print_r($models);
    return $models[0];
  }
  */
  /*
  function Create($model, $data)
  {
    try
    {
      $table = $this->GetTable($model);
      //echo "would create an entry in $table";
      foreach ($data as $col => $val)
      {
        $cols[] = $col;
        $vars[] = ":$col";
      }
      $q = "INSERT INTO $table (" . implode($cols, ',');
      $q .= ") VALUES (" . implode($vars, ',') . ")";
      Puts($q);
      $statement = $this->db->prepare($q);
      if ($statement != NULL)
        $statement->execute($data);
      else
      {
        echo "<p class='warn'>Cannot execute query: " . $q . "</p>";
        return false;
      }

    }
    catch (PDOException $e)
    {
      echo "<p class='warn'>" . $e->getMessage() . "</p>";
      return false;
    }
    return true;
    
  }

   */
  /*
  function Update($model, $id, $data)
  {
    $table = $this->GetTable($model);
    foreach ($data as $col => $val)
    {
       $da[] = "$col = :$col";
       $v[":$col"] = $val;
    }
    $dstr = implode($da, ", ");
    $q = "UPDATE $table SET $dstr WHERE id = $id";
    Puts($q);
    $stm = $this->db->prepare($q);
    if ($stm != NULL)
      $stm->execute($v);
    else
    {
      echo "<p class='warn'>Cannot execute query: " . $q . "</p>";
      return false;
    }
    return true;
  }
   */
}
