<?php

use App\Http\Controllers\SawController;
use App\Models\AhpComparison;
use App\Models\Alternative;
use App\Models\Criterion;
use App\Models\User;
use Database\Seeders\AhpComparisonSeeder;
use Database\Seeders\AlternativeScoreSeeder;
use Database\Seeders\AlternativeSeeder;
use Database\Seeders\CriteriaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'ahp@example.test', 'password' => 'password']));
});

function calculationCriteria(int $count = 2): array
{
    $criteria = [];
    for ($i = 0; $i < $count; $i++) {
        $criteria[] = Criterion::create(['name' => 'Kriteria '.($i + 1), 'attribute' => $i === 1 ? 'cost' : 'benefit']);
    }
    for ($i = 0; $i < $count; $i++) {
        for ($j = $i + 1; $j < $count; $j++) {
            AhpComparison::create(['criterion_a_id' => $criteria[$i]->id, 'criterion_b_id' => $criteria[$j]->id, 'value' => 1]);
        }
    }

    return $criteria;
}

function calculationMenu(string $name, array $criteria, array $values): Alternative
{
    $alternative = Alternative::create(['name' => $name]);
    foreach ($criteria as $i => $criterion) {
        $alternative->scores()->create(['criterion_id' => $criterion->id, 'value' => $values[$i]]);
    }

    return $alternative;
}

it('seeds the kost case study and keeps existing AHP judgments', function () {
    $this->seed([CriteriaSeeder::class, AhpComparisonSeeder::class, AlternativeSeeder::class, AlternativeScoreSeeder::class]);
    expect(DB::table('criteria')->orderBy('id')->pluck('name')->all())
        ->toBe(['Harga', 'Jarak', 'Fasilitas', 'Keamanan', 'Luas Kamar']);
    $this->assertDatabaseHas('criteria', ['name' => 'Harga', 'attribute' => 'cost', 'unit' => 'skor 1-5']);
    $this->assertDatabaseHas('criteria', ['name' => 'Jarak', 'attribute' => 'benefit', 'unit' => 'skor 1-5']);
    $this->assertDatabaseCount('alternatives', 30);
    $this->assertDatabaseCount('alternative_scores', 150);
    $wisma = Alternative::where('name', 'Wisma Pusparini')->firstOrFail();
    $harga = Criterion::where('name', 'Harga')->firstOrFail();
    $this->assertDatabaseHas('alternative_scores', ['alternative_id' => $wisma->id, 'criterion_id' => $harga->id, 'value' => 1]);

    $response = $this->post(route('ahp.calculate'))->assertOk()->assertSee('Konsisten');
    $ahp = $response->viewData('ahp');
    foreach (array_values($ahp['weights']) as $i => $weight) {
        expect(abs($weight - [0.3750632931, 0.2146809678, 0.1214844045, 0.2146809678, 0.0740903669][$i]))->toBeLessThan(1e-9);
    }
    expect(abs(array_sum($ahp['weights']) - 1))->toBeLessThan(1e-12);
    foreach (['lambdaMax' => 5.0331703389, 'ci' => 0.0082925847, 'cr' => 0.0074040935] as $key => $value) {
        expect(abs($ahp[$key] - $value))->toBeLessThan(1e-9);
    }
    $ranking = $this->post(route('saw.calculate'))->assertOk()->viewData('saw')['ranking'];
    expect($ranking)->toHaveCount(30);
    expect($ranking[0]['name'])->toBe('Wisma Pusparini');
    $pair = AhpComparison::first();
    $pair->update(['value' => 7]);
    $this->seed(AhpComparisonSeeder::class);
    expect((float) $pair->fresh()->value)->toBe(7.0);
    $this->assertDatabaseCount('ahp_comparisons', 10);
});

it('maps kost criteria by name with reversed nonsequential database IDs', function () {
    foreach (['Keamanan' => 4, 'Harga' => 17, 'Luas Kamar' => 29, 'Fasilitas' => 43, 'Jarak' => 61] as $name => $id) {
        DB::table('criteria')->insert(['id' => $id, 'name' => $name, 'attribute' => $name === 'Harga' ? 'cost' : 'benefit']);
    }
    $this->seed(AhpComparisonSeeder::class);
    $this->assertDatabaseHas('ahp_comparisons', ['criterion_a_id' => 4, 'criterion_b_id' => 43, 'value' => 2]);
    $ahp = $this->post(route('ahp.calculate'))->assertOk()->viewData('ahp');
    expect(abs($ahp['weights'][17] - 0.3750632931))->toBeLessThan(1e-9);
    expect(abs($ahp['cr'] - 0.0074040935))->toBeLessThan(1e-9);
});

