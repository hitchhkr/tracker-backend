<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\General;

    class Attempts extends Mongo {

        private ObjectId $id;
        public ?ObjectId $_film_id = null;
        public ?ObjectId $_user_id = null;
        public UTCDateTime $created;
        public bool $success = false;
        public string $search;

        public function __construct(){

            parent::__construct();
            $this->setCollection(strtolower(General::get_class_name(get_class($this))));
            $this->setCreated();

        }

        public function setId(string $id)
        {
            $this->id = $this->convertMongoId($id);

            return $this;
        }

        public function setFilmId(string $val)
        {
            $this->_film_id = $this->convertMongoId($val);
            return $this;
        }

        public function setUserId(string $val)
        {
            $this->_user_id = $this->convertMongoId($val);
            return $this;
        }

        public function setRating(Rating $val)
        {
            $this->rating = $val;
            return $this;
        }

        public function setCreated()
        {

            $this->created = new UTCDateTime();

            return $this;

        }

        public function setSuccess(bool $val):object
        {
            $this->success = $val;
            return $this;
        }

        public function setSearch(string $val):object
        {
            $this->search = $val;
            return $this;
        }

        public function create():?array
        {

            return $this->dbinsert($this);

        }

        public function getSummary():?array
        {

            $match = [
                '$match' => [
                    '_film_id' => $this->_film_id
                ]
            ];

            if($this->_user_id)
            {

                $match['$match']['_user_id'] = $this->_user_id;

            }

            $group = [
                '$group' => [
                    '_id' => '$_film_id',
                    'count' => [
                        '$sum' => 1
                    ],
                    'recent' => [
                        '$last' => '$created'
                    ]
                ]
            ];

            $aggs = [
                $match,
                $group
            ];

            $result = $this->dbagg($aggs);

            if($result){
                if(count($result) == 1){
                    return $result[0];
                }else{
                    return $result;
                }
            }else{
                return [
                    'count' => 0
                ];
            }

        }

    }