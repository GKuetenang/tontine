<?php

namespace App\Http\Controllers;

use App\Actions\Transactions\BuildSessionTransactionJournalAction;
use App\Data\SessionData;
use App\Enums\TransactionDirection;
use App\Enums\TransactionType;
use App\Models\Group;
use App\Models\Session;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(
        Request $request,
        Group $group,
        Session $session,
        BuildSessionTransactionJournalAction $buildJournal,
    ): Response {
        $this->authorize(
            'viewAny',
            [Transaction::class, $session],
        );

        $filters = $request->validate([
            'direction' => ['nullable', Rule::enum(TransactionDirection::class)],
            'type' => ['nullable', Rule::enum(TransactionType::class)],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'sort' => ['nullable', Rule::in(['type', 'direction', 'amount', 'occurred_at', 'created_at'])],
            'dir' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $journal = $buildJournal->execute($session, $filters);

        return Inertia::render('transactions/index', [
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'slug' => $group->slug,
            ],
            'session' => SessionData::fromModel($session),
            'collection' => $journal['collection'],
            'filters' => $filters,
            'summary' => $journal['summary'],
            'transaction_types' => TransactionType::getOptions(),
            'transaction_directions' => TransactionDirection::getOptions(),
        ]);
    }
}
