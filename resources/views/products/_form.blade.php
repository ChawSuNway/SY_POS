@php
    $isEdit = isset($product);
    $pType  = old('type', $isEdit ? $product->type : ($presetType ?? 'rice'));
    $existingUnits = $isEdit
        ? $product->units->map(fn($u)=>['id'=>$u->id,'label'=>$u->label,'factor'=>(float)$u->factor,'selling_price'=>(float)$u->selling_price,'is_active'=>(bool)$u->is_active])->values()
        : collect();
    $oldUnits = old('units', $existingUnits->count() ? $existingUnits->toArray() : [
        ['label'=>'','factor'=>1,'selling_price'=>0,'is_active'=>true],
    ]);
@endphp

<div class="grid cols-2">
    <div class="card">
        <div class="card-head"><h3>📦 {{ __('app.products') }}</h3></div>
        <div class="card-body">
            <div class="form-grid">
                <div class="field">
                    <label>{{ __('app.type') }}</label>
                    <select name="type" id="pType">
                        <option value="rice" {{ $pType=='rice'?'selected':'' }}>🌾 {{ __('app.rice') }}</option>
                        <option value="oil"  {{ $pType=='oil'?'selected':'' }}>🛢️ {{ __('app.oil') }}</option>
                    </select>
                </div>
                <div class="field">
                    <label>{{ __('app.base_unit') }}</label>
                    <input type="text" name="base_unit" id="baseUnit"
                           value="{{ old('base_unit', $isEdit ? $product->base_unit : '') }}"
                           placeholder="{{ app()->getLocale()=='my'?'ဥပမာ — ဗူး / ဆယ်သား':'e.g. ဗူး / ဆယ်သား' }}">
                    <div class="hint">{{ app()->getLocale()=='my'?'အသေးဆုံး ရောင်းချသည့် ယူနစ် (လက်ကျန်တွက်ရန်)':'Smallest sellable unit (for stock)' }}</div>
                </div>
                <div class="field">
                    <label>{{ __('app.category') }}</label>
                    <select id="parentCat"><!-- JS populated --></select>
                </div>
                <div class="field">
                    <label>{{ __('app.sub_category') }}</label>
                    <select name="category_id" id="categorySel"><!-- JS populated --></select>
                </div>
                <div class="field">
                    <label>{{ __('app.brand') }}</label>
                    <select name="brand_id" id="brandSel">
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" data-type="{{ $b->type }}"
                                {{ old('brand_id', $isEdit ? $product->brand_id : '')==$b->id?'selected':'' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field full">
                    <label>{{ __('app.name') }} <span class="muted small">({{ app()->getLocale()=='my'?'မဖြည့်လည်းရ':'optional' }})</span></label>
                    <input type="text" name="name" value="{{ old('name', $isEdit ? $product->name : '') }}"
                           placeholder="{{ app()->getLocale()=='my'?'ဥပမာ — အထူး/ရိုးရိုး':'e.g. special grade'}}">
                </div>
                <div class="field">
                    <label>{{ __('app.low_stock_threshold') }}</label>
                    <input type="number" name="low_stock_threshold" min="0" step="0.001"
                           value="{{ old('low_stock_threshold', $isEdit ? qty_fmt($product->low_stock_threshold) : '0') }}">
                    <div class="hint">{{ __('app.base_unit') }} {{ app()->getLocale()=='my'?'ဖြင့်':'unit' }}</div>
                </div>
                <div class="field" style="display:flex;align-items:flex-end">
                    <label class="check"><input type="checkbox" name="is_active" value="1"
                        {{ old('is_active', $isEdit ? $product->is_active : true)?'checked':'' }}> {{ __('app.active') }}</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <h3>📐 {{ __('app.manage_units') }}</h3>
            <button type="button" class="btn ghost sm" id="addUnit">＋ {{ __('app.add_row') }}</button>
        </div>
        <div class="card-body">
            @unless($canSetPrice)
                <div class="alert" style="background:var(--blue-bg);color:var(--blue)">
                    ℹ️ {{ app()->getLocale()=='my'?'ရောင်းစျေးကို Admin သာ သတ်မှတ်နိုင်ပါသည်။':'Only Admin can set selling prices.' }}
                </div>
            @endunless

            <div class="repeater">
                <div class="rep-row" style="grid-template-columns:1.2fr 1fr 1.3fr auto">
                    <div class="rep-head">{{ __('app.unit') }}</div>
                    <div class="rep-head">{{ __('app.factor') }}</div>
                    <div class="rep-head">{{ __('app.selling_price') }}</div>
                    <div></div>
                </div>
                <div id="unitRows"></div>
            </div>
            <div class="hint" style="margin-top:6px">
                {{ app()->getLocale()=='my'
                    ? 'Factor = ယူနစ် ၁ ခုတွင် အခြေခံယူနစ် မည်မျှ ပါဝင်သည် (ဥပမာ ဆီ ၁ ပုံး = ၁၀၀ ဆယ်သား ⇒ factor 100)'
                    : 'Factor = how many base units are in one of this unit (e.g. 1 ပုံး = 100 ဆယ်သား ⇒ factor 100)' }}
            </div>
        </div>
    </div>
</div>

<div class="btn-row" style="margin-top:16px">
    <button type="submit" class="btn primary lg">💾 {{ __('app.save') }}</button>
    <a class="btn ghost lg" href="{{ route('products.index') }}">{{ __('app.cancel') }}</a>
</div>

@push('scripts')
<script>
@php
    $catJson = $categories->map(fn ($c) => [
        'id' => $c->id, 'name' => $c->name, 'type' => $c->type, 'parent_id' => $c->parent_id,
    ])->values();
    $selectedCat = old('category_id', $isEdit ? $product->category_id : null);
@endphp
const OLD_UNITS = @json($oldUnits);
const CAN_PRICE = @json((bool)$canSetPrice);
const CATEGORIES = @json($catJson);
const SELECTED_CAT = @json($selectedCat);
const RICE_PRESET = [
    {label:'အိတ်', factor:384}, {label:'ပြည်', factor:8}, {label:'ဗူး', factor:1},
];
const OIL_PRESET = [
    {label:'ပုံး (၁၀ ပိဿာ)', factor:100}, {label:'ပိဿာ', factor:10}, {label:'ဆယ်သား', factor:1},
];
let uIdx = 0;

function unitRow(data){
    const i = uIdx++;
    const price = CAN_PRICE
        ? `<input type="number" name="units[${i}][selling_price]" min="0" step="1" value="${data.selling_price ?? 0}">`
        : `<input type="number" value="${data.selling_price ?? 0}" disabled title="Admin only">`;
    const div = document.createElement('div');
    div.className = 'rep-row';
    div.style.gridTemplateColumns = '1.2fr 1fr 1.3fr auto';
    div.innerHTML = `
        ${data.id?`<input type="hidden" name="units[${i}][id]" value="${data.id}">`:''}
        <input type="hidden" name="units[${i}][is_active]" value="1">
        <input type="text" name="units[${i}][label]" value="${data.label??''}" placeholder="အိတ်/ပြည်/ဗူး" required>
        <input type="number" name="units[${i}][factor]" min="0.0001" step="0.0001" value="${data.factor??1}" required>
        ${price}
        <button type="button" class="btn danger sm rm">✕</button>`;
    return div;
}

const rows = document.getElementById('unitRows');
function addRow(data){ rows.appendChild(unitRow(data||{label:'',factor:1,selling_price:0})); }

// initial
(OLD_UNITS.length?OLD_UNITS:[{label:'',factor:1,selling_price:0}]).forEach(addRow);

document.getElementById('addUnit').addEventListener('click', ()=>addRow());
rows.addEventListener('click', e=>{
    if(e.target.classList.contains('rm')){
        if(rows.children.length>1) e.target.closest('.rep-row').remove();
    }
});

// type -> filter brands + suggest base unit & preset units (only when empty/new)
const pType = document.getElementById('pType');
function filterOptions(sel, type){
    let firstVisible = null;
    [...sel.options].forEach(o=>{
        const show = o.dataset.type===type;
        o.hidden = !show; o.disabled = !show;
        if(show && firstVisible===null) firstVisible = o;
    });
    if(sel.selectedOptions[0]?.hidden && firstVisible) sel.value = firstVisible.value;
}

// ---- Category cascade (parent -> sub) ----
const parentSel = document.getElementById('parentCat');
const catSel    = document.getElementById('categorySel');
const GENERAL   = @json('— '.__('app.general').' —');

function populateParents(type, selectId){
    parentSel.innerHTML = '';
    CATEGORIES.filter(c=>c.parent_id===null && c.type===type).forEach(c=>{
        const o = new Option(c.name, c.id);
        if(selectId && c.id===selectId) o.selected = true;
        parentSel.add(o);
    });
}
function populateSubs(parentId, selectLeafId){
    catSel.innerHTML = '';
    // parent ကိုယ်တိုင် (ယေဘုယျ) — sub မရွေးလျှင် parent ကို သုံး
    const gen = new Option(GENERAL, parentId);
    catSel.add(gen);
    CATEGORIES.filter(c=>c.parent_id===parentId).forEach(c=>{
        const o = new Option('▸ '+c.name, c.id);
        catSel.add(o);
    });
    catSel.value = selectLeafId ?? parentId;
}
function rebuildCategory(type, leafId){
    // leafId ရှိလျှင် ၎င်း၏ parent ကို ရှာ
    let parentId = null;
    if(leafId){
        const cat = CATEGORIES.find(c=>c.id===leafId);
        if(cat) parentId = cat.parent_id ?? cat.id;
    }
    populateParents(type, parentId);
    const chosenParent = parseInt(parentSel.value) || (parentSel.options[0] && parseInt(parentSel.options[0].value));
    populateSubs(chosenParent || null, leafId ?? null);
}
parentSel.addEventListener('change', ()=>populateSubs(parseInt(parentSel.value)));

function onTypeChange(applyPreset){
    const t = pType.value;
    filterOptions(document.getElementById('brandSel'), t);
    rebuildCategory(t, null);
    const baseInput = document.getElementById('baseUnit');
    if(applyPreset){
        baseInput.value = t==='rice' ? 'ဗူး' : 'ဆယ်သား';
        rows.innerHTML=''; uIdx=0;
        (t==='rice'?RICE_PRESET:OIL_PRESET).forEach(u=>addRow({label:u.label,factor:u.factor,selling_price:0}));
    }
}
pType.addEventListener('change', ()=>onTypeChange(true));
// initial (no preset overwrite) — preselect existing category if editing
filterOptions(document.getElementById('brandSel'), pType.value);
rebuildCategory(pType.value, SELECTED_CAT);
</script>
@endpush