it('creates reversed pairs through the new form and rejects duplicate pairs', function () {
    DB::table('criteria')->insert([
        ['id' => 8, 'name' => 'Rasa', 'attribute' => 'benefit'],
        ['id' => 31, 'name' => 'Harga', 'attribute' => 'cost'],
    ]);
    $this->get(route('ahp-comparisons.create'))->assertOk()->assertSee('Kriteria A')->assertSee('Kriteria B');
    $this->post(route('ahp-comparisons.store'), [
        'criterion_a_id' => 31, 'criterion_b_id' => 8, 'more_important' => 'a', 'intensity' => 3,
    ])->assertSessionHasNoErrors()->assertRedirect(route('ahp-comparisons.index'));
    $pair = AhpComparison::sole();
    expect($pair->criterion_a_id)->toBe(8);
    expect(abs((float) $pair->value - 1 / 3))->toBeLessThan(1e-11);
    $this->get(route('ahp-comparisons.edit', $pair))->assertOk()->assertSee('Ubah Perbandingan');
    $this->post(route('ahp-comparisons.store'), [
        'criterion_a_id' => 8, 'criterion_b_id' => 31, 'more_important' => 'a', 'intensity' => 2,
    ])->assertSessionHasErrors('criterion_b_id');
    $ahp = $this->post(route('ahp.calculate'))->assertOk()->viewData('ahp');
    expect($ahp['matrix'][8][31])->toBe(1 / 3);
    expect($ahp['ci'])->toBe(0);
    expect($ahp['cr'])->toBe(0);
});

it('validates comparison input and updates or deletes one pair', function () {
    $criteria = calculationCriteria(3);
    $pair = AhpComparison::first();
    $base = ['criterion_a_id' => $criteria[0]->id, 'criterion_b_id' => $criteria[2]->id, 'more_important' => 'a', 'intensity' => 9];
    foreach ([0, -1, 10, 1.5, 'abc'] as $bad) {
        $this->post(route('ahp-comparisons.store'), [...$base, 'intensity' => $bad])->assertSessionHasErrors('intensity');
    }
    $this->post(route('ahp-comparisons.store'), [...$base, 'criterion_b_id' => 999])->assertSessionHasErrors('criterion_b_id');
    $this->post(route('ahp-comparisons.store'), [...$base, 'criterion_b_id' => $base['criterion_a_id']])->assertSessionHasErrors('criterion_b_id');
    $this->put(route('ahp-comparisons.update', $pair), ['more_important' => 'equal', 'intensity' => 9])->assertSessionHasErrors('intensity');
    $this->put(route('ahp-comparisons.update', $pair), ['more_important' => 'b', 'intensity' => 3])
        ->assertSessionHasNoErrors()->assertRedirect(route('ahp-comparisons.index'));
    expect(abs((float) $pair->fresh()->value - 1 / 3))->toBeLessThan(1e-11);
    $this->get(route('ahp-comparisons.index'))->assertOk()->assertSee('3 dari 3 pasangan terisi');
    $this->delete(route('ahp-comparisons.destroy', $pair))->assertRedirect(route('ahp-comparisons.index'));
    $this->postJson(route('saw.calculate'))->assertUnprocessable();
    $this->assertDatabaseCount('ahp_comparisons', 2);
});

it('blocks SAW on incomplete or invalid comparisons scores and inconsistent AHP even with forged frontend results', function () {
    $criteria = calculationCriteria(3);
    $alternative = calculationMenu('Menu Uji', $criteria, [80, 20, 70]);
    $pair = AhpComparison::first();
    $original = $pair->only(['criterion_a_id', 'criterion_b_id', 'value']);
    $pair->delete();
    $this->get(route('ahp.index'))->assertOk()->assertSee('pasangan belum diisi');
    $this->postJson(route('saw.calculate'))->assertUnprocessable();
    $pair = AhpComparison::create($original);
    foreach ([0, 101] as $value) {
        $alternative->scores()->first()->update(['value' => $value]);
        $this->postJson(route('saw.calculate'))->assertUnprocessable()->assertSee('Menu Uji');
    }
    $alternative->scores()->first()->update(['value' => 80]);
    $score = $alternative->scores()->first();
    $attributes = $score->only(['criterion_id', 'value']);
    $score->delete();
    $this->postJson(route('saw.calculate'))->assertUnprocessable()->assertSee('Kriteria 1');
    $alternative->scores()->create($attributes);
    $pair->update(['value' => 0.33]);
    $this->postJson(route('saw.calculate'))->assertUnprocessable();
    $pair->update(['value' => 9]);
    AhpComparison::where('criterion_a_id', $criteria[1]->id)->update(['value' => 9]);
    AhpComparison::where('criterion_a_id', $criteria[0]->id)->where('criterion_b_id', $criteria[2]->id)->update(['value' => 1 / 9]);
    $this->post(route('ahp.calculate'))->assertOk()->assertSee('Belum konsisten');
    $this->postJson(route('saw.calculate'), ['cr' => 0, 'weights' => [1, 0, 0]])->assertUnprocessable();
});

