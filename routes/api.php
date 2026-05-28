<?php

use Illuminate\Support\Facades\Route;

// Auth
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\ReSendController;
use App\Http\Controllers\Auth\PasswordReset\SendOtpController;
use App\Http\Controllers\Auth\PasswordReset\VerifyOtpController;
use App\Http\Controllers\Auth\PasswordReset\ResetPasswordController;
use App\Http\Controllers\Api\Auth\MeController;

// App API
use App\Http\Controllers\Api\Articles\ArticleController as AppArticleController;
use App\Http\Controllers\Api\Comments\CommentController;
use App\Http\Controllers\Api\Reactions\ReactionController;
use App\Http\Controllers\Api\Emergency\SosController;
use App\Http\Controllers\Api\Emergency\MembershipController;
use App\Http\Controllers\Api\Emergency\NotificationController;
use App\Http\Controllers\Api\Emergency\EmergencyCaseController;
use App\Http\Controllers\Api\Emergency\AppChatController;
use App\Http\Controllers\Api\Emergency\AppRatingController;
use App\Http\Controllers\Api\Emergency\AppReportController;
use App\Http\Controllers\Api\AppRoleRequestController;
use App\Http\Controllers\Api\AppProfileController;
use App\Http\Controllers\Api\Reactions\ReactionListController;

// Dashboard
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\PermissionController;
use App\Http\Controllers\Dashboard\Articles\ArticleController as DashboardArticleController;
use App\Http\Controllers\Dashboard\Articles\ArticleApprovalController;
use App\Http\Controllers\Dashboard\Emergency\EmergencyGroupController;
use App\Http\Controllers\Dashboard\Emergency\GroupMemberController;
use App\Http\Controllers\Dashboard\Emergency\GroupChatController;
use App\Http\Controllers\Dashboard\Emergency\PendingGroupController;
use App\Http\Controllers\Dashboard\Emergency\RoleRequestController;
use App\Http\Controllers\Dashboard\Emergency\EmergencyController;
use App\Http\Controllers\Dashboard\Emergency\RatingController;
use App\Http\Controllers\Dashboard\Emergency\ReportController;
use App\Http\Controllers\Dashboard\Emergency\UserBanController;
use App\Http\Controllers\Dashboard\Emergency\AdminActionLogController;

// ─── Auth ─────────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('verify-code', [VerificationController::class, 'verify'])
        ->middleware('verify.temp.token:email_verification');
    Route::post('login', [LoginController::class, 'login']);
    Route::post('logout', LogoutController::class)->middleware('auth:sanctum');
    Route::get('me', MeController::class)->middleware('auth:sanctum');
    Route::post('resend', [ReSendController::class, 'resendOtp'])
        ->middleware('verify.temp.token:email_verification,reset_password_request');

    Route::prefix('password')->group(function () {
        Route::post('forgot', SendOtpController::class);

        Route::post('verify', VerifyOtpController::class)
            ->middleware('verify.temp.token:reset_password_request');

        Route::post('reset', ResetPasswordController::class)
            ->middleware('verify.temp.token:reset_password_confirm');
    });
});

// ─── Public App ───────────────────────────────────────────────────────────────
Route::get('articles', [AppArticleController::class, 'index']);
Route::get('articles/{article}', [AppArticleController::class, 'show'])
    ->where('article', '[0-9]+');
Route::get('users/{user}/articles', [AppArticleController::class, 'byUser'])
    ->where('user', '[0-9]+');
Route::get('articles/{article}/comments', [CommentController::class, 'index'])
    ->where('article', '[0-9]+');
Route::get('articles/{article}/reactions', [ReactionListController::class, 'forArticle'])
    ->where('article', '[0-9]+');
Route::get('comments/{comment}/reactions', [ReactionListController::class, 'forComment']);

