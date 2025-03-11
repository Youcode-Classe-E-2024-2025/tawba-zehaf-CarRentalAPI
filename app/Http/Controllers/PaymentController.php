<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;
use App\Models\Payment;
use App\Models\Rental;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/payments/user",
     *     summary="Get all payments for authenticated user",
     *     description="Returns all payments associated with the authenticated user's rentals",
     *     operationId="getUserPaymentsById",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="rental_id", type="integer", example=5),
     *                 @OA\Property(property="amount", type="number", format="float", example=199.99),
     *                 @OA\Property(property="method", type="string", example="credit_card"),
     *                 @OA\Property(property="status", type="string", example="completed"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function getUserPaymentsById()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payments = Payment::whereHas('rental', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        return response()->json($payments, 200);
    }

    /**
     * @OA\Get(
     *     path="/api/payments/rental/{rentalId}",
     *     summary="Get payment by rental ID",
     *     description="Returns payment information for a specific rental",
     *     operationId="getPaymentByRentalId",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="rentalId",
     *         in="path",
     *         description="ID of rental",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="rental_id", type="integer", example=5),
     *             @OA\Property(property="amount", type="number", format="float", example=199.99),
     *             @OA\Property(property="method", type="string", example="credit_card"),
     *             @OA\Property(property="status", type="string", example="completed"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment or rental not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found")
     *         )
     *     )
     * )
     */
    public function getPaymentByRentalId($rentalId)
    {
        $rental = Rental::where('user_id', Auth::id())->find($rentalId);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        $payment = Payment::where('rental_id', $rentalId)->first();

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        return response()->json($payment, 200);
    }

    /**
     * @OA\Post(
     *     path="/api/payments",
     *     summary="Create a new payment",
     *     description="Create a new payment for a rental",
     *     operationId="createPayment",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rental_id", "amount", "method", "status"},
     *             @OA\Property(property="rental_id", type="integer", example=5),
     *             @OA\Property(property="amount", type="number", format="float", example=199.99),
     *             @OA\Property(property="method", type="string", enum={"credit_card", "paypal", "cash"}, example="credit_card"),
     *             @OA\Property(property="status", type="string", enum={"pending", "completed", "failed"}, example="completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="rental_id", type="integer", example=5),
     *             @OA\Property(property="amount", type="number", format="float", example=199.99),
     *             @OA\Property(property="method", type="string", example="credit_card"),
     *             @OA\Property(property="status", type="string", example="completed"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized or rental not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized or rental not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function createOne(Request $request)
    {
        $validatedData = $request->validate([
            'rental_id' => 'required|exists:rentals,id',
            'amount' => 'required|numeric',
            'method' => 'required|in:credit_card,paypal,cash',
            'status' => 'required|in:pending,completed,failed',
        ]);

        $rental = Rental::where('user_id', Auth::id())->find($validatedData['rental_id']);

        if (!$rental) {
            return response()->json(['message' => 'Unauthorized or rental not found'], 403);
        }

        $payment = Payment::create($validatedData);

        return response()->json($payment, 201);
    }

    /**
     * @OA\Put(
     *     path="/api/payments/{id}",
     *     summary="Update an existing payment",
     *     description="Update payment information",
     *     operationId="updatePayment",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of payment to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount", "method", "status"},
     *             @OA\Property(property="amount", type="number", format="float", example=249.99),
     *             @OA\Property(property="method", type="string", enum={"credit_card", "paypal", "cash"}, example="paypal"),
     *             @OA\Property(property="status", type="string", enum={"pending", "completed", "failed"}, example="completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="rental_id", type="integer", example=5),
     *             @OA\Property(property="amount", type="number", format="float", example=249.99),
     *             @OA\Property(property="method", type="string", example="paypal"),
     *             @OA\Property(property="status", type="string", example="completed"),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function updateOne(Request $request, $id)
    {
        $payment = Payment::whereHas('rental', function ($query) {
            $query->where('user_id', Auth::id());
        })->find($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'method' => 'required|in:credit_card,paypal,cash',
            'status' => 'required|in:pending,completed,failed',
        ]);

        $payment->update($validatedData);

        return response()->json($payment, 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/payments/{id}",
     *     summary="Delete a payment",
     *     description="Delete a payment by ID",
     *     operationId="deletePayment",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of payment to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment not found")
     *         )
     *     )
     * )
     */
    public function deleteOne($id)
    {
        $payment = Payment::whereHas('rental', function ($query) {
            $query->where('user_id', Auth::id());
        })->find($id);

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $payment->delete();
        return response()->json(['message' => 'Payment deleted successfully'], 200);
    }
}