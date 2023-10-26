<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Requests\AssetCheckoutRequest;
use App\Models\User;
use App\Models\HandoverPaper;
use Dompdf\Dompdf;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;
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

        if ($request->has('type') && $request->type != -1) {
            array_push($where, array('is_verify', '=', $request->type));
        }

        $papers = HandoverPaper::where($where)
            ->paginate(10);

        $users = User::all();
        $this->authorize('view', HandoverPaper::class);
        return view('handover_paper/index', [
                'papers' => $papers,
                'users' => $users,
                'number_of_report' => $request->number_of_report,
                'user_search' => $request->user,
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

  public function submitHandover(Request $request, $assetId, $checkoutUser, $target)
  {
    if ($request->hasFile('pdf-file')) 
    {
        $file = $request->file('pdf-file');

        $admin = User::find($checkoutUser);
        $targetuser = User::find($target);
        
        if ($admin && $targetuser)
        {
        

            $now = new \DateTime('NOW');
            $numberOfReport = $now->format('dmy');
            $numberOfReport = "{$numberOfReport}-{$targetuser->employee_num}";
            $nameOfFile = "checkout-{$numberOfReport}-{$admin->employee_num}-{$targetuser->employee_num}";
            $msg = ('Create handover paper successful.');

            $path = $file->storeAs('temp',$nameOfFile);  
    
            // Tải file lên Google Drive
            $fileId = $this->uploadFileToGoogleDrive($path);
            $msg .= ' Here is link of handover paper <a href="https://docs.google.com/file/d/'. $fileId . '/view"> https://docs.google.com/file/d/'.$fileId .'/view  </a>';

            $newHandoverPaper = new HandoverPaper([
                'link' => 'https://docs.google.com/file/d/'. $fileId . '/view',
                'sender_id' => $admin->id,
                'receiver_id' => $targetuser->id,
                'number_of_report' => $numberOfReport,
                'is_verify' => 0,
                'type' => 1
            ]);

            $newHandoverPaper->save();
            // Xóa file tạm thời
            Storage::delete($path);
            return redirect()->route('hardware.index')->withInput(['success' => $msg]);
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


    public function previewHandoverPaper(AssetCheckoutRequest $request, $assetId)
    {
        $asset = Asset::find($assetId);
        $checkoutData = $request->all();
        $checkoutUser = Auth::user();

       // Find the user and get their full name
       $target = User::find($checkoutData['assigned_user']);
       $fullName = $target ? $target->getFullNameAttribute() : '';

        $now = new \DateTime('NOW');
        $numberOfReport = $now->format('dmy');
        $numberOfReport = "SGS-{$numberOfReport}-{$target->employee_num}";


        $settings = \App\Models\Setting::getSettings();

        if ($settings->full_multiple_companies_support){
            if ($target->company_id != $asset->company_id){
                return redirect()->to("hardware/$assetId/checkout")->with('error', trans('general.error_user_or_location_company'));
            }
        }


        return view('handover_paper.preview', compact('asset', 'checkoutData', 'target','checkoutUser','now','numberOfReport'));
    }

}
