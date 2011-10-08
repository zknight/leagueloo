<?
/// MainMenu maps programs to the main menu, tracks which program is
/// current 
class MainMenu extends \simp\Module
{

    public static $current_program = "";
//    private static $_breadcrumb = array();
    private $_programs;
    private $_sub_links;

    protected function Setup($args)
    {
        $this->current = $args['current'];
        //echo "current page: $this->current";

        if (Cache::Exists('programs'))
        {
            global $log; $log->logDebug("MainMenu::Setup() reading programs from cache.");
            $this->_programs = Cache::Read("programs");
        }
        else
        {
            $this->_programs = \simp\Model::FindAll("Program", "order by weight asc");
            Cache::Write("programs", $this->_programs);
        }

        $this->_sub_links = array();
        foreach ($this->_programs as $p)
        {
            //\R::debug(true);
            $pages = Page::GetPagesForLocation("Program", $p->id, Page::MAIN_MENU);
            //\R::debug(false);
            //print_r($pages);
            $page_links = array();
            foreach ($pages as $page)
            {
                $page_links[] = l($page->title, Path::Relative("{$page->entity_name}/page/show/{$page->short_title}"), array("class" => "sub"));
            }
            $this->_sub_links[$p->id] = $page_links;
            //print_r($this->_sub_links);
        }
    }

    public function GetPrograms()
    {
        return $this->_programs;
    }

    public function GetSubLinks($id)
    {
        return $this->_sub_links[$id];
    }

    /*
    public static function SetBreadcrumb($breadcrumb)
    {
        self::$_breadcrumb = $breadcrumb;
    }

    public static function GetBreadcrumb()
    {
        $html = "<span class=\"breadcrumb\">";
        $trail = \simp\Breadcrumb::Instance()->GetTrail();
        $link_arr = array();
        foreach ($trail as $target => $link)
        {
            $link_arr[] = l($target, $link);
        }
        $html .= implode("&gt;", $link_arr);
        return $html;
    }
     */


}
