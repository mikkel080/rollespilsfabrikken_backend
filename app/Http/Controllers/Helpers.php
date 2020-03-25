<?php


namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;

class Helpers
{
    public function convertDate($date)
    {
        if ($date == null || $date == '' || $date == false) return false;
        $approvedFormats = [
            'Y-m-d H:i:s',
            'd-m-Y H:i:s',
            'y-m-d H:i:s',
            'd-m-y H:i:s',
            'd M Y H:i:s',
            'Y-m-d H:i',
            'd-m-Y H:i',
            'y-m-d H:i',
            'd-m-y H:i',
            'd M Y H:i',
            'Y-m-d',
            'd-m-Y',
            'y-m-d',
            'd-m-y',
            'd M Y'
        ];

        foreach($approvedFormats as $format) {
            if($carbon = Carbon::createFromFormat($format, $date)) {
                return $carbon->format('Y-m-d H:i:s');
            }
        }

        return false;
    }

    public function filterItems(FormRequest $request, Builder $models) {
        $items = 5;
        $sortBy = 'created_at';
        $order  = 'asc';

        if ($request->query('before') && $date = $this->convertDate($request->query('before'))) {
            $models->where('created_at', '<', $date);
        }

        if ($request->query('after') && $date = $this->convertDate($request->query('after'))) {
            $models->where('created_at', '>', $date);
        }

        if ($request->query('user')) {
            $models->where('user_id', '=', $request->query('user'));
        }

        if ($request->query('direction') && in_array($request->query('direction'), ['asc', 'desc'])) {
            $order = $request->query('direction');
        }

        if ($request->query('sort') && in_array($request->query('sort'), $models->first()->getTableColumns())) {
            $models->orderBy($request->query('sort'), $order);
        }

        if ($request->query('items')) {
            $items = $request->query('items');
        }

        $models = $models->paginate($items);
        $models->appends($request->except('page'))->links();

        return $models;
    }

    public function searchItems(FormRequest $request, $model, $wheres) {
        $sortBy = 'created_at';
        $order  = 'asc';
        $items = 5;

        $models = $model::search($request->query('search'));

        if ($request->query('items')) {
            $items = $request->query('items');
        }

        if ($request->query('direction') && in_array($request->query('direction'), ['asc', 'desc'])) {
            $order = $request->query('direction');
        }

        if ($request->query('sort') && in_array($request->query('sort'), $models->first()->getTableColumns())) {
            $models->orderBy($request->query('sort'), $order);
        }

        foreach ($wheres as $where) {
            $models->where($where['key'], $where['value']);
        }

        $models = $models->paginate($items);
        $models->appends($request->except('page'))->links();

        return $models;
    }
}
