<?php

namespace App\Observers;

use App\Models\Calendar;
use App\Models\Obj;
use App\Models\Permission;
use Exception;
use Illuminate\Support\Str;

class CalendarObserver
{
    /**
     * Handle the calendar "created" event.
     *
     * @param Calendar $calendar
     * @return void
     */
    public function created(Calendar $calendar)
    {
        $perms = [
            [
                'title' => 'Kan ikke se',
                'description' => '',
            ],
            [
                'title' => 'Kan se',
                'description' => '',
            ],
            [
                'title' => 'Kan kommentere',
                'description' => '',
            ],
            [
                'title' => 'Kan oprette',
                'description' => '',
            ],
            [
                'title' => 'Kan moderere',
                'description' => '',
            ],
            [
                'title' => 'Kan administrere',
                'description' => '',
            ],
        ];

        for ($j = 0; $j < count($perms); $j++) {
            (new Obj)
                ->find($calendar['obj_id'])
                ->permissions()
                ->save((new Permission)->fill([
                        'level' => $j + 1,
                        'title' => $calendar['title'] . ' - ' . $perms[$j]['title'],
                        'description' => $perms[$j]['description']
                    ])
                );
        }
    }

    /**
     * Handle the calendar "updated" event.
     *
     * @param Calendar $calendar
     * @return void
     */
    public function updated(Calendar $calendar)
    {
        $perms = (new Obj)
            ->find($calendar['obj_id'])
            ->permissions;

        for ($j = 0; $j < count($perms); $j++) {
            $title = $perms[$j]['title'];
            $perms[$j]['title'] = Str::replaceFirst(
                $title,
                Str::beforeLast($title, ' - '),
                $calendar['title']
            ) . ' - ' . Str::afterLast($title, ' - ');

            $perms[$j]->save();
        }
    }

    /**
     * Handle the calendar "deleted" event.
     *
     * @param Calendar $calendar
     * @return void
     * @throws Exception
     */
    public function deleted(Calendar $calendar)
    {
        (new Obj)
            ->find($calendar['obj_id'])
            ->delete();

        return;
    }

    /**
     * Handle the calendar "restored" event.
     *
     * @param Calendar $calendar
     * @return void
     */
    public function restored(Calendar $calendar)
    {
        //
    }

    /**
     * Handle the calendar "force deleted" event.
     *
     * @param Calendar $calendar
     * @return void
     */
    public function forceDeleted(Calendar $calendar)
    {
        //
    }
}
