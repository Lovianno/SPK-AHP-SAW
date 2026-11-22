<?php

use App\Models\Alternative;
use App\Models\Criterion;
use App\Models\User;
use App\Services\AlternativeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

function alternativeAdmin(): User
{
    return User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password']);
}

it('protects all alternative routes with authentication', function () {
    $alternative = Alternative::create(['name' => 'Menu']);
    foreach (['index', 'create', 'show', 'edit'] as $action) {
        $this->get(route('alternatives.'.$action, in_array($action, ['show', 'edit']) ? $alternative : []))
            ->assertRedirect(route('login'));
    }
    $this->post(route('alternatives.store'), [])->assertRedirect(route('login'));
    $this->put(route('alternatives.update', $alternative), [])->assertRedirect(route('login'));
    $this->delete(route('alternatives.destroy', $alternative))->assertRedirect(route('login'));
});

it('creates and updates an alternative and its scores in one form then cascades deletion', function () {
    $this->actingAs(alternativeAdmin());
    $taste = Criterion::create(['name' => 'Rasa', 'attribute' => 'benefit']);
    $price = Criterion::create(['name' => 'Harga', 'attribute' => 'cost']);
    $this->get(route('alternatives.create'))->assertOk()->assertSee('Rasa')->assertSee('Harga');
    $data = ['name' => 'Menu Baru', 'description' => 'Menu uji', 'scores' => [$taste->id => 100, $price->id => 1]];
    $this->post(route('alternatives.store'), $data)->assertRedirect(route('alternatives.index'))->assertSessionHasNoErrors();
    $alternative = Alternative::sole();
    $this->assertDatabaseHas('alternative_scores', ['alternative_id' => $alternative->id, 'criterion_id' => $taste->id, 'value' => 100]);
    $this->get(route('alternatives.index'))->assertOk()->assertSee('Menu Baru');
    $this->get(route('alternatives.show', $alternative))->assertOk()->assertSee('Rasa')->assertSee('Harga');
    $this->get(route('alternatives.edit', $alternative))->assertOk()->assertSee('value="100.0000"', false);
    $extra = Criterion::create(['name' => 'Popularitas', 'attribute' => 'benefit']);
    $this->get(route('alternatives.show', $alternative))->assertOk()->assertSee('Belum diisi');
    $this->get(route('alternatives.edit', $alternative))->assertOk()->assertSee('Popularitas');
    $data['name'] = 'Menu Diubah';
    $data['scores'] = [$taste->id => 85.1234, $price->id => 20, $extra->id => 90];
    foreach ([1, 2] as $pass) {
        $this->put(route('alternatives.update', $alternative), $data)->assertSessionHasNoErrors()->assertRedirect(route('alternatives.index'));
    }
    $this->assertDatabaseCount('alternative_scores', 3);
    $this->assertDatabaseHas('alternatives', ['id' => $alternative->id, 'name' => 'Menu Diubah']);
    $this->assertDatabaseHas('alternative_scores', ['alternative_id' => $alternative->id, 'criterion_id' => $taste->id, 'value' => 85.1234]);
    $this->delete(route('alternatives.destroy', $alternative))->assertSessionHasNoErrors()->assertRedirect(route('alternatives.index'));
    $this->assertDatabaseCount('alternatives', 0);
    $this->assertDatabaseCount('alternative_scores', 0);
    $this->assertDatabaseCount('criteria', 3);
});

it('rejects missing, unknown and invalid scores without changing data', function () {
    $this->actingAs(alternativeAdmin());
    $criterion = Criterion::create(['name' => 'Rasa', 'attribute' => 'benefit']);
    foreach ([0, 101, 'abc', '', 1.12345] as $value) {
        $this->from(route('alternatives.create'))->post(route('alternatives.store'), ['name' => 'Menu', 'scores' => [$criterion->id => $value]])
            ->assertSessionHasErrors('scores.'.$criterion->id)->assertSessionHasInput('name', 'Menu');
    }
    $this->post(route('alternatives.store'), ['name' => 'Menu'])->assertSessionHasErrors('scores');
    $this->post(route('alternatives.store'), ['name' => 'Menu', 'scores' => [$criterion->id => 80, 999 => 50]])->assertSessionHasErrors('scores');
    $this->post(route('alternatives.store'), ['name' => 'Menu', 'scores' => [999 => 50]])->assertSessionHasErrors('scores.'.$criterion->id);
    $this->post(route('alternatives.store'), ['name' => str_repeat('a', 151), 'scores' => [$criterion->id => 80]])->assertSessionHasErrors('name');
    $this->assertDatabaseCount('alternatives', 0);
    $alternative = app(AlternativeService::class)->create(['name' => 'Original', 'scores' => [$criterion->id => 80]]);
    $this->put(route('alternatives.update', $alternative), ['name' => 'Changed', 'scores' => [$criterion->id => 101]])->assertSessionHasErrors('scores.'.$criterion->id);
    $this->assertDatabaseHas('alternatives', ['name' => 'Original']);
    $this->assertDatabaseHas('alternative_scores', ['value' => 80]);
});

it('rolls back alternative and score changes if saving scores fails', function () {
    $criterion = Criterion::create(['name' => 'Rasa', 'attribute' => 'benefit']);
    $service = app(AlternativeService::class);
    expect(fn () => $service->create(['name' => 'Failed', 'scores' => [$criterion->id => 80, 999 => 20]]))
        ->toThrow(\Illuminate\Database\QueryException::class);
    $this->assertDatabaseCount('alternatives', 0);
    $this->assertDatabaseCount('alternative_scores', 0);
    $alternative = $service->create(['name' => 'Original', 'scores' => [$criterion->id => 80]]);
    expect(fn () => $service->update($alternative, ['name' => 'Failed update', 'scores' => [$criterion->id => 90, 999 => 20]]))
        ->toThrow(\Illuminate\Database\QueryException::class);
    $this->assertDatabaseHas('alternatives', ['id' => $alternative->id, 'name' => 'Original']);
    $this->assertDatabaseHas('alternative_scores', ['value' => 80]);
});

it('handles an empty criteria list and rejects scores for nonexistent criteria', function () {
    $this->actingAs(alternativeAdmin());
    $this->get(route('alternatives.create'))->assertOk()->assertSee('Belum ada kriteria');
    $this->post(route('alternatives.store'), ['name' => 'Menu'])->assertSessionHasNoErrors();
    $this->post(route('alternatives.store'), ['name' => 'Invalid', 'scores' => [999 => 80]])->assertSessionHasErrors('scores');
    $this->get(route('alternatives.show', Alternative::sole()))->assertOk()->assertSee('Belum ada kriteria');
});

it('searches and paginates alternatives and handles empty results', function () {
    $this->actingAs(alternativeAdmin());
    foreach (range(1, 12) as $i) Alternative::create(['name' => 'Menu '.$i]);
    Alternative::create(['name' => 'Lainnya']);
    $this->get(route('alternatives.index', ['search' => 'Menu']))->assertOk()->assertViewHas('total', 12)->assertSee('search=Menu')->assertDontSee('Lainnya');
    $this->get(route('alternatives.index', ['search' => 'missing']))->assertOk()->assertSee('Tidak ada data ditemukan.');
    $this->get(route('alternatives.show', 999))->assertNotFound();
});
