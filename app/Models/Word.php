<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\General;

    class Word extends Mongo {

        public string $word;
        public int $length;
        public string $beginsWith;
        public $created;

        public function __construct($word = null){

            parent::__construct();
            $this->setCollection('dictionary');

            if($word){
                $this->setWord($word);
            }

        }

        public function setWord(string $word)
        {

            $this->word = strtolower(trim($word));
            $this->length = strlen($this->word);
            $this->beginsWith = substr($this->word,0,1);

            return $this;

        }

        public function get()
        {

            if(!$this->word){
                throw new Exception('word not set');
            }

            $q = [
                'word' => $this->word
            ];

            return $this->dbfind($q);

        }

        public function getByLength($min = 1, $max = null){

            $q = [
                'length' => [
                    '$gte' => $min
                ] 
            ];

            if($max){
                $q['length']['$lte'] = $max;
            }

            $options = [
                'sort' => [
                    'word' => 1
                ]
            ];

            return $this->dbfind($q,true,$options);

        }

        public function add()
        {

            if(!$this->word){
                throw new Exception('word not set');
            }

            $check = $this->get();
            if($check){
                return null;
            }

            $this->created = new UTCDateTime();

            return $this->dbinsert($this);

        }

    }

?>