<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Detail;
use App\Models\Menu;
use App\Models\Seat;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        return view('backend.transaction.list-of-transaction');
    }

    public function data(Request $request)
    {
        if ($request->ajax()) {
            $data = Transaction::all();
            // $data = Detail::groupBy('transaction_id')->get();
            // dd($data);
            // foreach ($data as $key) {
                // echo($key->menu->name);
                // echo($key->id);
                // echo($key->detail->user->name);
                // echo($key->detail->seat->seat_number);
                // echo($key->qty);
                // echo($key->subtotal);
            // }
            // die;
            return Datatables::of($data)
                                        ->addIndexColumn()
                                        ->editColumn('user', function($data){
                                            return $data->user->name;
                                        })
                                        ->editColumn('customer_name', function($data){
                                            return $data->customer_name;
                                        })
                                        ->editColumn('seat', function($data){
                                            return $data->seat->seat_number;
                                        })
                                        ->editColumn('status', function($data){
                                            return $data->status;
                                        })
                                        // ->editColumn('menu', function($data){
                                        //     return $data->menu->name;
                                        // })
                                        // ->editColumn('qty', function($data){
                                        //     return $data->qty;
                                        // })
                                        // ->editColumn('subtotal', function($data){
                                        //     return $data->subtotal;
                                        // })
                                        ->editColumn('action', function($data){
                                            return '<div class="input-group d-flex w-25"><div class="input-group-btn d-flex justify-items-center align-items-center"><a class="btn btn-outline-primary btn-sm" href="'.route('transaction.detailpage', ['id' => $data->id]).'"><i class="ti-eye"></i> Detail</a></div></div>';
                                        })
                                        ->rawColumns(['action'])
                                        ->make();        
        }
    }

    public function add()
    {
        $menu = Menu::all();
        $seat = Seat::where('status', 'available')->get();

        return view('backend.transaction.add',[
            'menu' => $menu,
            'seat' =>$seat
        ]);
    }

    public function insert(Request $request)
    {
        $validatedData = $request->validate([
            'user_id'=> 'required',
            'seat_id' => 'required',
            'customer_name' => 'required',
            'menu' => 'required',
        ]);
        dd($validatedData);

        Transaction::create($validatedData);

        return redirect()->back()->with('success', 'Data has been inserted successfully');
    }

    public function newInsert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'seat_id' => 'required',
            'customer_name' => 'required',       
        ]);
        //get status meja
        $get_seat = DB::table('seats')->where('id', $request->seat_id)->get(); //cek status meja

        // get status meja
        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        } 
        else if ($get_seat[0]->status == 'not available'){
            return response()->json(['success' => false, 'message' => 'Seat not available']);
            exit;
        }

        $update_meja = Seat::where('id', $request->seat_id)->update([
            'status' => 'not available'
        ]);

        $transaksi = new Transaction();
        $transaksi->user_id = $request->user_id;
        $transaksi->seat_id = $request->seat_id;
        $transaksi->customer_name = $request->customer_name;
        $transaksi->status = 'unpayed';
		$transaksi->save();
        
        for($i = 0; $i < count($request->detail); $i++){
            $detail_transaksi = new Detail();
            $detail_transaksi->transaction_id = $transaksi->id;
            $detail_transaksi->menu_id = $request->detail[$i]['menu_id'];
            $detail_transaksi->qty = $request->detail[$i]['qty'];
            $menu = Menu::where('id', '=', $detail_transaksi->menu_id)->first();
            $harga = $menu->price;
            $detail_transaksi->subtotal = $request->detail[$i]['qty'] * $harga;
            $detail_transaksi->save();
        }

        $detail = Detail::where('transaction_id', '=', $detail_transaksi->transaction_id)->get();
        return response()->json([
            'data' => $transaksi,
            'detail lengkap' => $detail,
        ]);
    }

    public function payment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tunai' => 'required',    
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson());
        }
        $total = DB::select("SELECT transaction_id, SUM(subtotal) as 'total' from details WHERE transaction_id = $id GROUP BY transaction_id");
        $total_akhir = intval($total[0]->total);
        // print_r($total);
        $kembali = $request->tunai - $total_akhir;

        if ($request->tunai < $total_akhir) {
            return response()->json(['success' => false, 'message' => 'Tunai kurang']);
            exit;
        }

        $update_bayar = Transaction::where('id', $id)->update([
            'status' => 'payed'
        ]);

        $get_meja = DB::table('transactions')->where('id', $id)->get(); //get status meja

        $update_meja = Seat::where('id', $get_meja[0]->seat_id)->update([
            'status' => 'available'
        ]);

        return response()->json([
            'Total' => $total_akhir,
            'Tunai' => $request->tunai,
            'Kembali' => $kembali
        ]); 
    }

    public function detailpage($id)
    {
        $data = Detail::where('transaction_id', $id)->get();
        $total = Detail::where('transaction_id', $id)->sum('subtotal');   
        return view('backend.transaction.list-of-detailtransaction',[
            'id' => $id,
            'total' => $total
            ]);
    }

    public function detail($id, Request $request)
    {
        $data = Detail::where('transaction_id', $id)->get();

        // if($request->ajax()) {
            return Datatables::of($data)
                                        ->addIndexColumn()
                                        ->editColumn('menu', function($data){
                                            return $data->menu->name;
                                        })
                                        ->editColumn('qty', function($data){
                                            return $data->qty;
                                        })
                                        ->editColumn('subtotal', function($data){
                                            return $data->subtotal;
                                        })
                                        ->rawColumns(['action'])
                                        ->make();        
        // }
    }
}