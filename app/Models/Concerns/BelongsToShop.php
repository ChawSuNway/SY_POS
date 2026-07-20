<?php

namespace App\Models\Concerns;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;

/**
 * Multi-tenant scoping — model query များကို login ၀င်ထားသူ၏ ဆိုင်ဖြင့် အလိုအလျောက် စစ်ထုတ်ပြီး၊
 * create လုပ်ရာတွင် shop_id ကို အလိုအလျောက် ဖြည့်ပေးသည်။
 *
 * shop context မရှိလျှင် (super_admin သို့မဟုတ် console/guest) scope မသုံး — data အားလုံး မြင်ရသည်။
 * ထို့ကြောင့် super_admin (manage-only) သည် သီးသန့် screen များတွင်သာ cross-shop data ကို ကိုင်တွယ်သည်။
 */
trait BelongsToShop
{
    public static function bootBelongsToShop(): void
    {
        static::addGlobalScope('shop', function (Builder $builder) {
            if ($shopId = current_shop_id()) {
                $builder->where($builder->getModel()->getTable().'.shop_id', $shopId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->shop_id) && ($shopId = current_shop_id())) {
                $model->shop_id = $shopId;
            }
        });
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /** global scope ကို ကျော်၍ query (super_admin cross-shop အသုံးပြုရန်) */
    public static function forShop(?int $shopId): Builder
    {
        $query = static::withoutGlobalScope('shop');

        return $shopId ? $query->where('shop_id', $shopId) : $query;
    }
}
