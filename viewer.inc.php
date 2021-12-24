<?php

switch ($itemInfo["type"])
{
    case "audio":
        $item .= "
            <audio controls>
                <source src='index.php?action=display&path={$itemInfo["htmlPath"]}&display=full&regenerate={$regenerate}' type='audio/mp3'>
            </audio>
        ";
        break;

    case "document":
        $item .= "<iframe src='index.php?action=display&path={$itemInfo["htmlPath"]}&display=full'></iframe>";
        break;

    case "image":
        $item .= "
            <a href='index.php?path={$itemInfo["htmlPath"]}&action=display&display=full'>
                <img src='index.php?path={$itemInfo["htmlPath"]}&action=display&display=preview&regenerate={$regenerate}'>
            </a>
        ";

        $itemData                       = @exif_read_data($itemInfo["fullPath"]);
        $itemData["Album bestand"]      = printFilesize(@filesize($itemInfo["fullPath"]));
        $itemData["Album preview"]      = printFilesize(@filesize($itemInfo["previewPath"]));
        $itemData["Album thumbnail"]    = printFilesize(@filesize($itemInfo["thumbnailPath"]));
        $menu["informatie"]             = "<a href='{$_SERVER["REQUEST_URI"]}&info=true' target='_blank' title='Informatie'>Informatie</a>";
        $menu["miniatuur"]              = "<a href='{$_SERVER["REQUEST_URI"]}&regenerate=true' title='Miniatuur opnieuw genereren'>Miniatuur opnieuw genereren</a>";
        break;

    case "video":
        $item .= "
            <video controls preload='metadata'>
                <source src='index.php?action=display&path={$itemInfo["htmlPath"]}&display=full&regenerate={$regenerate}' type='video/mp4'>
            </video>
        ";
         
        exec(EXIFTOOL . " '{$itemInfo["fullPath"]}'", $exif);
        $itemData = array();
        foreach($exif as $key => $value)
        {
            $value = explode(" : ", $value);
            $itemData[trim($value[0])] = $value[1];
        }        
        $menu["informatie"] = "<a href='{$_SERVER["REQUEST_URI"]}&info=true' target='_blank' title='Informatie'>Informatie</a>";
        $menu["miniatuur"]  = "<a href='{$_SERVER["REQUEST_URI"]}&regenerate=true' title='Miniatuur opnieuw genereren'>Miniatuur opnieuw genereren</a>";
        break;
        
    default:
        $item .= "<img src='index.php?action=display&path=unknown.png&display=icon'>";
        break;
}

    
// item links
$currentItemOffset  = array_search($itemInfo["path"], $albumFiles);
$firstItemOffset    = 0;
$previousItemOffset = false;
$nextItemOffset     = false;
$lastItemOffset     = count($albumFiles) - 1;

$first      = "<span class='header disabled'> |< </span>";
$previous   = "<span class='header disabled'> < </span>";
$next       = "<span class='header disabled'> > </span>";
$last       = "<span class='header disabled'> >| </span>";

// eerste/ vorige item links
if ($currentItemOffset > 0)
{
    $first      = "<a href='index.php?path={$albumFiles[$firstItemOffset]}' class='header' accesskey='w' title='acceskey + w'> |< </a>";
    $previousItemOffset = $currentItemOffset - 1;
    $previous   = "<a href='index.php?path={$albumFiles[$previousItemOffset]}' class='header' accesskey='a' title='acceskey + a'> < </a>";
}

// volgende/ laatste item links
if ($currentItemOffset < count($albumFiles) - 1)
{
    $nextItemOffset = $currentItemOffset + 1;
    $next       = "<a href='index.php?path={$albumFiles[$nextItemOffset]}' class='header' accesskey='d' title='acceskey + d'> > </a>";
    $last       = "<a href='index.php?path={$albumFiles[$lastItemOffset]}' class='header' accesskey='s' title='acceskey + s'> >| </a>";
}

$counter = "<span class='header disabled'>(" . ($currentItemOffset + 1) . " / " . count($albumFiles) . ")</span>";

$page = "
    <div class='viewer'>
        <div class='item'>{$item}</div>
        <br/>
        <div class='navigation'>{$first} {$previous} {$counter} {$next} {$last}</div>
    </div>
";

?>