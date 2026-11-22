<?php

namespace App\Services;

use App\Models\Alternative;
use Illuminate\Support\Facades\DB;

class AlternativeService
{
    public function getAll(?string $search = null, int $perPage = 10)
    {
        return Alternative::query()
            ->withCount('scores')
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Alternative
    {
        return DB::transaction(function () use ($data) {
            $alternative = Alternative::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);
            $this->saveScores($alternative, $data['scores'] ?? []);

            return $alternative;
        });
    }

    public function update(Alternative $alternative, array $data): bool
    {
        return DB::transaction(function () use ($alternative, $data) {
            $result = $alternative->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);
            $this->saveScores($alternative, $data['scores'] ?? []);

            return $result;
        });
    }

    private function saveScores(Alternative $alternative, array $scores): void
    {
        foreach ($scores as $criterionId => $value) {
            $alternative->scores()->updateOrCreate(
                ['criterion_id' => $criterionId],
                ['value' => $value]
            );
        }
    }

    public function delete(Alternative $alternative): ?bool
    {
        // Skor ikut dihapus melalui cascadeOnDelete pada foreign key.
        return $alternative->delete();
    }
}
