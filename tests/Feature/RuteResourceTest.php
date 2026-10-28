<?php

use Illuminate\Support\Facades\Route;

test('route resource tanpa method controller tidak terdaftar (cegah 500)', function () {
    expect(Route::has('pengajar.kuis.show'))->toBeFalse();
    expect(Route::has('pengajar.kelas.pertemuan.index'))->toBeFalse();
});

test('route turunan resource yang masih dipakai tetap terdaftar', function () {
    expect(Route::has('pengajar.kuis.index'))->toBeTrue();
    expect(Route::has('pengajar.kuis.create'))->toBeTrue();
    expect(Route::has('pengajar.kuis.edit'))->toBeTrue();
    expect(Route::has('pengajar.kelas.pertemuan.create'))->toBeTrue();
    expect(Route::has('pengajar.pertemuan.show'))->toBeTrue();
    expect(Route::has('pengajar.pertemuan.edit'))->toBeTrue();
});

test('URL resource yang dibuang tidak lagi 500 (method tak didukung -> 405)', function () {
    $this->get('/pengajar/kuis/1')->assertStatus(405);
    $this->get('/pengajar/kelas/1/pertemuan')->assertStatus(405);
});
