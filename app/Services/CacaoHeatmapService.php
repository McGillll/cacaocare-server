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

    private function initQuery(array $status, string $year): Builder
    {
        return DB::table('cacaos')
            ->selectRaw("YEAR(created_at) as year, uploaderId")
            ->whereIn('label', $status)
            ->whereYear('created_at', $year)
            ->groupBy('uploaderId', 'year');
    }

}
