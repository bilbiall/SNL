{{-- The shared fields of a pack (create + edit). $p = the Pack, or null for a new one. --}}
<div class="col-span-12 sm:col-span-4"><label class="gs-h">Pack name</label><input name="name" value="{{ old('name', $p?->name) }}" class="gs-in" maxlength="60" placeholder="Christmas party pack" required></div>
<div class="col-span-12 sm:col-span-4"><label class="gs-h">Sponsor (optional) — shows "Presented by …"</label><input name="sponsor" value="{{ old('sponsor', $p?->sponsor) }}" class="gs-in" maxlength="60" placeholder="Java House"></div>
<div class="col-span-12 sm:col-span-4"><label class="gs-h">Tagline (optional)</label><input name="tagline" value="{{ old('tagline', $p?->tagline) }}" class="gs-in" maxlength="120"></div>
<div class="col-span-6 sm:col-span-3"><label class="gs-h">Live from</label><input type="date" name="starts_on" value="{{ old('starts_on', $p?->starts_on?->toDateString()) }}" class="gs-in"></div>
<div class="col-span-6 sm:col-span-3"><label class="gs-h">Live until</label><input type="date" name="ends_on" value="{{ old('ends_on', $p?->ends_on?->toDateString()) }}" class="gs-in"></div>
<div class="col-span-12 sm:col-span-3"><label class="gs-h">Sponsor link (optional)</label><input type="url" name="link" value="{{ old('link', $p?->link) }}" class="gs-in" maxlength="200" placeholder="https://…"></div>
<div class="col-span-12 sm:col-span-3"><label class="gs-h">Logo (png / jpg / webp, ≤1 MB)</label><input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="gs-in"></div>
<div class="col-span-12">
    <label class="gs-h">Modes (tick none = every mode)</label>
    <div style="display:flex;flex-wrap:wrap;gap:.9rem;margin-top:.2rem;">
        @foreach($modes as $k => $m)
            @if($m['enabled'])
            <label style="font-size:.76rem;color:#d1d5db;display:flex;align-items:center;gap:.3rem;"><input type="checkbox" name="modes[]" value="{{ $k }}" {{ in_array($k, (array) old('modes', $p?->modes ?? []), true) ? 'checked' : '' }}> {{ $m['label'] }}</label>
            @endif
        @endforeach
    </div>
</div>
