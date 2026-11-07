<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTraineeRequest;
use App\Http\Requests\UpdateTraineeRequest;
use App\Http\Resources\TraineeResource;
use App\Models\Trainee;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TraineeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Trainee::class);

        return TraineeResource::collection(Trainee::query()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTraineeRequest $request): TraineeResource
    {
        $validated = $request->validated();
        $trainee = Trainee::create($validated);

        return new TraineeResource($trainee);
    }

    /**
     * Display the specified resource.
     */
    public function show(Trainee $trainee): TraineeResource
    {
        Gate::authorize('view', $trainee);

        return new TraineeResource($trainee);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTraineeRequest $request, Trainee $trainee): TraineeResource
    {
        $validated = $request->validated();
        if (isset($validated['password']) && empty($validated['password'])) {
            unset($validated['password']);
        }
        $trainee->update($validated);

        return new TraineeResource($trainee);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trainee $trainee): Response
    {
        Gate::authorize('delete', $trainee);
        $trainee->delete();

        return response()->noContent();
    }
}
