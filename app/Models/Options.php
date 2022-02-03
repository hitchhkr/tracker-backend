<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\General;

    class Options extends Mongo {

        private ObjectId $id;
        public string $_type;
        public string $value;
        public UTCDateTime $created;

        public function __construct(){

            parent::__construct();
            $this->setCollection(strtolower(General::get_class_name(get_class($this))));
            $this->setCreated();

        }

        public function set($prop,$val){

            $this->{$prop} = $val;

            return $this;

        }

        public function setId(string $id)
        {
            $this->id = $this->convertMongoId($id);

            return $this;
        }

        public function setType(?string $type)
        {

            if(!$type){
                throw new Exception('type is required');
            }

            $this->_type = $type;

            return $this;

        }

        public function setValue(?string $value)
        {

            if(!$value){
                throw new Exception('value is required');
            }

            $this->value = $value;

            return $this;

        }

        public function setCreated(){

            $this->created = new UTCDateTime();

            return $this;

        }


        public function get()
        {

            $q = [];
            $multi = true;

            if(isset($this->id)){
                $q = [
                    '_id' => $this->id
                ];
                $multi = false;
            }

            if(isset($this->_type))
            {
                $q['_type'] = $this->_type;
                $multi = true;
            }


            if(isset($this->value))
            {
                $q['value'] = $this->value;
                $multi = false;
            }

            $options = [
                'projection' => [
                    'created' => 0
                ],
                'sort' => [
                    'value' => 1
                ]
            ];

            return $this->dbfind($q,$multi,$options);

        }

        public function create()
        {

            if(!$this->value){
                throw new Exception('No Value set');
            }

            if(!$this->_type){
                throw new Exception('No Type set');
            }

            return $this->dbinsert($this);

        }

    }