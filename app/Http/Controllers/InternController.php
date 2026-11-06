<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInternRequest;
use App\Http\Requests\UpdateInternRequest;
use App\Http\Resources\InternResource;
use App\Models\Intern;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class InternController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Intern::class);

        return InternResource::collection(Intern::query()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInternRequest $request): InternResource
    {
        $validated = $request->validated();
        $intern = Intern::create($validated);

        return new InternResource($intern);
    }

    /**
     * Display the specified resource.
     */
    public function show(Intern $intern): InternResource
    {
        Gate::authorize('view', $intern);

        return new InternResource($intern);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInternRequest $request, Intern $intern): InternResource
    {
        $validated = $request->validated();
        if (isset($validated['password']) && empty($validated['password'])) {
            unset($validated['password']);
        }
        $intern->update($validated);

        return new InternResource($intern);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Intern $intern): Response
    {
        Gate::authorize('delete', $intern);
        $intern->delete();

        return response()->noContent();
    }
}
