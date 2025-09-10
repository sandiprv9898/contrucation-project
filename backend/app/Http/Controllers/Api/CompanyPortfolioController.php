<?php

namespace App\Http\Controllers\Api;

use App\Domain\Companies\Models\CompanyPortfolio;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyPortfolio\StorePortfolioItemRequest;
use App\Http\Requests\CompanyPortfolio\UpdatePortfolioItemRequest;
use App\Http\Resources\CompanyPortfolioResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompanyPortfolioController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $company = $user->company;
        
        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $query = $company->portfolioItems();

        if ($request->has('category')) {
            $query->byCategory($request->get('category'));
        }

        if ($request->boolean('featured_only')) {
            $query->featured();
        }

        if ($request->boolean('active_only', true)) {
            $query->active();
        }

        $items = $query->ordered()->get();

        return response()->json([
            'success' => true,
            'data' => CompanyPortfolioResource::collection($items)
        ]);
    }

    public function store(StorePortfolioItemRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $company = $user->company;

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = $request->validated();

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $path = $file->store("companies/{$company->id}/portfolio", 'public');
                $data['image_path'] = $path;
            }

            $item = CompanyPortfolio::create([
                'company_id' => $company->id,
                ...$data
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Portfolio item created successfully',
                'data' => new CompanyPortfolioResource($item)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create portfolio item',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(CompanyPortfolio $portfolioItem): JsonResponse
    {
        $user = request()->user();
        
        if ($portfolioItem->company_id !== $user->company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to portfolio item'
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'data' => new CompanyPortfolioResource($portfolioItem)
        ]);
    }

    public function update(UpdatePortfolioItemRequest $request, CompanyPortfolio $portfolioItem): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            
            if ($portfolioItem->company_id !== $user->company->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to portfolio item'
                ], Response::HTTP_FORBIDDEN);
            }

            $data = $request->validated();

            if ($request->hasFile('image')) {
                if ($portfolioItem->image_path && Storage::disk('public')->exists($portfolioItem->image_path)) {
                    Storage::disk('public')->delete($portfolioItem->image_path);
                }

                $file = $request->file('image');
                $path = $file->store("companies/{$portfolioItem->company_id}/portfolio", 'public');
                $data['image_path'] = $path;
            }

            $portfolioItem->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Portfolio item updated successfully',
                'data' => new CompanyPortfolioResource($portfolioItem->fresh())
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update portfolio item',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(CompanyPortfolio $portfolioItem): JsonResponse
    {
        try {
            $user = request()->user();
            
            if ($portfolioItem->company_id !== $user->company->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to portfolio item'
                ], Response::HTTP_FORBIDDEN);
            }

            if ($portfolioItem->image_path && Storage::disk('public')->exists($portfolioItem->image_path)) {
                Storage::disk('public')->delete($portfolioItem->image_path);
            }

            $portfolioItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Portfolio item deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete portfolio item',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function reorder(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            $company = $user->company;

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|string|exists:company_portfolio,id',
                'items.*.display_order' => 'required|integer|min:0'
            ]);

            foreach ($request->input('items') as $item) {
                CompanyPortfolio::where('id', $item['id'])
                    ->where('company_id', $company->id)
                    ->update(['display_order' => $item['display_order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Portfolio items reordered successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder portfolio items',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function toggleFeatured(CompanyPortfolio $portfolioItem): JsonResponse
    {
        try {
            $user = request()->user();
            
            if ($portfolioItem->company_id !== $user->company->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to portfolio item'
                ], Response::HTTP_FORBIDDEN);
            }

            $portfolioItem->update([
                'is_featured' => !$portfolioItem->is_featured
            ]);

            return response()->json([
                'success' => true,
                'message' => $portfolioItem->is_featured 
                    ? 'Portfolio item featured successfully'
                    : 'Portfolio item unfeatured successfully',
                'data' => new CompanyPortfolioResource($portfolioItem)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle portfolio item featured status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getCategories(): JsonResponse
    {
        $categories = [
            'project' => 'Project',
            'certification' => 'Certification',
            'award' => 'Award',
            'team' => 'Team Member',
            'testimonial' => 'Testimonial',
            'case_study' => 'Case Study'
        ];

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}