<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * ဂိုဒေါင်လက်ကျန် နှင့် Weighted-Average ကုန်ကျစရိတ် စီမံခန့်ခွဲမှု။
 *
 * Base unit ဖြင့်သာ တွက်ချက်သည် (rice: ဗူး / oil: ဆယ်သား)။
 */
class InventoryService
{
    /**
     * ၀ယ်ယူမှု — လက်ကျန်တိုး၍ weighted-average cost ကို ပြန်တွက်သည်။
     *
     * new_avg = (old_stock*old_avg + qty_base*unit_cost_base) / (old_stock + qty_base)
     */
    public function receiveStock(
        Product $product,
        float $qtyBase,
        float $unitCostBase,
        ?int $userId = null,
        $reference = null,
        ?string $note = null
    ): StockMovement {
        return DB::transaction(function () use ($product, $qtyBase, $unitCostBase, $userId, $reference, $note) {
            $product = Product::lockForUpdate()->find($product->id);

            $oldStock = (float) $product->stock;
            $oldAvg   = (float) $product->avg_cost;
            $newStock = $oldStock + $qtyBase;

            $newAvg = $newStock > 0
                ? (($oldStock * $oldAvg) + ($qtyBase * $unitCostBase)) / $newStock
                : $unitCostBase;

            $product->stock    = $newStock;
            $product->avg_cost = $newAvg;
            $product->save();

            return $this->recordMovement($product, 'purchase', $qtyBase, $newStock, $userId, $reference, $note);
        });
    }

    /**
     * ရောင်းချမှု — လက်ကျန်လျှော့သည်။ ရောင်းချချိန် avg_cost ကို ပြန်ပေးသည် (COGS တွက်ရန်)။
     *
     * @return float ရောင်းချချိန် base-unit တစ်ခုလျှင် cost
     */
    public function issueStock(
        Product $product,
        float $qtyBase,
        ?int $userId = null,
        $reference = null,
        ?string $note = null
    ): float {
        return DB::transaction(function () use ($product, $qtyBase, $userId, $reference, $note) {
            $product = Product::lockForUpdate()->find($product->id);

            $costBase = (float) $product->avg_cost;
            $newStock = (float) $product->stock - $qtyBase;

            $product->stock = $newStock;
            $product->save();

            $this->recordMovement($product, 'sale', -$qtyBase, $newStock, $userId, $reference, $note);

            return $costBase;
        });
    }

    /**
     * ဖွင့်လှစ်လက်ကျန် (Opening stock) — စတင်လက်ကျန် နှင့် ကုန်ကျစရိတ်ကို တိုက်ရိုက် သတ်မှတ်သည်။
     *
     * receiveStock ကဲ့သို့ ပေါင်းထည့်ခြင်း မဟုတ်ဘဲ လက်ကျန် နှင့် avg_cost ကို အသစ် "SET" လုပ်သည်။
     */
    public function setOpeningStock(
        Product $product,
        float $qtyBase,
        float $unitCostBase,
        ?int $userId = null,
        ?string $note = null
    ): StockMovement {
        return DB::transaction(function () use ($product, $qtyBase, $unitCostBase, $userId, $note) {
            $product = Product::lockForUpdate()->find($product->id);

            $product->stock    = $qtyBase;
            $product->avg_cost = $unitCostBase;
            $product->save();

            return $this->recordMovement(
                $product,
                'opening',
                $qtyBase,
                $qtyBase,
                $userId,
                null,
                $note ?? 'ဖွင့်လှစ်လက်ကျန် (Opening stock)'
            );
        });
    }

    /** လက်ရှိ လက်ကျန်ကို တိုက်ရိုက် ချိန်ညှိခြင်း (stock-take)။ */
    public function adjustStock(
        Product $product,
        float $newQtyBase,
        ?int $userId = null,
        ?string $note = null
    ): StockMovement {
        return DB::transaction(function () use ($product, $newQtyBase, $userId, $note) {
            $product = Product::lockForUpdate()->find($product->id);

            $delta = $newQtyBase - (float) $product->stock;
            $product->stock = $newQtyBase;
            $product->save();

            return $this->recordMovement($product, 'adjustment', $delta, $newQtyBase, $userId, null, $note);
        });
    }

    private function recordMovement(
        Product $product,
        string $type,
        float $qtyBase,
        float $balanceAfter,
        ?int $userId,
        $reference,
        ?string $note
    ): StockMovement {
        return StockMovement::create([
            'product_id'     => $product->id,
            'type'           => $type,
            'qty_base'       => $qtyBase,
            'balance_after'  => $balanceAfter,
            'reference_type' => $reference ? $reference->getMorphClass() : null,
            'reference_id'   => $reference?->getKey(),
            'user_id'        => $userId,
            'note'           => $note,
        ]);
    }
}
