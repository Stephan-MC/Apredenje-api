<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Payment::class);

        return PaymentResource::collection(Payment::query()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request): PaymentResource
    {
        $data = $request->validated();
        if (empty($data['registrar_id'])) {
            $data['registrar_id'] = $request->user()?->id;
        }
        $payment = Payment::create($data);

        return new PaymentResource($payment);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): PaymentResource
    {
        Gate::authorize('view', $payment);

        return new PaymentResource($payment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): PaymentResource
    {
        $payment->update($request->validated());

        return new PaymentResource($payment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment): Response
    {
        Gate::authorize('delete', $payment);
        $payment->delete();

        return response()->noContent();
    }
}
