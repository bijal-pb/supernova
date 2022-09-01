<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customer = User::where('type', 2);
        if ($request->search != null) {
            $customer = $customer->where('users.name', 'LIKE', '%' . $request->search . '%')
                ->orWhere('users.email', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->sortby != null && $request->sorttype) {
            $customer = $customer->orderBy($request->sortby, $request->sorttype);
        } else {
            $customer = $customer->orderBy('id', 'desc');
        }
        if ($request->perPage != null) {
            $customer = $customer->paginate($request->perPage);
        } else {
            $customer = $customer->paginate(10);
        }
        if ($request->ajax()) {
            return response()->json(view('admin.business.detail', compact('customer'))->render());
        }
        return view('admin.customer.list', compact(['customer']));
    }

    public function customers(Request $request)
    {
        $columns = array(
            0 => 'id',
            2 => 'name',
            3 => 'email',
            4 => 'status',

        );
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        //$users = User::query();

        $customer = User::where('type', 2);
        if ($request->search['value'] != null) {
            $customer = $customer->where('users.name', 'LIKE', '%' . $request->search['value'] . '%')
                ->orWhere('users.email', 'LIKE', '%' . $request->search['value'] . '%')
                ->where('type', 2);
        }
        if ($request->length != '-1') {
            $customer = $customer->take($request->length);
        } else {
            $customer = $customer->take();
        }
        $customer = $customer->skip($request->start)
            ->orderBy($order, $dir)
            ->get();

        $data = array();
        if (!empty($customer)) {
            foreach ($customer as $customer_user) {
                $url = route('admin.customer.get', ['user_id' => $customer_user->id]);
                $statusUrl = route('admin.customer.status.change', ['user_id' => $customer_user->id]);
                $checked = $customer_user->status == 1 ? 'checked' : '';
                $user_image = $customer_user->photo == null ? url('/admin_assets/img/logo.png') : $customer_user->photo;

                $nestedData['id'] = $customer_user->id;
                $nestedData['photo'] = "<img src=' $user_image' width='50' height='50'>";
                $nestedData['name'] = $customer_user->name;
                $nestedData['email'] = $customer_user->email;
                $nestedData['status'] = "<div class='custom-control custom-switch'>
                                            <input type='radio' class='custom-control-input active' data-url='$statusUrl' id='active$customer_user->id' name='active$customer_user->id' $checked>
                                            <label class='custom-control-label' for='active$customer_user->id'></label>
                                        </div>";
                // $nestedData['action'] = "<button class='edit-cat btn btn-outline-warning btn-sm btn-icon' data-toggle='modal' data-target='#default-example-modal' data-url=' $url '><i class='fal fa-pencil'></i></button>";
                $data[] = $nestedData;

            }
        }
        return response()->json([
            'draw' => $request->draw,
            'data' => $data,
            'recordsTotal' => User::where('type', 2)->count(),
            'recordsFiltered' => $request->search['value'] != null ? $customer->count() : User::where('type', 2)->count(),
        ]);
    }

    public function getCustomer(Request $request)
    {
        $customer = User::find($request->user_id);
        return response()->json(['data' => $customer]);
    }

    public function changeStatus(Request $request)
    {
        $user = User::find($request->user_id);
        if ($user->status == 1) {
            $user->status = 2;
        } else {
            $user->status = 1;
        }
        $user->save();
        return response()->json(['status' => 'success']);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|max:255',
            'email' => 'nullable|email',
            'photo' => 'nullable|image|mimes:png,jpeg,jpg,svg,ico|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()]);
        }

        if ($request->user_id != null) {
            $customer = User::find($request->user_id);
        } else {
            $customer = new User;
        }
        try {
            $customer->name = $request->name;
            $customer->email = $request->email;
            $customer->type = 2;
            $customer->photo = $request->photo;
            $customer->save();
            $customer->assignRole(['Customer']);
            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

    }
}
