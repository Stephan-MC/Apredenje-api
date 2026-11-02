<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBatchTypeRequest;
use App\Http\Requests\UpdateBatchTypeRequest;
use App\Models\BatchType;

class BatchTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBatchTypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(BatchType $batchType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBatchTypeRequest $request, BatchType $batchType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BatchType $batchType)
    {
        //
    }
}
