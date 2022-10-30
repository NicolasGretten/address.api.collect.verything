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
 * @property mixed address_line_1
 * @property mixed state
 * @property mixed zip_code
 * @property mixed addressLine4
 * @property mixed addressLine3
 * @property mixed address_line_2
 */
class Address extends Model
{
    use SoftDeletes, HasFactory;

    protected $dates = ['created_at, updated_at, deleted_at'];
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
