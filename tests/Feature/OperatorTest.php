<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
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
