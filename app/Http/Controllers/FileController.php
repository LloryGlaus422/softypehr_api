<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use DB;

class FileController extends Controller
{
    //
    public function serve($folder, $filename){
        $dir = $folder."/".$filename;
        $path = public_path()."/".$dir;
        return File::get($path);
    }

    public function store(Request $request)
    {
        
        // $validator = Validator::make($request->all(), [
        //     'price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
        //     'productCategory' => 'required|string|max:255',
        //     'productName' => 'required|string|max:255',
        // ]);
        // if($validator->fails()){
        //     return response()->json($validator->errors()->toJson(), 400);
        // }

        try{
            $imageName = time().'.'.$request->file->getClientOriginalExtension();
            $request->file->move(public_path('images'), $imageName);
            $response = ['data' => [],'error' => false, 'message' => "Success!"];
            return response()->json($response, 200);
        }catch(\Exception $e){
            $response = ['data' => $e, "error" => true, "message" => $e->getMessage()];
            return response()->json($response, 500);
        }
       
        
        // return response()->json(['success'=>'You have successfully upload image.']);
    }

    public function addFile(Request $request)
    {
        try{
            $images = ["jpg","png"];
            $docs = ["docs","pdf","txt"];
            $imageName = time().'.'.$request->file->getClientOriginalExtension();
            $extension = $request->file->getClientOriginalExtension();
            if (in_array($extension,$images)){
                $request->file->move(public_path('images'), $imageName);
                $path = public_path('images');
                $type = "img";
            }elseif (in_array($extension,$docs)){
                $request->file->move(public_path('documents'), $imageName);
                $path = public_path('documents');
                $type = "dodocuments";
            }else{
                $request->file->move(public_path('others'), $imageName);
                $path = public_path('others');
                $type = "others";
            }
            
            $file = \DB::select(
                'call AddFile(?,?,?,?,?)', 
                array(
                    $request->employeeId,
                    $imageName,
                    $path, 
                    $type,
                    $request->description)
                );
            
            $result = collect($file);
            $file_id = $result[0]->id;
            $response = $this->retrieveLimitedFile($file_id);
            return response()->json($file_id, 200);
        }catch(\Exception $e){
            return $e;
        }
    }

    public function retrieveLimitedFile($id)
    {
        try {
            $files = DB::select('call retrieveLimitedFile(?)', array($id));
            $result = collect($files);
            $response = ['data' => ['files' => $result], 'error' => false, 'message' => 'success'];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = ['data' => $e, "error" => true, "message" => $e->getMessage()];
            return response()->json($response, 500);
        }
    }
    
    public function retrieveFiles(){
        try {
            $files = DB::select('call RetrieveFiles');
            $result = collect($files);
            $response = ['data' => ['files' => $result], 'error' => false, 'message' => 'success'];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = ['data' => $e, "error" => true, "message" => $e->getMessage()];
            return response()->json($response, 500);
        }
    }   

    public function retrieveFilesByType(){
        try {
            $files = DB::select('call RetrieveByFileType(?)', array($id));
            $result = collect($files);
            $response = ['data' => ['files' => $result], 'error' => false, 'message' => 'success'];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = ['data' => $e, "error" => true, "message" => $e->getMessage()];
            return response()->json($response, 500);
        }
    }
    
    public function deleteFile($id){
        try {
            DB::beginTransaction();
            $deleted_files = DB::select('call DeleteFile(?)', array($id));
            $response = ['error' => false, 'message' => 'success'];
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $response = ['data' => $e, "error" => true, "message" => $e->getMessage()];
            return response()->json($response, 500);
        }
    }
}
