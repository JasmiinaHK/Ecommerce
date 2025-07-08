<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with statistics and recent activity.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // Basic statistics
        $stats = [
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_orders' => Order::count(),
            'total_customers' => User::where('is_admin', false)->count(),
            'total_revenue' => Order::where('status', 'completed')->sum('grand_total'),
            'pending_orders' => Order::where('status', 'pending')->count(),
        ];

        // Recent orders with user and items relationship
        $recentOrders = Order::with(['user', 'items.product'])
            ->latest()
            ->take(10)
            ->get();

        // Monthly revenue data for the last 6 months
        $monthlyRevenue = $this->getMonthlyRevenueData(5);

        // Top selling products with order count and primary image
        $topProducts = Product::with(['primaryImage'])
            ->select([
                'products.id',
                'products.name',
                'products.price',
                'products.sale_price',
                'products.sku',
                'products.created_at',
                'products.updated_at'
            ])
            ->selectRaw('COUNT(order_items.id) as order_items_count')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy([
                'products.id',
                'products.name',
                'products.price',
                'products.sale_price',
                'products.sku',
                'products.created_at',
                'products.updated_at'
            ])
            ->orderBy('order_items_count', 'desc')
            ->take(5)
            ->get();

        // Recent customers with their order counts
        $recentCustomers = User::where('is_admin', false)
            ->withCount('orders')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'monthlyRevenue' => $monthlyRevenue,
            'topProducts' => $topProducts,
            'recentCustomers' => $recentCustomers,
        ]);
    }

    /**
     * Get monthly revenue data for the specified number of months.
     *
     * @param int $months Number of months to retrieve data for
     * @return array
     */
    protected function getMonthlyRevenueData($months = 5): array
    {
        $revenueData = [];
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subMonths($months);

        // Initialize all months with 0 revenue
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $monthKey = $currentDate->format('M Y');
            $revenueData[$monthKey] = [
                'month' => $monthKey,
                'revenue' => 0,
            ];
            $currentDate->addMonth();
        }

        // Get actual revenue data from the database
        $monthlyRevenue = Order::select(
                DB::raw('DATE_FORMAT(created_at, "%b %Y") as month'),
                DB::raw('SUM(grand_total) as total')
            )
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Merge actual data with initialized months
        foreach ($monthlyRevenue as $month => $revenue) {
            if (isset($revenueData[$month])) {
                $revenueData[$month]['revenue'] = (float) $revenue;
            }
        }

        return array_values($revenueData);
    }

    /**
     * Get the top selling products with sales count.
     *
     * @param int $limit Number of products to return
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getTopSellingProducts($limit = 5)
    {
        return Product::with(['primaryImage'])
            ->select([
                'products.id',
                'products.name',
                'products.price',
                'products.sale_price',
                'products.sku',
                'products.created_at',
                'products.updated_at'
            ])
            ->selectRaw('COUNT(order_items.id) as order_items_count')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy([
                'products.id',
                'products.name',
                'products.price',
                'products.sale_price',
                'products.sku',
                'products.created_at',
                'products.updated_at'
            ])
            ->orderBy('order_items_count', 'desc')
            ->take($limit)
            ->get();
    }
}
