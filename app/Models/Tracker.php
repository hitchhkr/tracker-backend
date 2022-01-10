<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use App\Extra\General;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Models\Users;

    class Tracker extends Mongo {

        private ObjectId $id;
        public string $_type; 
        public ObjectId $_userid;
        public UTCDateTime $created;
        public array $values;

        public function __construct(){

            parent::__construct();
            $this->setCollection(strtolower(General::get_class_name(get_class($this))));

        }

        public function set($prop,$val){

            if($val instanceof \DateTime){
                $this->{$prop} = new UTCDateTime($val->getTimestamp() * 1000);
            }else{
                $this->{$prop} = $val;
            }

            return $this;

        }

        public function setUid(string $id)
        {
            $this->_userid = $this->convertMongoId($id);

            return $this;
        }

        public function setType(string $type)
        {
            $this->_type = $type;
        
            return $this;
        }

        public function create($vars)
        {

            if(!$this->_type){
                throw new Exception('No type has been set');
            }

            if(!$this->_userid){
                throw new Exception('No user id has been set');
            }

            $this->created = new UTCDateTime();
            $this->values = $this->formatForInput($vars);

            return $this->dbinsert($this);

        }

        public function  getSummary($type = null):array
        {

            $filter = [
                '$match' => [
                    '_userid' => $this->_userid
                ]
            ];

            $sort = [
                '$sort' => [
                    'values.date' => -1
                ]
            ];

            $group = [
                '$group' => [
                    '_id' => '$_type',
                    'sum' => [
                        '$sum' => 1
                    ],
                    'values' => [
                        '$push' => '$values'
                    ]
                ]
            ];

            $aggs = [
                $filter,
                $sort,
                $group
            ];

            return $this->dbagg($aggs);

        }

        public function getTokenSummary($limit = 10)
        {

            $filter = [
                '$match' => [
                    '_userid' => $this->_userid,
                    '_type' => $this->_type
                ]
            ];

            $sort = [
                '$sort' => [
                    '_id' => -1
                ]
            ];

            $group = [
                '$group' => [
                    '_id' => '$values.date',
                    'tokens' => [
                        '$sum' => '$values.tokens'
                    ]
                ]
            ];

            $limit = [
                '$limit' => $limit
            ];

            $aggs = [
                $filter,
                $group,
                $sort,
                $limit
            ];

            return $this->dbagg($aggs);

        }

        public function getType(\DateTime $date = null, int $limit= null, $sort = -1){

            $q = [
                '_userid' => $this->_userid,
                '_type' => $this->_type
            ];

            if($date){
                $q['values.date'] = new UTCDateTime($date->getTimestamp() * 1000);
            }

            $options = [
                'sort' => [
                    'values.date' => $sort
                ]
            ];

            if($limit){
                $options['limit'] = $limit;
            }

            return $this->dbfind($q,true,$options);

        }

    }

?>