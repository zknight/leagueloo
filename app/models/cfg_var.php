<?
// config var is cached, so on save, add it to the cache
class CfgVar extends \simp\Model
{
    // name
    // value
    public function AfterSave()
    {
        Cache::Write("cfgvar:{$this->name}", $this->value);
    }
}
