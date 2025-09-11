<?php
namespace App\Services;

use App\Models\Cacao;
use Illuminate\Database\Eloquent\Builder;

class CacaoYearGraphService
{
    /**
     * Get monthly counts for Black Pod Rot (Rot, not Disease).
     */
    public function getYearlyBlackpodData(
        string $year,
        ?string $region = null,
        ?string $city   = null
    ): array {
        $counts = [];

        // Loop months 1–12
        for ($m = 1; $m <= 12; $m++) {
            $counts[] = $this->baseQuery($year, $m, $region, $city)
                ->where('label', 'Black Pod Rot')
                ->distinct('uploaderId')
                ->count('uploaderId');
        }

        return $counts;
    }

    /**
     * Get monthly counts for Frosty Pod Rot.
     */
    public function getYearlyFrostypodData(
        string $year,
        ?string $region = null,
        ?string $city   = null
    ): array {
        $counts = [];

        for ($m = 1; $m <= 12; $m++) {
            $counts[] = $this->baseQuery($year, $m, $region, $city)
                ->where('label', 'Frosty Pod Rot')
                ->distinct('uploaderId')
                ->count('uploaderId');
        }

        return $counts;
    }

    /**
     * Shared base query builder for a given year, month, region, and city.
     */
    private function baseQuery(
        string $year,
        int    $month,
        ?string $region = null,
        ?string $city   = null
    ): Builder {
        return Cacao::whereYear('created_at', "=", $year)
            ->whereMonth('created_at', "=", $month)
            ->when($region, fn($q, $r) => $q->where('region', $r))
            ->when($city,   fn($q, $c) => $q->where('city',   $c));
    }
}
