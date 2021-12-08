<?php

    namespace App\Extra;

    class Label
    {

        private static function getLength():int
        {

            return env('LABEL_LENGTH') ? env('LABEL_LENGTH') : 10;

        }

        public static function create():string
        {

            $length = self::getLength();
            $range1 = range('a','z');
            $range2 = range('A','Z');
            $range3 = range(0,9);
            $out = '';

            $range = array_merge($range1,$range2,$range3);

            for($i = 0; $i < $length; $i++){
                $index = mt_rand(0,count($range) - 1);
                $out .= $range[$index];
            }

            if(self::validate($out) === false){
                throw new \Exception('Label does not validate');
            }

            return $out;

        }

        public static function validate(string $label):bool
        {
            $valid = false;
            if(strlen($label) == self::getLength()){
                $valid = true;
            }
            return $valid;
        }

    }

?>