<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\General;
    use App\Extra\Label;

    class One extends Mongo {

        private ObjectId $id;
        public string $_label;
        public string $title;
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

        public function setLabel(?string $label)
        {

            $this->_label = $label ? $label : Label::create();

            return $this;

        }

        public function setCreated(){

            $this->created = new UTCDateTime();

            return $this;

        }

        public function get()
        {

            if(isset($this->id)){
                $q = [
                    '_id' => $this->id
                ];
                $multi = false;
            }else{
                $q = [];
                $multi = true;
            }

            $options = [
                'sort' => [
                    'title' => 1
                ]
            ];

            return $this->dbfind($q,$multi,$options);

        }

        public function create()
        {

            if(!$this->_label){
                throw new Exception('No label set');
            }

            return $this->dbinsert($this);

        }

    }

?>