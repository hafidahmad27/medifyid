<?php

namespace App\Http\Controllers;

use App\Models\KategoriItem;
use App\Models\MasterItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KategoriItemController extends Controller
{
    public function index()
    {
        return view('kategori_items.index.index');
    }

    public function search(Request $request)
    {
        $kode = $request->kode;
        $nama = $request->nama;

        $data_search = KategoriItem::query();

        if (!empty($kode)) $data_search = $data_search->where('kode', $kode);
        if (!empty($nama)) $data_search = $data_search->where('nama', 'LIKE', '%' . $nama . '%');

        $data_search = $data_search->select('kode', 'nama')->orderBy('id')->get();


        return json_encode([
            'status' => 200,
            'data' => $data_search
        ]);
    }

    public function formView($method, $id = 0)
    {
        if ($method == 'new') {
            $item = [];
        } else {
            $item = KategoriItem::find($id);
        }
        $data['item'] = $item;
        $data['method'] = $method;
        return view('kategori_items.form.index', $data);
    }

    public function singleView($kode)
    {
        $data['data'] = KategoriItem::where('kode', $kode)->first();
        return view('kategori_items.single.index', $data);
    }

    public function formSubmit(Request $request, $method, $id = 0)
    {
        if ($method == 'new') {
            $data_item = new KategoriItem;
            $kode = KategoriItem::count('id');
            $kode = $kode + 1;
            $kode = str_pad($kode, 5, '0', STR_PAD_LEFT);
            sleep(3);
        } else {
            $data_item = KategoriItem::find($id);
            $kode = $data_item->kode;
        }

        // upload foto
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();

            $file->move(public_path('uploads/kategori_items'), $filename);

            // hapus foto lama (opsional, tapi best practice)
            if ($method != 'new' && $data_item->foto && file_exists(public_path('uploads/kategori_items/' . $data_item->foto))) {
                unlink(public_path('uploads/kategori_items/' . $data_item->foto));
            }

            $data_item->foto = $filename;
        }


        $data_item->nama = $request->nama;
        $data_item->kode = $kode;
        $data_item->save();

        return redirect('kategori-items');
    }

    public function delete($id)
    {
        KategoriItem::find($id)->delete();
        return redirect('kategori-items');
    }

    public function print($id)
    {
        $kategori = KategoriItem::findOrFail($id);

        $items = MasterItem::where('kategori', $id)
            ->select('kode', 'nama', 'harga_beli', 'supplier')
            ->get();

        $tanggal_cetak = Carbon::now()->format('d-m-Y H:i:s');

        $pdf = Pdf::loadView('kategori_items.print', [
            'kategori' => $kategori,
            'items' => $items,
            'tanggal_cetak' => $tanggal_cetak
        ]);

        return $pdf->download('kategori-item-' . $kategori->nama . '.pdf');
    }
}
