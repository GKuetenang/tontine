<?php

namespace App\Http\Controllers;

use App\Actions\Memberships\CreateMembershipAction;
use App\Actions\Tontines\CreateTontineAction;
use App\Data\TontineData;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class TontineController extends Controller
{
    public function __construct()
    {

        $this->authorizeResource(Tontine::class, 'tontine');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Tontine::query()
            ->with('media')
            ->where('user_id', $request->user()->id)
            ->withCount('members')
            ->orderFromRequest($request);
        $search_query = $request->input('q');

        if ($request->has('q')) {
            $query->where('name', 'like', "%{$search_query}%");
        }

        $collection = TontineData::collect(
            $query->paginate(10)->withQueryString(),
        );

        // dd($collection[0]);
        return Inertia::render('tontines/index', [
            'collection' => $collection,
            'q' => $search_query,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        Request $request,
        TontineData $data,
        CreateTontineAction $createTontineAction
    ): RedirectResponse {
        $tontine = $createTontineAction->execute(
            $data,
            $request->user()
        );
        $this->handleFormRequest($data, $tontine);

        Inertia::flash('success', __('Tontine crée avec succès.'));
        return to_route('tontines.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('tontines/form', [
            'tontine' => TontineData::empty(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tontine $tontine): Response
    {
        $tontine->load('media');
        return Inertia::render('tontines/form', [
            'tontine' => TontineData::from($tontine),
        ]);
    }

    private function handleFormRequest(TontineData $data, Tontine $tontine): void
    {
        $image = $data->image_file;

        if ($image instanceof UploadedFile) {
            $extension = $image->getClientOriginalExtension();

            $filename = sprintf(
                '%s-%s.%s',
                $tontine->slug,
                \Str::uuid(),
                strtolower($extension)
            );

            $tontine
                ->addMedia($image)
                ->usingFileName($filename)
                ->toMediaCollection('image');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TontineData $data, Tontine $tontine): RedirectResponse
    {

        $fillable = $tontine->getFillable();
        $updateData = $data->only(...$fillable)->toArray();
        $tontine->update($updateData);
        $this->handleFormRequest($data, $tontine);

        Inertia::flash('success', __('Tontine mise à jour avec succès.'));
        return to_route('tontines.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tontine $tontine): RedirectResponse
    {
        $tontine->deleteOrFail();

        Inertia::flash('success', __('Tontine supprimée avec succès.'));
        return to_route('tontines.index');
    }
}
