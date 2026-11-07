<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBatchTypeRequest;
use App\Http\Requests\UpdateBatchTypeRequest;
use App\Http\Resources\BatchTypeResource;
use App\Models\BatchType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class BatchTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', BatchType::class);

        return BatchTypeResource::collection(BatchType::query()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBatchTypeRequest $request): BatchTypeResource
    {
        $batchType = BatchType::create($request->validated());

        return new BatchTypeResource($batchType);
    }

    /**
     * Display the specified resource.
     */
    public function show(BatchType $batchType): BatchTypeResource
    {
        Gate::authorize('view', $batchType);

        return new BatchTypeResource($batchType);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBatchTypeRequest $request, BatchType $batchType): BatchTypeResource
    {
        $batchType->update($request->validated());

        return new BatchTypeResource($batchType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BatchType $batchType): Response
    {
        Gate::authorize('delete', $batchType);
        $batchType->delete();

        return response()->noContent();
    }
}
