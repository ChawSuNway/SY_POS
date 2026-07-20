<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesLogo;
use App\Models\Shop;
use Illuminate\Http\Request;

/**
 * ဆိုင် Admin — မိမိဆိုင်၏ အချက်အလက် / logo ပြင်ဆင်ခြင်း (current shop)။
 */
class SettingController extends Controller
{
    use ManagesLogo;

    public function edit()
    {
        $shop = current_shop();
        abort_unless($shop, 404);

        return view('settings.edit', compact('shop'));
    }

    public function update(Request $request)
    {
        $shop = current_shop();
        abort_unless($shop, 404);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'name_en'     => ['nullable', 'string', 'max:150'],
            'tagline'     => ['nullable', 'string', 'max:200'],
            'tagline_en'  => ['nullable', 'string', 'max:200'],
            'address'     => ['nullable', 'string', 'max:300'],
            'phone'       => ['nullable', 'string', 'max:100'],
            'logo'        => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $shop->fill([
            'name'       => $data['name'],
            'name_en'    => $data['name_en'] ?? null,
            'tagline'    => $data['tagline'] ?? null,
            'tagline_en' => $data['tagline_en'] ?? null,
            'address'    => $data['address'] ?? null,
            'phone'      => $data['phone'] ?? null,
        ]);

        if ($request->hasFile('logo')) {
            $this->deleteLogoFile($shop->logo);
            $shop->logo = $this->storeLogo($request->file('logo'));
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteLogoFile($shop->logo);
            $shop->logo = null;
        }

        $shop->save();

        return redirect()->route('settings.edit')->with('success', __('app.saved'));
    }
}
