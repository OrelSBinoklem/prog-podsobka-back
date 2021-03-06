<?php

namespace App\Traits;

//use App\Model;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use App\Helpers\Contracts\BelongsToUsers;

trait ImmunityUsers
{
    public function immunityIsHigherForPermission($permissions, $users, $require = FALSE, $equal = TRUE)
    {
        $max_immunity_1 = $this->__getMaxImmunityPerms($this);
        $max_immunity_2 = $this->__getMaxImmunityPerms($users);
        if(is_array($permissions)) {
            $max_immunity_1 = array_filter($max_immunity_1, function($k) use ($permissions) {
                foreach($permissions as $permName) {
                    //foo*    foobar***************************
                    if(strpos($permName, $k) === 0) {
                        return TRUE;
                    }
                }
                return FALSE;
            }, ARRAY_FILTER_USE_KEY);
        }
        else {
            $max_immunity_1 = array_filter($max_immunity_1, function($k) use ($permissions) {
                //foo*    foobar***************************
                return strpos($permissions, $k) === 0;
            }, ARRAY_FILTER_USE_KEY);
        }

        foreach($max_immunity_1 as $permName => $immunity) {
            $isHigher = TRUE;

            foreach ($max_immunity_2 as $permName_2 => $immunity_2) {
                //foo*    foobar***************************
                if(strpos($permName, $permName_2) === 0) {
                    $isHigher = FALSE;
                    if($immunity > $max_immunity_2[$permName_2] || ($equal && $immunity == $max_immunity_2[$permName_2])) {
                        $isHigher = TRUE;
                        break;
                    }
                }
            }

            if($isHigher && !$require) {
                return TRUE;
            }
            else if(!$isHigher && $require) {
                return FALSE;
            }
        }
        return  $require;
    }

    public function immunityIsHigherForAccessToModel($permissions, Model $model, $require = FALSE, $equal = FALSE) {
        if($model instanceof BelongsToUsers) {
            return $this->immunityIsHigherForPermission($permissions, $model->BelongsToUsers(), $require, $equal);
        } else {
            return NULL;
        }
    }

    private function __getMaxImmunityPerms($users) {
        $max_immunity = [];
        if($users instanceof Collection) {
            $roles = (new Collection(
                $users->load("roles")->pluck('roles')->collapse()->unique('id')
            ))->load('permissions');
        } else if($users instanceof User) {
            $roles = $users->roles;
        } else {
            throw new Exception('?????????????? ???????????????????????? ??????. ???????? ?????????? ???????? ?????????????? User ???????? ???????????????????? ??????????????');
        }
        foreach($roles as $role) {
            $immunity = $role->immunity ? $role->immunity : 0;
            foreach($role->permissions as $perm) {
                if(isset($max_immunity[$perm->name])) {
                    if($max_immunity[$perm->name] < $immunity) {
                        $max_immunity[$perm->name] = $immunity;
                    }
                } else {
                    $max_immunity[$perm->name] = $immunity;
                }
            }
        }
        return $max_immunity;
    }
}
