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
class News extends \simp\Model
{
    public function Setup()
    {
    }    
}
