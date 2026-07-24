<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesLogo;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Super Admin — ဆိုင်များ စီမံခန့်ခွဲ (ဖန်တီး / ပြင် / ပိတ်)။
 */
class ShopController extends Controller
{
    use ManagesLogo;

    public function index()
    {
        $shops = Shop::withCount(['users', 'products', 'sales'])
            ->orderBy('id')->get();

        return view('shops.index', compact('shops'));
    }

    public function create()
    {
        return view('shops.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'name_en'        => ['nullable', 'string', 'max:150'],
            'tagline'        => ['nullable', 'string', 'max:200'],
            'phone'          => ['nullable', 'string', 'max:100'],
            'address'        => ['nullable', 'string', 'max:300'],
            'logo'           => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:2048'],
            // ဆိုင်၏ ပထမ Admin account
            'admin_name'     => ['required', 'string', 'max:150'],
            'admin_email'    => ['required', 'email', 'max:190', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $shop = new Shop([
                'name'       => $data['name'],
                'name_en'    => $data['name_en'] ?? null,
                'tagline'    => $data['tagline'] ?? null,
                'phone'      => $data['phone'] ?? null,
                'address'    => $data['address'] ?? null,
                'is_active'  => true,
            ]);
            if ($request->hasFile('logo')) {
                $shop->logo = $this->storeLogo($request->file('logo'));
            }
            $shop->save();

            User::create([
                'name'      => $data['admin_name'],
                'email'     => $data['admin_email'],
                'password'  => $data['admin_password'],
                'role'      => User::ROLE_ADMIN,
                'shop_id'   => $shop->id,
                'is_active' => true,
            ]);
        });

        return redirect()->route('shops.index')->with('success', __('app.saved'));
    }

    public function edit(Shop $shop)
    {
        return view('shops.edit', compact('shop'));
    }

    public function update(Request $request, Shop $shop)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'name_en'     => ['nullable', 'string', 'max:150'],
            'tagline'     => ['nullable', 'string', 'max:200'],
            'tagline_en'  => ['nullable', 'string', 'max:200'],
            'phone'       => ['nullable', 'string', 'max:100'],
            'address'     => ['nullable', 'string', 'max:300'],
            'is_active'   => ['boolean'],
            'logo'        => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $shop->fill([
            'name'       => $data['name'],
            'name_en'    => $data['name_en'] ?? null,
            'tagline'    => $data['tagline'] ?? null,
            'tagline_en' => $data['tagline_en'] ?? null,
            'phone'      => $data['phone'] ?? null,
            'address'    => $data['address'] ?? null,
            'is_active'  => $request->boolean('is_active'),
        ]);

        if ($request->hasFile('logo')) {
            $this->deleteLogoFile($shop->logo);
            $shop->logo = $this->storeLogo($request->file('logo'));
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteLogoFile($shop->logo);
            $shop->logo = null;
        }

        $shop->save();

        return redirect()->route('shops.index')->with('success', __('app.saved'));
    }

    /** Super Admin — ဤဆိုင်ကို ဝင်စီမံ (session context သတ်မှတ်) */
    public function enter(Shop $shop)
    {
        session(['sa_shop_id' => $shop->id]);

        return redirect()->route('dashboard')
            ->with('success', __('app.now_managing').' — '.$shop->displayName());
    }

    /** Super Admin — ဆိုင်စီမံမှ ထွက် (platform သို့ ပြန်) */
    public function leave()
    {
        session()->forget('sa_shop_id');

        return redirect()->route('shops.index');
    }

    public function destroy(Shop $shop)
    {
        // data ရှိသော ဆိုင်ကို ဖျက်၍မရ — ပိတ်ထားရန် အကြံပြု
        if ($shop->products()->exists() || $shop->sales()->exists()) {
            return back()->with('error', __('app.shop_has_data'));
        }

        DB::transaction(function () use ($shop) {
            $this->deleteLogoFile($shop->logo);
            $shop->users()->delete();
            $shop->delete();
        });

        return back()->with('success', __('app.deleted'));
    }
}
