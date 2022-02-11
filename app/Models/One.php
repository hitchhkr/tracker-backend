<?php

    namespace App\Models;

    use App\Extra\Mongo;
    use MongoDB\BSON\UTCDateTime;
    use MongoDB\BSON\ObjectId;
    use App\Extra\General;
    use App\Extra\Label;
    use App\Extra\Mongodb\Aggregate\Sort;
    use App\Extra\Mongodb\Aggregate\Sample;
    use App\Extra\Mongodb\Aggregate\Unwind;

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

        public function getByDirector($id):?array
        {

            $match = [
                '$match' => [
                    'director' => [
                        '$elemMatch' => [
                            '_id' => new ObjectId($id)
                        ]
                    ]
                ]
            ];

            $sort = (new Sort())->addCondition('year','desc');

            $agg = [
                $match,
                $sort->get()
            ];

            return $this->dbagg($agg);

        }

        public function getByDecade(int $id, bool $random = true, int $limit = 10):?array
        {

            $match = [
                '$match' => [
                    'decade' => $id
                ]
            ];

            $agg = [
                $match
            ];

            if($random == true){
                $sample = (new Sample())->setLimit($limit)->get();
                array_push($agg,$sample);
            }

            return $this->dbagg($agg);

        }

        public function getByGenre(string $id, bool $random = true, int $limit = 10):?array
        {

            $match = [
                '$match' => [
                    'genres' => [
                        '$elemMatch' => [
                            '_id' => new ObjectId($id)
                        ]
                    ]
                ]
            ];

            $agg = [
                $match
            ];

            if($random == true){
                $sample = (new Sample())->setLimit($limit)->get();
                array_push($agg,$sample);
            }

            return $this->dbagg($agg);

        }

        public function getFocus(string $field, string $not = null):?array
        {

            $unwind = (new Unwind())->setField($field)->get();

            $group = [
                '$group' => [
                    '_id' => '$' . $field,
                    'info' => [
                        '$push' => [
                            '_id' => '$_id',
                            'title' => '$title',
                            'cropped' => '$cropped'
                        ]
                    ]
                ]
            ];

            $sample = (new Sample())->setLimit(1)->get();
            $unwind2 = (new Unwind())->setField('info')->get();
            $sample2 = (new Sample())->setLimit(1)->get();

            $agg = [
                $unwind,
                $group,
                $sample,
                $unwind2,
                $sample2
            ];

            if($not){
                $match = [
                    '$match' => [
                        '_id' => [
                            '$ne' => new ObjectId($not)
                        ]
                    ]
                ];
                array_unshift($agg,$match);
            }

            return $this->dbagg($agg);

        }

        public function getSimilar(string $id, int $limit):array
        {

            $compare = $this->setId($id)->get();

            $genres = [];

            if($compare['genres']){
                foreach($compare['genres'] AS $g)
                {
                    $genres[] = $g['value'];
                }
            }

            $match = [
                '$match' => [
                    '_id' => [ '$ne' => $compare['_id'] ],
                    '$or' => [
                        [
                            'decade' => $compare['decade']
                        ],
                        [
                            'genres' => [
                                '$elemMatch' => [
                                    'value' => [
                                        '$in' => $genres
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $sample = (new Sample())->setLimit($limit)->get();

            $aggs = [
                $match,
                $sample
            ];

            return $this->dbagg($aggs);

        }

        public function create()
        {

            if(!$this->_label){
                throw new Exception('No label set');
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