<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'organisation_name', 'vat_number', 'address', 'post_code', 'city', 'country', 'website_url'])]
class CoordinatorProfile extends Model
{
    //
}
