<div><label class="block text-sm font-medium mb-1">Nama Kapten</label><input name="name" value="{{ old('name', $captain->name ?? '') }}" required class="w-full rounded-lg border-slate-300"></div>
<div><label class="block text-sm font-medium mb-1">Nomor HP</label><input name="phone" value="{{ old('phone', $captain->phone ?? '') }}" class="w-full rounded-lg border-slate-300"></div>
<div><label class="block text-sm font-medium mb-1">Alamat</label><textarea name="address" class="w-full rounded-lg border-slate-300">{{ old('address', $captain->address ?? '') }}</textarea></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $captain->is_active ?? true) ? 'checked' : '' }}> Aktif</label>
