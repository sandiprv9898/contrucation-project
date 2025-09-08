<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Domain\User\Models\Company;
use Exception;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_companies' => Company::count(),
                'active_users' => User::whereNotNull('email_verified_at')->count(),
                'user_roles' => [
                    'admin' => 1,
                    'project_manager' => 2,
                    'supervisor' => 3,
                    'field_worker' => User::count() - 6
                ],
                'recent_users' => User::latest()
                    ->take(5)
                    ->get()
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role ?? 'field_worker',
                            'company_name' => 'Construction Co.',
                            'created_at' => $user->created_at ? $user->created_at->diffForHumans() : 'Just now',
                        ];
                    }),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function recentActivity(): JsonResponse
    {
        try {
            // For now, just return user registrations as activity
            $recentUsers = User::latest()
                ->take(10)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'type' => 'user_registered',
                        'description' => "New user {$user->name} joined",
                        'user' => [
                            'name' => $user->name,
                            'role' => $user->role ?? 'field_worker',
                            'company_name' => 'Construction Co.',
                        ],
                        'created_at' => $user->created_at ? $user->created_at->diffForHumans() : 'Just now',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $recentUsers
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent activity',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}