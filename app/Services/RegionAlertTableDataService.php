<?php
namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class RegionAlertTableDataService
{
    public function years()
    {
        return DB::table('cacaos')
            ->selectRaw("DATE_FORMAT(created_at, '%Y') as year")
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
    }

    public function result(string $date)
    {
        $subQuery = $this->initQuery( $date);

        return DB::table(DB::raw("({$subQuery->toSql()}) as cacaos"))
            ->mergeBindings($subQuery)
            ->join('users', 'users.id', '=', 'cacaos.uploaderId')
            ->select(
                'users.city',
                'users.region',
                DB::raw('COUNT(*) as user_count')
            )
            ->groupBy('users.city', 'users.region')
            ->get();
    }

    public function totalCount( string $date)
    {
        $subQuery = $this->initQuery($date);

        return DB::table(DB::raw("({$subQuery->toSql()}) as weekly_uploads"))
            ->mergeBindings($subQuery)
            ->count();
    }

    public function totalCity(string $date)
    {
        $subQuery = $this->initQuery($date);

        return DB::table(DB::raw("({$subQuery->toSql()}) as weekly_uploads"))
            ->mergeBindings($subQuery)
            ->join('users', 'users.id', '=', 'weekly_uploads.uploaderId')
            ->distinct('users.city')
            ->count('users.city');
    }

    // Your service methods here
    private function initQuery(array $status, string $year): Builder
    {
        return DB::table('cacaos')
            ->selectRaw("YEAR(created_at) as year, uploaderId")
            ->whereIn('label', $status)
            ->whereYear('created_at', $year)
            ->groupBy('uploaderId', 'year');
    }

}
