<?php
class Links extends \simp\Module
{
    public function Setup($args)
    {
        $this->entity_type = GetEntityType();
        $this->entity_id = GetEntityId();
        $this->entity_name = GetEntityName();

        $this->links = \simp\Model::Find(
            "Link",
            "entity_type = ? and entity_id = ? order by weight",
            array(SnakeCase($this->entity_type), $this->entity_id)
        );

        $this->page_links = array();
        $pages = Page::GetPagesForLocation($this->entity_type, $this->entity_id, Page::LINK_MENU);
        foreach ($pages as $page)
        {
            switch ($this->entity_type)
            {
            case "Main":
                $this->page_links[] = l($page->title, Path::main('page', $page->short_title));
                break;
            case "Team":
                $this->page_links[] = l($page->title, Path::Relative("page/show/{$page->id}"));
                break;
            case "Program":
                $this->page_links[] = l($page->title, Path::Relative("{$page->entity_name}/page/show/{$page->short_title}"));
                break;
            }
        }

        $this->has_links = (count($this->page_links) > 0) || (count($this->links) > 0);
    }
}