// ─── Authenticated App ────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('articles/{article}/comments', [CommentController::class, 'store'])
        ->where('article', '[0-9]+');
    Route::put('comments/{comment}',    [CommentController::class, 'update']);
    Route::patch('comments/{comment}',  [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);
    Route::post('reactions', [ReactionController::class, 'store']);

    // Profile — my own content
    Route::get('profile/reactions', [AppProfileController::class, 'reactions']);
    Route::get('profile/comments',  [AppProfileController::class, 'comments']);
    Route::get('profile/articles',  [AppProfileController::class, 'articles'])->middleware('role:trusted');

    // ─── Trusted-only App routes ──────────────────────────────────────────────
    Route::middleware('role:trusted')->group(function () {

        // SOS
        Route::post('emergency/sos', [SosController::class, 'sos']);
        Route::post('emergency/cases/{emergency}/retry', [SosController::class, 'retry']);

        // Articles
        Route::post('articles',                          [AppArticleController::class, 'store']);
        Route::put('articles/{article}',                 [AppArticleController::class, 'update'])->where('article', '[0-9]+');
        Route::patch('articles/{article}',               [AppArticleController::class, 'update'])->where('article', '[0-9]+');
        Route::delete('articles/{article}',              [AppArticleController::class, 'destroy'])->where('article', '[0-9]+');

        // Articles — Trash
        Route::get('articles/trash',                     [AppArticleController::class, 'trash']);
        Route::post('articles/{id}/restore',             [AppArticleController::class, 'restore'])->where('id', '[0-9]+');
        Route::delete('articles/{id}/force',             [AppArticleController::class, 'forceDestroy'])->where('id', '[0-9]+');

        // Emergency — Membership
        Route::post('emergency/profile/home-location', [MembershipController::class, 'setHomeLocation']);
        Route::get('emergency/my-group', [MembershipController::class, 'myGroup']);

        // Notifications
        Route::get('emergency/notifications', [NotificationController::class, 'index']);
        Route::post('emergency/notifications/{notification}/respond', [NotificationController::class, 'respond']);

        // Cases
        Route::get('emergency/cases/{emergency}', [EmergencyCaseController::class, 'show']);
        Route::post('emergency/cases/{emergency}/resolve', [EmergencyCaseController::class, 'resolve']);

        // Group Chat
        Route::get('emergency/groups/{emergencyGroup}/chat', [AppChatController::class, 'index']);
        Route::post('emergency/groups/{emergencyGroup}/chat', [AppChatController::class, 'store']);

        // Ratings & Reports
        Route::post('emergency/ratings', [AppRatingController::class, 'store']);
        Route::post('emergency/reports', [AppReportController::class, 'store']);

        // Role Requests
        Route::post('role-requests', [AppRoleRequestController::class, 'store']);
        Route::get('role-requests/my', [AppRoleRequestController::class, 'my']);
    });

    // ─── Dashboard ────────────────────────────────────────────────────────────
    Route::prefix('dashboard')->group(function () {

        Route::get('users', [UserController::class, 'index'])->middleware('can:users.view');
        Route::post('users', [UserController::class, 'store'])->middleware('can:users.create');
        Route::put('users/{user}', [UserController::class, 'update'])->middleware('can:users.edit');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('can:users.delete');

        Route::apiResource('roles', RoleController::class)->middleware('role:admin');
        Route::get('roles/{role}/matrix', [RoleController::class, 'matrix'])->middleware('role:admin');
        Route::post('roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions'])->middleware('role:admin');
        Route::get('permissions', [PermissionController::class, 'index'])->middleware('role:admin');

        Route::middleware('role:admin|moderator|creator')->group(function () {
            Route::get('articles/pending', [ArticleApprovalController::class, 'pending']);
            Route::post('articles/{article}/approve', [ArticleApprovalController::class, 'approve'])
                ->where('article', '[0-9]+');
            Route::post('articles/{article}/reject', [ArticleApprovalController::class, 'reject'])
                ->where('article', '[0-9]+');
        });

        Route::get('articles', [DashboardArticleController::class, 'index']);
        Route::post('articles', [DashboardArticleController::class, 'store']);
        Route::get('articles/{article}', [DashboardArticleController::class, 'show'])
            ->where('article', '[0-9]+');
        Route::put('articles/{article}', [DashboardArticleController::class, 'update'])
            ->where('article', '[0-9]+');
        Route::delete('articles/{article}', [DashboardArticleController::class, 'destroy'])
            ->where('article', '[0-9]+');

        Route::prefix('emergency')->group(function () {
            Route::get('groups', [EmergencyGroupController::class, 'index']);
            Route::get('groups/{emergencyGroup}', [EmergencyGroupController::class, 'show']);
            Route::post('groups', [EmergencyGroupController::class, 'store']);
            Route::put('groups/{emergencyGroup}', [EmergencyGroupController::class, 'update']);
            Route::delete('groups/{emergencyGroup}', [EmergencyGroupController::class, 'destroy']);
            Route::patch('groups/{emergencyGroup}/toggle-active', [EmergencyGroupController::class, 'toggleActive']);

            Route::get('groups/{emergencyGroup}/members', [GroupMemberController::class, 'index']);
            Route::patch('groups/{emergencyGroup}/members/{member}/remove', [GroupMemberController::class, 'remove']);
            Route::patch('groups/{emergencyGroup}/members/{member}/grant-messages', [GroupMemberController::class, 'grantMessages']);

            Route::get('groups/{emergencyGroup}/chat', [GroupChatController::class, 'index']);
            Route::post('groups/{emergencyGroup}/chat', [GroupChatController::class, 'store']);
            Route::delete('groups/{emergencyGroup}/chat/{message}', [GroupChatController::class, 'destroy']);
            Route::patch('groups/{emergencyGroup}/chat/{message}/toggle-spam', [GroupChatController::class, 'toggleSpam']);

            Route::get('groups/{emergencyGroup}/ratings', [RatingController::class, 'index']);
            Route::get('groups/{emergencyGroup}/ratings/stats/{user}', [RatingController::class, 'stats']);

            Route::get('pending-groups', [PendingGroupController::class, 'index']);
            Route::post('pending-groups/{pendingGroupRequest}/approve', [PendingGroupController::class, 'approve']);
            Route::patch('pending-groups/{pendingGroupRequest}/reject', [PendingGroupController::class, 'reject']);

            Route::get('role-requests', [RoleRequestController::class, 'index']);
            Route::get('role-requests/{roleRequest}', [RoleRequestController::class, 'show']);
            Route::post('role-requests/{roleRequest}/approve', [RoleRequestController::class, 'approve']);
            Route::post('role-requests/{roleRequest}/reject', [RoleRequestController::class, 'reject']);

            Route::get('cases', [EmergencyController::class, 'index']);
            Route::get('cases/{emergency}', [EmergencyController::class, 'show']);
            Route::patch('cases/{emergency}/mark-false', [EmergencyController::class, 'markFalse']);

            Route::get('reports', [ReportController::class, 'index']);
            Route::post('reports/{report}/approve', [ReportController::class, 'approve']);
            Route::patch('reports/{report}/reject', [ReportController::class, 'reject']);

            Route::get('bans', [UserBanController::class, 'index']);
            Route::post('bans', [UserBanController::class, 'store']);
            Route::patch('bans/{userBan}/lift', [UserBanController::class, 'lift']);

            Route::get('action-logs', [AdminActionLogController::class, 'index']);
        });
    });
});
