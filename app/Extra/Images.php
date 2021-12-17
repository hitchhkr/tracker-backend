<?php

    namespace App\Extra;

    class Images
    {

        public static function resize($binary,int $width,int $height,float $blur = 0.5,int $quality = 25){

            $imagick = new \Imagick();
            $imagick->readImageBlob($binary);
            $imagick->resizeImage($width,$height,\Imagick::FILTER_QUADRATIC,$blur);
            $imagick->setImageCompression(\Imagick::COMPRESSION_JPEG);
            $imagick->setImageCompressionQuality($quality);

            return $imagick->getImageBlob();

        }

        public static function getRatio($ratio = '16x9',int $width)
        {

            $multiplier = self::decodeRatio($ratio);

            return [
                'width' => $width,
                'height' => $width / $multiplier
            ];

        }

        private static function decodeRatio(string $ratio)
        {

            $split = explode('x',$ratio);
            $multiplier = (float)$split[0] / (float)$split[1];

            return $multiplier;

        }

    }

?>