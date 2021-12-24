<?php

/*
 * Album / 2020 / 2020-07 verjaardag stan / Vid 20200710 083441-1.m4v error
 */
 
session_start();

require("functions.inc.php");
require("settings.inc.php");

// standaard waardes
(! isset($_SESSION["theme"])) ? $_SESSION["theme"] = "light" : false;

// verkrijg parameters uit url
$action             = $_GET["action"] ?? false;
$display            = $_GET["display"] ?? false;
$path               = $_GET["path"] ?? "/";
$regenerate         = $_GET["regenerate"] ?? false;
$slideshow          = $_GET["slideshow"] ?? false;
$slideshowRepeat    = $_GET["slideshowRepeat"] ?? false;
$slideshowTimer     = $_GET["slideshowTimer"] ?? 4;

// initialisatie variabelen
$cumulativePath     = "";
$footer             = "<a href='#top'>Boven</a>";
$item               = "";
$menu               = array();
$navigation         = array();
$page               = "";

// verkrijg bestanden in album
$albumPath          = sanitizePath($path);
$itemInfo           = getItemInfo($albumPath);
$albumFiles         = glob(PATH . "/{$itemInfo["album"]}/*");
$albumFiles         = str_replace(PATH . "/", "", $albumFiles);
natsort($albumFiles);
$albumFiles         = array_reverse($albumFiles);
$albumFiles         = array_values($albumFiles);
$albumCount         = count($albumFiles);

// album navigatie
$navigation[]       = "<a href='?path=/'>Album</a>";
$pathElements       = explode("/", $albumPath);
$pathElements       = array_filter($pathElements);

foreach ($pathElements as $element)
{
    $cumulativePath .= $element . "/";
    $cumulativePath = htmlspecialchars($cumulativePath, ENT_QUOTES); // Om bestandsnamen met bijv. ' te accepteren.
    $element        = str_replace("_", " ", $element);
    $element        = htmlspecialchars_decode($element); // Om HTML % codes leesbaar te maken.
    $navigation[]   = "<a href='?path={$cumulativePath}'>{$element}</a>";
}

$navigation = implode("<span> / </span>", $navigation);

// menuopties
$menu["diavoorstelling"] = "<a href='index.php?action=slideshow&path={$albumFiles[0]}&slideshow=true&slideshowTimer={$slideshowTimer}&slideshowRepeat={$slideshowRepeat}'>Diavoorstelling</a>";
$menu["thema"] = "<a href='index.php?action=switchTheme&path={$albumPath}' title='Thema wijzigen'>Thema wijzigen</a>";

// verwerk acties
switch ($action)
{
    case "display":
        require_once("item.inc.php");
        break;

    case "generateThumbnails":
        break;

    case "itemInformation":
        break;

    case "slideshow":
        require_once("slideshow.inc.php");
        $footer = "";
        $menu = "";
        $navigation = "";
        break;

    case "switchTheme":
        ($_SESSION["theme"] == "light") ? $_SESSION["theme"] = "dark" : $_SESSION["theme"] = "light";
        header("Location: index.php?path=" . $albumPath);
        die();
        break;

    default:
        ($itemInfo["type"] == "album") ? require_once("album.inc.php") : require_once("viewer.inc.php");
        break;
}

// menu
if ($menu)
{
    ksort($menu);
    $menu = preg_replace("/(.+)/", "<li>$1</li>", $menu);
    $menu = "
        <span>&#9776;&nbsp;</span>
        <div class='options'>
            <ul>" . implode($menu) . "</ul>
        </div>
    ";
}

// pagina afdrukken
echo "
    <!DOCTYPE html>
    <html lang='nl'>
    
        <head>
            <link rel='stylesheet' href='css/stylesheet.css'>
            <link rel='stylesheet' href='css/{$_SESSION["theme"]}.css'>
            <meta http-equiv='content-type' content='text/html; charset=utf-8'>
            <meta name='viewport' content='width=device-width, user-scalable=yes, initial-scale=1, maximum-scale=1'>
            <meta name='robots' content='noindex'>
            <meta name='robots' content='nofollow'>
            <title>Album</title>
        </head>

        <body>
            <div class='header'>
                <a name='top'></a>
                <div class='menu'>{$menu}</div>
                <div class='navigation'>{$navigation}</div>
                <hr/>
            </div>
            {$page}
        </body>

    </html>
";

?>