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

test('operator manager list follows locale and provides touch-sized actions', function () {
    $manager = User::factory()->create(['role' => 'pengelola_operator', 'status' => 'verified']);

    $response = $this->actingAs($manager)
        ->get(route('pengelola-operator.operator.index'))
        ->assertOk()
        ->assertSee('<title>Kelola Akun Operator - Pengelola Operator</title>', false)
        ->assertSee('Akun Operator')
        ->assertSee('Buat dan tugaskan operator per kategori mata pelajaran')
        ->assertSee('Kategori Mapel')
        ->assertSee('min-h-11', false)
        ->assertSee('min-w-11', false);

    $this->from(route('pengelola-operator.operator.index'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('pengelola-operator.operator.index'));

    $response = $this->get(route('pengelola-operator.operator.index'))
        ->assertOk()
        ->assertSee('<title>Manage Operator Accounts</title>', false)
        ->assertSee('Operator Accounts')
        ->assertSee('Create and assign operators to subject categories')
        ->assertSee('Subject Category')
        ->assertSee('Edit')
        ->assertSee('Delete')
        ->assertSee('Delete this operator?')
        ->assertSee($this->operator->email)
        ->assertSee('min-h-11', false)
        ->assertSee('min-w-11', false);

    expect(substr_count($response->getContent(), 'min-h-11'))->toBeGreaterThanOrEqual(3);

    $this->operator->delete();

    $this->get(route('pengelola-operator.operator.index'))
        ->assertOk()
        ->assertSee('There are no operators yet.')
        ->assertSee('Add the First Operator')
        ->assertSee('min-h-11', false)
        ->assertSee('min-w-11', false);
});

test('operator manager CRUD feedback follows the selected locale', function () {
    $manager = User::factory()->create(['role' => 'pengelola_operator', 'status' => 'verified']);
    $target = User::factory()->operator()->create();
    KategoriMataPelajaran::create(['nama' => 'Matematika']);

    $this->actingAs($manager)
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect();

    $this->put(route('pengelola-operator.operator.update', $target), [
        'name' => 'Updated Operator',
        'email' => $target->email,
        'kategori_mapel' => 'Matematika',
    ])->assertRedirect(route('pengelola-operator.operator.index'))
        ->assertSessionHas('success', 'Operator account updated.');

    $this->post(route('pengelola-operator.operator.store'), [
        'name' => 'New Operator',
        'email' => 'new-operator@example.test',
        'password' => 'password123',
        'kategori_mapel' => 'Matematika',
    ])->assertRedirect(route('pengelola-operator.operator.index'))
        ->assertSessionHas('success', 'Operator account created.');

    $createdOperator = User::where('email', 'new-operator@example.test')->firstOrFail();

    $this->delete(route('pengelola-operator.operator.destroy', $createdOperator))
        ->assertRedirect(route('pengelola-operator.operator.index'))
        ->assertSessionHas('success', 'Operator account deleted.');
});

test('operator manager create and edit forms follow locale and have touch-sized controls', function () {
    $manager = User::factory()->create(['role' => 'pengelola_operator', 'status' => 'verified']);
    $target = User::factory()->operator()->create(['kategori_mapel' => 'Matematika']);
    KategoriMataPelajaran::create(['nama' => 'Matematika']);

    $this->actingAs($manager)
        ->get(route('pengelola-operator.operator.create'))
        ->assertOk()
        ->assertSee('<title>Tambah Operator - Pengelola Operator</title>', false)
        ->assertSee('Tambah Akun Operator')
        ->assertSee('Buat operator dan tugaskan kategori mata pelajaran')
        ->assertSee('Nama')
        ->assertSee('Minimal 8 karakter')
        ->assertSee('Kategori Mata Pelajaran')
        ->assertSee('Simpan')
        ->assertSee('min-h-11', false)
        ->assertSee('min-w-11', false);

    $this->get(route('pengelola-operator.operator.edit', $target))
        ->assertOk()
        ->assertSee('Edit Akun Operator')
        ->assertSee('Kata Sandi Baru (opsional)')
        ->assertSee('Kosongkan jika tidak ingin mengubah');

    $this->from(route('pengelola-operator.operator.create'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('pengelola-operator.operator.create'));

    $createResponse = $this->get(route('pengelola-operator.operator.create'))
        ->assertOk()
        ->assertSee('<title>Add Operator - Operator Manager</title>', false)
        ->assertSee('Add Operator Account')
        ->assertSee('Create an operator and assign a subject category')
        ->assertSee('Operator full name')
        ->assertSee('At least 8 characters')
        ->assertSee('Subject Category')
        ->assertSee('Save')
        ->assertSee('Back')
        ->assertSee('min-h-11', false)
        ->assertSee('min-w-11', false);

    $editResponse = $this->get(route('pengelola-operator.operator.edit', $target))
        ->assertOk()
        ->assertSee('<title>Edit Operator - Operator Manager</title>', false)
        ->assertSee('Edit Operator Account')
        ->assertSee('Update the operator details and assigned category')
        ->assertSee('New password (optional)')
        ->assertSee('Leave blank to keep the current password')
        ->assertSee('Subject Category')
        ->assertSee('Save')
        ->assertSee('Back')
        ->assertSee('min-h-11', false)
        ->assertSee('min-w-11', false);

    expect(substr_count($createResponse->getContent(), 'min-h-11'))->toBeGreaterThanOrEqual(6)
        ->and(substr_count($createResponse->getContent(), 'min-w-11'))->toBeGreaterThanOrEqual(2)
        ->and(substr_count($editResponse->getContent(), 'min-h-11'))->toBeGreaterThanOrEqual(6)
        ->and(substr_count($editResponse->getContent(), 'min-w-11'))->toBeGreaterThanOrEqual(2);
});

test('operator manager dashboard follows the selected locale and has touch-sized links', function () {
    $manager = User::factory()->create(['role' => 'pengelola_operator', 'status' => 'verified']);

    $this->actingAs($manager)
        ->get(route('pengelola-operator.dashboard'))
        ->assertOk()
        ->assertSee('<title>Dashboard Pengelola Operator - SIMBA</title>', false)
        ->assertSee('Dashboard Pengelola Operator')
        ->assertSee('Operator')
        ->assertSee('Kategori Mapel')
        ->assertSee('Pengajar')
        ->assertSee('Pelajar')
        ->assertSee('Tindakan Cepat')
        ->assertSee('Kelola Kategori Mata Pelajaran')
        ->assertSee('Kelola Akun Operator');

    $this->from(route('pengelola-operator.dashboard'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('pengelola-operator.dashboard'));

    $response = $this->get(route('pengelola-operator.dashboard'))
        ->assertOk()
        ->assertSee('<title>Operator Manager Dashboard - SIMBA</title>', false)
        ->assertSee('Operator Manager Dashboard')
        ->assertSee('Operators')
        ->assertSee('Subject Categories')
        ->assertSee('Teachers')
        ->assertSee('Students')
        ->assertSee('Quick Actions')
        ->assertSee('Manage Subject Categories')
        ->assertSee('Manage Operator Accounts')
        ->assertSee(route('pengelola-operator.kategori-mata-pelajaran.index'))
        ->assertSee(route('pengelola-operator.operator.index'));

    expect(substr_count($response->getContent(), 'min-h-11'))->toBeGreaterThanOrEqual(2)
        ->and(substr_count($response->getContent(), 'min-w-11'))->toBeGreaterThanOrEqual(2);
});

test('global navigation labels and controls follow the selected locale', function () {
    $manager = User::factory()->create(['role' => 'pengelola_operator', 'status' => 'verified']);

    $this->actingAs($manager)
        ->get(route('pengelola-operator.dashboard'))
        ->assertOk()
        ->assertSee('aria-label="Buka atau tutup navigasi"', false)
        ->assertSee('aria-label="Tutup navigasi"', false)
        ->assertSee('aria-label="Notifikasi"', false)
        ->assertSee('aria-label="Menu akun"', false)
        ->assertSee('aria-label="Pilih bahasa"', false)
        ->assertSee('aria-controls="sidebar-group-manajemen"', false)
        ->assertSee('id="sidebar-group-manajemen"', false)
        ->assertSee('aria-controls="account-menu"', false)
        ->assertSee('id="account-menu"', false)
        ->assertSee('aria-controls="locale-menu"', false)
        ->assertSee('id="locale-menu"', false)
        ->assertSee(':aria-expanded="open.toString()"', false)
        ->assertSee('Kategori Mata Pelajaran')
        ->assertSee('Kelola Operator')
        ->assertSee('focus-visible:outline-2', false)
        ->assertSee('min-h-11', false);

    $this->from(route('pengelola-operator.dashboard'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('pengelola-operator.dashboard'));

    $this->get(route('pengelola-operator.dashboard'))
        ->assertOk()
        ->assertSee('aria-label="Toggle navigation"', false)
        ->assertSee('aria-label="Close navigation"', false)
        ->assertSee('aria-label="Notifications"', false)
        ->assertSee('aria-label="Account menu"', false)
        ->assertSee('aria-label="Choose language"', false)
        ->assertSee('aria-controls="sidebar-group-management"', false)
        ->assertSee('id="sidebar-group-management"', false)
        ->assertSee('aria-controls="account-menu"', false)
        ->assertSee('id="account-menu"', false)
        ->assertSee('aria-controls="locale-menu"', false)
        ->assertSee('id="locale-menu"', false)
        ->assertSee('Subject Categories')
        ->assertSee('Manage Operators')
        ->assertDontSee('Kategori Mata Pelajaran')
        ->assertDontSee('Kelola Operator');

    $student = User::factory()->verified()->create();

    $this->actingAs($student)
        ->get(route('pelajar.dashboard'))
        ->assertOk()
        ->assertSee('AI Settings')
        ->assertSee('Advance Grade Level')
        ->assertDontSee('Pengaturan AI')
        ->assertDontSee('Naik Jenjang/Tingkat');
});

test('operator dashboard follows the selected locale and uses distinct accessible quick links', function () {
    $this->actingAs($this->operator)
        ->get(route('operator.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Operator')
        ->assertSee('Akses Cepat')
        ->assertSee('Mata Pelajaran dan Materi')
        ->assertSee('min-h-11', false)
        ->assertSee(route('operator.mata-pelajaran.index'));

    $this->from(route('operator.dashboard'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('operator.dashboard'));

    $response = $this->get(route('operator.dashboard'))
        ->assertOk()
        ->assertSee('Operator Dashboard')
        ->assertSee('Choose an area to open.')
        ->assertSee('Subjects and Materials')
        ->assertSee('Manage Users')
        ->assertSee(route('operator.mata-pelajaran.index'));

    expect(substr_count($response->getContent(), 'min-h-11'))->toBeGreaterThanOrEqual(5);
});

test('operator import page returns 200', function () {
    $this->actingAs($this->operator)
        ->get(route('operator.import'))
        ->assertOk();
});

test('operator import page follows the selected locale and has accessible touch targets', function () {
    $this->actingAs($this->operator)
        ->get(route('operator.import'))
        ->assertOk()
        ->assertSee('<title>Import Data Pengguna</title>', false);

    $this->from(route('operator.import'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('operator.import'));

    $response = $this->get(route('operator.import'))
        ->assertOk()
        ->assertSee('<title>Import User Data</title>', false)
        ->assertSee('Download Template')
        ->assertSee('File (CSV / Excel)')
        ->assertSee('Upload a file');

    expect(substr_count($response->getContent(), 'min-h-11'))->toBeGreaterThanOrEqual(3);
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

test('operator dapat mengimpor pelajar tanpa nisn saat jalur belum diketahui', function () {
    $csvContent = "name,email,role,nip,nisn,password\nPelajar Uji,tanpa-nisn@example.test,pelajar,,,password123";

    $this->actingAs($this->operator)
        ->post(route('operator.import.users'), ['file' => createCsvUpload($csvContent)])
        ->assertRedirect()
        ->assertSessionHas('success', '1 pengguna berhasil diimport.')
        ->assertSessionMissing('import_errors');

    $this->assertDatabaseHas('users', ['email' => 'tanpa-nisn@example.test', 'nisn' => null]);
});

test('operator receives the import success message in the selected locale', function () {
    $this->actingAs($this->operator)
        ->from(route('operator.import'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('operator.import'));

    $this->post(route('operator.import.users'), [
        'file' => createCsvUpload("name,email,role,nip,nisn,password\nJane Doe,jane-en@test.com,pelajar,,98765432,secret123"),
    ])
        ->assertRedirect()
        ->assertSessionHas('success', '1 user imported successfully.');
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
    $this->operator->update(['kategori_mapel' => 'Matematika & Ilmu Alam']);

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
    $this->operator->update(['kategori_mapel' => 'Matematika & Ilmu Alam']);

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

    $this->from(route('operator.kelas.index'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('operator.kelas.index'));

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
        ->assertSessionHas('success', '1 class imported successfully.');

    $this->assertDatabaseHas('kelas', [
        'nama_kelas' => 'Kelas Lengkap',
        'jalur_id' => $jalurId,
        'jenjang_id' => $jenjangId,
        'tingkat_id' => DB::table('tingkat_pendidikan')->where('jenjang_id', $jenjangId)->value('id'),
        'mata_pelajaran_id' => DB::table('mata_pelajaran')->where('kode', 'matematika')->value('id'),
    ]);
});

test('operator import kelas menolak kategori yang tidak sesuai dengan mapel atau katalog', function () {
    $this->operator->update(['kategori_mapel' => 'Matematika & Ilmu Alam']);

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

test('operator import hanya membuat kelas dalam kategori tugasnya', function () {
    $this->operator->update(['kategori_mapel' => 'Matematika & Ilmu Alam']);
    $jalurId = DB::table('jalur_pendidikan')->insertGetId([
        'kode' => 'formal', 'nama' => 'Formal', 'created_at' => now(), 'updated_at' => now(),
    ]);
    $jenjangId = DB::table('jenjang_pendidikan')->insertGetId([
        'jalur_id' => $jalurId, 'kode' => 'sd', 'nama' => 'SD', 'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('tingkat_pendidikan')->insert([
        'jenjang_id' => $jenjangId, 'kode' => 'kelas_4', 'nama' => 'Kelas 4', 'created_at' => now(), 'updated_at' => now(),
    ]);
    foreach ([
        ['matematika', 'Matematika', 'Matematika & Ilmu Alam'],
        ['bahasa', 'Bahasa', 'Bahasa & Komunikasi'],
    ] as [$kode, $nama, $kategori]) {
        KategoriMataPelajaran::create(['nama' => $kategori]);
        MataPelajaran::create(['kode' => $kode, 'nama' => $nama, 'kategori' => $kategori]);
    }

    $csv = "nama_kelas,mata_pelajaran,kategori,jalur,jenjang,tingkat,kapasitas,periode,status,aturan_kelayakan,deskripsi\n"
        ."Kelas Sendiri,Matematika,Matematika & Ilmu Alam,Formal,SD,Kelas 4,30,2026,dibuka,terbuka,Deskripsi\n"
        ."Kelas Lintas,Bahasa,Bahasa & Komunikasi,Formal,SD,Kelas 4,30,2026,dibuka,terbuka,Deskripsi\n";

    $this->actingAs($this->operator)
        ->post(route('operator.import.kelas'), ['file' => createCsvUpload($csv)])
        ->assertRedirect(route('operator.kelas.index'))
        ->assertSessionHas('success', '1 kelas berhasil diimport.')
        ->assertSessionHas('import_errors', ['Baris 3: Kategori kelas harus sesuai dengan kategori yang ditugaskan kepada Anda.']);

    $this->assertDatabaseHas('kelas', ['nama_kelas' => 'Kelas Sendiri', 'kategori' => 'Matematika & Ilmu Alam']);
    $this->assertDatabaseMissing('kelas', ['nama_kelas' => 'Kelas Lintas']);
    $this->assertDatabaseMissing('berita', ['judul' => 'Pembukaan Kelas Baru: Kelas Lintas']);
});

test('operator tanpa kategori tidak dapat mengimpor kelas', function () {
    $this->operator->update(['kategori_mapel' => null]);

    $this->actingAs($this->operator)
        ->post(route('operator.import.kelas'), ['file' => createCsvUpload("nama_kelas\n")])
        ->assertForbidden();
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
