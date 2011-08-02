<?php
namespace app\admin;
class CacheController extends \simp\Controller
{
    public function Setup()
    {
        $this->SetLayout("admin");
        $this->RequireAuthorization(
            array(
                'index',
                'show',
                'clear',
            )
        );

        $this->AddPreaction("all", "CheckAccess");
    }

    protected function CheckAccess()
    {
        if (!$this->GetUser()->super)
        {
            AddFlash("You don't have sufficient privilege for this action.");
            \Redirect(GetReturnURL());
        }
    }

    public function Index()
    {
        // check to see if cache is enabled
        $this->StoreLocation();
        $this->enabled = \Cache::$enabled;
        if ($this->enabled === true)
        {
            $this->cache_stats = apc_sma_info();
            $cache_iter = new \APCIterator('user');
            $this->cache = array('Configuration Variables' => array());
            //echo "<pre>";
            // load all the cached variables
            foreach ($cache_iter as $cache_item)
            {
                if (strpos($cache_item['key'], 'cfgvar') === 0)
                {
                    $this->cache['Configuration Variables'][] = $cache_item;
                }
                //print_r($cache_item);
            }
        }
        //echo "</pre>";
        return true;
    }
}
