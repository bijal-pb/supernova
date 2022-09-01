<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $service = Service::select('services.*', 'categories.name as cat_name')
            ->leftJoin('categories', 'services.category_id', 'categories.id');
        if ($request->search != null) {
            $service = $service->where('name', 'LIKE', '%' . $request->search . '%');
        }
        if ($request->sortby != null && $request->sorttype) {
            $service = $service->orderBy($request->sortby, $request->sorttype);
        } else {
            $service = $service->orderBy('id', 'desc');
        }

        if ($request->perPage != null) {
            $service = $service->paginate($request->perPage);
        } else {
            $service = $service->paginate(10);
        }
        $category = Category::select('id', 'name')->get();
        if ($request->ajax()) {
            return response()->json(view('admin.service.cat_data', compact('service'))->render());
        }
        return view('admin.service.list', compact(['service', 'category']));
    }

    public function services(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'category_id',
            2 => 'name',
        );
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        $service = Service::select('services.*', 'categories.name as cat_name')
            ->leftJoin('categories', 'services.category_id', 'categories.id');

        if ($request->search['value'] != null) {
            $service = $service->where('services.name','LIKE','%'.$request->search['value'].'%')
            ->orWhere('categories.name','LIKE','%'.$request->search['value'].'%');

        }
        if ($request->length != '-1') {
            $service = $service->take($request->length);
        } else {
            $service = $service->take(Service::count());
        }
        $service = $service->skip($request->start)
            ->orderBy($order, $dir)
            ->get();

        $data = array();
        if (!empty($service)) {
            foreach ($service as $u_service) {
                $url = route('admin.service.get', ['service_id' => $u_service->id]);
                $statusUrl = route('admin.service.status.change', ['service_id' => $u_service->id]);
                $checked = $u_service->status == 1 ? 'checked' : '';

                $user_image = $u_service->image == null ? url('/admin_assets/img/logo.png') : $u_service->image;

                $nestedData['id'] = $u_service->id;
                $nestedData['category_id'] = $u_service->cat_name;
                $nestedData['name'] = $u_service->name;
                $nestedData['image'] = "<img src=' $user_image' width='50' height='50'>";
                $nestedData['status'] = "<div class='custom-control custom-switch'>
                                            <input type='radio' class='custom-control-input active' data-url='$statusUrl' id='active$u_service->id' name='active$u_service->id' $checked>
                                            <label class='custom-control-label' for='active$u_service->id'></label>
                                        </div>";
                $nestedData['action'] = "<td>
                                             <button class='edit-service btn btn-outline-warning btn-sm btn-icon' data-toggle='modal' data-target='#default-example-modal' data-url=' $url '><i class='fal fa-pencil'></i></button>
                                         </td>";
                $data[] = $nestedData;

            }
        }
        return response()->json([
            'draw' => $request->draw,
            'data' => $data,
            'recordsTotal' => Service::count(),
            'recordsFiltered' => $request->search['value'] != null ? $service->count() : Service::count(),
        ]);
    }

    public function getService(Request $request)
    {
        $service = Service::find($request->service_id);
        return response()->json(['data' => $service]);
    }

    public function changeStatus(Request $request)
    {
        $service = Service::find($request->service_id);
        if ($service->status == 1) {
            $service->status = 2;
        } else {
            $service->status = 1;
        }
        $service->save();
        return response()->json(['status' => 'success']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'name' => 'required|max:255',
            'image' => 'nullable|image|mimes:png,jpeg,jpg,svg,ico|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
        }
        $filename = null;
        if ($request->hasfile('image')) {
            $file = $request->file('image');
            $filename = time() . $file->getClientOriginalName();
            $file->move(public_path() . '/service/', $filename);
        }
        if ($request->service_id != null) {
            $service = Service::find($request->service_id);
        } else {
            $service = new Service;
        }
        $service->category_id = $request->category_id;
        $service->name = $request->name;
        //  $cat->image = $request->image;
        if ($request->hasfile('image')) {
            $service->image = $filename;
        }
        $service->status = $request->status;
        $service->save();
        return response()->json(['status' => 'success']);

    }
    public function service_list(Request $request){

        $services = Service::where('category_id',$request->cat_id)->get();
        return response()->json(['status' => 'success', 'services' => $services]);

    }

}
