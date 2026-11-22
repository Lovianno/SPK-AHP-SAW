<?php

namespace App\Services;

use App\Models\AhpComparison;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AhpComparisonService
{
    public function create(array $data): AhpComparison
    {
        return DB::transaction(function () use ($data) {
            // Pasangan A-B dan B-A dianggap pasangan yang sama.
            $a = (int) $data['criterion_a_id'];
            $b = (int) $data['criterion_b_id'];
            if ($a > $b) {
                [$a, $b] = [$b, $a];
                $data['more_important'] = match ($data['more_important']) {
                    'a' => 'b',
                    'b' => 'a',
                    default => 'equal',
                };
            }

            if (AhpComparison::where('criterion_a_id', $a)->where('criterion_b_id', $b)->exists()) {
                throw ValidationException::withMessages(['criterion_b_id' => 'Pasangan kriteria ini sudah ada. Gunakan tombol Ubah.']);
            }

            return AhpComparison::create([
                'criterion_a_id' => $a,
                'criterion_b_id' => $b,
                'value' => $this->ratio($data),
            ]);
        });
    }

    public function update(AhpComparison $comparison, array $data): bool
    {
        return $comparison->update(['value' => $this->ratio($data)]);
    }

    public function delete(AhpComparison $comparison): ?bool
    {
        return $comparison->delete();
    }

    private function ratio(array $data): float
    {
        $intensity = (int) $data['intensity'];
        if ($data['more_important'] === 'equal' && $intensity !== 1) {
            throw ValidationException::withMessages(['intensity' => 'Jika sama penting, pilih intensitas 1.']);
        }
        if ($data['more_important'] !== 'equal' && $intensity === 1) {
            throw ValidationException::withMessages(['more_important' => 'Untuk intensitas 1, pilih Sama penting.']);
        }

        return $data['more_important'] === 'b' ? 1 / $intensity : $intensity;
    }
}
