<?php

use App\Models\AhpComparison;
use App\Models\Alternative;
use App\Models\Criterion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows actual SPK data counts and links to the calculation pages', function () {
    $this->withoutVite();
    $this->actingAs(User::create(['name' => 'Admin', 'email' => 'dashboard@example.test', 'password' => 'password']));

    $first = Criterion::create(['name' => 'Rasa', 'attribute' => 'benefit']);
    $second = Criterion::create(['name' => 'Harga', 'attribute' => 'cost']);
    AhpComparison::create(['criterion_a_id' => $first->id, 'criterion_b_id' => $second->id, 'value' => 1]);
    $alternative = Alternative::create(['name' => 'Menu Uji']);
    $alternative->scores()->create(['criterion_id' => $first->id, 'value' => 80]);
    $alternative->scores()->create(['criterion_id' => $second->id, 'value' => 20]);

    $this->get(route('dashboard'))->assertOk()
        ->assertViewHas('criteriaCount', 2)
        ->assertViewHas('alternativesCount', 1)
        ->assertViewHas('comparisonsCount', 1)
        ->assertViewHas('expectedComparisons', 1)
        ->assertViewHas('scoresCount', 2)
        ->assertViewHas('expectedScores', 2)
        ->assertSee(route('ahp.index'))
        ->assertSee(route('saw.index'))
        ->assertDontSee('Ringkasan Penjualan');
});
