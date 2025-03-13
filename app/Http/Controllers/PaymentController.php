<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\PaymentIntent;

use Stripe\Checkout\Session;
// use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use App\Models\Payment;
use App\Models\Rental;
use Illuminate\Support\Facades\Auth;

use OpenApi\Annotations as OA;


/**
 * @OA\Schema(
 *     schema="Payment",
 *     type="object",
 *     required={"rental_id", "amount", "method", "status"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="rental_id", type="integer", example=1),
 *     @OA\Property(property="amount", type="number", format="float", example=150.00),
 *     @OA\Property(property="method", type="string", enum={"credit_card", "paypal", "cash"}, example="credit_card"),
 *     @OA\Property(property="status", type="string", enum={"pending", "completed", "failed"}, example="completed"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-03-15T12:00:00"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-03-15T12:00:00")
 * )
 */


class PaymentController extends Controller
{

    /**
 * @OA\Get(
 *     path="/api/payments",
 *     summary="Get a list of all rentals",
 *     tags={"Payments"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="A list of rentals",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Rental")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error"
 *     )
 * )
 */

 public function index()
 {
     // Fetch all rentals from the database
     $payments = Payment::all();

     // Return the rentals as a JSON response
     return response()->json($payments);
 }


    /**
     * @OA\Post(
     *     path="/api/payments",
     *     summary="Store a new payment",
     *     description="Create a new payment entry",
     *     operationId="storePayment",
     *     tags={"Payments"},
     * 
 *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rental_id", "amount", "method", "status"},
     *             @OA\Property(property="rental_id", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", format="float", example=100.50),
     *             @OA\Property(property="method", type="string", example="Credit Card"),
     *             @OA\Property(property="status", type="string", example="Completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Payment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid data")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'rental_id' => 'required|exists:rentals,id',
            'amount' => 'required|numeric',
            'method' => 'required|string',
            'status' => 'required|string',
        ]);

        $payment = Payment::create([
            'rental_id' => $request->rental_id,
            'amount' => $request->amount,
            'method' => $request->method,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Payment created successfully',
            'data' => $payment
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/payments/{id}",
     *     summary="Show a specific payment",
     *     description="Retrieve a specific payment by ID",
     *     operationId="showPayment",
     *     tags={"Payments"},
     * 
 *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment details",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Payment not found")
     * )
     */
    public function show($id)
    {
        $payment = Payment::findOrFail($id);

        return response()->json([
            'data' => $payment
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/payments/{id}",
     *     summary="Update a payment",
     *     description="Update payment details",
     *     operationId="updatePayment",
     *     tags={"Payments"},
     * 
 *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount", "method", "status"},
     *             @OA\Property(property="amount", type="number", format="float", example=100.50),
     *             @OA\Property(property="method", type="string", example="Credit Card"),
     *             @OA\Property(property="status", type="string", example="Completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment updated successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Payment not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric',
            'method' => 'required|string',
            'status' => 'required|string',
        ]);

        $payment->update([
            'amount' => $request->amount,
            'method' => $request->method,
            'status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Payment updated successfully',
            'data' => $payment
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/payments/{id}",
     *     summary="Delete a payment",
     *     description="Delete a specific payment by ID",
     *     operationId="deletePayment",
     *     tags={"Payments"},
     * 
 *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Payment ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Payment deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Payment not found")
     * )
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully'
        ]);
    }


     /**
     * Get the payment for a specific rental by rental_id.
     *
     * @OA\Get(
     *     path="/api/payments/rental/{rental_id}",
     *     summary="Get payment by rental ID",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="rental_id",
     *         in="path",
     *         required=true,
     *         description="ID of the rental to fetch payment for",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment details for the rental",
     *         @OA\JsonContent(ref="#/components/schemas/Payment")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found for the rental ID"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getPaymentByRentalId($rental_id)
    {
        // Check if rental exists
        $rental = Rental::find($rental_id);

        if (!$rental) {
            return response()->json(['message' => 'Rental not found'], 404);
        }

        // Get the associated payment for the rental
        $payment = $rental->payment;

        if (!$payment) {
            return response()->json(['message' => 'Payment not found for this rental'], 404);
        }

        // Return the payment as a response
        return response()->json($payment);
    }
/**
 * @OA\Get(
 *     path="/api/payments/user/{user_id}",
 *     summary="Get all payments made by a user through their rentals",
 *     tags={"Payments"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         description="ID of the user to fetch payments for",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="List of payments made by the user through rentals",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(ref="#/components/schemas/Payment")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No payments found for the user"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     )
 * )
 */
public function getUserPaymentsById($user_id)
{
    // Fetch all rentals with payments for the user (eager load 'payment' relationship)
    $rentals = Rental::where('user_id', $user_id)->with('payment')->get();

    // Check if the user has any rentals
    if ($rentals->isEmpty()) {
        return response()->json(['message' => 'No rentals found for this user'], 404);
    }

    // Get payments from rentals
    $payments = $rentals->flatMap(function ($rental) {
        return $rental->payment ? [$rental->payment] : []; // Return payment if exists
    });

    // Check if any payments exist
    if ($payments->isEmpty()) {
        return response()->json(['message' => 'No payments found for this user'], 404);
    }

    // Return the list of payments
    return response()->json($payments);
}


}