<?php

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\PengajarKompetensi;
use App\Models\User;

beforeEach(function () {
    $this->operator = User::factory()->operator()->create([
        'kategori_mapel' => 'Matematika',
    ]);
});

test('operator can browse teacher materials from subject to material detail', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode' => 'matematika',
        'nama' => 'Matematika',
        'kategori' => 'Matematika',
        'aktif' => true,
    ]);
    $pengajar = User::factory()->pengajar()->create([
        'name' => 'Pengajar Matematika',
    ]);
    $kelas = Kelas::factory()->create([
        'nama_kelas' => 'Kelas Matematika',
        'kategori' => 'Matematika',
        'mata_pelajaran_id' => $mataPelajaran->id,
        'guru_id' => $pengajar->id,
    ]);
    $materi = Materi::factory()->create([
        'judul' => 'Persamaan Linear',
        'kelas_id' => $kelas->id,
        'guru_id' => $pengajar->id,
        'file_path' => 'materi/persamaan-linear.jpg',
        'file_type' => 'jpg',
    ]);

    $this->actingAs($this->operator)
        ->get(route('operator.mata-pelajaran.index'))
        ->assertOk()
        ->assertSee('min-h-11', false)
        ->assertSee(route('operator.mata-pelajaran.show', $mataPelajaran));

    $this->get(route('operator.mata-pelajaran.show', $mataPelajaran))
        ->assertOk()
        ->assertSee('Daftar Mata Pelajaran')
        ->assertSee('Daftar kelas pada mata pelajaran ini')
        ->assertSee('Pengajar')
        ->assertSee('Materi')
        ->assertSee($kelas->nama_kelas)
        ->assertSee('min-h-11', false)
        ->assertSee('min-w-11', false)
        ->assertSee(route('operator.kelas.materi', $kelas));

    $this->get(route('operator.kelas.materi', $kelas))
        ->assertOk()
        ->assertSee($materi->judul)
        ->assertSee('min-h-11', false)
        ->assertSee(route('operator.materi.show', $materi));

    $this->get(route('operator.materi.show', $materi))
        ->assertOk()
        ->assertSee($materi->judul)
        ->assertSee($pengajar->name)
        ->assertSee('min-h-11', false)
        ->assertSee('min-w-11', false)
        ->assertSee('aspect-[4/3]', false)
        ->assertSee('object-contain', false);
});

test('operator material pages follow the selected locale', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode' => 'matematika',
        'nama' => 'Matematika',
        'kategori' => 'Matematika',
        'aktif' => true,
    ]);
    $kelas = Kelas::factory()->create([
        'nama_kelas' => 'Kelas Matematika',
        'kategori' => 'Matematika',
        'mata_pelajaran_id' => $mataPelajaran->id,
    ]);
    $materi = Materi::factory()->create([
        'judul' => 'Persamaan Linear',
        'deskripsi' => 'Uraian materi',
        'kelas_id' => $kelas->id,
        'file_path' => 'materi/persamaan-linear.pdf',
        'file_type' => 'pdf',
    ]);

    $this->actingAs($this->operator)
        ->from(route('operator.kelas.materi', $kelas))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('operator.kelas.materi', $kelas));

    $this->get(route('operator.kelas.materi', $kelas))
        ->assertOk()
        ->assertSee('Materials - Kelas Matematika')
        ->assertSee('Materials uploaded by teachers for this class')
        ->assertSee('Meeting')
        ->assertSee('Teacher');

    $this->get(route('operator.materi.show', $materi))
        ->assertOk()
        ->assertSee('Materials Kelas Matematika')
        ->assertSee('Class')
        ->assertSee('Subject')
        ->assertSee('View')
        ->assertSee('Download')
        ->assertSee('Description');
});

test('operator subject detail follows the selected locale in its data and empty states', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode' => 'matematika',
        'nama' => 'Matematika',
        'kategori' => 'Matematika',
        'aktif' => true,
    ]);
    $kelas = Kelas::factory()->create([
        'nama_kelas' => 'Kelas Matematika',
        'kategori' => 'Matematika',
        'mata_pelajaran_id' => $mataPelajaran->id,
    ]);
    $mataPelajaranKosong = MataPelajaran::create([
        'kode' => 'kimia',
        'nama' => 'Kimia',
        'kategori' => 'Matematika',
        'aktif' => true,
    ]);

    $this->actingAs($this->operator)
        ->from(route('operator.mata-pelajaran.show', $mataPelajaran))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('operator.mata-pelajaran.show', $mataPelajaran));

    $this->get(route('operator.mata-pelajaran.show', $mataPelajaran))
        ->assertOk()
        ->assertSee('Operator - Matematika')
        ->assertSee('Subject List')
        ->assertSee('Classes in this subject')
        ->assertSee('Class')
        ->assertSee('Teacher')
        ->assertSee('Materials')
        ->assertSee($kelas->nama_kelas);

    $this->get(route('operator.mata-pelajaran.show', $mataPelajaranKosong))
        ->assertOk()
        ->assertSee('No classes are linked to this subject yet.');
});

