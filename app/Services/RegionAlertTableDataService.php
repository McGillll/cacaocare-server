<?php
namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class RegionAlertTableDataService
{
    public function years()
    {
        return DB::table('cacaos')
            ->select(DB::raw("DATE_FORMAT('%Y', created_at) as year"))
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
    private function initQuery(?string $date = null): Builder
    {
        $query = DB::table('cacaos')
            ->select([
                'uploaderId',
                DB::raw("DATE_FORMAT('%Y', created_at) as year"),
            ])
            ->where('year', $date);

        return $query->groupBy('uploaderId', 'year');
    }
}
