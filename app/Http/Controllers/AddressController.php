<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Traits\FiltersTrait;
use App\Traits\IdTrait;
use App\Traits\JwtTrait;
use App\Traits\PaginationTrait;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddressController extends Controller
{
    /**
     * @OA\Info(title="Address API Collect&Verything", version="0.1")
     */
    use JWTTrait, FiltersTrait, PaginationTrait, IdTrait;

    /**
     * @OA\Get(
     *      path="/api/addresses/{id}",
     *      operationId="retrieve",
     *      tags={"Addresses"},
     *      summary="Get address information",
     *      description="Returns address data",
     *      @OA\Parameter(name="id",description="Address id",required=true,in="path"),
     *      @OA\Response(response=200, description="successful operation"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Account not found."),
     *      @OA\Response(response=409, description="Conflict"),
     *      @OA\Response(response=500, description="Servor Error"),
     * )
     */
    public function retrieve(Request $request): JsonResponse
    {
        try {

            $resultSet = Address::select('addresses.*')->where('id', $request->id);

            $address = $resultSet->first();

            if(empty($address)) {
                throw new ModelNotFoundException('Address not found.', 404);
            }

            return response()->json($address, 200);
        }
        catch(ValidationException | ModelNotFoundException | Exception $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 409);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/addresses",
     *      operationId="list",
     *      tags={"Addresses"},
     *      summary="Get all addresses information",
     *      description="Returns address data",
     *      @OA\Response(response=200, description="successful operation"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=409, description="Conflict"),
     *      @OA\Response(response=500, description="Servor Error"),
     * )
     */
    public function list(Request $request): JsonResponse
    {
        try {
//            $request->validate([
//                'limit'         => 'int|required_with:page',
//                'page'          => 'int|required_with:limit',
//                'items_id'      => 'json'
//            ]);

            $resultSet = Address::select('addresses.*');

            $this->filter($resultSet, ['date', 'itemsId'])->paginate($resultSet);

            return response()->json($resultSet->get(), 200, ['pagination' => $this->pagination]);
        }
        catch(ValidationException | ModelNotFoundException | Exception $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 409);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/addresses",
     *      operationId="create",
     *      tags={"Addresses"},
     *      summary="Post a new address",
     *      description="Create a new address",
     *      @OA\Parameter(name="title", description="Address title", required=true, in="query"),
     *      @OA\Parameter(name="address_line_1", description="Address line 1", required=true, in="query"),
     *      @OA\Parameter(name="address_line_2", description="Address line 2", in="query"),
     *      @OA\Parameter(name="zip_code", description="Zip code", required=true, in="query"),
     *      @OA\Parameter(name="city", description="City", required=true, in="query"),
     *      @OA\Parameter(name="country", description="Address country", required=true, in="query"),
     *      @OA\Response(response=201,description="Account created"),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title'                     => 'required|string',
                'address_line_1'            => 'required|string',
                'address_line_2'            => 'string',
                'zip_code'                  => 'required|string',
                'city'                      => 'required|string',
                'country'                   => 'required|string',
            ]);

            DB::beginTransaction();

            $geocoding = app('geocoder')->geocode($request->input('address_line_1').', '.$request->input('zip_code').''.$request->input('city').''.$request->input('country'))->get()->first();

            $address = new Address();
            $address->id                        = $this->generateId('address', $address);
            $address->title                     = $request->input('title');
            $address->address_line_1            = $request->input('address_line_1');
            $address->address_line_2            = $request->input('address_line_2');
            $address->zip_code                  = $request->input('zip_code');
            $address->city                      = $request->input('city');
            $address->country                   = $request->input('country');
            $address->latitude                  = $geocoding->getCoordinates()->getLatitude();
            $address->longitude                 = $geocoding->getCoordinates()->getLongitude();

            $address->save();

            DB::commit();

            return response()->json($address->fresh(), 201);
        }
        catch(JsonEncodingException $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), $e->getCode());
        }
        catch(ValidationException $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 409);
        }
        catch(Exception $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Patch (
     *      path="/api/addresses/{id}",
     *      operationId="update",
     *      tags={"Addresses"},
     *      summary="Patch a address",
     *      description="Update an address",
     *      @OA\Parameter(name="title", description="Address title", in="query"),
     *      @OA\Parameter(name="address_line_1", description="Address line 1", in="query"),
     *      @OA\Parameter(name="address_line_2", description="Address line 2", in="query"),
     *      @OA\Parameter(name="zip_code", description="Zip code", in="query"),
     *      @OA\Parameter(name="city", description="City", in="query"),
     *      @OA\Parameter(name="country", description="Address country", in="query"),
     *      @OA\Response(
     *          response=200,
     *          description="Account updated"
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'title'                     => 'required|string',
                'address_line_1'            => 'required|string',
                'address_line_2'            => 'string',
                'zip_code'                  => 'required|string',
                'city'                      => 'required|string',
                'country'                   => 'required|string',
            ]);

            DB::beginTransaction();

            $address = Address::select('addresses.*')->where('id', $request->id)->first();

            if(empty($address)) {
                throw new ModelNotFoundException('Address not found.', 404);
            }

            $address->title                     = $request->input('title', $address->getOriginal('title'));
            $address->address_line_1            = $request->input('address_line_1', $address->getOriginal('address_line_1'));
            $address->address_line_2            = $request->input('address_line_2', $address->getOriginal('address_line_2'));
            $address->zip_code                  = $request->input('zip_code', $address->getOriginal('zip_code'));
            $address->city                      = $request->input('city', $address->getOriginal('city'));
            $address->country                   = $request->input('country', $address->getOriginal('country'));

            $geocoding = app('geocoder')->geocode($address->address_line_1.', '.$address->zip_code.''.$address->city.''.$address->country)->get()->first();

            $address->latitude                  = $geocoding->getCoordinates()->getLatitude();
            $address->longitude                 = $geocoding->getCoordinates()->getLongitude();

            $address->save();

            DB::commit();

            return response()->json($address, 200);
        }
        catch(ModelNotFoundException | JsonEncodingException $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), $e->getCode());
        }
        catch(ValidationException $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 409);
        }
        catch(Exception $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete  (
     *      path="/api/addresses/{id}",
     *      operationId="delete",
     *      tags={"Addresses"},
     *      summary="Delete a address",
     *      description="Soft delete a address",
     *      @OA\Parameter(
     *          name="id",
     *          description="Account id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="String"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Account deleted"
     *       ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function delete(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $resultSet = Address::select('addresses.*')->where('id', $request->id);

            $address = $resultSet->first();

            if(empty($address)) {
                throw new ModelNotFoundException('Address not found.', 404);
            }

            $address->delete();

            DB::commit();

            return response()->json($address->fresh(), 200);
        }
        catch(ModelNotFoundException $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), $e->getCode());
        }
        catch(ValidationException $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 409);
        }
        catch(AuthenticationException $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 403);
        }
        catch(Exception $e) {
            Bugsnag::notifyException($e);
            return response()->json($e->getMessage(), 500);
        }
    }
}
