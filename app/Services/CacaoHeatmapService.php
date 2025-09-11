<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CacaoHeatmapService
{
    private $startOfWeek = Carbon::now()->startOfWeek();
    private $endOfWeek = Carbon::now()->endOfWeek();

    public function result(array $status){
        $subQuery = $this->initquery($status);

        $result = DB::table(DB::raw("({$subQuery->toSql()}) as cacaos"))
            ->mergeBindings($subQuery)
            ->join('users', 'users.id', '=', 'cacaos.uploaderId')
            ->select(
                'users.city',
                'users.barangay',
                'users.region',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('users.city', 'users.barangay', 'users.region')
            ->get();

        return $result;
    }

    public function totalCount(array $status){
        $subQuery = $this->initquery($status);

        $totalCount = DB::table(DB::raw("({$subQuery->toSql()}) as weekly_uploads"))
        ->mergeBindings($subQuery)
        ->count();

        return $totalCount;
    }

    private function initquery(array $status):Builder{
        $subQuery = DB::table('cacaos')
        ->select(
            'uploaderId',
            DB::raw("DATE_FORMAT(created_at, '%Y-%v') as year_week")
        )
        ->whereIn('label', $status)
        ->whereBetween('created_at', [
            $this->startOfWeek->toDateTimeString(),
            $this->endOfWeek->toDateTimeString(),
        ])
        ->groupBy('uploaderId', 'year_week');

        return $subQuery;
    }
}
