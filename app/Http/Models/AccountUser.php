<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountUser extends Model
{
  const CREATED_AT = 'cretime';
  const UPDATED_AT = 'modtime';

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'tm_users';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $guarded = ['id'];
  protected $primaryKey = 'id';
}
