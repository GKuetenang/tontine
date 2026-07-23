<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormTontineRequest;
use App\Http\Resources\TontineDetailedResource;
use App\Http\Resources\TontineResource;
use App\Models\Enum\TontineUnit;
use App\Models\Tontine;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;

class TontineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tontine::query()
            ->with('media')
            ->where('user_id', auth()->id())
            ->orderFromRequest($request);
        $search_query = $request->input('q');

        if ($request->has('q')) {
            $query->where('name', 'like', "%{$search_query}%");
        }

        return Inertia::render('tontines/index', [
            'collection' => TontineResource::collection(
                $query->paginate(10)->withQueryString(),
            ),
            'q' => $search_query,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FormTontineRequest $request)
    {
//        dd(Auth::user()->tontines);
        $tontine = auth()->user()->tontines()->create($request->validated());
        $this->handleFormRequest($request, $tontine);

        return to_route('tontines.index')->with('success', 'Successfully created tontine.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
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

        return Inertia::render('tontines/form', [
            'tontine' => new TontineDetailedResource($tontine),
        ]);
    }

    private function handleFormRequest(FormTontineRequest $request, Tontine $tontine)
    {
        $image = $request->validated('image');

        if ($image instanceof UploadedFile) {
            $tontine->addMedia($image)->toMediaCollection('image');
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FormTontineRequest $request, Tontine $tontine)
    {
        $tontine->update($request->validated());
        $this->handleFormRequest($request, $tontine);

        return to_route('tontines.index')->with('success', 'Successfully updated tontine.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tontine $tontine)
    {
        $tontine->delete();
        return to_route('tontines.index')->with('success', 'Successfully deleted tontine.');
    }
}
