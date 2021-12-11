<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\General;
    use App\Extra\Label;

    class Questions extends Mongo {

        private ObjectId $id;
        public string $_label = '';
        public ObjectId $_quizid;
        public string $type;
        public string $question;
        public string $answer;
        public int $points = 1;
        public int $sort = 10;
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

        public function setQuizId(string $id)
        {

            $this->_quizid = $this->convertMongoId($id);

            return $this;

        }

        public function setCreated(){

            $this->created = new UTCDateTime();

            return $this;

        }

        public function get()
        {
            $multi = true;
            $q = [];
            if($this->_label != ''){
                $q['_label'] = $this->_label;
            }

            if(isset($this->id)){
                $multi = false;
                $q = [
                    '_id' => $this->id
                ];
            }

            $options = [
                'sort' => [
                    'sort' => 1
                ]
            ];

            return $this->dbfind($q,$multi,$options);

        }

        public function create()
        {

            if(!$this->_label){
                throw new Exception('no label has been set');
            }

            $count = $this->get();
            if(count($count) > 0){
                $this->sort = (count($count) * 10) + 10;
            }

            return $this->dbinsert($this);

        }

        public function remove()
        {

            if(!$this->id){
                throw new Exception('no id');
            }

            $q = [
                '_id' => $this->id
            ];

            return $this->dbdelete($q);

        }

    }

?>