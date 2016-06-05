<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 04.06.16
 * Time: 15:21
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Countries extends Model
{
    /**
     * Get the phone record associated with the user.
     */
    public function phoneNumbers()
    {
        return $this->hasMany('App\Models\PhoneNumbers');
    }
}