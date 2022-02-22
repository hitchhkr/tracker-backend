<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\General;

    class Found extends Mongo {

        private ObjectId $id;
        public ?ObjectId $_film_id = null;
        public ?ObjectId $_user_id = null;
        public UTCDateTime $created;
        public string $guess;

        public function __construct(){

            parent::__construct();
            $this->setCollection(strtolower(General::get_class_name(get_class($this))));
            $this->setCreated();

        }

        public function setId(string $id):object
        {
            $this->id = $this->convertMongoId($id);

            return $this;
        }

        public function setFilmId(string $val):object
        {
            $this->_film_id = $this->convertMongoId($val);
            return $this;
        }

        public function setUserId(string $val):object
        {
            $this->_user_id = $this->convertMongoId($val);
            return $this;
        }

        public function setCreated():object
        {
            $this->created = new UTCDateTime();
            return $this;
        }

        public function setGuess(string $val):object
        {
            $this->guess = $val;
            return $this;
        }

        public function getTotal():?array
        {

            $agg = [];

            if($this->_user_id){
                $match = [
                    '$match' => [
                        '_user_id' => $this->_user_id
                    ]
                ];
                array_push($agg,$match);
            }

            $count = [
                '$count' => 'films'
            ];

            array_push($agg,$count);

            $res = $this->dbagg($agg);

            return $res ? $res[0] : ['films' => 0];

        }

        public function add():array
        {
            if(!$this->_film_id)
            {
                throw new Exception('No film id');
            }

            if(!$this->_user_id)
            {
                throw new Exception('No user id');
            }

            return $this->dbinsert($this);
        }

    }