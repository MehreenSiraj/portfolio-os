<?php

use App\Models\Expense;
use App\Models\Media;
use App\Models\Project;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Security\SecurityHelpers;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
});

function attachFile(Project $project, string $name = 'plan.txt', string $body = 'attachment body'): Media
{
    $path = UploadedFile::fake()->createWithContent($name, $body)->store('project-files', 'local');

    return $project->media()->create([
        'disk' => 'local',
        'path' => $path,
        'original_name' => $name,
        'mime_type' => 'text/plain',
        'size' => strlen($body),
        'uploaded_by' => $project->owners->first()?->id,
    ]);
}

it('lets someone who can see the project download its attachment', function () {
    $admin = SecurityHelpers::user('admin');
    $project = SecurityHelpers::project($admin, ['domain' => 'attach-ok.test']);

    $media = attachFile($project);

    $response = $this->actingAs($admin)->get(route('media.download', $media));

    $response->assertOk();
    expect($response->streamedContent())->toBe('attachment body');
});

it('offers the attachment on the project page instead of listing it as a dead end', function () {
    $admin = SecurityHelpers::user('admin');
    $project = SecurityHelpers::project($admin, ['domain' => 'attach-link.test']);

    $media = attachFile($project, 'brief.txt');

    $this->actingAs($admin)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee(route('media.download', $media), escape: false);
});

it('refuses an attachment on a project the user is not assigned to', function () {
    $admin = SecurityHelpers::user('admin');
    $staff = SecurityHelpers::user('staff');

    $project = SecurityHelpers::project($admin, ['domain' => 'attach-denied.test']);
    $media = attachFile($project, 'secret-plan.txt');

    // Uploads sit on the private disk, so this route is the only way to read one
    // back. It must apply the same project scoping as the page that lists them.
    $this->actingAs($staff)->get(route('media.download', $media))->assertForbidden();
});

it('never serves an attachment as renderable content', function () {
    $admin = SecurityHelpers::user('admin');
    $project = SecurityHelpers::project($admin, ['domain' => 'attach-html.test']);

    $media = attachFile($project, 'payload.html', '<script>alert(1)</script>');

    $response = $this->actingAs($admin)->get(route('media.download', $media));

    // An uploaded page must download, not execute on this origin.
    $response->assertOk()
        ->assertHeader('content-type', 'application/octet-stream')
        ->assertHeader('x-content-type-options', 'nosniff');

    expect($response->headers->get('content-disposition'))->toContain('attachment');
});

it('gates an expense receipt behind expense viewing permission', function () {
    $admin = SecurityHelpers::user('admin');
    $staff = SecurityHelpers::user('staff');
    $project = SecurityHelpers::project($admin, ['domain' => 'receipt.test']);

    $path = UploadedFile::fake()->createWithContent('receipt.pdf', 'receipt bytes')->store('expense-receipts', 'local');

    $expense = Expense::query()->create([
        'project_id' => $project->id,
        'description' => 'Hosting renewal',
        'amount_paisa' => 12_000_00,
        'expense_date' => now()->toDateString(),
        'is_shared' => false,
        'is_paid' => true,
        'receipt_path' => $path,
        'receipt_original_name' => 'receipt.pdf',
        'created_by' => $admin->id,
    ]);

    $this->actingAs($admin)->get(route('expenses.receipt', $expense))->assertOk();
    $this->actingAs($staff)->get(route('expenses.receipt', $expense))->assertForbidden();
});

it('404s a receipt route for an expense with no receipt', function () {
    $admin = SecurityHelpers::user('admin');
    $project = SecurityHelpers::project($admin, ['domain' => 'no-receipt.test']);

    $expense = Expense::query()->create([
        'project_id' => $project->id,
        'description' => 'Cash expense',
        'amount_paisa' => 500_00,
        'expense_date' => now()->toDateString(),
        'is_shared' => false,
        'is_paid' => true,
        'created_by' => $admin->id,
    ]);

    $this->actingAs($admin)->get(route('expenses.receipt', $expense))->assertNotFound();
});
