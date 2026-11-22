<?php

use App\Models\Criterion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

it('requires authentication for criteria', function () {
    $this->get(route('criteria.index'))->assertRedirect(route('login'));
    $this->post(route('criteria.store'), [])->assertRedirect(route('login'));
});

it('creates, displays, updates and deletes a criterion', function () {
    $this->actingAs(User::create(['name' => 'Admin Test', 'email' => 'admin@example.test', 'password' => 'password']));
    $data = ['name' => 'Rasa', 'attribute' => 'benefit', 'unit' => 'skor 1-100', 'description' => 'Kualitas rasa'];
    $this->get(route('criteria.create'))->assertOk()->assertSee('Nama Kriteria');
    $this->post(route('criteria.store'), $data)->assertRedirect(route('criteria.index'))->assertSessionHasNoErrors();
    $criterion = Criterion::sole();
    $this->get(route('criteria.index'))->assertOk()->assertSee('Rasa')->assertSee(route('criteria.destroy', $criterion));
    $this->get(route('criteria.show', $criterion))->assertOk()->assertSee('Kualitas rasa');
    $this->get(route('criteria.edit', $criterion))->assertOk()->assertSee('Rasa');
    $this->put(route('criteria.update', $criterion), [...$data, 'name' => 'Harga', 'attribute' => 'cost'])
        ->assertRedirect(route('criteria.index'))->assertSessionHasNoErrors();
    $this->assertDatabaseHas('criteria', ['id' => $criterion->id, 'name' => 'Harga', 'attribute' => 'cost']);
    $this->delete(route('criteria.destroy', $criterion))->assertRedirect(route('criteria.index'))->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('criteria', ['id' => $criterion->id]);
});

it('validates criterion fields', function () {
    $this->actingAs(User::create(['name' => 'Admin Test', 'email' => 'admin@example.test', 'password' => 'password']));
    $data = ['name' => 'Rasa', 'attribute' => 'benefit'];
    Criterion::create($data);
    $other = Criterion::create([...$data, 'name' => 'Harga']);
    $this->post(route('criteria.store'), [])->assertSessionHasErrors(['name', 'attribute']);
    $this->post(route('criteria.store'), [...$data, 'attribute' => 'invalid'])->assertSessionHasErrors(['attribute']);
    $this->put(route('criteria.update', $other), [...$data, 'attribute' => 'invalid'])->assertSessionHasErrors('attribute');
    $this->post(route('criteria.store'), [...$data, 'name' => str_repeat('a', 101), 'unit' => str_repeat('a', 51)])
        ->assertSessionHasErrors(['name', 'unit']);
    expect(Criterion::count())->toBe(2);
});

it('searches by name and keeps the search during pagination', function () {
    $this->actingAs(User::create(['name' => 'Admin Test', 'email' => 'admin@example.test', 'password' => 'password']));
    for ($i = 1; $i <= 12; $i++) {
        Criterion::create(['name' => "Kualitas {$i}", 'attribute' => 'benefit']);
    }
    Criterion::create(['name' => 'Harga', 'attribute' => 'cost']);
    $this->get(route('criteria.index', ['search' => 'Kualitas']))->assertOk()
        ->assertViewHas('total', 12)->assertSee('search=Kualitas')->assertDontSee('Harga');
    $this->get(route('criteria.index', ['search' => 'Harga']))->assertOk()->assertViewHas('total', 1)->assertSee('Harga');
    $this->get(route('criteria.index', ['search' => 'missing']))->assertOk()->assertSee('Tidak ada data ditemukan.');
});

it('prevents deleting criteria used by scores or either side of AHP comparisons', function () {
    $this->actingAs(User::create(['name' => 'Admin Test', 'email' => 'admin@example.test', 'password' => 'password']));
    $a = Criterion::create(['name' => 'Rasa', 'attribute' => 'benefit']);
    $b = Criterion::create(['name' => 'Harga', 'attribute' => 'cost']);
    $alternativeId = DB::table('alternatives')->insertGetId(['name' => 'Menu']);
    DB::table('alternative_scores')->insert(['alternative_id' => $alternativeId, 'criterion_id' => $a->id, 'value' => 80]);
    $this->delete(route('criteria.destroy', $a))->assertSessionHasErrors('delete');
    DB::table('alternative_scores')->delete();
    DB::table('ahp_comparisons')->insert(['criterion_a_id' => $a->id, 'criterion_b_id' => $b->id, 'value' => 3]);
    foreach ([$a, $b] as $criterion) {
        $this->delete(route('criteria.destroy', $criterion))->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('criteria', ['id' => $criterion->id]);
    }
    expect(DB::table('ahp_comparisons')->count())->toBe(1);
});
