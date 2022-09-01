<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $staff = User::where('type', 3);
        if ($request->search != null) {
            $staff = $staff->where('users.name', 'LIKE', '%' . $request->search . '%')
                ->orWhere('users.email', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->sortby != null && $request->sorttype) {
            $staff = $staff->orderBy($request->sortby, $request->sorttype);
        } else {
            $staff = $staff->orderBy('id', 'desc');
        }
        if ($request->perPage != null) {
            $staff = $staff->paginate($request->perPage);
        } else {
            $staff = $staff->paginate(10);
        }
        if ($request->ajax()) {
            return response()->json(view('admin.staff.detail', compact('staff'))->render());
        }
        return view('admin.staff.list', compact(['staff']));
    }

    public function staffs(Request $request)
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

        $staff = User::where('type', 3);
        if ($request->search['value'] != null) {
            $staff = $staff->where('users.name', 'LIKE', '%' . $request->search['value'] . '%')
                ->orWhere('email', 'LIKE', '%' . $request->search['value'] . '%')
                ->where('type', 3);
        }
        if ($request->length != '-1') {
            $staff = $staff->take($request->length);
        } else {
            $staff = $staff->take();
        }
        $staff = $staff->skip($request->start)
            ->orderBy($order, $dir)
            ->get();

        $data = array();
        if (!empty($staff)) {
            foreach ($staff as $staff_user) {
                $url = route('admin.customer.get', ['user_id' => $staff_user->id]);
                $statusUrl = route('admin.customer.status.change', ['user_id' => $staff_user->id]);
                $checked = $staff_user->status == 1 ? 'checked' : '';
                $user_image = $staff_user->photo == null ? url('/admin_assets/img/logo.png') : $staff_user->photo;

                $nestedData['id'] = $staff_user->id;
                $nestedData['photo'] = "<img src=' $user_image' width='50' height='50'>";
                $nestedData['name'] = $staff_user->name;
                $nestedData['email'] = $staff_user->email;
                $nestedData['status'] = "<div class='custom-control custom-switch'>
                                            <input type='radio' class='custom-control-input active' data-url='$statusUrl' id='active$staff_user->id' name='active$staff_user->id' $checked>
                                            <label class='custom-control-label' for='active$staff_user->id'></label>
                                        </div>";
                // $nestedData['action'] = "<button class='edit-cat btn btn-outline-warning btn-sm btn-icon' data-toggle='modal' data-target='#default-example-modal' data-url=' $url '><i class='fal fa-pencil'></i></button>";
                $data[] = $nestedData;

            }
        }
        return response()->json([
            'draw' => $request->draw,
            'data' => $data,
            'recordsTotal' => User::where('type', 3)->count(),
            'recordsFiltered' => $request->search['value'] != null ? $staff->count() : User::where('type', 3)->count(),
        ]);
    }

    public function getStaff(Request $request)
    {
        $staff = User::find($request->user_id);
        return response()->json(['data' => $staff]);
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
            $staff = User::find($request->user_id);
        } else {
            $staff = new User;
        }
        try {
            $staff->name = $request->name;
            $staff->email = $request->email;
            $staff->type = 3;
            $staff->photo = $request->photo;
            $staff->save();
            $staff->assignRole(['Provider']);
            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }

    }
}
