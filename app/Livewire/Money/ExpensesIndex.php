<?php

namespace App\Livewire\Money;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Models\RecurringExpense;
use App\Policies\FinancePolicy;
use App\Services\CsvExportService;
use App\Services\ExpenseService;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Expenses')]
class ExpensesIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $projectFilter = '';

    #[Url]
    public string $monthFilter = '';

    #[Url]
    public string $paidFilter = '';

    public bool $showForm = false;

    public bool $showRecurring = false;

    public ?int $editingId = null;

    public string $project_id = '';

    public bool $is_shared = false;

    public string $expense_category_id = '';

    public string $amount = '';

    public string $description = '';

    public string $expense_date = '';

    public string $notes = '';

    public bool $is_paid = false;

    public $receipt = null;

    /** Recurring form */
    public string $rec_description = '';

    public string $rec_amount = '';

    public string $rec_day = '1';

    public string $rec_project_id = '';

    public bool $rec_is_shared = false;

    public string $rec_category_id = '';

    /** @var array<int, bool> */
    public array $selected = [];

    public function mount(): void
    {
        abort_unless(FinancePolicy::viewExpenses(Auth::user()), 403);
        app(ExpenseService::class)->seedSystemCategories();
        if ($this->monthFilter === '') {
            $this->monthFilter = now('Asia/Karachi')->format('Y-m');
        }
        $this->expense_date = now('Asia/Karachi')->toDateString();
    }

    public function create(): void
    {
        abort_unless(FinancePolicy::manageExpenses(Auth::user()), 403);
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        abort_unless(FinancePolicy::manageExpenses(Auth::user()), 403);
        $e = Expense::query()->findOrFail($id);
        $this->editingId = $e->id;
        $this->project_id = (string) ($e->project_id ?? '');
        $this->is_shared = (bool) $e->is_shared;
        $this->expense_category_id = (string) ($e->expense_category_id ?? '');
        $this->amount = Money::paisaToMajor((int) $e->amount_paisa);
        $this->description = $e->description;
        $this->expense_date = $e->expense_date->format('Y-m-d');
        $this->notes = (string) $e->notes;
        $this->is_paid = (bool) $e->is_paid;
        $this->showForm = true;
    }

    public function save(ExpenseService $expenses): void
    {
        abort_unless(FinancePolicy::manageExpenses(Auth::user()), 403);

        $this->validate([
            'is_shared' => ['boolean'],
            'project_id' => [$this->is_shared ? 'nullable' : 'required', 'integer', 'exists:projects,id'],
            'expense_category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['required', 'string', 'max:500'],
            'expense_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_paid' => ['boolean'],
            'receipt' => ['nullable', 'file', 'max:5120'],
        ]);

        $data = [
            'project_id' => $this->project_id !== '' ? (int) $this->project_id : null,
            'is_shared' => $this->is_shared,
            'expense_category_id' => $this->expense_category_id !== '' ? (int) $this->expense_category_id : null,
            'amount' => $this->amount,
            'description' => $this->description,
            'expense_date' => $this->expense_date,
            'notes' => $this->notes ?: null,
            'is_paid' => $this->is_paid,
        ];

        if ($this->editingId) {
            $expenses->updateManual(Expense::query()->findOrFail($this->editingId), $data, Auth::user(), $this->receipt);
            session()->flash('status', 'Expense updated.');
        } else {
            $expenses->createManual($data, Auth::user(), $this->receipt);
            session()->flash('status', 'Expense recorded.');
        }

        $this->showForm = false;
        $this->resetForm();
        $this->receipt = null;
    }

    public function delete(int $id, ExpenseService $expenses): void
    {
        abort_unless(FinancePolicy::manageExpenses(Auth::user()), 403);
        $expenses->softDelete(Expense::query()->findOrFail($id), Auth::user());
        session()->flash('status', 'Expense soft-deleted.');
    }

    public function bulkMarkPaid(ExpenseService $expenses): void
    {
        abort_unless(FinancePolicy::manageExpenses(Auth::user()), 403);
        $ids = collect($this->selected)->filter()->keys()->map(fn ($k) => (int) $k)->all();
        $count = $expenses->bulkMarkPaid($ids, Auth::user(), true);
        $this->selected = [];
        session()->flash('status', "Marked {$count} expense(s) paid.");
    }

    public function saveRecurring(ExpenseService $expenses): void
    {
        abort_unless(FinancePolicy::manageExpenses(Auth::user()), 403);
        $this->validate([
            'rec_description' => ['required', 'string', 'max:500'],
            'rec_amount' => ['required', 'numeric', 'min:0'],
            'rec_day' => ['required', 'integer', 'min:1', 'max:28'],
            'rec_is_shared' => ['boolean'],
            'rec_project_id' => [$this->rec_is_shared ? 'nullable' : 'required', 'integer', 'exists:projects,id'],
            'rec_category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
        ]);

        $day = (int) $this->rec_day;
        $next = now('Asia/Karachi')->startOfMonth()->day(min($day, now('Asia/Karachi')->daysInMonth));
        if ($next->lt(now('Asia/Karachi')->startOfDay())) {
            $next = $next->addMonthNoOverflow()->day(min($day, $next->daysInMonth));
        }

        RecurringExpense::query()->create([
            'project_id' => $this->rec_is_shared ? null : (int) $this->rec_project_id,
            'is_shared' => $this->rec_is_shared,
            'expense_category_id' => $this->rec_category_id !== '' ? (int) $this->rec_category_id : null,
            'amount_paisa' => Money::pkrToPaisa($this->rec_amount),
            'description' => $this->rec_description,
            'day_of_month' => $day,
            'next_run_date' => $next->toDateString(),
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        $this->showRecurring = false;
        session()->flash('status', 'Recurring expense template saved. Runs via expenses:generate-recurring.');
    }

    public function exportCsv(CsvExportService $csv)
    {
        abort_unless(FinancePolicy::export(Auth::user()), 403);
        $rows = $this->baseQuery()->with(['project', 'category'])->orderByDesc('expense_date')->get()
            ->map(fn (Expense $e) => [
                $e->id,
                $e->is_shared ? 'shared' : ($e->project?->domain ?? ''),
                $e->category?->name,
                Money::paisaToMajor((int) $e->amount_paisa),
                $e->description,
                $e->expense_date->format('Y-m-d'),
                $e->is_paid ? 'paid' : 'unpaid',
            ]);

        return $csv->download('expenses.csv', [
            'id', 'project_or_shared', 'category', 'amount_pkr', 'description', 'date', 'status',
        ], $rows);
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->project_id = $this->projectFilter;
        $this->is_shared = false;
        $this->expense_category_id = '';
        $this->amount = '';
        $this->description = '';
        $this->expense_date = now('Asia/Karachi')->toDateString();
        $this->notes = '';
        $this->is_paid = false;
    }

    protected function baseQuery()
    {
        $user = Auth::user();

        return Expense::query()
            ->accessibleBy($user)
            ->when($this->projectFilter !== '', fn ($q) => $q->where(function ($q) {
                $q->where('project_id', $this->projectFilter)->orWhere('is_shared', true);
            }))
            ->when($this->monthFilter !== '', function ($q) {
                $start = $this->monthFilter.'-01';
                $end = \Carbon\Carbon::parse($start)->endOfMonth()->toDateString();
                $q->whereBetween('expense_date', [$start, $end]);
            })
            ->when($this->paidFilter === 'paid', fn ($q) => $q->where('is_paid', true))
            ->when($this->paidFilter === 'unpaid', fn ($q) => $q->where('is_paid', false))
            ->when($this->search !== '', function ($q) {
                $s = '%'.$this->search.'%';
                $q->where(function ($inner) use ($s) {
                    $inner->where('description', 'like', $s)
                        ->orWhere('notes', 'like', $s);
                });
            });
    }

    public function render()
    {
        $user = Auth::user();

        return view('livewire.money.expenses-index', [
            'expenses' => $this->baseQuery()->with(['project', 'category'])->orderByDesc('expense_date')->orderByDesc('id')->paginate(20),
            'projects' => Project::query()->accessibleBy($user)->orderBy('domain')->get(),
            'categories' => ExpenseCategory::query()->orderBy('name')->get(),
            'canManage' => FinancePolicy::manageExpenses($user),
            'recurring' => RecurringExpense::query()->where('is_active', true)->orderBy('next_run_date')->limit(10)->get(),
        ]);
    }
}
