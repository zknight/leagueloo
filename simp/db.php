<?
namespace simp;
require_once "rb.php";
require_once "model.php";
class ModelNameFormatter implements \RedBean_IModelFormatter
{
    public function formatModel($model)
    {
        global $log;
        //$log->logDebug("attempting to format: $model");
        return ClassCase($model);
    }
}

class DB
{
    // constants
    private $_db;
    private $_toolbox;

    // singleton
    public static function Instance()
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
        //$this->_toolbox = \RedBean_Setup::kickStartDev("sqlite:db/development.db");
        //$this->_db = $this->_toolbox->getRedBean();
        \R::setup("sqlite:db/development.db");
        \RedBean_ModelHelper::setModelFormatter(new ModelNameFormatter);
        //\R::debug(true);
    }

    private function ModelPath($model_name)
    {
        global $APP_BASE_PATH;
        $path = $APP_BASE_PATH . "/models/" . $model_name . ".php";
        return $path;
    }
    
    public function Load($model_name, $id)
    {
        $bean_name = SnakeCase($model_name);
        require_once $this->ModelPath($bean_name);
        return \R::load($bean_name, $id);
    }
    
    public function Create($model_name)
    {
        global $log;
        $bean_name = SnakeCase($model_name);
        require_once $this->ModelPath($bean_name);
        return \R::dispense($bean_name);
    }

    public function Find($model_name, $conditions, $values)
    {
        $bean_name = SnakeCase($model_name);
        require_once $this->ModelPath($bean_name);
        global $log;
        $log->logDebug("find($bean_name, $conditions, " . print_r($values, true). ")");
        return \R::find($bean_name, $conditions, $values);
    }

    public function FindAll($model_name)
    {
        $bean_name = SnakeCase($model_name);
        require_once $this->ModelPath($bean_name);
        return \R::find($bean_name);
    }

    public function FindOne($model_name, $conditions, $values)
    {
        $bean_name = SnakeCase($model_name);
        require_once $this->ModelPath($bean_name);
        return \R::findOne($bean_name, $conditions, $values);
    }

    public function Save($model)
    {
        return \R::store($model);
    }

    public function Delete($model)
    {
        \R::trash($model);
    }

}

