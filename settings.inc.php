<?php

define("CACHE", "/mnt/data/album/cache");
define("EXIFTOOL", "/usr/bin/exiftool");
define("FFMPEG", "/usr/bin/ffmpeg");
define("FILETYPES", array(
    "/"     => "album",
    
    "mp3"   => "audio",
    "ogg"   => "audio",
    "wav"   => "audio",

    "pdf"   => "document",
    "txt"   => "document",

    "bmp"   => "image",
    "gif"   => "image",
    "jpg"   => "image",
    "jpeg"  => "image",
    "png"   => "image",

    "3gp"   => "video",
    "gp3"   => "video",
    "mov"   => "video",
    "mp4"   => "video",
    "mpg"   => "video",
    "webm"  => "video"
));
define("PATH", "/mnt/data/album/albums");
define("PREVIEWSIZE", 1024);
define("QUALITY", 70);
define("THUMBNAILSIZE", 300);

?>
