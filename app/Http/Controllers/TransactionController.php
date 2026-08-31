<?php

namespace App\Http\Controllers;

use App\Actions\Transactions\BuildSessionTransactionJournalAction;
use App\Data\SessionData;
use App\Models\Session;
use App\Models\Tontine;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(
        Request $request,
        Tontine $tontine,
        Session $session,
        BuildSessionTransactionJournalAction $buildJournal,
    ): Response {
        $this->authorize(
            'viewAny',
            [Transaction::class, $session],
        );

        $filters = $request->only(['direction', 'type', 'from', 'to']);
        $journal = $buildJournal->execute($session, $filters);

        return Inertia::render('transactions/index', [
            'tontine' => [
                'id' => $tontine->id,
                'name' => $tontine->name,
                'slug' => $tontine->slug,
            ],
            'session' => SessionData::fromModel($session),
            'collection' => $journal['collection'],
            'filters' => $filters,
            'summary' => $journal['summary'],
        ]);
    }
}
