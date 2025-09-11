<?php
namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;


class CacaoHeatmapService
{

    public function result(array $status, string $date)
    {
        $subQuery = $this->initQuery($status, $date);

        return DB::table(DB::raw("({$subQuery->toSql()}) as cacaos"))
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
    }

    public function totalCount(array $status, string $date)
    {
        $subQuery = $this->initQuery($status, $date);

        return DB::table(DB::raw("({$subQuery->toSql()}) as weekly_uploads"))
            ->mergeBindings($subQuery)
            ->count();
    }

    private function initQuery(array $status, string $date): Builder
    {
        $q = DB::table('cacaos')
            ->select(
                'uploaderId',
                DB::raw("DATE_FORMAT(created_at, '%Y') as year")
            )
            ->whereIn('label', $status)
            ->where('year', $date);;

        return $q->groupBy('uploaderId', 'year');
    }

}
