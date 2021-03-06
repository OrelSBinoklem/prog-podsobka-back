<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;

use App\Models\Menu;
use App\Models\MenuItems;

class ClearMenuClientCache
{
    public function handle($event)
    {
        $model = $event->getModel();

        if($model instanceof Menu) {
            \Cache::forget('menu_client_' . $model->id);
        }

        if($model instanceof MenuItems) {
            \Cache::forget('menu_client_' . $model->menu_id);
        }
    }
}
