<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
//use App\Http\Controllers\CheckInOutRequest;
//use App\Http\Requests\AssetCheckoutRequest;
use App\Models\User;
use App\Models\HandoverPaper;
use Dompdf\Dompdf;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
class HandoverPaperController extends Controller

{

    public function index(Request $request) {
        $where = [];
        if ($request->has('number_of_report') && $request->number_of_report) {
            array_push($where, array('number_of_report', 'LIKE', "%$request->number_of_report%"));
        }

        if ($request->has('user') && $request->user) {
            array_push($where, array('receiver_id', '=', $request->user));
        }

        if ($request->has('asset_tag') && $request->asset_tag) {
            array_push($where, array('asset_tag', '=', $request->asset_tag));
        }

        if ($request->has('status') && $request->status != -1) {
            array_push($where, array('is_verify', '=', $request->status));
        }
        if ($request->has('type') && $request->type != -1) {
            array_push($where, array('type', '=', $request->type));
        }

        $papers = HandoverPaper::where($where)
            ->paginate(10);

        $users = User::all();
        $assets = Asset::all();
        $this->authorize('view', HandoverPaper::class);
        return view('handover_paper/index', [
                'papers' => $papers,
                'users' => $users,
                'assets' => $assets,
                'number_of_report' => $request->number_of_report,
                'user_search' => $request->user,
                'asset_search' => $request->asset,
                'status' => $request->status,
                'type' => $request->type
            ]
        );
    }

    public function verify(Request $request) {
        $handoverPaper = HandoverPaper::find($request->id);
        if (!$handoverPaper) {
            return redirect()->back()->with('error', 'Handover Paper does not exist');
        }

        $handoverPaper->is_verify = !$handoverPaper->is_verify;
        $handoverPaper->save();

        return redirect()->back()->with('success', 'Updated successfully');
    }

  public function submitHandover(Request $request)
  {
    if ($request->hasFile('pdf-file')) 
    {
        $assetId = $request->input('assetId');
        $checkoutUser = $request->input('checkoutUser');
        $target = $request->input('target');
        $file = $request->file('pdf-file');

        $admin = User::find($checkoutUser);
        $targetuser = User::find($target);
        $asset = Asset::find($assetId);
        
        if ($admin && $targetuser)
        {
        

            $now = new \DateTime('NOW');
            $numberOfReport = $now->format('dmy');
            // $numberOfReport = "{$numberOfReport}-{$targetuser->employee_num}";
            $numberOfReport = "SGS-{$numberOfReport}-{$targetuser->employee_num}";

            $nameOfFile = "checkout-{$numberOfReport}-{$admin->employee_num}-{$targetuser->employee_num}";
            $msg = ('Create handover paper successful.');

            $path = $file->storeAs('temp',$nameOfFile);  
    
            // Tải file lên Google Drive
            $fileId = $this->uploadFileToGoogleDrive($path);
            $msg .= ' Here is link of handover paper <a href="https://docs.google.com/file/d/'. $fileId . '/view"> https://docs.google.com/file/d/'.$fileId .'/view  </a>';
            $link = 'https://docs.google.com/file/d/' . $fileId . '/view';
            $newHandoverPaper = new HandoverPaper([
                'link' => 'https://docs.google.com/file/d/'. $fileId . '/view',
                'sender_id' => $admin->id,
                'receiver_id' => $targetuser->id,
                'asset_tag' => $asset->asset_tag,
                'number_of_report' => $numberOfReport,
                'is_verify' => 0,
                'type' => 1
            ]);

            $newHandoverPaper->save();
            // Xóa file tạm thời
            Storage::delete($path);
            $data = [
                'msg' => $msg,
                'assetId' => $assetId,
                'checkoutUser' => $checkoutUser,
                'target' => $target,
                'linkfile' => $link
            ];
            return  response()->json(["filelink" => $link]);
            // return redirect()->route('hardware.index')->withInput(['success' => $msg]);
        }
    
         else {
            return redirect()->route('hardware.index')->with('error', 'Admin or target user not found.');
        }
    }

 
}
  
private function uploadFileToGoogleDrive($fileName, $mimeType = 'application/pdf') 
{
    // Tạo một đối tượng client
    $client = new Google_Client();
    $client->setAuthConfig(storage_path('snipeit.json'));
    $client->addScope(Google_Service_Drive::DRIVE);

    // Tạo một đối tượng service
    $service = new Google_Service_Drive($client);

    // Tạo một đối tượng file
    $file = new Google_Service_Drive_DriveFile();
    $file->setName($fileName);
    $file->setDescription('Your file description');
    $file->setMimeType($mimeType);
    $file->setParents(['1VRB1JFIofxW7dtLiXqjGLoF9abi7oAU3']); // Set the parent folder

    // Tải file lên Google Drive
    $result = $service->files->create($file, [
        'data' => file_get_contents(storage_path($fileName)),
        'mimeType' => $mimeType,
        'uploadType' => 'multipart'
    ]);

    // Trả về ID của file
    return $result->getId();
}


