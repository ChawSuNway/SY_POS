# 🌾 Shwe Yee POS — ဆန်နှင့် ဆီ ရောင်းဝယ်ရေး POS

ဆန် (Rice) နှင့် ဆီ (Oil) အရောင်းဆိုင်အတွက် Point-of-Sale + Inventory + Report စနစ်။
Laravel 12 + MySQL + Blade ဖြင့် တည်ဆောက်ထားသည်။ မြန်မာ / English နှစ်ဘာသာ။

## အင်္ဂါရပ်များ (Features)

- **ဆန်** — အိတ် / ပြည် / ဗူး ခွဲရောင်း (base unit = ဗူး ၊ ၁ ပြည် = ၈ ဗူး)
- **ဆီ** — ပုံး (၁၀ ပိဿာ) / ပိဿာ / ဆယ်သား ခွဲရောင်း (base unit = ဆယ်သား ၊ ၁ ပိဿာ = ၁၀ ဆယ်သား ၊ ၁ ပုံး = ၁၀၀ ဆယ်သား)
- **Dynamic** အမျိုးအစား (Category) နှင့် တံဆိပ် (Brand) — ကိုယ်တိုင် ရိုက်ထည့်နိုင်
- **Unit တစ်ခုချင်းစီ** ရောင်းစျေး — **Admin** သာ သတ်မှတ်နိုင်
- **အဝယ်စာရင်း** (Purchases) — လက်ကျန်တိုး + **Weighted-Average** ကုန်ကျစရိတ် အလိုအလျောက် ပြန်တွက်
- **အရောင်းစာရင်း** (Sales/POS) — ယူနစ်ရွေး၍ ရောင်းချ ၊ လက်ကျန်စစ် ၊ ဘောက်ချာ ပရင့်
- **Report များ** — အရောင်း ၊ အဝယ် ၊ အရှုံးအမြတ် (product/brand/category/type အလိုက်) ၊ လက်ကျန် ၊ လက်ကျန်နည်း
- **Role-Based Authentication** — Cashier / Manager / Admin

### ခွင့်ပြုချက် (Permissions)

| လုပ်ဆောင်ချက် | Cashier | Manager | Admin |
|---|:---:|:---:|:---:|
| POS ရောင်းချ / ဘောက်ချာ | ✅ (မိမိအရောင်း) | ✅ | ✅ |
| အဝယ် ၊ ကုန်ပစ္စည်း ၊ Report | ❌ | ✅ | ✅ |
| ရောင်းစျေး သတ်မှတ် | ❌ | ❌ | ✅ |
| အမျိုးအစား ၊ တံဆိပ် ၊ User စီမံ | ❌ | ❌ | ✅ |

## တပ်ဆင်ခြင်း (Setup — XAMPP)

```bash
# 1. MySQL (XAMPP) စတင်ပြီး database ဆောက်ရန်
mysql -u root -e "CREATE DATABASE shweyee_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Dependencies (already installed) — လိုအပ်လျှင်
composer install

# 3. Migrate + demo data
php artisan migrate:fresh --seed

# 4. Server run
php artisan serve
# http://127.0.0.1:8000
```

`.env` — `DB_DATABASE=shweyee_pos`, `DB_USERNAME=root`, `DB_PASSWORD=` (XAMPP default)။

## Demo အကောင့်များ (password အားလုံး `password`)

| Role | Email |
|---|---|
| Admin | `admin@shweyee.test` |
| Manager | `manager@shweyee.test` |
| Cashier | `cashier@shweyee.test` |

## Architecture

- **Products** — `type` (rice/oil) + `category_id` + `brand_id` ပေါင်းစပ်။ `base_unit` ဖြင့် လက်ကျန် (`stock`) နှင့် `avg_cost` သိမ်း။
- **Product Units** — ရောင်းချနိုင်သည့် ယူနစ်တစ်ခုစီ (`label`, `factor` = base-unit အရေအတွက်, `selling_price`)။
- **InventoryService** — `receiveStock()` (weighted-avg ပြန်တွက်) ၊ `issueStock()` (COGS return) ၊ `adjustStock()` — DB transaction + row lock ဖြင့်။
- **Sales** — `total_cost` (COGS) + `profit` သိမ်း၍ အရှုံးအမြတ် တွက်ချက်။
- **Stock Movements** — ဝင်/ထွက် အားလုံး audit trail။

Weighted-average formula:
`new_avg = (old_stock × old_avg + qty_base × unit_cost_base) ÷ (old_stock + qty_base)`
