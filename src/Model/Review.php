<?php


namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{

    //the only allowable columns to be updated
    protected $fillable = ['course_id','rating','comment'];

    public function courses()
    {
        return $this->belongsTo('App\Model\Course')->orderBy('id','asc');
    }

    public function delete()
    {
        parent::delete();
        return ["message" => "The review was deleted"];
    }
}