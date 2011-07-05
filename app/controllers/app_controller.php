<?php
namespace app;
class AppController extends \simp\Controller
{
    public function Setup()
    {
        global $log;
        $log->logDebug('In: AppController::Setup()');
        $this->AddPreaction('all', 'SetCurPage');
    }

    protected function SetCurPage()
    {
        global $log;
        $log->logDebug("SetCurPage: params = " . print_r($this->_params, true));
        if (isset($this->_params['program']))
        {
            $log->logDebug("setting cur_page to: {$this->_params['program']}");
            $this->cur_page = $this->_params['program'];
        }
    }
}
