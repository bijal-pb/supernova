<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $cats = Category::query();
        if($request->search != null){
            $cats = $cats->where('name','LIKE','%'.$request->search.'%');
        }
        if($request->sortby!= null && $request->sorttype)
        {
            $cats = $cats->orderBy($request->sortby,$request->sorttype);
        }else{
            $cats= $cats->orderBy('id','desc');
        }
        
        if($request->perPage != null){
            $cats= $cats->paginate($request->perPage);
        }else{
            $cats= $cats->paginate(10);
        }
        if($request->ajax())
        {
            return response()->json( view('admin.categories.cat_data', compact('cats'))->render());
        }
        return view('admin.categories.list' , compact('cats'));
    }

    public function categories(Request $request)
    {
        $columns = array( 
            0 =>'id', 
            1 =>'name',
            2 =>'image',
        );  
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        $categories = Category::query();
        if($request->search['value'] != null){
            $categories = $categories->where('name','LIKE','%'.$request->search['value'].'%');
                           
        }
        if($request->length != '-1')
        {
            $categories = $categories->take($request->length);
        }else{
            $categories = $categories->take(Category::count());
        }
        $categories = $categories->skip($request->start)
                        ->orderBy($order,$dir)
                        ->get();
       
        $data = array();
        if(!empty($categories))
        {
            foreach ($categories as $category)
            {
                $url = route('admin.category.get', ['cat_id' => $category->id]);
                $statusUrl = route('admin.category.status.change', ['cat_id' => $category->id]);
                $checked = $category->status == 1 ? 'checked' : '';
            
                $user_image = $category->image == null ? url('/admin_assets/img/logo.png') : $category->image;

                $nestedData['id'] = $category->id;
                $nestedData['name'] = $category->name;
                $nestedData['image'] = "<img src=' $user_image' width='50' height='50'>";
                $nestedData['status'] = "<div class='custom-control custom-switch'>
                                            <input type='radio' class='custom-control-input active' data-url='$statusUrl' id='active$category->id' name='active$category->id' $checked>
                                            <label class='custom-control-label' for='active$category->id'></label>
                                        </div>";
                $nestedData['action'] =  "<td>
                                             <button class='edit-cat btn btn-outline-warning btn-sm btn-icon' data-toggle='modal' data-target='#default-example-modal' data-url=' $url '><i class='fal fa-pencil'></i></button>
                                         </td>";
                $data[] = $nestedData;

            }
        }
        return response()->json([
            'draw' => $request->draw,
            'data' =>$data,
            'recordsTotal' => Category::count(),
            'recordsFiltered' => $request->search['value'] != null ? $categories->count() : Category::count(),
        ]);
    }

    public function getCategory(Request $request){
        $cat = Category::find($request->cat_id);
        return response()->json(['data'=>$cat]);
    }

    public function changeStatus(Request $request)
    {
        $category = Category::find($request->cat_id);
        if ($category->status == 1) {
            $category->status = 2;
        } else {
            $category->status = 1;
        }
        $category->save();
        return response()->json(['status' => 'success']);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|max:255',
            'image' => 'nullable|image|mimes:png,jpeg,jpg,svg,ico|max:2048'
		]);

		if($validator->fails())
		{
            return response()->json(['status'=>'error','message' => $validator->errors()->first()]);
        }
        $filename = null;
		if($request->hasfile('image')) {
            $file = $request->file('image');
            $filename = time().$file->getClientOriginalName();
            $file->move(public_path().'/category/', $filename);  
		}
        if($request->cat_id != null)
        {
            $cat = Category::find($request->cat_id);
        }else{
            $cat = new Category;
        }
        
        $cat->name = $request->name;
        //  $cat->image = $request->image;
        if($request->hasfile('image'))
        {
            $cat->image = $filename;
        }
        $cat->status = $request->status;
        $cat->save();
        return response()->json(['status'=>'success']);
        
    }
}
