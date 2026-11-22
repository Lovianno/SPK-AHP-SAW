<?php

namespace App\Services;

use App\Models\Criterion;
use Illuminate\Support\Facades\DB;

class CriterionService
{
    /**
     * Get all criteria with optional search filter and pagination.
     */
    public function getAll(?string $search = null, int $perPage = 10)
    {
        return Criterion::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Create a new criterion.
     */
    public function create(array $data): Criterion
    {
        DB::beginTransaction();
        try {
            $criterion = Criterion::query()->create($data);
            DB::commit();

            return $criterion;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing criterion.
     */
    public function update(Criterion $criterion, array $data): bool
    {
        DB::beginTransaction();
        try {
            $result = $criterion->update($data);
            DB::commit();

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a criterion.
     */
    public function delete(Criterion $criterion): ?bool
    {
        $hasScores = DB::table('alternative_scores')->where('criterion_id', $criterion->id)->exists();
        $hasComparisons = DB::table('ahp_comparisons')
            ->where('criterion_a_id', $criterion->id)
            ->orWhere('criterion_b_id', $criterion->id)
            ->exists();

        if ($hasScores || $hasComparisons) {
            throw new \DomainException('Kriteria tidak dapat dihapus karena digunakan pada skor alternatif atau perbandingan AHP.');
        }

        return $criterion->delete();
    }
}