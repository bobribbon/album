<?php

$menu["miniaturen"] = "<a href='{$_SERVER["REQUEST_URI"]}&regenerate=true' title='Miniaturen opnieuw genereren'>Miniaturen opnieuw genereren</a>";

// items in album verwerken
foreach($albumFiles as $file)
{    
    $file               = sanitizePath($file);
    $fileInfo           = getItemInfo(PATH . "/" . $file);
    $information        = $fileInfo["title"];
    $overlay            = "";
    $informationType    = "item";
    $thumbnail          = getThumbnail($fileInfo["fullPath"]);

    $image              = "<img src='{$thumbnail}&regenerate={$regenerate}'>"; // thumbnail
    
    switch($fileInfo["type"])
    {
        case "album":
            $numberOfItems      = count(glob($fileInfo["fullPath"] . "/*"));
            $information        = "{$fileInfo["title"]}<br/>{$numberOfItems} items";
            $informationType    = "album";
            break;

        case "audio":
        case "document":
        case "image":
            break;

        case "video":
            $overlay = "<img src='index.php?path=video.png&action=display&display=icon'>";
            break;
    }
    
    $page .= "
        <a href='index.php?path={$fileInfo["htmlPath"]}' title='{$fileInfo["filename"]}'>
            <div class='item'>
                <div class='image'>{$image}</div>
                <div class='information {$informationType}'>{$information}</div>
                <div class='overlay'>{$overlay}</div>
            </div>
        </a>
    ";
}

$page = "<div class='album'>{$page}</div>";
$navigation .= " <span>({$albumCount})</span>";

?>