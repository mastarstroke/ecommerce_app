<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserDashboardController extends Controller
{
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
        
        return view('dashboard.index', compact(
            'user', 'stats', 'recentOrders', 
            'cartSummary', 'recommendedProducts', 'wishlistItems'
        ));
    }

    public function editProfile()
    {
        $user = auth()->user();
        return view('dashboard.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user->update($request->only(['name', 'email', 'phone', 'address']));

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }


    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->back()->with('success', 'Password updated successfully!');
    }


    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = auth()->user();

        // Delete old avatar if not default
        if ($user->avatar && !str_contains($user->avatar, 'ui-avatars.com')) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return redirect()->back()->with('success', 'Profile picture updated successfully!');
    }


    public function addToWishlist($productId)
    {
        $wishlist = session()->get('wishlist', []);
        
        if (!in_array($productId, $wishlist)) {
            $wishlist[] = $productId;
            session()->put('wishlist', $wishlist);
            $message = 'Product added to wishlist!';
        } else {
            $message = 'Product already in wishlist!';
        }
        
        return redirect()->back()->with('success', $message);
    }


    public function removeFromWishlist($productId)
    {
        $wishlist = session()->get('wishlist', []);
        
        if (($key = array_search($productId, $wishlist)) !== false) {
            unset($wishlist[$key]);
            session()->put('wishlist', array_values($wishlist));
        }
        
        return redirect()->back()->with('success', 'Product removed from wishlist!');
    }
}