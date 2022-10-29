<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @method static select(string $string)
 * @property mixed title
 * @property mixed              city
 * @property mixed              country
 * @property mixed              latitude
 * @property mixed              longitude
 * @property false|mixed|string id
 * @property mixed addressLine1
 * @property mixed state
 * @property mixed zipCode
 * @property mixed addressLine4
 * @property mixed addressLine3
 * @property mixed addressLine2
 */
class Address extends Model
{
    use SoftDeletes, HasFactory;

    protected $dates = ['createdAt, updatedAt, deletedAt'];
    const DELETED_AT = 'deletedAt';
    const UPDATED_AT = 'updatedAt';
    const CREATED_AT = 'createdAt';
    protected $connection = 'data';
    protected $table = 'addresses';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}
