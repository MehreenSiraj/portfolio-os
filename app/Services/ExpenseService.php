<?php

namespace App\Services;

use App\Enums\ArticleStatus;
use App\Enums\LinkWorkflowStatus;
use App\Enums\TaskStatus;
use App\Models\Article;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Link;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Minimal expense creation for Work approval hooks.
 * Full Money module ships in M5; this only materializes writer/link costs.
 */
class ExpenseService
{
    public function ensureCategory(string $slug, string $name): ExpenseCategory
    {
        return ExpenseCategory::query()->firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'is_system' => true],
        );
    }

    /**
     * Create expense from article approval once (idempotent).
     */
    public function createFromArticleApproval(Article $article, User $actor): Expense
    {
        if ($article->status !== ArticleStatus::Approved) {
            throw new InvalidArgumentException('Article must be approved before creating expense.');
        }

        return $this->createOnce(
            source: $article,
            projectId: (int) $article->project_id,
            amountPaisa: (int) $article->cost_paisa,
            description: 'Writer cost: '.$article->title.' ('.$article->target_keyword.')',
            expenseDate: ($article->publish_date ?? now())->toDateString(),
            categorySlug: 'article_writer',
            categoryName: 'Article writer',
            actor: $actor,
            linkOnSource: 'expense_id',
        );
    }

    /**
     * Create expense from link approval once (idempotent).
     */
    public function createFromLinkApproval(Link $link, User $actor): Expense
    {
        if ($link->workflow_status !== LinkWorkflowStatus::Approved) {
            throw new InvalidArgumentException('Link must be approved before creating expense.');
        }

        return $this->createOnce(
            source: $link,
            projectId: (int) $link->project_id,
            amountPaisa: (int) $link->cost_paisa,
            description: 'Link cost: '.$link->source_domain.' → '.$link->target_page,
            expenseDate: ($link->link_date ?? now())->toDateString(),
            categorySlug: 'link_building',
            categoryName: 'Link building',
            actor: $actor,
            linkOnSource: 'expense_id',
        );
    }

    /**
     * @template T of Model
     * @param  T  $source
     */
    protected function createOnce(
        Model $source,
        int $projectId,
        int $amountPaisa,
        string $description,
        string $expenseDate,
        string $categorySlug,
        string $categoryName,
        User $actor,
        string $linkOnSource,
    ): Expense {
        return DB::transaction(function () use (
            $source,
            $projectId,
            $amountPaisa,
            $description,
            $expenseDate,
            $categorySlug,
            $categoryName,
            $actor,
            $linkOnSource,
        ) {
            $source->refresh();

            if ($source->{$linkOnSource}) {
                $existing = Expense::query()->find($source->{$linkOnSource});
                if ($existing) {
                    return $existing;
                }
            }

            $existingBySource = Expense::query()
                ->where('source_type', $source::class)
                ->where('source_id', $source->getKey())
                ->first();

            if ($existingBySource) {
                if (! $source->{$linkOnSource}) {
                    $source->update([$linkOnSource => $existingBySource->id]);
                }

                return $existingBySource;
            }

            // Zero-cost still creates a zero row so re-approval is a no-op.
            $category = $this->ensureCategory($categorySlug, $categoryName);

            $expense = Expense::query()->create([
                'project_id' => $projectId,
                'expense_category_id' => $category->id,
                'amount_paisa' => max(0, $amountPaisa),
                'currency' => 'PKR',
                'description' => $description,
                'expense_date' => $expenseDate,
                'source_type' => $source::class,
                'source_id' => $source->getKey(),
                'created_by' => $actor->id,
            ]);

            $source->update([$linkOnSource => $expense->id]);

            return $expense;
        });
    }
}
