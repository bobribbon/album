<?php

/*
 * verkrijg links naar thumbnail op basis van gegeven path
 */
function getThumbnail($path)
{
    // verkrijg willekeurig bestand als het een album betreft
    if (is_dir($path))
    {
        $album = glob($path . "/*", GLOB_BRACE);
        shuffle($album);
        $path = $album[0];
    }

    $itemInfo = getItemInfo($path);
   
    switch($itemInfo["type"])
    {
        case "album":
            $thumbnail = "album.png&display=icon";
            break;

        case "audio":
            $thumbnail = "audio.png&display=icon";
            break;

        case "document":
            $thumbnail = "document.png&display=icon";
            break;

        case "image":
        case "video":
            $thumbnail = "{$itemInfo["dirname"]}/{$itemInfo["filename"]}.{$itemInfo["extension"]}&display=thumbnail";
            break;
            
        default:
            $thumbnail = "unknown.png&display=icon";
            break;
    }
    
    $thumbnail = htmlspecialchars($thumbnail, ENT_QUOTES);

    return "index.php?path={$thumbnail}&action=display";
}

/*
 * schoon het gegeven path op
 */
function sanitizePath($path)
{
    // verkrijg path uit url
    $path = array_filter(explode("/", $path),
        function($element)
        {
            // onwenselijke onderdelen verwijderen
            if ($element != "." && $element != "..")
            {
                return trim($element);
            }
            return false;
        }
    );
    $elements = array_filter($path);
    $path = implode("/", $elements);

    return $path;
}

/*
 * verkrijg bestandsinformatie
 */
function getItemInfo($path)
{
    $itemInfo = array();

    // PATH als prefix verwijderen.
    $path = str_replace(PATH . "/", "", $path);

    $itemInfo["album"]          = $path;

    if (is_file(PATH . "/" . $path))
    {
        $itemInfo["album"]      = pathinfo($path)["dirname"];
    }

    $itemInfo["dirname"]        = pathinfo($path)["dirname"] ?? "";
    $itemInfo["extension"]      = pathinfo($path)["extension"] ?? "/";
    $itemInfo["filename"]       = pathinfo($path)["filename"] ?? "";

    $itemInfo["filesize"]       = printFileSize(@filesize(PATH . "/" . $path));

    $itemInfo["fullPath"]       = PATH . "/" . $path;
    $itemInfo["htmlPath"]       = htmlspecialchars($path, ENT_QUOTES);
    $itemInfo["path"]           = $path;
    $itemInfo["previewPath"]    = CACHE . "/" . $itemInfo["dirname"] . "/" . $itemInfo["filename"] . "-preview.jpg";
    $itemInfo["thumbnailPath"]  = CACHE . "/" . $itemInfo["dirname"] . "/". $itemInfo["filename"] . "-thumbnail.jpg";
    
    $itemInfo["type"]           = FILETYPES[strtolower($itemInfo["extension"])] ?? "onbekend";
    $itemInfo["title"]          = str_replace("_", " ", $itemInfo["filename"]);

    return $itemInfo;
}

/*
 * genereer een afbeelding geschaalde/ gedraaide miniatuur
 */
function generateImage($inputFile, $outputFile, $width, $height)
{
    $itemInfo = getItemInfo($inputFile);

    switch($itemInfo["type"])
    {
        case "image":
            if (! $image = @new Imagick($inputFile))
            {
                break;
            }

            // draaien en spiegelen op basis van exif?
            $exif = @exif_read_data($inputFile);
            if (is_array($exif))
            {
                $orientation = $exif["Orientation"] ?? 1;
                switch($orientation)
                {
                    case 1:
                        break;

                    case 2:
                        $image->flopImage();
                        break;
                        
                    case 3:
                        $image->rotateimage("#FFF", 180);
                        break;

                    case 4:
                        $image->rotateimage("#FFF", 180);
                        $image->flopImage();
                        break;

                    case 5:
                        $image->rotateimage("#FFF", 90);
                        $image->flipImage();
                        break;
    
                    case 6:
                        $image->rotateimage("#FFF", 90);
                        break;

                    case 7:
                        $image->rotateimage("#FFF", -90);
                        $image->flipImage();
                        break;
    
                    case 8:
                        $image->rotateimage("#FFF", -90);
                        break;
                }
            }
            break;

        case "video":
            $tempFile = tempnam(sys_get_temp_dir(), "album");
            exec(FFMPEG . " -i '{$inputFile}' -y -deinterlace -an -ss 1 -t 00:00:01 -r 1 -y -vcodec mjpeg -f mjpeg '{$tempFile}' 2>&1");
            $image = @new Imagick($tempFile);
            unlink($tempFile);
            break;
    }

    if ($image)
    {
        // cache map aanmaken?
        if (! file_exists(dirname($outputFile)))
        {
            mkdir(dirname($outputFile), 0775, true); // Recursief!
        }

        // schalen
        $inputWidth     = $image->getImageWidth();
        $inputHeight    = $image->getImageHeight();
        $scaleFactor    = $inputHeight/ $height;
        $outputWidth    = $inputWidth/ $scaleFactor;
        $outputHeight   = $height;
        $image->resizeImage($outputWidth, $outputHeight, Imagick::FILTER_BOX, 1, false);

        // schrijf image
        $image->setImageCompressionQuality(QUALITY);
        $image->stripImage();
        $image->writeImage($outputFile);
        $image->clear();    
    }

    return;
}

/*
 * druk bestandsgrootte af in leesbaar formaat
 * Jeffrey Sambells (https://stackoverflow.com/a/23888858)
 */
function printFilesize($bytes, $dec = 2) 
{
    $size   = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
}

?>