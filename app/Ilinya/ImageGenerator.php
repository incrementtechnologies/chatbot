<?php

namespace App\Ilinya;
use Intervention\Image\ImageManager;
use Intervention\Image\Image;

class ImageGenerator{
   public static function create(){
    $img = Image::make('storage/qcards/1.png');
   }

}