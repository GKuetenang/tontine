<?php

namespace App\Http\Controllers;

use App\Data\TontineData;
use App\Http\Requests\FormTontineRequest;
use App\Models\Tontine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class TontineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Tontine::query()
            ->with('media')
            ->where('user_id', auth()->id())
            ->orderFromRequest($request);
        $search_query = $request->input('q');

        if ($request->has('q')) {
            $query->where('name', 'like', "%{$search_query}%");
        }

        $collection = TontineData::collect(
            $query->paginate(10)->withQueryString(),
        );

//        dd($resCollection, $collection);
        return Inertia::render('tontines/index', [
            'collection' => $collection,
            'q' => $search_query,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FormTontineRequest $request): RedirectResponse
    {
        $tontine = auth()->user()->tontines()->create($request->validated());
        $this->handleFormRequest($request, $tontine);

        return to_route('tontines.index')->with('success', 'Successfully created tontine.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $tontine = new Tontine([
        ]);

        return $this->edit($tontine);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tontine $tontine)
    {
        $tontine->load('media');
//        dd(TontineData::fromModel($tontine)->toArray());
        return Inertia::render('tontines/form', [
            'tontine' => TontineData::fromModel($tontine),
        ]);
    }

    private function handleFormRequest(TontineData $data, Tontine $tontine): void
    {
        $image = $data->image_file;

        if ($image instanceof UploadedFile) {
            $tontine->addMedia($image)->toMediaCollection('image');
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TontineData $data, Tontine $tontine): RedirectResponse
    {

        $fillable = $tontine->getFillable();
        $tontine->update($data->only(...$fillable)->toArray());
        $this->handleFormRequest($data, $tontine);

        return to_route('tontines.index')->with('success', 'Successfully updated tontine.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tontine $tontine): RedirectResponse
    {
        $tontine->delete();
        return to_route('tontines.index')->with('success', 'Successfully deleted tontine.');
    }
}
