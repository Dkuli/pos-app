<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\Tenant;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Create a new notification
     *
     * @param array $data
     * @return Notification
     */
    public function create(array $data): Notification
    {
        return Notification::create($data);
    }

    /**
     * Mark notification as read
     *
     * @param Notification $notification
     * @return Notification
     */
    public function markAsRead(Notification $notification): Notification
    {
        $notification->update([
            'read_at' => Carbon::now(),
        ]);
        return $notification;
    }

    /**
     * Delete notification
     *
     * @param Notification $notification
     * @return bool
     */
    public function deleteNotification(Notification $notification): bool
    {
        return $notification->delete();
    }

    /**
     * Get unread notifications for a user
     *
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnreadNotificationsForUser(User $user, int $limit = 10)
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all notifications for a user
     *
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllNotificationsForUser(User $user, int $limit = 50)
    {
        return Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Create low stock notifications for a tenant
     *
     * @param Tenant $tenant
     * @return int Number of notifications created
     */
    public function createLowStockNotifications(Tenant $tenant): int
    {
        $lowStockProducts = $tenant->products()
            ->where('track_inventory', true)
            ->whereRaw('stock_alert_quantity >= (SELECT COALESCE(SUM(quantity), 0) FROM product_inventories WHERE product_id = products.id)')
            ->get();

        $count = 0;
        $adminUsers = $tenant->users()->where('role', 'admin')->orWhere('role', 'inventory')->get();

        foreach ($lowStockProducts as $product) {
            foreach ($adminUsers as $user) {
                // Check if notification already exists for this product and user
                $existingNotification = Notification::where('user_id', $user->id)
                    ->where('type', 'low_stock')
                    ->where('data->product_id', $product->id)
                    ->whereDate('created_at', Carbon::today())
                    ->whereNull('read_at')
                    ->first();

                if (!$existingNotification) {
                    $this->create([
                        'user_id' => $user->id,
                        'type' => 'low_stock',
                        'title' => 'Low Stock Alert',
                        'message' => "Product '{$product->name}' is running low on stock.",
                        'data' => [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'current_quantity' => $product->inventories()->sum('quantity'),
                            'alert_quantity' => $product->stock_alert_quantity
                        ],
                    ]);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Create transaction notifications
     *
     * @param int $transactionId
     * @param string $type
     * @param array $data
     * @return Notification
     */
    public function createTransactionNotification(int $transactionId, string $type, array $data): Notification
    {
        return Notification::create([
            'user_id' => $data['user_id'],
            'type' => $type,
            'title' => $data['title'],
            'message' => $data['message'],
            'data' => array_merge(['transaction_id' => $transactionId], $data['additional_data'] ?? []),
        ]);
    }

    /**
     * Create system notifications for all users with specific role
     *
     * @param int $tenantId
     * @param string $role
     * @param string $title
     * @param string $message
     * @param array $data
     * @return int Number of notifications created
     */
    public function createSystemNotificationForRole(
        int $tenantId,
        string $role,
        string $title,
        string $message,
        array $data = []
    ): int {
        $users = User::where('tenant_id', $tenantId)
            ->where('role', $role)
            ->where('is_active', true)
            ->get();

        $count = 0;
        foreach ($users as $user) {
            $this->create([
                'user_id' => $user->id,
                'type' => 'system',
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]);
            $count++;
        }

        return $count;
    }
}
