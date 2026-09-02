<?php

namespace App\Http\Controllers;

use App\Actions\Groups\CreateGroupAction;
use App\Actions\Groups\UpdateGroupAction;
use App\Data\GroupData;
use App\Data\SessionData;
use App\Models\Group;
use App\Support\GroupAbilities;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function __construct()
    {

        $this->authorizeResource(Group::class, 'group');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, GroupAbilities $groupAbilities): Response
    {
        $user = $request->user();

        $query = Group::query()
            ->with('media')
            ->accessibleBy($request->user())
            ->withCount('members')
            ->withCount('sessions')
            ->orderFromRequest($request);

        $search_query = $request->input('q');

        if ($request->has('q')) {
            $query->where('name', 'like', "%{$search_query}%");
        }

        $collection = $query
            ->paginate(10)
            ->withQueryString()
            ->through(
                fn (Group $group): GroupData => GroupData::fromModel(
                    group: $group,
                    can: $groupAbilities->for(
                        user: $user,
                        group: $group,
                    ),
                ),
            );

        // dd($collection);
        return Inertia::render('groups/index', [
            'collection' => $collection,
            'q' => $search_query,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        Request $request,
        GroupData $data,
        CreateGroupAction $createGroupAction
    ): RedirectResponse {
        $group = $createGroupAction->execute(
            $data,
            $request->user()
        );

        $this->handleFormRequest($data, $group);

        Inertia::flash('success', __('Réunion créée avec succès.'));

        return to_route('groups.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('groups/form', [
            'group' => GroupData::empty(),
        ]);
    }

    public function show(
        Request $request,
        Group $group,
        GroupAbilities $groupAbilities,
    ): Response {
        $group->loadCount([
            'members',
            'sessions',
        ]);

        $sessions = $group
            ->sessions()
            ->withCount('participants')
            ->latest()
            ->limit(5)
            ->get();

        return Inertia::render('groups/show', [
            'group' => GroupData::fromModel(
                group: $group,
                can: $groupAbilities->for(
                    user: $request->user(),
                    group: $group,
                ),
            ),

            'sessions' => SessionData::collect(
                $sessions,
            ),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Group $group): Response
    {
        $group->load('media');

        return Inertia::render('groups/form', [
            'group' => GroupData::from($group),
        ]);
    }

    private function handleFormRequest(GroupData $data, Group $group): void
    {
        $image = $data->image_file;

        if ($image instanceof UploadedFile) {
            $extension = $image->getClientOriginalExtension();

            $filename = sprintf(
                '%s-%s.%s',
                $group->slug,
                \Str::uuid(),
                strtolower($extension)
            );

            $group
                ->addMedia($image)
                ->usingFileName($filename)
                ->toMediaCollection('image');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        GroupData $data,
        Group $group,
        UpdateGroupAction $updateGroupAction
    ): RedirectResponse {

        $updateGroupAction->execute(
            group: $group,
            data: $data
        );

        $this->handleFormRequest($data, $group);

        Inertia::flash('success', __('Réunion mise à jour avec succès.'));

        return to_route('groups.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group): RedirectResponse
    {
        $group->deleteOrFail();

        Inertia::flash('success', __('Réunion supprimée avec succès.'));

        return to_route('groups.index');
    }
}
