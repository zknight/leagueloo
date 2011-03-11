<?
namespace simp;
require_once "rb.php";
class ModelNameFormatter implements \RedBean_IModelFormatter
{
    public function formatModel($model)
    {
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
    }
    
    public function Load($model_name, $id)
    {
        return \R::load(SnakeCase($model_name), $id);
    }
    
    public function Create($model_name)
    {
        global $log;
        $log->logDebug(SnakeCase($model_name));
        return \R::dispense(SnakeCase($model_name));
    }

    public function Find($model_name, $conditions, $values)
    {
        return \R::find(SnakeCase($model_name), $conditions, $valus);
    }

    public function FindAll($model_name)
    {
        return \R::find(SnakeCase($model_name));
    }

    public function FindOne($model_name, $conditions, $values)
    {
        return \R::findOne(SnakeCase($model_name), $conditions, $values);
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

