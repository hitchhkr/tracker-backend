<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\General;

    class Actors extends Mongo {

        private ObjectId $id;
        public string $name;
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

        public function setName(?string $name)
        {

            if(!$name){
                throw new Exception('name is required');
            }

            $this->name = $name;

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

            if(isset($this->name))
            {
                $q = [
                    'name' => $this->name
                ];
                $multi = false;
            }

            $options = [
                'projection' => [
                    'created' => 0
                ],
                'sort' => [
                    'name' => 1
                ]
            ];

            return $this->dbfind($q,$multi,$options);

        }

        public function create()
        {

            if(!$this->name){
                throw new Exception('No Name set');
            }

            return $this->dbinsert($this);

        }

        public function update($vars)
        {

            if(!$this->id){
                throw new Exception('no id has been set');
            }

            $q = [
                '_id' => $this->id
            ];

            $vars['updated'] = new UTCDateTime();

            $update = [
                '$set' => $vars,
                '$inc' => [
                    'version' => 1
                ]
            ];

            return $this->dbupdate($q,$update);

        }

    }

?>