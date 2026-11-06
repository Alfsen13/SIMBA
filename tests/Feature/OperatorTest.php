<?php

use App\Models\KategoriMataPelajaran;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

beforeEach(function () {
    $this->operator = User::factory()->operator()->create();
});

test('operator dashboard returns 200', function () {
    $this->actingAs($this->operator)
        ->get(route('operator.dashboard'))
        ->assertOk();
});

test('operator import page returns 200', function () {
    $this->actingAs($this->operator)
        ->get(route('operator.import'))
        ->assertOk();
});

test('operator can import users from csv', function () {
    $csvContent = "name,email,role,nip,nisn,password\nJohn Doe,john@test.com,pelajar,,12345678,secret123\nJane Doe,jane@test.com,pelajar,,87654321,secret456";

    $this->actingAs($this->operator)
        ->post(route('operator.import.users'), [
            'file' => createCsvUpload($csvContent),
        ])
        ->assertRedirect()
        ->assertSessionHas('success', '2 pengguna berhasil diimport.');

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@test.com',
        'role' => 'pelajar',
        'nisn' => '12345678',
    ]);

    expect(Hash::check('secret123', User::where('email', 'john@test.com')->value('password')))->toBeTrue();
});

test('operator can import users from xlsx', function () {
    $file = createXlsxUpload([
        ['name' => 'Alice', 'email' => 'alice@test.com', 'role' => 'pelajar', 'nisn' => '23456789', 'password' => 'secret123'],
        ['name' => 'Bob', 'email' => 'bob@test.com', 'role' => 'pelajar', 'nisn' => '34567890', 'password' => 'secret456'],
    ]);

    $this->actingAs($this->operator)
        ->post(route('operator.import.users'), [
            'file' => $file,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');
});

test('operator import rejects shared default passwords and privileged roles', function (array $row) {
    $csvContent = "name,email,role,nip,nisn,password\n".implode(',', $row);

    $this->actingAs($this->operator)
        ->post(route('operator.import.users'), [
            'file' => createCsvUpload($csvContent),
        ])
        ->assertRedirect()
        ->assertSessionHas('import_errors');

    $this->assertDatabaseMissing('users', ['email' => $row[1]]);
})->with([
    'password kosong' => [['User Kosong', 'kosong@test.com', 'pelajar', '', '45678901', '']],
    'password pendek' => [['User Pendek', 'pendek@test.com', 'pelajar', '', '56789012', 'short']],
    'role operator' => [['Operator Impor', 'operator@test.com', 'operator', '', '', 'secret123']],
    'role pengelola' => [['Pengelola Impor', 'manager@test.com', 'pengelola_operator', '', '', 'secret123']],
]);

test('operator import menolak urutan kolom csv lama yang ambigu', function () {
    $csvContent = "name,email,password\nUser Lama,lama@test.com,secret123";

    $this->actingAs($this->operator)
        ->post(route('operator.import.users'), [
            'file' => createCsvUpload($csvContent),
        ])
        ->assertRedirect()
        ->assertSessionHas('import_errors');

    $this->assertDatabaseMissing('users', ['email' => 'lama@test.com']);
});

test('template impor menyediakan kolom pengguna sesuai urutan importer', function () {
    $this->actingAs($this->operator)
        ->get(route('operator.import.template', ['jenis' => 'pengguna']))
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename="template-import-pengguna.csv"')
        ->assertSeeText('name,email,role,nip,nisn,password');
});

test('template impor kelas tetap menyediakan header dan contoh kelas', function () {
    $this->actingAs($this->operator)
        ->get(route('operator.import.template', ['jenis' => 'kelas']))
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename="template-import-kelas.csv"')
        ->assertSeeText('nama_kelas,mata_pelajaran,kategori')
        ->assertSeeText('Matematika Kelas 4 SD');
});

test('operator import kelas menolak header template yang hilang atau salah eja', function () {
    $csvContent = "nama_kelas,mata_pelajaran,kategori,jalur,jenjang,tingkat,kapasitas,periode,status,aturan_kelayakan,deskripsi\n"
        ."Kelas Tidak Lengkap,Matematika,Matematika,Formal,SD,Kelas 4,30,2026/2027 Ganjil,dibuka,sesuai_profil,Deskripsi kelas\n";

    $this->actingAs($this->operator)
        ->post(route('operator.import.kelas'), [
            'file' => createCsvUpload(str_replace('tingkat', 'tinggkat', $csvContent)),
        ])
        ->assertRedirect()
        ->assertSessionHas('success', '0 kelas berhasil diimport.')
        ->assertSessionHas('import_errors');

    $this->assertDatabaseMissing('kelas', ['nama_kelas' => 'Kelas Tidak Lengkap']);
});

test('operator import kelas menolak metadata wajib yang kosong dan menerima template ber-BOM yang lengkap', function () {
    $headers = 'nama_kelas,mata_pelajaran,kategori,jalur,jenjang,tingkat,kapasitas,periode,status,aturan_kelayakan,deskripsi';
    $row = 'Kelas Lengkap,Matematika,Matematika & Ilmu Alam,Formal,SD,Kelas 4,30,2026/2027 Ganjil,dibuka,sesuai_profil,Pembelajaran matematika.';

    $this->actingAs($this->operator)
        ->post(route('operator.import.kelas'), [
            'file' => createCsvUpload($headers."\n".str_replace(',SD,', ',,', $row)."\n"),
        ])
        ->assertRedirect()
        ->assertSessionHas('success', '0 kelas berhasil diimport.')
        ->assertSessionHas('import_errors');

    $this->assertDatabaseMissing('kelas', ['nama_kelas' => 'Kelas Lengkap']);

    $jalurId = DB::table('jalur_pendidikan')->insertGetId([
        'kode' => 'formal',
        'nama' => 'Formal',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $jenjangId = DB::table('jenjang_pendidikan')->insertGetId([
        'jalur_id' => $jalurId,
        'kode' => 'sd',
        'nama' => 'SD',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('tingkat_pendidikan')->insert([
        'jenjang_id' => $jenjangId,
        'kode' => 'kelas_4',
        'nama' => 'Kelas 4',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mata_pelajaran')->insert([
        'kode' => 'matematika',
        'nama' => 'Matematika',
        'kategori' => 'Matematika & Ilmu Alam',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    KategoriMataPelajaran::create(['nama' => 'Matematika & Ilmu Alam']);

    $this->actingAs($this->operator)
        ->post(route('operator.import.kelas'), [
            'file' => createCsvUpload("\u{FEFF}".$headers."\n".$row."\n"),
        ])
        ->assertRedirect()
        ->assertSessionHas('success', '1 kelas berhasil diimport.');

    $this->assertDatabaseHas('kelas', [
        'nama_kelas' => 'Kelas Lengkap',
        'jalur_id' => $jalurId,
        'jenjang_id' => $jenjangId,
        'tingkat_id' => DB::table('tingkat_pendidikan')->where('jenjang_id', $jenjangId)->value('id'),
        'mata_pelajaran_id' => DB::table('mata_pelajaran')->where('kode', 'matematika')->value('id'),
    ]);
});

test('operator import kelas menolak kategori yang tidak sesuai dengan mapel atau katalog', function () {
    $jalurId = DB::table('jalur_pendidikan')->insertGetId([
        'kode' => 'formal',
        'nama' => 'Formal',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $jenjangId = DB::table('jenjang_pendidikan')->insertGetId([
        'jalur_id' => $jalurId,
        'kode' => 'sd',
        'nama' => 'SD',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('tingkat_pendidikan')->insert([
        'jenjang_id' => $jenjangId,
        'kode' => 'kelas_4',
        'nama' => 'Kelas 4',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('mata_pelajaran')->insert([
        'kode' => 'matematika',
        'nama' => 'Matematika',
        'kategori' => 'Matematika & Ilmu Alam',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    KategoriMataPelajaran::create(['nama' => 'Matematika & Ilmu Alam']);
    KategoriMataPelajaran::create(['nama' => 'Bahasa & Komunikasi']);

    $headers = 'nama_kelas,mata_pelajaran,kategori,jalur,jenjang,tingkat,kapasitas,periode,status,aturan_kelayakan,deskripsi';
    $rows = [
        'Kelas Kategori Salah,Matematika,Bahasa & Komunikasi,Formal,SD,Kelas 4,30,2026/2027 Ganjil,dibuka,sesuai_profil,Deskripsi',
        'Kelas Kategori Tidak Terdaftar,Matematika,Kategori Karangan,Formal,SD,Kelas 4,30,2026/2027 Ganjil,dibuka,sesuai_profil,Deskripsi',
    ];

    $this->actingAs($this->operator)
        ->post(route('operator.import.kelas'), [
            'file' => createCsvUpload($headers."\n".implode("\n", $rows)."\n"),
        ])
        ->assertRedirect()
        ->assertSessionHas('success', '0 kelas berhasil diimport.')
        ->assertSessionHas('import_errors');

    $this->assertDatabaseMissing('kelas', ['nama_kelas' => 'Kelas Kategori Salah']);
    $this->assertDatabaseMissing('kelas', ['nama_kelas' => 'Kelas Kategori Tidak Terdaftar']);
});

test('pengelola operator tidak dapat mengubah atau menghapus akun non-operator', function (string $role) {
    $manager = User::factory()->create(['role' => 'pengelola_operator', 'status' => 'verified']);
    $target = User::factory()->verified()->create(['role' => $role]);

    $this->actingAs($manager)
        ->get(route('pengelola-operator.operator.edit', $target))
        ->assertForbidden();

    $this->put(route('pengelola-operator.operator.update', $target), [
        'name' => 'Nama Diubah',
        'email' => 'diubah@example.test',
        'kategori_mapel' => 'Matematika',
    ])->assertForbidden();

    $this->delete(route('pengelola-operator.operator.destroy', $target))
        ->assertForbidden();

    expect($target->fresh())->not->toBeNull()
        ->and($target->fresh()->name)->not->toBe('Nama Diubah')
        ->and($target->fresh()->email)->not->toBe('diubah@example.test');
})->with([
    'pelajar' => 'pelajar',
    'pengajar' => 'pengajar',
]);

test('pengelola operator tetap dapat mengelola akun operator', function () {
    $manager = User::factory()->create(['role' => 'pengelola_operator', 'status' => 'verified']);
    $target = User::factory()->operator()->create();
    MataPelajaran::create([
        'kode' => 'matematika',
        'nama' => 'Matematika',
        'kategori' => 'Matematika',
    ]);
    KategoriMataPelajaran::create(['nama' => 'Matematika']);

    $this->actingAs($manager)
        ->get(route('pengelola-operator.operator.edit', $target))
        ->assertOk();

    $this->put(route('pengelola-operator.operator.update', $target), [
        'name' => 'Operator Diperbarui',
        'email' => 'operator-baru@example.test',
        'kategori_mapel' => 'Matematika',
    ])->assertRedirect(route('pengelola-operator.operator.index'));

    expect($target->fresh()->name)->toBe('Operator Diperbarui')
        ->and($target->fresh()->email)->toBe('operator-baru@example.test');

    $this->delete(route('pengelola-operator.operator.destroy', $target))
        ->assertRedirect(route('pengelola-operator.operator.index'));

    expect(User::find($target->id))->toBeNull();
});

test('pengelola operator tidak dapat menetapkan kategori yang tidak terdaftar', function () {
    $manager = User::factory()->create(['role' => 'pengelola_operator', 'status' => 'verified']);
    $target = User::factory()->operator()->create(['kategori_mapel' => 'Matematika']);

    $this->actingAs($manager)
        ->from(route('pengelola-operator.operator.create'))
        ->post(route('pengelola-operator.operator.store'), [
            'name' => 'Operator Baru',
            'email' => 'operator-baru@example.test',
            'password' => 'password123',
            'kategori_mapel' => 'Kategori Tidak Ada',
        ])
        ->assertRedirect(route('pengelola-operator.operator.create'))
        ->assertSessionHasErrors('kategori_mapel');

    $this->assertDatabaseMissing('users', ['email' => 'operator-baru@example.test']);

    $this->put(route('pengelola-operator.operator.update', $target), [
        'name' => $target->name,
        'email' => $target->email,
        'kategori_mapel' => 'Kategori Tidak Ada',
    ])->assertSessionHasErrors('kategori_mapel');

    expect($target->fresh()->kategori_mapel)->toBe('Matematika');
});

test('operator import requires valid file', function () {
    $this->actingAs($this->operator)
        ->post(route('operator.import.users'), [])
        ->assertSessionHasErrors('file');
});

test('non-operator cannot access operator pages', function () {
    $guru = User::factory()->pengajar()->verified()->create();

    $this->actingAs($guru)
        ->get(route('operator.dashboard'))
        ->assertRedirect(route('pengajar.dashboard'));
});

function createCsvUpload(string $content): UploadedFile
{
    $path = sys_get_temp_dir().'/test-import-'.uniqid().'.csv';
    file_put_contents($path, $content);

    return new UploadedFile(
        $path,
        'users.csv',
        'text/csv',
        null,
        true
    );
}

function createXlsxUpload(array $rows): UploadedFile
{
    $writer = new Writer;
    $path = sys_get_temp_dir().'/test-import-'.uniqid().'.xlsx';
    $writer->openToFile($path);

    $columns = ['name', 'email', 'role', 'nip', 'nisn', 'password'];
    $writer->addRow(new Row(array_map(fn ($column) => Cell::fromValue($column), $columns)));

    foreach ($rows as $row) {
        $cells = array_map(fn ($column) => Cell::fromValue($row[$column] ?? ''), $columns);
        $writer->addRow(new Row($cells));
    }

    $writer->close();

    return new UploadedFile(
        $path,
        'users.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true
    );
}
