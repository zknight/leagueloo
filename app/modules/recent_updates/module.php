<?php
class RecentUpdates extends \simp\Module
{
    protected function Setup($args)
    {
        $this->updates = array();
        $changed = GetCfgVar("recently_updated");
        if ($changed == false)
        {
            if (Cache::Exists("updates"))
            {
                $this->updates = Cache::Read("updates");
                global $log;
                $log->logDebug("updates from cache: " . print_r($this->updates, true));
            }
            else
            {
                $updates = \simp\Model::Find("RecentUpdate", "1 order by time desc", array());
                foreach ($updates as $update)
                {
                    $this->updates[] = array(
                        'text' => $update->text,
                        'url' => $update->url,
                        'time' => $update->time
                    );
                }
            }
        }

        if ($changed == true || !is_array($this->updates))
        {
            $max_recent = GetCfgVar("max_recent", 5);
            SetCfgVar("recently_updated", false);
            $items = array();

            // the following is based on the assumption that no two items can have the same
            // exact updated_on
            $updated_news = \simp\Model::Find(
                "News",
                "entity_type = ? and publish_on <= ? and expiration >= ? order by updated_on desc limit {$max_recent}",
                array("Program", time(), time()));
            foreach ($updated_news as $news)
            {
                $items[$news->updated_on] = array(
                    'text' => $news->title,
                    'url' => "{$news->entity_name}/news/show/{$news->id}",
                    'time' => $news->updated_on);
            }

            $updated_pages = \simp\Model::Find(
                "Page",
                "entity_type <> ? and published = ? order by updated_on desc limit {$max_recent}",
                array("Team", true));

            foreach ($updated_pages as $page)
            {
                $items[$page->updated_on] = array(
                    'text' => $page->title,
                    'url' => "/{$page->entity_name}/page/{$page->short_title}",
                    'time' => $page->updated_on);
            }

            $updated_camps = \simp\Model::Find(
                "Camp",
                "1 order by updated_on desc limit {$max_recent}",
                array());
            foreach ($updated_camps as $camp)
            {
                $items[$camp->updated_on] = array(
                    'text' => $camp->name,
                    'url' => "/camps/camp/show/{$camp->short_name}",
                    'time' => $camp->updated_on);
            }

            $updated_tournaments = \simp\Model::Find(
                "Tournament",
                "1 order by updated_on desc limit {$max_recent}",
                array());
            foreach ($updated_tournaments as $tournament)
            {
                $items[$tournament->updated_on] = array(
                    'text' => $tournament->name,
                    'url' => "/tournaments/tournament/show/{$tournament->short_name}",
                    'time' => $tournament->updated_on);
            }

            krsort($items);

            $items = array_slice($items, 0, $max_recent);
            $i = 0;
            foreach ($items as $item)
            {
                $update = \simp\Model::FindOrCreate('RecentUpdate', "update_id = ?", array($i));
                $update->text = $item['text'];
                $update->url = $item['url'];
                $update->time = $item['time'];
                $update->update_id = $i;
                $this->updates[] = $item;
                $update->Save();
                $i++;
            }
            /*
            foreach ($this->recent_updates as $update)
            {
                $this->updates[] = array(
                    'text' => $update->text,
                    'url' => $update->url,
                    'time' => $update->updated_at
                );
            }
             */

            Cache::Write("updates", $items);
        }
    }
}
