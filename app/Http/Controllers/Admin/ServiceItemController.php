<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Models\ServiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceItemController extends Controller
{
    public function index(Request $request)
    {
        $service_item = ServiceItem::select('service_items.*', 'services.name as service_name')
        ->leftJoin('services', 'service_items.service_id', 'services.id');

        if ($request->search != null) {
            $service_item = $service_item->where('name', 'LIKE', '%' . $request->search . '%');
        }
        if ($request->sortby != null && $request->sorttype) {
            $service_item = $service_item->orderBy($request->sortby, $request->sorttype);
        } else {
            $service_item = $service_item->orderBy('id', 'desc');
        }

        if ($request->perPage != null) {
            $service_item = $service_item->paginate($request->perPage);
        } else {
            $service_item = $service_item->paginate(10);
        }
        $category = Category::select('id', 'name')->get();
        $service = Service::select('id', 'name')->get();
        if ($request->ajax()) {
            return response()->json(view('admin.service_item.cat_data', compact('service_item'))->render());
        }
        return view('admin.service_item.list', compact(['service_item', 'service','category']));
    }

    public function service_items(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'service_id',
            2 => 'name',
        );
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $service_item = ServiceItem::select('service_items.*', 'services.name as service_name')
        ->leftJoin('services', 'service_items.service_id', 'services.id');

        if ($request->search['value'] != null) {
            $service_item = $service_item->where('service_items.name','LIKE','%'.$request->search['value'].'%')
            ->orWhere('services.name','LIKE','%'.$request->search['value'].'%');

        }
        if ($request->length != '-1') {
            $service_item = $service_item->take($request->length);
        } else {
            $service_item = $service_item->take(ServiceItem::count());
        }
        $service_item = $service_item->skip($request->start)
            ->orderBy($order, $dir)
            ->get();

        $data = array();
        if (!empty($service_item)) {
            foreach ($service_item as $s_item) {
                $url = route('admin.service_item.get', ['service_item_id' => $s_item->id]);
                $statusUrl = route('admin.service_item.status.change', ['service_item_id' => $s_item->id]);
                $checked = $s_item->status == 1 ? 'checked' : '';

                $user_image = $s_item->image == null ? url('/admin_assets/img/logo.png') : $s_item->image;

                $nestedData['id'] = $s_item->id;
                $nestedData['service_id'] = $s_item->service_name;
                $nestedData['name'] = $s_item->name;
                $nestedData['image'] = "<img src=' $user_image' width='50' height='50'>";
                $nestedData['status'] = "<div class='custom-control custom-switch'>
                                            <input type='radio' class='custom-control-input active' data-url='$statusUrl' id='active$s_item->id' name='active$s_item->id' $checked>
                                            <label class='custom-control-label' for='active$s_item->id'></label>
                                        </div>";
                $nestedData['action'] = "<td>
                                             <button class='edit-service_item btn btn-outline-warning btn-sm btn-icon' data-toggle='modal' data-target='#default-example-modal' data-url=' $url '><i class='fal fa-pencil'></i></button>
                                         </td>";
                $data[] = $nestedData;

            }
        }
        return response()->json([
            'draw' => $request->draw,
            'data' => $data,
            'recordsTotal' => ServiceItem::count(),
            'recordsFiltered' => $request->search['value'] != null ? $service_item->count() : ServiceItem::count(),
        ]);
    }

    public function getServiceItem(Request $request)
    {
        $service_item = ServiceItem::find($request->service_item_id);
        $service = Service::find($service_item->service_id);
        $services = Service::where('category_id',$service->category_id)->get();
        return response()->json(['data' => $service_item, 'service' => $service, 'services' => $services]);
    }

    public function changeStatus(Request $request)
    {
        $service_item = ServiceItem::find($request->service_item_id);
        if ($service_item->status == 1) {
            $service_item->status = 2;
        } else {
            $service_item->status = 1;
        }
        $service_item->save();
        return response()->json(['status' => 'success']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required',
            'name' => 'required|max:255',
            'description' => 'required|max:500',
            'image' => 'nullable|image|mimes:png,jpeg,jpg,svg,ico|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
        }
        $filename = null;
        if ($request->hasfile('image')) {
            $file = $request->file('image');
            $filename = time() . $file->getClientOriginalName();
            $file->move(public_path() . '/serviceItem/', $filename);
        }
        if ($request->service_item_id != null) {
            $service_item = ServiceItem::find($request->service_item_id);
        } else {
            $service_item = new ServiceItem;
        }
        $service_item->service_id = $request->service_id;
        $service_item->name = $request->name;
        $service_item->amount = $request->amount;
        $service_item->description = $request->description;
        if ($request->hasfile('image')) {
            $service_item->image = $filename;
        }
        $service_item->status = $request->status;
        $service_item->save();
        return response()->json(['status' => 'success']);

    }
}
