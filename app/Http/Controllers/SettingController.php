<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\Request;

/**
 * ဆိုင် Admin — မိမိဆိုင်၏ အချက်အလက် / logo ပြင်ဆင်ခြင်း (current shop)။
 */
class SettingController extends Controller
{
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
            $this->deleteLogo($shop);
            $shop->logo = $this->storeLogo($request->file('logo'));
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteLogo($shop);
            $shop->logo = null;
        }

        $shop->save();

        return redirect()->route('settings.edit')->with('success', __('app.saved'));
    }

    private function storeLogo($file): string
    {
        $dir = public_path('uploads');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = 'logo_'.time().'_'.mt_rand(1000, 9999).'.'.$file->getClientOriginalExtension();
        $file->move($dir, $name);

        return 'uploads/'.$name;
    }

    private function deleteLogo(Shop $shop): void
    {
        if ($shop->logo && is_file(public_path($shop->logo))) {
            @unlink(public_path($shop->logo));
        }
    }
}