    public function previewHandoverPaper(Request $request)
    {
        $assetId = $request->input('asset_id');
        $asset = Asset::find($assetId);
        $IDasset = $asset->id;
        $checkoutData = $request->all();
        $checkoutUser = Auth::user();
        $fullNameUserCheckout = $checkoutUser->getFullNameAttribute();
        $checkoutUserID = $checkoutUser->id;
       // Find the user and get their full name
       $target = User::find($checkoutData['assigned_user']);
       $fullNameUserTarget = $target ? $target->getFullNameAttribute() : '';
       $targetUserID = $target->id;
       

        $now = new \DateTime('NOW');
        $checkoutDate = $now->format('d/m/Y');
        $numberOfReport = $now->format('dmy');
        $numberOfReport = "SGS-{$numberOfReport}-{$target->employee_num}";


        $settings = \App\Models\Setting::getSettings();

        if ($settings->full_multiple_companies_support){
            if ($target->company_id != $asset->company_id){
                return redirect()->to("hardware/$assetId/checkout")->with('error', trans('general.error_user_or_location_company'));
            }
        }

        $data = [
            'asset' => $asset,
            'assetId' => $IDasset,
            'fullNameUserTarget' => $fullNameUserTarget,
            'targetUserID' => $targetUserID,
            'fullNameUserCheckout' => $fullNameUserCheckout,
            'checkoutUserID' => $checkoutUserID,
            'checkoutDate' => $checkoutDate,
            'numberOfReport' => $numberOfReport
        ];
    
        return response()->json($data);
        // return view('handover_paper.preview', compact('asset', 'checkoutData', 'target','checkoutUser','now','numberOfReport'));
    }


