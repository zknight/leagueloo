<?php
/// SectionMenu maps section specific links
/// Programs: news, events, teams (if enabled), ???
/// Teams: news, events, ???
class SectionMenu extends \simp\Module
{

    protected function Setup($args)
    {
        $this->links = array();
        $current = $args['current'];
        $entity_type = $args['entity_type'];
        $entity_id = $args['entity_id'];

        $pages = Page::GetPagesForLocation($entity_type, $entity_id, Page::MAIN_MENU);

        // $args should have current page, entity_type, entity_id
        if ($entity_type === "Main")
        {
            $this->links['News'] = array(
                'link' => Path::home(),
                'class' => ($current == 'index' ? 'selected' : ''),
            );
            $this->links['Events'] = array(
                'link' => Path::event_calendar("main", 0),
                'class' => ($current == 'calendar' ? 'selected' : ''),
            );
            foreach ($pages as $page)
            {
                $this->links[$page->title] = array(
                    'link' => Path::main('page', $page->short_title),
                    'class' => ($current == $page->short_title ? 'selected' : '')
                );
            }
        }
        else if ($entity_type === "Program")
        {
            $program = \simp\Model::FindById("Program", $entity_id);

            $this->links['About'] = array(
                'link' => Path::Relative("{$program->name}/about"),
                'class' => ($current == 'about' ? 'selected' : ''),
            );
                
            $this->links['News'] = array(
                'link' => Path::Relative($program->name),
                'class' => ($current == 'index' ? 'selected' : ''),
            );

            $this->links['Events'] = array(
                'link' => Path::event_calendar("program", $program->id),
                'class' => ($current == 'calendar' ? 'selected' : ''),
            );
            if ($program->has_teams == true)
            {
                $this->links['Teams'] = array(
                    'link' => Path::Relative("{$program->name}/teams"),
                    'class' => ($current == 'teams' ? 'selected' : ''),
                );
            }
            foreach ($pages as $page)
            {
                $this->links[$page->title] = array(
                    'link' => Path::Relative("{$program->name}/page/show/{$page->short_title}"),
                    'class' => ($current == $page->short_title ? 'selected' : '')
                );
            }
        } 
        else if ($entity_type === "Team")
        {
            $team = \simp\Model::FindById("Team", $entity_id);
            $this->links['News'] = array(
                'link' => Path::Relative(GetTeamPath("", $team)),
                'class' => ($current == 'team' ? 'selected' : ''),
            );

            $this->links['Events'] = array(
                'link' => Path::event_calendar("team", $team->id),
                'class' => ($current == 'calendar' ? 'selected' : ''),
            );
            foreach ($pages as $page)
            {
                $this->links[$page->title] = array(
                    'link' => Path::Relative("page/show/{$page->id}"),
                    'class' => ($current == $page->short_title ? 'selected' : '')
                );
            }
        }


    }
}
