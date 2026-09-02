<?php

namespace Modules\Store\Services;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Store\Models\Country;
use Yajra\DataTables\DataTables;

class CountryService
{
    public function getCountryDataTable(Request $request)
    {
        $query = Country::query()->orderBy('name');

        return DataTables::of($query)
            ->addColumn('action', function (Country $country) {
                return view('components.action-buttons', [
                    'id' => $country->id,
                    'edit' => 'countryEdit',
                    'delete' => 'countryDelete',
                ])->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function saveCountry(array $data): JsonResponse
    {
        try {
            return DB::transaction(function () use ($data) {
                $countryId = $data['country_id'] ?? null;
                unset($data['country_id']);

                if ($countryId) {
                    $country = Country::findOrFail($countryId);
                    $country->update($data);
                    $message = 'Country updated successfully.';
                } else {
                    $country = Country::create($data);
                    $message = 'Country created successfully.';
                }

                return ApiResponse::success($country->fresh(), $message);
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error saving country: '.$e->getMessage(), 500);
        }
    }

    public function getCountryById(int $id): JsonResponse
    {
        try {
            $country = Country::findOrFail($id);

            return ApiResponse::success($country);
        } catch (\Exception $e) {
            return ApiResponse::notFound('Country not found.');
        }
    }

    public function deleteCountry(int $id): JsonResponse
    {
        try {
            return DB::transaction(function () use ($id) {
                $country = Country::findOrFail($id);
                $country->delete();

                return ApiResponse::success(null, 'Country deleted successfully.');
            });
        } catch (\Exception $e) {
            return ApiResponse::error('Error deleting country: '.$e->getMessage(), 500);
        }
    }

    public function getAllCountries(): array
    {
        return Country::orderBy('name')->get()->toArray();
    }
}