it('normalizes benefit and cost over all alternatives and uses competition ranks for ties', function () {
    $criteria = calculationCriteria();
    $first = calculationMenu('Z Menu', $criteria, [100, 20]);
    $second = calculationMenu('A Menu', $criteria, [100, 20]);
    $third = calculationMenu('Menu Ketiga', $criteria, [50, 40]);
    for ($i = 0; $i < 12; $i++) {
        calculationMenu('Lain '.$i, $criteria, [10, 80]);
    }
    $saw = $this->post(route('saw.calculate'))->assertOk()->viewData('saw');
    expect($saw['ranking'])->toHaveCount(15);
    expect($saw['normal'][$first->id][$criteria[1]->id])->toBe(1.0);
    expect($saw['normal'][$third->id][$criteria[1]->id])->toBe(0.5);
    expect($saw['normal'][$third->id][$criteria[0]->id])->toBe(0.5);
    expect(array_column(array_slice($saw['ranking'], 0, 3), 'rank'))->toBe([1, 1, 3]);
    expect(array_column($saw['best'], 'id'))->toBe([$second->id, $first->id]);
    $outsideFirstPage = calculationMenu('Maximum Baru', $criteria, [100, 1]);
    $new = $this->post(route('saw.calculate'))->assertOk()->viewData('saw');
    expect($new['min'][$criteria[1]->id])->toBe(1.0);
    expect($new['best'][0]['id'])->toBe($outsideFirstPage->id);
});

it('groups numeric ties against the first score and sorts before rounding', function () {
    $controller = app(SawController::class);
    $method = new ReflectionMethod($controller, 'calculateSaw');
    $data = ['criteria' => [['id' => 7, 'attribute' => 'benefit']], 'alternatives' => [], 'alternative_scores' => []];
    foreach ([100, 99.99999992, 99.99999984, 99.99998] as $i => $value) {
        $data['alternatives'][] = ['id' => $i + 1, 'name' => ['Z', 'A', 'B', 'C'][$i]];
        $data['alternative_scores'][] = ['alternative_id' => $i + 1, 'criterion_id' => 7, 'value' => $value];
    }
    $result = $method->invoke($controller, $data, ['weights' => [7 => 1.0]]);
    expect(array_column($result['ranking'], 'rank'))->toBe([1, 1, 3, 4]);
    expect(array_column($result['ranking'], 'id'))->toBe([2, 1, 3, 4]);
});

it('recomputes SAW from current comparisons criteria and scores', function () {
    $criteria = calculationCriteria();
    $a = calculationMenu('Menu A', $criteria, [100, 100]);
    $b = calculationMenu('Menu B', $criteria, [50, 20]);
    $before = $this->post(route('saw.calculate'))->viewData('saw');
    $pair = AhpComparison::sole();
    $this->put(route('ahp-comparisons.update', $pair), ['more_important' => 'a', 'intensity' => 9])
        ->assertSessionHasNoErrors();
    $after = $this->post(route('saw.calculate'))->viewData('saw');
    expect($after['best'][0]['id'])->not->toBe($before['best'][0]['id']);
    $a->scores()->where('criterion_id', $criteria[0]->id)->update(['value' => 1]);
    $latest = $this->post(route('saw.calculate'))->viewData('saw');
    expect($latest['best'][0]['id'])->toBe($b->id);
    Criterion::create(['name' => 'Baru', 'attribute' => 'benefit']);
    $this->postJson(route('saw.calculate'))->assertUnprocessable();
    $this->get(route('ahp.index'))->assertOk()->assertSee('pasangan belum diisi');
});

