<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
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
    $csvContent = "name,email,password\nJohn Doe,john@test.com,secret123\nJane Doe,jane@test.com,secret456";

    $this->actingAs($this->operator)
        ->post(route('operator.import.users'), [
            'file' => createCsvUpload($csvContent),
        ])
        ->assertRedirect()
        ->assertSessionHas('success');
});

test('operator can import users from xlsx', function () {
    $file = createXlsxUpload([
        ['name' => 'Alice', 'email' => 'alice@test.com'],
        ['name' => 'Bob', 'email' => 'bob@test.com'],
    ]);

    $this->actingAs($this->operator)
        ->post(route('operator.import.users'), [
            'file' => $file,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');
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

    $writer->addRow(new Row([
        Cell::fromValue('name'),
        Cell::fromValue('email'),
        Cell::fromValue('password'),
        Cell::fromValue('role'),
    ]));

    foreach ($rows as $row) {
        $cells = [];
        foreach (['name', 'email', 'password', 'role'] as $col) {
            $cells[] = Cell::fromValue($row[$col] ?? '');
        }
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
