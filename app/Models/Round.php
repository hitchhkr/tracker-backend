<?php

    namespace App\Models;

    use MongoDB\BSON\UTCDateTime;
    //use MongoDB\BSON\ObjectId;
    //use App\Extra\General;
    use App\Extra\Label;

    class Round
    {

        public string $title;
        public string $description;
        public string $type;
        public string $label;
        public $icon = null;
        public bool $enabled = false;
        public UTCDateTime $created;
        //public ?UTCDateTime $enabledDate = null;

        public function __construct()
        {
            $this->setLabel();
            $this->setCreated();
        }

        public function setLabel(){

            $this->label = Label::create();

            return $this;

        }

        public function setCreated()
        {

            $this->created = new UTCDateTime();

            return $this;

        }

        public function setActive(bool $active)
        {

            $this->active = $active;

            return $this;

        }

        public function setDescription(string $desc){

            $this->description = trim($desc);

            return $this;

        }

        public function setType(string $type){

            $this->type = trim($type);

            return $this;

        }

        public function setTitle(string $title)
        {

            $this->title = trim($title);

            if($this->title == ''){
                throw new Exception('Title must not be blank');
            }

            return $this;

        }

    }