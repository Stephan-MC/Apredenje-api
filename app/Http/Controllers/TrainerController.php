<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainerRequest;
use App\Http\Requests\UpdateTrainerRequest;
use App\Http\Resources\TrainerResource;
use App\Models\Trainer;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TrainerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Trainer::class);

        return TrainerResource::collection(Trainer::query()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTrainerRequest $request): TrainerResource
    {
        $validated = $request->validated();
        $trainer = Trainer::create($validated);

        return new TrainerResource($trainer);
    }

    /**
     * Display the specified resource.
     */
    public function show(Trainer $trainer): TrainerResource
    {
        Gate::authorize('view', $trainer);

        return new TrainerResource($trainer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTrainerRequest $request, Trainer $trainer): TrainerResource
    {
        $validated = $request->validated();
        if (isset($validated['password']) && empty($validated['password'])) {
            unset($validated['password']);
        }
        $trainer->update($validated);

        return new TrainerResource($trainer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trainer $trainer): Response
    {
        Gate::authorize('delete', $trainer);
        $trainer->delete();

        return response()->noContent();
    }
}