it('rejects unsupported sizes and handles all supported RI sizes', function () {
    $this->postJson(route('ahp.calculate'))->assertUnprocessable();
    for ($n = 1; $n <= 11; $n++) {
        $new = Criterion::create(['name' => 'K '.$n, 'attribute' => 'benefit']);
        foreach (Criterion::where('id', '<', $new->id)->get() as $other) {
            AhpComparison::create(['criterion_a_id' => $other->id, 'criterion_b_id' => $new->id, 'value' => 1]);
        }
        $response = $this->postJson(route('ahp.calculate'));
        if ($n < 2 || $n > 10) {
            $response->assertUnprocessable();
        } else {
            $response->assertOk();
            expect(abs($response->viewData('ahp')['cr']))->toBeLessThan(1e-12);
        }
    }
});

it('requires authentication for every calculation endpoint', function () {
    auth()->logout();
    $this->get(route('ahp.index'))->assertRedirect(route('login'));
    $this->get(route('ahp-comparisons.index'))->assertRedirect(route('login'));
    $this->get(route('ahp-comparisons.create'))->assertRedirect(route('login'));
    $this->get(route('saw.index'))->assertRedirect(route('login'));
    foreach (['ahp.calculate', 'saw.calculate', 'ahp-comparisons.store'] as $route) {
        $this->postJson(route($route))->assertUnauthorized();
    }
});

it('shows AHP and SAW as separate pages and menu links', function () {
    $this->seed([CriteriaSeeder::class, AhpComparisonSeeder::class]);
    $this->get(route('ahp.index'))->assertOk()->assertSee('Pembobotan AHP')
        ->assertSee(route('ahp-comparisons.index'))->assertSee(route('saw.index'))
        ->assertDontSee('data-bs-toggle="tab"', false);
    $this->get(route('saw.index'))->assertOk()->assertSee('Pemeringkatan SAW')
        ->assertSee(route('saw.calculate'))->assertDontSee('id="comparison-form"', false)
        ->assertDontSee('data-bs-toggle="tab"', false);
});

it('shows no AHP result before the calculate button is submitted', function () {
    $this->seed([CriteriaSeeder::class, AhpComparisonSeeder::class]);

    $initial = $this->get(route('ahp.index'))->assertOk()
        ->assertSee('Pasangan terisi')
        ->assertViewHas('criterionCount', 5)
        ->assertDontSee('1. Matriks perbandingan');
    expect($initial->viewData('ahp'))->toBeNull();

    $calculated = $this->post(route('ahp.calculate'))->assertOk()
        ->assertSee('1. Matriks perbandingan')->assertSee('2. Normalisasi dan bobot')->assertSee('3. Uji konsistensi')
        ->assertSee('Uji konsistensi');
    expect($calculated->viewData('ahp'))->not->toBeNull();

    $this->get(route('ahp.index'))->assertOk()
        ->assertSee('Pasangan terisi')
        ->assertDontSee('1. Matriks perbandingan');
});

it('shows the three SAW calculation steps only after the button is submitted', function () {
    $criteria = calculationCriteria();
    calculationMenu('Menu Uji', $criteria, [80, 20]);

    $initial = $this->get(route('saw.index'))->assertOk()
        ->assertSee('Hitung Peringkat SAW')
        ->assertViewHas('alternativeCount', 1)
        ->assertDontSee('1. Data penilaian');
    expect($initial->viewData('saw'))->toBeNull();
    $initial->assertViewMissing('ahp');

    $calculated = $this->post(route('saw.calculate'))->assertOk()
        ->assertSee('1. Data penilaian')
        ->assertSee('2. Normalisasi dan kontribusi')
        ->assertSee('3. Hasil pemeringkatan')
        ->assertSee('Menu Uji');
    expect($calculated->viewData('saw'))->not->toBeNull();

    $this->get(route('saw.index'))->assertOk()
        ->assertDontSee('1. Data penilaian');
});

it('checks SAW requirements after the calculate button is submitted', function () {
    $this->get(route('saw.index'))->assertOk()
        ->assertSee('Hitung Peringkat SAW')
        ->assertDontSee('Perhitungan belum dapat dijalankan');

    $this->post(route('saw.calculate'))->assertRedirect(route('saw.index'))
        ->assertSessionHasErrors('calculation');
    $this->get(route('saw.index'))->assertOk()
        ->assertSee('Tambahkan minimal satu alternatif')
        ->assertDontSee('1. Data penilaian');
});
