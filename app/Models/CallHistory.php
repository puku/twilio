<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 04.06.16
 * Time: 15:21
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class CallHistory extends Model
{
    protected $table = 'call_history';

    protected $fillable = [
        'application_sid',
        'call_sid',
        'account_sid',
        'from',
        'to',
        'call_status',
        'api_version',
        'direction',
        'called',
        'caller',
    ];

}