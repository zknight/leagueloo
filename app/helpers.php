<?

function GetArticlePath($article)
{
    return Path::Relative("{$article->entity_name}/news/{$article->short_title}");
}
