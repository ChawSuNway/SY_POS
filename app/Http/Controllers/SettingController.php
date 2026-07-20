<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /** ဆိုင်အချက်အလက် ချိန်ညှိသည့် screen */
    public function edit()
    {
        return view('settings.edit');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'shop_name'       => ['required', 'string', 'max:150'],
            'shop_name_en'    => ['nullable', 'string', 'max:150'],
            'shop_tagline'    => ['nullable', 'string', 'max:200'],
            'shop_tagline_en' => ['nullable', 'string', 'max:200'],
            'shop_address'    => ['nullable', 'string', 'max:300'],
            'shop_phone'      => ['nullable', 'string', 'max:100'],
            'logo'            => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:2048'],
            'remove_logo'     => ['nullable', 'boolean'],
        ]);

        $pairs = [
            'shop_name'       => $data['shop_name'],
            'shop_name_en'    => $data['shop_name_en'] ?? '',
            'shop_tagline'    => $data['shop_tagline'] ?? '',
            'shop_tagline_en' => $data['shop_tagline_en'] ?? '',
            'shop_address'    => $data['shop_address'] ?? '',
            'shop_phone'      => $data['shop_phone'] ?? '',
        ];

        // logo — အသစ်တင်လျှင် သိမ်း / ဖယ်လျှင် ဖျက်
        if ($request->hasFile('logo')) {
            $this->deleteCurrentLogo();

            $dir = public_path('uploads');
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $file = $request->file('logo');
            $name = 'logo_'.time().'.'.$file->getClientOriginalExtension();
            $file->move($dir, $name);

            $pairs['shop_logo'] = 'uploads/'.$name;
        } elseif ($request->boolean('remove_logo')) {
            $this->deleteCurrentLogo();
            $pairs['shop_logo'] = '';
        }

        Setting::putMany($pairs);

        return redirect()->route('settings.edit')->with('success', __('app.saved'));
    }

    private function deleteCurrentLogo(): void
    {
        $current = Setting::get('shop_logo');
        if ($current && is_file(public_path($current))) {
            @unlink(public_path($current));
        }
    }
}
