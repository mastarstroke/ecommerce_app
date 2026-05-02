<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserDashboardController extends Controller
{
    use LogsActivity;

    protected $orderRepository;
    protected $productRepository;
    protected $cartService;

    public function __construct(
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        CartService $cartService
    ) {
        $this->middleware('auth');
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
    }

    public function index()
    {
        $user = auth()->user();
        $stats = $user->stats;
        
        $recentOrders = $this->orderRepository->getUserOrders($user->id, 5);
        $cartSummary = $this->cartService->getCartSummary();
        $recommendedProducts = $this->productRepository->getFeaturedProducts(6);
        
        $wishlistIds = session()->get('wishlist', []);
        $wishlistItems = !empty($wishlistIds) ? $this->productRepository->getAll(['ids' => $wishlistIds]) : collect([]);
        
        $this->logActivity(
            'viewed',
            'dashboard',
            'User viewed their dashboard',
            null,
            null,
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'total_orders' => $stats['total_orders'],
                'total_spent' => $stats['total_spent'],
                'completed_orders' => $stats['completed_orders'],
                'cart_items' => $cartSummary['total_quantity'],
                'wishlist_items' => count($wishlistIds),
                'recent_orders_count' => $recentOrders->count()
            ],
            'success'
        );
        
        return view('dashboard.index', compact(
            'user', 'stats', 'recentOrders', 
            'cartSummary', 'recommendedProducts', 'wishlistItems'
        ));
    }

    public function editProfile()
    {
        $user = auth()->user();
        
        $this->logActivity(
            'viewed',
            'profile',
            'User accessed profile edit page',
            null,
            null,
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email
            ],
            'success'
        );
        
        return view('dashboard.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = auth()->user();
            $oldData = $user->toArray();
            
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
            ]);

            $user->update($request->only(['name', 'email', 'phone', 'address']));
            
            $changes = [];
            foreach ($request->only(['name', 'email', 'phone', 'address']) as $key => $value) {
                if (isset($oldData[$key]) && $oldData[$key] != $value) {
                    $changes[$key] = [
                        'old' => $oldData[$key],
                        'new' => $value
                    ];
                }
            }
            
            $this->logActivity(
                'updated',
                'profile',
                'User updated their profile information',
                $oldData,
                $user->toArray(),
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'changes' => $changes,
                    'fields_updated' => array_keys($changes)
                ],
                'success'
            );

            return redirect()->back()->with('success', 'Profile updated successfully!');
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'profile',
                'Failed to update profile: ' . $e->getMessage(),
                $request->all(),
                null,
                [
                    'user_id' => auth()->id(),
                    'error' => $e->getMessage()
                ],
                'failed'
            );
            
            return redirect()->back()->withErrors(['error' => 'Failed to update profile']);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $user = auth()->user();
            
            $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if (!Hash::check($request->current_password, $user->password)) {
                $this->logActivity(
                    'failed',
                    'profile',
                    'Password update failed - incorrect current password',
                    null,
                    null,
                    [
                        'user_id' => $user->id,
                        'user_email' => $user->email
                    ],
                    'failed'
                );
                
                return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect']);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);
            
            $this->logActivity(
                'updated',
                'profile',
                'User updated their password',
                null,
                null,
                [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'password_changed_at' => now()->toDateTimeString()
                ],
                'success'
            );

            return redirect()->back()->with('success', 'Password updated successfully!');
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'profile',
                'Failed to update password: ' . $e->getMessage(),
                null,
                null,
                [
                    'user_id' => auth()->id(),
                    'error' => $e->getMessage()
                ],
                'failed'
            );
            
            return redirect()->back()->withErrors(['error' => 'Failed to update password']);
        }
    }

    public function updateAvatar(Request $request)
    {
        try {
            $user = auth()->user();
            $oldAvatar = $user->avatar;
            
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($user->avatar && !str_contains($user->avatar, 'ui-avatars.com')) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);
            
            $this->logActivity(
                'updated',
                'profile',
                'User updated their profile picture',
                ['avatar' => $oldAvatar],
                ['avatar' => $path],
                [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'old_avatar' => $oldAvatar,
                    'new_avatar' => $path
                ],
                'success'
            );

            return redirect()->back()->with('success', 'Profile picture updated successfully!');
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'profile',
                'Failed to update avatar: ' . $e->getMessage(),
                null,
                null,
                [
                    'user_id' => auth()->id(),
                    'error' => $e->getMessage()
                ],
                'failed'
            );
            
            return redirect()->back()->withErrors(['error' => 'Failed to update profile picture']);
        }
    }

    public function addToWishlist($productId)
    {
        try {
            $wishlist = session()->get('wishlist', []);
            $product = $this->productRepository->findById($productId);
            
            if (!in_array($productId, $wishlist)) {
                $wishlist[] = $productId;
                session()->put('wishlist', $wishlist);
                $message = 'Product added to wishlist!';
                
                $this->logActivity(
                    'created',
                    'wishlist',
                    "Product '{$product->name}' added to wishlist",
                    null,
                    null,
                    [
                        'user_id' => auth()->id(),
                        'user_name' => auth()->user()->name,
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'wishlist_size' => count($wishlist)
                    ],
                    'success'
                );
            } else {
                $message = 'Product already in wishlist!';
                
                $this->logActivity(
                    'viewed',
                    'wishlist',
                    "Attempted to add duplicate product to wishlist",
                    null,
                    null,
                    [
                        'user_id' => auth()->id(),
                        'product_id' => $productId,
                        'product_name' => $product->name
                    ],
                    'info'
                );
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'wishlist',
                'Failed to add product to wishlist: ' . $e->getMessage(),
                null,
                null,
                [
                    'user_id' => auth()->id(),
                    'product_id' => $productId,
                    'error' => $e->getMessage()
                ],
                'failed'
            );
            
            return redirect()->back()->with('error', 'Failed to add to wishlist');
        }
    }

    public function removeFromWishlist($productId)
    {
        try {
            $wishlist = session()->get('wishlist', []);
            $product = $this->productRepository->findById($productId);
            
            if (($key = array_search($productId, $wishlist)) !== false) {
                unset($wishlist[$key]);
                session()->put('wishlist', array_values($wishlist));
                
                $this->logActivity(
                    'deleted',
                    'wishlist',
                    "Product '{$product->name}' removed from wishlist",
                    null,
                    null,
                    [
                        'user_id' => auth()->id(),
                        'user_name' => auth()->user()->name,
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'wishlist_size' => count($wishlist)
                    ],
                    'success'
                );
            }
            
            return redirect()->back()->with('success', 'Product removed from wishlist!');
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'wishlist',
                'Failed to remove product from wishlist: ' . $e->getMessage(),
                null,
                null,
                [
                    'user_id' => auth()->id(),
                    'product_id' => $productId,
                    'error' => $e->getMessage()
                ],
                'failed'
            );
            
            return redirect()->back()->with('error', 'Failed to remove from wishlist');
        }
    }
}