<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTrainingRequest;
use App\Http\Requests\UpdateTrainingRequest;
use App\Http\Resources\TrainingResource;
use App\Models\Training;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Training::class);

        return TrainingResource::collection(Training::query()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTrainingRequest $request): TrainingResource
    {
        $training = Training::create($request->validated());

        return new TrainingResource($training);
    }

    /**
     * Display the specified resource.
     */
    public function show(Training $training): TrainingResource
    {
        Gate::authorize('view', $training);

        return new TrainingResource($training);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTrainingRequest $request, Training $training): TrainingResource
    {
        $training->update($request->validated());

        return new TrainingResource($training);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Training $training): Response
    {
        Gate::authorize('delete', $training);
        $training->delete();

        return response()->noContent();
    }
}
