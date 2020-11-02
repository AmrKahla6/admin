<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Carbon\Carbon;
use DB;
use App\member;
use App\Service;
use App\Category;
use App\order;
use App\order_item;
use App\setting;

class adminserviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $mainactive = 'services';
        $subactive  = 'service';
        $logo       = DB::table('settings')->value('logo');
        $services   = Service::orderBy('id', 'desc')->get();

        return view('admin.services.index', compact('mainactive', 'logo', 'subactive', 'services'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $mainactive   = 'services';
        $subactive    = 'addservice';
        $logo         = DB::table('settings')->value('logo');
        $categories   = Category::get();
        return view('admin.services.create', compact('mainactive', 'subactive', 'logo' ,'categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate($request, [
            'name'              => 'required|max:200',
            'des'               => 'required',
            'price'             => 'required',
            'category_id'       => 'required',
        ]);
        $newservice                        = new Service;
        $newservice->name                  = $request['name'];
        $newservice->price                 = $request['price'];
        $newservice->des                   = $request['des'];
        $newservice->category_id           = $request['category_id'];

        if ($request->hasFile('image')) {
            $service = $request['image'];
            $img_name = rand(0, 999) . '.' . $service->getClientOriginalExtension();
            $service->move(base_path('users/images/'), $img_name);
            $newservice->image   = $img_name;
        }
        $newservice->save();
        session()->flash('success', 'تم اضافة المنتج بنجاح');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $mainactive      = 'services';
        $subactive       = 'addservice';
        $logo            = DB::table('settings')->value('logo');
        $showservice     = Service::findorfail($id);

        return view('admin.services.show', compact('mainactive', 'logo', 'subactive', 'showservice'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $mainactive   = 'services';
        $subactive    = 'addservice';
        $logo         = DB::table('settings')->value('logo');
        $category     = Category::findorfail($id);
        $service      = Service::findorfail($id);
        return view('admin.services.edit', compact('mainactive', 'logo', 'subactive', 'service' , 'category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $upitem = item::find($id);
        if (Input::has('suspensed')) {
            if ($upitem->suspensed == 0) {
                DB::table('items')->where('id', $id)->update(['suspensed' => 1]);
                session()->flash('success', 'تم تعطيل المنتج بنجاح');
                return back();
            } else {
                DB::table('items')->where('id', $id)->update(['suspensed' => 0]);
                session()->flash('success', 'تم تفعيل المنتج بنجاح');
                return back();
            }
        } else {
            $this->validate($request, [
                'artitle'     => 'required|max:200',
                'price'       => 'required',
            ]);


            $upitem->artitle       = $request['artitle'];
            $upitem->price         = $request['price'];
            $upitem->details        = $request['ardesc'];
            if ($request->hasFile('image')) {
                $item = $request['image'];
                $img_name = rand(0, 999) . '.' . $item->getClientOriginalExtension();
                $item->move(base_path('users/images/'), $img_name);
                $upitem->image   = $img_name;
            }
            $upitem->save();


            session()->flash('success', 'تم تعديل المنتج بنجاح');
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $delitem = Service::find($id);
        // if ($delservice) {
        //     $categories = Category::where('category_id', $delitem->id)->get();
        //     foreach ($categories as $category) {
        //         $order =  order::where('id', $order_item->order_id)->first();
        //         if ($order) {
        //             $order->delete();
        //         }
        //         $order_item->delete();
        //     }
        // }
        $delitem->delete();
        session()->flash('success', 'تم حذف المنتج بنجاح');
        return back();
    }

    public function deleteAll(Request $request)
    {
        $ids           = $request->ids;
        $selecteditems = DB::table("members")->whereIn('id', explode(",", $ids))->get();
        foreach ($selecteditems as $item) {
            $order_items = order_item::where('item_id', $item->id)->get();
            foreach ($order_items as $order_item) {
                $order =  order::where('id', $order_item->order_id)->first();
                if ($order) {
                    $order->delete();
                }
                $order_item->delete();
            }
            $item->delete();
        }
        return response()->json(['success' => "تم الحذف بنجاح"]);
    }
}