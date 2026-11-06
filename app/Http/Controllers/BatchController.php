<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\UpdateBatchRequest;
use App\Http\Resources\BatchResource;
use App\Models\Batch;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class BatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Batch::class);

        return BatchResource::collection(Batch::query()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBatchRequest $request): BatchResource
    {
        $batch = Batch::create($request->validated());

        return new BatchResource($batch);
    }

    /**
     * Display the specified resource.
     */
    public function show(Batch $batch): BatchResource
    {
        Gate::authorize('view', $batch);

        return new BatchResource($batch);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBatchRequest $request, Batch $batch): BatchResource
    {
        $batch->update($request->validated());

        return new BatchResource($batch);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Batch $batch): Response
    {
        Gate::authorize('delete', $batch);
        $batch->delete();

        return response()->noContent();
    }
}
