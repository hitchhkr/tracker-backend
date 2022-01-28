<?php

    namespace App\Extra;

    use MongoDB\BSON\ObjectId;
    use MongoDB\BSON\UTCDateTime;

    class General {

        public static function get_class_name($classname){

            if ($pos = strrpos($classname, '\\')) return substr($classname, $pos + 1);
            return $pos;

        }

        public static function formatMongoForJson(array $input):array{

            foreach($input AS $k => $v){

                if($v instanceof ObjectId){
                    $input[$k] = (string)$v; 
                }

                if($v instanceof UTCDateTime){
                    $input[$k] = $v->toDateTime()->format('c');
                }

                if(is_array($v)){
                    $input[$k] = self::formatMongoForJson($v);
                };

            }

            return $input;

        }

        public static function processImageURI($str)
        {

            $split = explode(';base64,',$str);

            $file = base64_decode(end($split));
            $ftype = substr($split[0],5);

            return [
                'contentType' => $ftype,
                'binary' => $file
            ];

        }

        public static function kgToLbs(float $kg, bool $dec = false)
        {

            $lbs = $kg / 0.453592;
            $stone = floor($lbs / 14);
            $lbs = $lbs - ($stone * 14);

            if($dec == true){

                return $stone + ($lbs / 14);

            }else{

                return [
                    $stone,
                    $lbs
                ];

            }

        }

        // public static function storeTestData($key,$val){

        //     $self::$key = $val;

        // }

    }

?>