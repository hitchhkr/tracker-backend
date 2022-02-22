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
    use App\Extra\Mongodb\Aggregate\Helpers;

    class One extends Mongo {

        private ?ObjectId $id = null;
        private ?ObjectId $uid = null;
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

        public function setUserId($uid):object
        {
            $this->uid = $this->convertMongoId($uid);
            return $this;
        }

        public function setCreated(){

            $this->created = new UTCDateTime();

            return $this;

        }

        public function get(bool $random = false, int $limit = 10):?array
        {

            $multi = false;

        //  If we have an id then we just send the single result
            if(!$this->id){
                $agg= [];
                $multi = true;
            }else{
                $match = [
                    '$match' => [
                        '_id' => $this->id
                    ]
                ];
                $agg = [$match];
            }

            if($random == true){
                $sample = (new Sample())->setLimit($limit)->get();
                array_push($agg,$sample);
            }

            $agg = (new Helpers())->buildOneUserDisplay($this->uid,$agg)->get();

            $sort = [
                '$sort' => [
                    'title' => 1
                ]
            ];
            array_push($agg,$sort);

            $result = $this->dbagg($agg);

            return $multi ? $result : $result[0];

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

            $agg = (new Helpers())->buildOneUserDisplay($this->uid,$agg)->get();

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

            $agg = (new Helpers())->buildOneUserDisplay($this->uid,$agg)->get();

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

            $agg = (new Helpers())->buildOneUserDisplay($this->uid,$agg)->get();

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

        public function searchTitle(string $q, int $limit = 10):?array
        {

            $search = [
                '$search' => [
                    'index' => 'one_search',
                    'autocomplete' => [
                        'path' => 'title',
                        'query' => $q
                    ]
                ]
            ];

            $limit = [
                '$limit' => $limit
            ];

            $project = [
                '$project' => [
                    'title' => 1,
                    'year' => 1,
                    'score' => [
                        '$meta' => 'searchScore'
                    ]
                ]
            ];

            $sort = [
                '$sort' => [
                    'score' => -1
                ]
            ];

            $agg = [
                $search,
                $limit,
                $project,
                $sort
            ];

            return $this->dbagg($agg);

        }

        public function getTotal():?array
        {

            $agg = [
                [
                    '$count' => 'films'
                ]
            ];

            $res = $this->dbagg($agg);

            return $res ? $res[0] : ['films' => 0];

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

            $aggs = (new Helpers())->buildOneUserDisplay($this->uid,$aggs)->get();

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