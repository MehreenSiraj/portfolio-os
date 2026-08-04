<?php

use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureUserIsActive;
use App\Livewire\Approvals\Queue as ApprovalQueue;
use App\Livewire\Articles\Index as ArticlesIndex;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Dashboard;
use App\Livewire\Links\Index as LinksIndex;
use App\Livewire\Projects\Index as ProjectsIndex;
use App\Livewire\Projects\Show as ProjectShow;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\Settings\TaskTemplates;
use App\Livewire\Tasks\Index as TasksIndex;
use App\Livewire\Tasks\Show as TaskShow;
use App\Livewire\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');

    Route::get('/users', UsersIndex::class)
        ->middleware(EnsurePermission::class.':users.view')
        ->name('users.index');

    Route::get('/settings', SettingsIndex::class)
        ->middleware(EnsurePermission::class.':settings.view')
        ->name('settings.index');

    Route::get('/settings/task-templates', TaskTemplates::class)
        ->middleware(EnsurePermission::class.':task_templates.manage')
        ->name('settings.task-templates');

    Route::get('/projects', ProjectsIndex::class)
        ->middleware(EnsurePermission::class.':projects.view')
        ->name('projects.index');

    Route::get('/projects/{project}', ProjectShow::class)
        ->middleware(EnsurePermission::class.':projects.view')
        ->name('projects.show');

    Route::get('/tasks', TasksIndex::class)
        ->middleware(EnsurePermission::class.':tasks.view')
        ->name('tasks.index');

    Route::get('/tasks/{task}', TaskShow::class)
        ->middleware(EnsurePermission::class.':tasks.view')
        ->name('tasks.show');

    Route::get('/articles', ArticlesIndex::class)
        ->middleware(EnsurePermission::class.':articles.view')
        ->name('articles.index');

    Route::get('/links', LinksIndex::class)
        ->middleware(EnsurePermission::class.':links.view')
        ->name('links.index');

    Route::get('/approvals', ApprovalQueue::class)
        ->name('approvals.queue');
});
