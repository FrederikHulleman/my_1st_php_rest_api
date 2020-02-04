<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{

//    //no created & updated timestamps in this model
//    public $timestamps = true;
   //the only allowable columns to be updated
    protected $fillable = ['title','url'];

    public function reviews()
    {
        return $this->hasMany('App\Model\Review')->orderBy('id','asc');
    }

    public function delete()
    {
        parent::delete();
        return ["message" => "The course was deleted"];
    }
}