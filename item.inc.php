<?php

// Bepaal volledige bestandsnaam en genereer niet bestaande miniaturen.
switch($display)
{
    case "full":
        $file = $itemInfo["fullPath"];

        if ($itemInfo["type"] == "document")
        {
            header("Content-Disposition:inline;filename=\"{$file}\"");
        }
        
        break;
        
    case "icon":
        $file = "img/{$path}";
        break;
        
    case "preview":
        $file = $itemInfo["previewPath"];

        if ($itemInfo["type"] == "image")
        {
            if (! file_exists($itemInfo["previewPath"]) || $regenerate)
            {
                generateImage($itemInfo["fullPath"], $itemInfo["previewPath"], PREVIEWSIZE, PREVIEWSIZE);
            }
        }
        break;

    case "thumbnail":
        $file = $itemInfo["thumbnailPath"];

        if ($itemInfo["type"] == "document" || $itemInfo["type"] == "video")
        {
            if (! file_exists($itemInfo["thumbnailPath"]) || $regenerate)
            {
                generateImage($itemInfo["fullPath"], $itemInfo["thumbnailPath"], THUMBNAILSIZE, THUMBNAILSIZE);
            }
        }
        break;
}

// Volledig bestand of deel van het bestand (zoekfunctie voor films) teruggeven?
// Gebaseerd op: https://stackoverflow.com/a/18978453
if (file_exists($file))
{
    @error_reporting(0); // Foutmeldingen uit om data niet te corrumperen.

    $filesize       = filesize($file);
    $length         = $filesize;
    $partial        = false;
    $range          = $_SERVER["HTTP_RANGE"] ?? false;

    // Standaard headers.
    header("Accept-Ranges: bytes");
    header("Cache-Control: max-age=31536000"); // 60 * 60 * 24 * 365 seconden = 1 jaar.
    header("Content-Length: $length");
    header("Content-Type: " . mime_content_type($file));
    header("Expires: " . gmdate("D, d M Y H:i:s \G\M\T", time() + 604800));
    header("Pragma: public");

    if ($range)
    {
        $partial = true;

        list($param, $range) = explode("=", $range);
        
        // Range niet in "bytes".
        if (strtolower(trim($param)) != "bytes")
        { 
            header("HTTP/1.1 400 Invalid Request");
            exit;
        }
        
        $range = explode(",", $range);
        $range = explode("-", $range[0]); // Alleen de eerste gevraagde range.
        
        // "bytes" ongeldig.
        if (count($range) != 2)
        { 
            header("HTTP/1.1 400 Invalid Request");
            exit;
        }
        
        // Geen startwaarde.
        if ($range[0] === "")
        { 
            $end = $filesize - 1;
            $start = $end - intval($range[0]);
        }
        // Geen eindwaarde.
        else if ($range[1] === "")
        { 
            $start = intval($range[0]);
            $end = $filesize - 1;
        }
        else 
        { 
            $start = intval($range[0]);
            $end = intval($range[1]);
            // Ongeldige range.
            if ($end >= $filesize || (! $start && (! $end || $end == ($filesize - 1))))
            {
                $partial = false;
            } 
        }
        $length = $end - $start + 1;
    } 


    // Extra headers.
    if ($partial)
    {
        header("HTTP/1.1 206 Partial Content");
        header("Content-Range: bytes $start-$end/$filesize");
        
        if (! $fp = fopen($file, "r"))
        {
            header("HTTP/1.1 500 Internal Server Error");
            exit;
        }
        
        if ($start)
        {
            fseek($fp, $start);
        }
        
        while ($length)
        {
            $read = ($length > 8192) ? 8192 : $length;
            $length -= $read;
            print(fread($fp, $read));
        }
        
        fclose($fp);
    }
    else
    {
        readfile($file);
    }
}

?>