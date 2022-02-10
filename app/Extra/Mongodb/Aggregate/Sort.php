<?php

    namespace App\Extra\Mongodb\Aggregate;

    use MongoDB\Client;
	use MongoDB\BSON\UTCDateTime;
    use App\Extra\Mongodb\Aggregate;
    use App\Extra\General;

    class Sort extends Aggregate
    {

        public $type;
        public $output;

        public function __construct()
        {

            $this->setType(strtolower(General::get_class_name(get_class($this))));

        }

        public function addCondition(string $field, string $direction = 'asc')
        {
            
            $order = $direction == 'desc' ? -1 : 1;

            $this->output[$this->type][$field] = $order;

            return $this;

        }

        public function get():array
        {

            return $this->output;

        }

    }