<?php

namespace App\Repositories;

use App\Interfaces\Cacao\CacaoRepositoryInterface;
use App\Models\Cacao;
use Illuminate\Support\Carbon;

class CacaoRepository implements CacaoRepositoryInterface
{
    public function feed(object $payload)
    {
        $status = ['Black Pod Rot', 'Frosty Pod Rot', 'Healthy Pod'];
        switch (strtoupper($payload->filter)) {
            case 'HEALTHY':
                $status = ['Healthy Pod'];
                break;
            case 'DISEASED':
                $status = ['Black Pod Rot', 'Frosty Pod Rot'];
                break;
            default:
                $status = ['Black Pod Rot', 'Frosty Pod Rot', 'Healthy Pod'];
        }

        $query = Cacao::select('cacaos.*', 'users.username', 'users.profile', 'users.city', 'users.barangay')
            ->join('users', 'users.id', '=', 'cacaos.uploaderId')
            ->orderBy('cacaos.created_at', $payload->order)
            ->whereIn('cacaos.label', $status);

        if (!empty($payload->username)) {
            $query->where('users.username', 'LIKE', '%'.$payload->username.'%');
        }

        return $query->paginate(6);
    }

    public function create(object $payload)
    {
        $cacao = new Cacao();
        $cacao->label = $payload->label;
        $cacao->confidence = $payload->confidence;
        $cacao->date_analyzed = $payload->date_analyzed;
        $cacao->caption = $payload->caption ?? null;
        $cacao->uploaderId = $payload->uploaderId;
        $cacao->photo = $payload->photo;
        $cacao->save();

        return $cacao;
    }

    public function update(Cacao $cacao, object $payload)
    {
        if (!empty($payload->label)) {
            $cacao->label = $payload->label;
        }
        if (!empty($payload->caption)) {
            $cacao->caption = $payload->caption;
        }
        if (!empty($payload->date_analyzed)) {
            $cacao->date_analyzed = $payload->date_analyzed;
        }
        if (!empty($payload->photo)) {
            $cacao->photo = $payload->photo;
        }
        $cacao->save();

        return $cacao;
    }

    public function delete(Cacao $cacao)
    {
        return $cacao->delete();
    }

    public function recent()
    {
        return Cacao::select('cacaos.*', 'users.username', 'users.profile', 'users.city', 'users.barangay')
            ->join('users', 'users.id', '=', 'cacaos.uploaderId')
            ->latest('cacaos.created_at')
            ->limit(3)
            ->get();
    }

    public function statusCount()
    {
        $healthy = Cacao::where('label', 'Healthy Pod')->count();
        $diseased = Cacao::where('label', '!=', 'Healthy Pod')->count();

        return [
            'healthy' => $healthy,
            'diseased' => $diseased,
            'all' => $healthy + $diseased,
        ];
    }

    public function uploadedTodayCount()
    {
        return Cacao::where('created_at', '>=', now()->subHours(24))->count();
    }

    public function uploadCountByDate()
    {
        $blackpodCounts = [];
        $frostypodCounts = [];

        $currentDate = Carbon::now();

        for ($i = 4; $i >= 0; $i--) {
            $targetDate = $currentDate->copy()->subMonths($i);
            $month = $targetDate->month;
            $year = $targetDate->year;

            $blackpodCount = Cacao::where('label', 'Black Pod Rot')
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->distinct('uploaderId')
                ->count('uploaderId');

            $frostypodCount = Cacao::where('label', 'Frosty Pod Rot')
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->distinct('uploaderId')
                ->count('uploaderId');

            $blackpodCounts[] = $blackpodCount;
            $frostypodCounts[] = $frostypodCount;
        }

        return [
            'blackpod' => $blackpodCounts,
            'frostypod' => $frostypodCounts,
        ];
    }

    public function uploadCountByUser(int $userId)
    {
        $healthy = Cacao::where('label', 'Healthy Pod')
            ->where('uploaderId', $userId)
            ->count();
        $diseased = Cacao::where('label', '!=', 'Healthy Pod')
            ->where('uploaderId', $userId)
            ->count();

        return [
            'all' => $healthy + $diseased,
            'healthy' => $healthy,
            'diseased' => $diseased,
        ];
    }

    public function cacaoUploadedByUser(int $userId)
    {
        return Cacao::select('cacaos.*', 'users.username', 'users.profile', 'users.city', 'users.barangay')
            ->join('users', 'users.id', '=', 'cacaos.uploaderId')
            ->where('users.id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(9);
    }

    public function highestDiseaseWithinTheWeek()
    {
        $cacaoStats = Cacao::selectRaw('cacaos.label, users.city, COUNT(*) as count')
            ->join('users', 'users.id', '=', 'cacaos.uploaderId')
            ->where('cacaos.created_at', '>=', now()->subDays(7))
            ->where('cacaos.label', '!=', 'Healthy Pod')
            ->groupBy('cacaos.label', 'users.city')
            ->orderByRaw('COUNT(*) DESC')
            ->get();

        $maxCount = $cacaoStats->max('count');

        return $cacaoStats->where('count', $maxCount);
    }
}
