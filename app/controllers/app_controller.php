<?php
namespace app;
class AppController extends \simp\Controller
{
    protected $_context;
    public function Setup()
    {
        global $log;
        $log->logDebug('In: AppController::Setup()');
        $this->AddPreaction('all', 'SetCurPage');
        if (!isset($this->_context)) $this->_context = "none";
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

    protected function SetContext($context)
    {
        global $log;
        $log->logDebug("AppController::SetContext() setting context to $context.");
        $this->_context = $context;
    }

    protected function GetContext($context)
    {
        return $this->_context; 
    }
}
