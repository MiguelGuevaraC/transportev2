<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BankController extends Controller
{
    /**
     * Get all banks with pagination
     * @OA\Get (
     *      path="/transporte/public/api/bank",
     *      tags={"Bank"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="List of active banks",
     *          @OA\JsonContent(
     *              @OA\Property(property="current_page", type="integer", example=1),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Bank")),
     *              @OA\Property(property="first_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/bank?page=1"),
     *              @OA\Property(property="from", type="integer", example=1),
     *              @OA\Property(property="next_page_url", type="string", example="http://develop.garzasoft.com/transporte/public/api/bank?page=2"),
     *              @OA\Property(property="path", type="string", example="http://develop.garzasoft.com/transporte/public/api/bank"),
     *              @OA\Property(property="per_page", type="integer", example=15),
     *              @OA\Property(property="prev_page_url", type="string", example="null"),
     *              @OA\Property(property="to", type="integer", example=15)
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated")
     *          )
     *      )
     * )
     */
    public function index()
    {
        return response()->json(Bank::simplePaginate(15));
    }

    /**
     * Store a newly created bank in storage.
     * @OA\Post (
     *      path="/transporte/public/api/bank",
     *      tags={"Bank"},
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/BankRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Bank created successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Bank")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Invalid data",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="The name has already been taken.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated")
     *          )
     *      )
     * )
     */
    public function store(Request $request)
    {
        $validator = validator()->make($request->all(), [
            'name' => [
                'required',
                'string',
                Rule::unique('banks', 'name')->whereNull('deleted_at'),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'name' => $request->input('name'),
        ];

        $bank = Bank::create($data);
        $bank = Bank::find($bank->id);

        return response()->json($bank);
    }

    /**
     * Display the specified bank.
     * @OA\Get (
     *      path="/transporte/public/api/bank/{id}",
     *      tags={"Bank"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Bank ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Bank found",
     *          @OA\JsonContent(ref="#/components/schemas/Bank")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Bank not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Bank not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated")
     *          )
     *      )
     * )
     */
    public function show(int $id)
    {
        $bank = Bank::find($id);

        if ($bank === null) {
            return response()->json(['message' => 'Bank not found'], 404);
        }

        return response()->json($bank);
    }

    /**
     * Update the specified bank in storage.
     * @OA\Put (
     *      path="/transporte/public/api/bank/{id}",
     *      tags={"Bank"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Bank ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/BankRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Bank updated successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Bank")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Bank not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Bank not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Invalid data",
     *          @OA\JsonContent(
     *              @OA\Property(property="error", type="string", example="The name has already been taken.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated")
     *          )
     *      )
     * )
     */
    public function update(Request $request, int $id)
    {
        $bank = Bank::find($id);

        if ($bank === null) {
            return response()->json(['message' => 'Bank not found'], 404);
        }

        $validator = validator()->make($request->all(), [
            'name' => [
                'required',
                'string',
                Rule::unique('banks', 'name')->whereNull('deleted_at')->ignore($bank->id),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $data = [
            'name' => $request->input('name'),
        ];

        $bank->update($data);
        $bank = Bank::find($bank->id);

        return response()->json($bank);
    }

    /**
     * Remove the specified bank from storage.
     * @OA\Delete (
     *      path="/transporte/public/api/bank/{id}",
     *      tags={"Bank"},
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Bank ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Bank deleted",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Bank deleted")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Bank not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Bank not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthenticated")
     *          )
     *      )
     * )
     */
    public function destroy(int $id)
    {
        $bank = Bank::find($id);

        if ($bank === null) {
            return response()->json(['message' => 'Bank not found'], 404);
        }

        $bank->delete();

        return response()->json(['message' => 'Bank deleted']);
    }
}
