<?php
namespace simp;
//require_once "rb.php";
//require_once "model.php";

class DatabaseConfig
{

    public function __construct()
    {
        global $BASE_PATH;
        // attempt to find configuration file
        $cfg_file = $BASE_PATH . "/db/config.xml";
        $xml = simplexml_load_file($cfg_file);
        if (!$xml) 
        {
            die("Couldn't load database configuration");
        }

        $mode = $xml->mode;
        foreach ($xml->database as $db)
        {
            if ((string)$db['mode'] == $mode)
            {
                $this->db = $db;
            }
        }
    }

    public function GetDSN()
    {
        if ($this->db->adapter == 'sqlite')
        {
            $dsn = "sqlite:{$this->db->filename}";
        }
        else
        {
            $dsn = "{$this->db->adapter}:host={$this->db->host};dbname={$this->db->schema};";
        }

        return $dsn;
    }

    public function GetUser()
    {
        $user = null;
        if ($this->db->adapter != 'sqlite')
        {
            $user = $this->db->user;
        }
        return $user;
    }

    public function GetPassword()
    {
        $password = null;
        if ($this->db->adapter != 'sqlite')
        {
            $password = $this->db->password;
        }
        return $password;
    }
}

