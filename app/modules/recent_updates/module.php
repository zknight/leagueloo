<?php
class RecentUpdates extends \simp\Module
{
    protected function Setup($args)
    {
        $changed = GetCfgVar("recently_updated");
        if ($changed == false)
        {
            $this->updates = Cache::Read("updates");
            global $log;
            $log->logDebug("updates from cache: " . print_r($this->updates));
        }

        if ($changed == true || !is_array($this->updates))
        {
            $this->updates = array();

            $this->recent_updates = \simp\Model::Find(
                "RecentUpdate", 
                "1 order by updated_at desc",
                array());

            foreach ($this->recent_updates as $update)
            {
                $this->updates[] = array(
                    'text' => $update->text,
                    'url' => $update->url,
                    'time' => $update->updated_at
                );
            }

            Cache::Write("updates", $this->updates);
        }
    }
}
