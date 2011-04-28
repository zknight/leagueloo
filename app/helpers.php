<?

function GetArticlePath($action, $article)
{
    return Path::Relative("{$article->entity_name}/news/{$action}/{$article->short_title}");
}
