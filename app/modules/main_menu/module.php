<?
/// MainMenu maps programs to the main menu, tracks which program is
/// current 
class MainMenu extends \simp\Module
{

    public static $current_program = "";
//    private static $_breadcrumb = array();
    private $_programs;

    protected function Setup($args)
    {
        $this->current = $args['current'];
        //echo "current page: $this->current";

        $this->_programs = \simp\Model::FindAll("Program", "order by weight asc");
    }

    public function GetPrograms()
    {
        return $this->_programs;
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
