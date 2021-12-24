<?php

// bepaal volgende dia
$currentItemOffset = array_search($itemInfo["path"], $albumFiles) ?? -1;

if ($currentItemOffset < $albumCount - 1)
{
    $nextItemOffset = $currentItemOffset + 1;
    $nextItemLink   = "index.php?action=slideshow&path={$albumFiles[$nextItemOffset]}&slideshow=true&slideshowRepeat={$slideshowRepeat}&slideshowTimer={$slideshowTimer}";
}
else
{
    // hierna terug naar het album of opnieuw beginnen?
    $nextItemLink = "index.php?path={$itemInfo["album"]}";
    if ($slideshowRepeat)
    {
        $nextItemLink = "index.php?action=slideshow&path={$albumFiles[0]}&slideshow=true&slideshowRepeat={$slideshowRepeat}&slideshowTimer={$slideshowTimer}";
    }
}

// verkrijg dia
if ($itemInfo["type"] == "image")
{
    $item = "
        <a href='index.php?action=display&path={$itemInfo["htmlPath"]}&display=full'>
            <img src='index.php?action=display&path={$itemInfo["htmlPath"]}&display=preview'>
        </a>
    ";
}
else
{    
    // gelijk naar de volgende dia
    header("Location: {$nextItemLink}");
    die();
}

// pagina afdrukken
$page = "
    <head>
        <meta http-equiv='refresh' content='{$slideshowTimer}; URL={$nextItemLink}'>
        <style>
            body {
                animation: fadeInOut ease {$slideshowTimer}s;
                animation-fill-mode: forwards;
                animation-iteration-count: 1;
                background: #000;
            }
        </style>
    </head>
    
    <div class='slideshow'>
      <div class='item'>
        {$item}
      </div>
    </div>
";

?>