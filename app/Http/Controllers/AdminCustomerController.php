<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class AdminCustomerController extends Controller
{
    public function destroy(Customer $customer): JsonResponse
    {
        $name = $customer->name;
        $customer->delete();

        return response()->json([
            'message' => "Customer details for {$name} were deleted.",
        ]);
    }
}
