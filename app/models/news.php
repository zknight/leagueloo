<?
/// News model
///
/// A news article belongs to a program or team.
/// fields:
///     short_title: short title used in url
///     title: news article title
///     intro: introductory text for listing on program page
///     body: body of article, added to intro
///     created_on: date/time of article creation
///     updated_on: date/time of article update
///     publish_on: date/time to publish article
///     expiration: publication expiration, article will not
///         be displayed after this date/time
///     editor: user id of last editor ** move this to editors table?
///     publisher: user id of publisher ** move this to publishers table?
///     entity_type: program or team
///     entity_id: id of entity owner
///     entity_name: name of entity
class News extends \simp\Model
{
    public function Setup()
    {
    }    

    public static function FindPublished($owner_type = NULL, $owner_id = NULL)
    {
        $dt = new DateTime("now");
        $curdate = $dt->getTimestamp();
        $params = "publish_on <= ? and expiration >= ?";
        $values = array($curdate, $curdate);
        if (isset($owner_type))
        {
            $params .= " and entity_type = ?";
            $values[] = $owner_type;
        }
        if (isset($owner_id))
        {
            $params .= " and entity_id = ?";
            $values[] = $owner_id;
        }

        return News::Find("news", $params, $values);
    }

    public static function FindUnpublished($owner_type = NULL, $owner_id = NULL)
    {
        $dt = new DateTime("now");
        $curdate = $dt->getTimestamp();
        $params = "(publish_on > ? or expiration < ?)";
        $values = array($curdate, $curdate);
        if (isset($owner_type))
        {
            $params .= " and entity_type = ?";
            $values[] = $owner_type;
        }
        if (isset($owner_id))
        {
            $params .= " and entity_id = ?";
            $values[] = $owner_id;
        }
        return News::Find("news", $params, $values);
    }

    public function __get($property)
    {
        if ($property == 'published')
        {
            $curdate = new DateTime("now");
            if ($curdate->getTimestamp() >= $this->publish_on && 
                $curdate->getTimestamp() <= $this->expiration)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else if ($property == 'entity_name')
        {
            return \R::getCell(
                "select name from ? where id = ?", 
                array(SnakeCase($this->entity_type), $this->entity_id));
        }
        else
        {
            return parent::__get($property);
        }
    }
}
