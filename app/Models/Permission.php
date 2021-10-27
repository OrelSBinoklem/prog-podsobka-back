<?php

namespace App\Models;

use App\Helpers\Contracts\BelongsToUsers;

class Permission extends Model implements BelongsToUsers
{
    //

    public function roles() {
		return $this->belongsToMany('App\Models\Role','permission_role');
	}

    public function BelongsToUsers() {
        return $this->roles->load("users")->pluck('users')->collapse()->unique('id');
    }
}