test('operator subject create and edit forms follow the selected locale', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode' => 'matematika',
        'nama' => 'Matematika',
        'kategori' => 'Matematika',
        'aktif' => true,
    ]);

    $this->actingAs($this->operator)
        ->get(route('operator.mata-pelajaran.create'))
        ->assertOk()
        ->assertSee('Tambah Mata Pelajaran')
        ->assertSee('Kode Mata Pelajaran')
        ->assertSee('Simpan');

    $this->actingAs($this->operator)
        ->from(route('operator.mata-pelajaran.create'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('operator.mata-pelajaran.create'));

    $this->get(route('operator.mata-pelajaran.create'))
        ->assertOk()
        ->assertSee('Add Subject')
        ->assertSee('Subject code')
        ->assertSee('Subject name')
        ->assertSee('For category Matematika')
        ->assertSee('Save');

    $this->get(route('operator.mata-pelajaran.edit', $mataPelajaran))
        ->assertOk()
        ->assertSee('Edit Subject')
        ->assertSee('Example: MTH101');
});

test('operator cannot browse materials outside their subject category', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode' => 'bahasa',
        'nama' => 'Bahasa Indonesia',
        'kategori' => 'Bahasa Indonesia',
        'aktif' => true,
    ]);
    $kelas = Kelas::factory()->create([
        'kategori' => 'Bahasa Indonesia',
        'mata_pelajaran_id' => $mataPelajaran->id,
    ]);

    $this->actingAs($this->operator)
        ->get(route('operator.mata-pelajaran.show', $mataPelajaran))
        ->assertForbidden();

    $this->get(route('operator.kelas.materi', $kelas))
        ->assertForbidden();
});

test('operator cannot delete a subject used by a class', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode' => 'matematika',
        'nama' => 'Matematika',
        'kategori' => 'Matematika',
    ]);
    $kelas = Kelas::factory()->create(['mata_pelajaran_id' => $mataPelajaran->id]);

    $this->actingAs($this->operator)
        ->from(route('operator.mata-pelajaran.index'))
        ->delete(route('operator.mata-pelajaran.destroy', $mataPelajaran))
        ->assertRedirect(route('operator.mata-pelajaran.index'))
        ->assertSessionHas('error');

    expect($mataPelajaran->fresh())->not->toBeNull()
        ->and($kelas->fresh()->mata_pelajaran_id)->toBe($mataPelajaran->id);
});

test('operator cannot delete a subject used by a teacher competency', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode' => 'fisika',
        'nama' => 'Fisika',
        'kategori' => 'Matematika',
    ]);
    $pengajar = User::factory()->pengajar()->create();
    $kompetensi = PengajarKompetensi::create([
        'user_id' => $pengajar->id,
        'mata_pelajaran_id' => $mataPelajaran->id,
    ]);

    $this->actingAs($this->operator)
        ->from(route('operator.mata-pelajaran.index'))
        ->delete(route('operator.mata-pelajaran.destroy', $mataPelajaran))
        ->assertRedirect(route('operator.mata-pelajaran.index'))
        ->assertSessionHas('error');

    expect($mataPelajaran->fresh())->not->toBeNull()
        ->and($kompetensi->fresh()->mata_pelajaran_id)->toBe($mataPelajaran->id);
});

test('operator can delete an unused subject in their category', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode' => 'kimia',
        'nama' => 'Kimia',
        'kategori' => 'Matematika',
    ]);

    $this->actingAs($this->operator)
        ->delete(route('operator.mata-pelajaran.destroy', $mataPelajaran))
        ->assertRedirect(route('operator.mata-pelajaran.index'))
        ->assertSessionHas('success');

    expect(MataPelajaran::find($mataPelajaran->id))->toBeNull();
});
