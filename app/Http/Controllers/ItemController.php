<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    private function authorizeManage(): void
    {
        abort_unless(in_array(session('role'), ['admin', 'super']), 403);
    }

    public function index()
    {
        $items = Item::orderBy('id')->paginate(15)->withQueryString();

        $role = session('role', 'staff');
        $view = in_array($role, ['super', 'admin', 'staff']) ? $role . '.master' : 'staff.master';

        return view($view, [
            'active' => 'master',
            'pageTitle' => 'Master Barang & Satuan',
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:Dry Goods,Chilled,Frozen,Packaging',
            'unit_buy' => 'nullable|string|max:50',
            'unit_count' => 'nullable|string|max:50',
            'ratio' => 'nullable|string|max:50',
            'safety_stock' => 'required|integer|min:0',
            'rack' => 'nullable|string|max:50',
            'is_active' => 'required|boolean',
        ]);

        $prefix = ['Dry Goods' => 'DRY', 'Chilled' => 'CHL', 'Frozen' => 'FZN', 'Packaging' => 'PKG'];
        $code = $prefix[$data['category']];
        $last = Item::where('sku', 'like', $code . '-%')->count();
        $sku = $code . '-' . str_pad($last + 1, 3, '0', STR_PAD_LEFT);

        $item = Item::create([
            'sku' => $sku,
            'name' => $data['name'],
            'category' => $data['category'],
            'unit_buy' => $data['unit_buy'],
            'unit_count' => $data['unit_count'],
            'ratio' => $data['ratio'],
            'safety_stock' => (int) $data['safety_stock'],
            'rack' => $data['rack'],
            'is_active' => (bool) $data['is_active'],
        ]);

        ActivityLog::record(session('email'), 'Create', $item->sku, 'Tambah barang: ' . $item->name);

        return redirect()->route('master')->with('success', 'Barang ' . $item->name . ' berhasil ditambahkan. SKU: ' . $sku);
    }

    public function update(Request $request, Item $item)
    {
        $this->authorizeManage();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:Dry Goods,Chilled,Frozen,Packaging',
            'unit_buy' => 'nullable|string|max:50',
            'unit_count' => 'nullable|string|max:50',
            'ratio' => 'nullable|string|max:50',
            'safety_stock' => 'required|integer|min:0',
            'rack' => 'nullable|string|max:50',
            'is_active' => 'required|boolean',
        ]);

        $old = $item->only(['name', 'category', 'unit_buy', 'unit_count', 'ratio', 'safety_stock', 'rack', 'is_active']);

        $item->update([
            'name' => $data['name'],
            'category' => $data['category'],
            'unit_buy' => $data['unit_buy'],
            'unit_count' => $data['unit_count'],
            'ratio' => $data['ratio'],
            'safety_stock' => (int) $data['safety_stock'],
            'rack' => $data['rack'],
            'is_active' => (bool) $data['is_active'],
        ]);

        ActivityLog::record(session('email'), 'Update', $item->sku, 'Ubah data barang: ' . $item->name . ' (' . json_encode($old) . ' → ' . json_encode($item->only(['name', 'category', 'unit_buy', 'unit_count', 'ratio', 'safety_stock', 'rack', 'is_active'])) . ')');

        return back()->with('success', 'Barang ' . $item->name . ' berhasil diperbarui.');
    }

    public function destroy(Item $item)
    {
        $this->authorizeManage();

        $mutationCount = $item->mutations()->count();

        if ($mutationCount > 0) {
            return back()->withErrors(['items' => 'Barang ' . $item->sku . ' punya ' . $mutationCount . ' riwayat mutasi. Nonaktifkan saja, tidak bisa dihapus.']);
        }

        ActivityLog::record(session('email'), 'Delete', $item->sku, 'Hapus barang: ' . $item->name);

        $item->delete();

        return back()->with('success', 'Barang ' . $item->sku . ' berhasil dihapus.');
    }
}