    public function previewHandoverPaperBulkcheckout(Request $request)
    {
        try {
            $iserror = 0;
            $iserror2 = 0;

            $asset_ids = [];
            $asset_tags = [];
            $asset_names = [];
            $asset_notes = [];
            $asset_ids_arr = [];

            $asset_ids = array_filter($request->get('selected_assets'));

            if(!$request->get('assigned_user'))
            {
                $info ="Please choose User to checkout!";
                $iserror = 5;
                $error = [
                    'info' => $info,
                    'iserror' => $iserror
                ];
                return response()->json($error);
            }

            if (count($asset_ids) == 0 && !$request->get('bulk_assettag_assets')) {

                $info ="Please choose Assets to checkout!";
                $iserror = 4;
                $error = [
                    'info' => $info,
                    'iserror' => $iserror
                ];
                return response()->json($error);
            }

            if (count($asset_ids) > 0 && $request->get('bulk_assettag_assets')) {

                $info ="Please choose only option. Assets field or bulk_asset_tag";
                $iserror = 3;
                $error = [
                    'info' => $info,
                    'iserror' => $iserror
                ];
                return response()->json($error);
            }
            $checkoutUser = Auth::user();
            $fullNameUserCheckout = $checkoutUser->getFullNameAttribute();
            $checkoutUserID = $checkoutUser->id;

    
            $asset_ids = [];
            $asset_tags = [];
            $asset_names = [];
            $asset_notes = [];
            $asset_ids_arr = [];

            if (count($asset_ids) >= 1) {
                $asset_ids = array_filter($request->get('selected_assets'));
                $asset_ids_string = implode(',', $asset_ids);
                $asset_ids_arr = explode(',', $asset_ids_string);
            }

            if ($request->get('bulk_assettag_assets')) {
                $errorTagsUndeploy = [];
                $bulkAssetTags = $request->get('bulk_assettag_assets');
                $assetTags = preg_split('/\r\n|\r|\n/', $bulkAssetTags, -1, PREG_SPLIT_NO_EMPTY);         
                $asset_ids_arr = [];
                foreach ($assetTags as $tag) {
                    $asset = Asset::where('asset_tag', trim($tag))->first();
                    if (!$asset) {
                        $assetTagNotFound[] = $tag;
                        $iserror2 = 2;
                    } else if ( $asset->assigned_to || $asset->status_id != 2)
                    {
                        $assetTagNotFound = [];
                        $errorTagsUndeploy[] = $asset->asset_tag;
                        $iserror2 = 2;
                    }else {
                        $asset_ids_arr[] = $asset->id;
                    }
                }
            }


            foreach ($asset_ids_arr as $asset_id) {
                $asset = Asset::findOrFail($asset_id);
                $asset_tags[] = $asset->asset_tag;
                $asset_names[] = $asset->name;
                $asset_notes[] = $asset->notes;
            }
     
            $target = User::find($request->input('assigned_user'));
            $fullNameUserTarget = $target ? $target->getFullNameAttribute() : '';
            $targetUserID = $target->id;
                
            $length = count($asset_tags);

            $now = new \DateTime('NOW');
            $checkoutDate = $now->format('d/m/Y');
            $numberOfReport = $now->format('dmy');
            $numberOfReport = "SGS-{$numberOfReport}-{$target->employee_num}";
                
            $data = [

                'fullNameUserTarget' => $fullNameUserTarget,
                'iserror' => $iserror,
                'targetUserID' => $targetUserID,
                'fullNameUserCheckout' => $fullNameUserCheckout,
                'checkoutUserID' => $checkoutUserID,
                'checkoutDate' => $checkoutDate,
                'length' => $length,
                'asset_ids' => $asset_ids,
                'asset_ids_arr' => $asset_ids_arr,
                'asset_tags' => $asset_tags,
                'asset_names' => $asset_names,
                'asset_notes' => $asset_notes,
                'numberOfReport' => $numberOfReport
            ];
            
            $settings = \App\Models\Setting::getSettings();        
            $errAssettOtherCompany = [];
            if ($settings->full_multiple_companies_support){
                foreach ($asset_ids_arr as $asset_id) {
                    $asset = Asset::findOrFail($asset_id);
                    if ($target->company_id != $asset->company_id){
                        $iserror = 1;
                        $errAssettOtherCompany[] = $asset->asset_tag;
                    }
                }
            }


            if ($iserror == 1|$iserror2 == 2) {
                $error_info = "One or more selected assets for checkout do not belong to the same company as the person or location they are being transferred to";
                $lengtherrAssettOtherCompany = count($errAssettOtherCompany);
                if($iserror2 == 2)
                {
                    $lengtherrorTags = count($errorTagsUndeploy);
                    $lengthAssetTagNotFound = count($assetTagNotFound);
                    $iserror = 2;
                    $error1 = [
                        'error_info' => $error_info,
                        'iserror' => $iserror,
                        'lengtherrAssettOtherCompany' => $lengtherrAssettOtherCompany,
                        'errAssettOtherCompany' => $errAssettOtherCompany,
                        'lengtherrorTags' => $lengtherrorTags,
                        'errorTagsUndeploy' =>$errorTagsUndeploy,
                        'lengthAssetTagNotFound' => $lengthAssetTagNotFound,
                        'assetTagNotFound' =>$assetTagNotFound
                    ];
                    return response()->json($error1); // Trả về thông tin về lỗi nếu có lỗi
                }else if ($iserror2 != 2)
                {
                    $error2 = [
                        'error_info' => $error_info,
                        'iserror' => $iserror,
                        'lengtherrAssettOtherCompany' => $lengtherrAssettOtherCompany,
                        'errAssettOtherCompany' => $errAssettOtherCompany
                    ];
                    return response()->json($error2);
                }
     
            }else if($iserror == 0) {

                return response()->json($data);       
            }
            
            } catch (ModelNotFoundException $e) {
                return redirect()->route('hardware.bulkcheckout.show')->with('error', $e->getErrors());
            }
            
    }




    public function uploadPdf(Request $request)
    {
        if ($request->hasFile('pdf')) {

            $pdf = $request->file('pdf');
            $path=$pdf->storeAs('temp', 'uploaded.pdf');
            $fileId = $this->uploadFileToGoogleDrive($path);

            return response()->json(['message' => 'File uploaded successfully']);
        } else {
            return response()->json(['message' => 'No file uploaded'], 400);
        }
    }

}
