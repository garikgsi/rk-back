<?php

namespace App\Observers;

use App\Models\Period;

class PeriodObserver
{
    /**
     * Handle the Period "saving" event.
     *
     * @param  \App\Models\Period  $period
     * @return void
     */
    public function saving(Period $period)
    {
        //
    }

    /**
     * Handle the Period "created" event.
     *
     * @param  \App\Models\Period  $period
     * @return void
     */
    public function created(Period $period)
    {
        //
    }

    /**
     * Handle the Period "updated" event.
     *
     * @param  \App\Models\Period  $period
     * @return void
     */
    public function updated(Period $period)
    {
        //
    }

    /**
     * Handle the Period "deleted" event.
     *
     * @param  \App\Models\Period  $period
     * @return void
     */
    public function deleted(Period $period)
    {
        //
    }

    /**
     * Handle the Period "restored" event.
     *
     * @param  \App\Models\Period  $period
     * @return void
     */
    public function restored(Period $period)
    {
        //
    }

    /**
     * Handle the Period "force deleted" event.
     *
     * @param  \App\Models\Period  $period
     * @return void
     */
    public function forceDeleted(Period $period)
    {
        //
    }
}
