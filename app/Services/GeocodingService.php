<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeocodingService
{
    protected string $baseUrl;

    public function __construct()
    {
        // You can move this to config/geocoding.php later
        $this->baseUrl = 'https://nominatim.openstreetmap.org/search';
    }

    /**
     * Fetches the bounding box for a barangay in a given city.
     *
     * @param  string  $barangay
     * @param  string  $city
     * @return array|null
     */
    public function getBarangayBoundingBox(string $barangay, string $city): ?array
    {
        $response = Http::acceptJson()
            ->get($this->baseUrl, [
                'q'      => "{$barangay}, {$city}, Philippines",
                'format' => 'json',
                'limit'  => 1,
            ]);

        if ($response->failed() || empty($response[0]['boundingbox'] ?? null)) {
            return null;
        }


        return $response[0]['boundingbox'];
    }
}
