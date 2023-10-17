<?php

namespace App\Http\Controllers\Accessories;

use App\Events\CheckoutableCheckedIn;
use App\Http\Controllers\Controller;
use App\Models\Accessory;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccessoryCheckinController extends Controller
{
    /**
     * Check the accessory back into inventory
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param Request $request
     * @param int $accessoryUserId
     * @param string $backto
     * @return View
     * @internal param int $accessoryId
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create($accessoryUserId = null, $backto = null)
    {
        // Check if the accessory exists
        if (is_null($accessory_user = DB::table('accessories_users')->find($accessoryUserId))) {
            // Redirect to the accessory management page with error
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.not_found'));
        }

        $accessory = Accessory::find($accessory_user->accessory_id);
        $this->authorize('checkin', $accessory);

        $qty_checkedout = $accessory_user->qty_checkedout;


        return view('accessories/checkin', compact('accessory','qty_checkedout'))->with('backto', $backto);
    }

    public function create2($accessoryLocationId = null, $backto = null)
    {
        // Check if the accessory exists
        if (is_null($accessory_location = DB::table('accessories_locations')->find($accessoryLocationId))) {
            // Redirect to the accessory management page with error
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.not_found'));
        }

        $accessory = Accessory::find($accessory_location->accessory_id);
        $this->authorize('checkin', $accessory);

        // Get the quantity checked out
        $qty_checkedout = $accessory_location->qty_checkedout;


        return view('accessories/checkin', compact('accessory','qty_checkedout'))->with('backto', $backto);
    }

    /**
     * Check in the item so that it can be checked out again to someone else
     *
     * @uses Accessory::checkin_email() to determine if an email can and should be sent
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param null $accessoryUserId
     * @param string $backto
     * @return Redirect
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @internal param int $accessoryId
     */

     public function store(Request $request, $accessoryUserId = null, $backto = null)
     {
         // Check if the accessory exists
         if (is_null($accessory_user = DB::table('accessories_users')->find($accessoryUserId))) {
             // Redirect to the accessory management page with error
             return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.does_not_exist'));
         }
 
         $accessory = Accessory::find($accessory_user->accessory_id);
 
         $this->authorize('checkin', $accessory);
 
         $checkin_at = date('Y-m-d');
         if ($request->filled('checkin_at')) {
             $checkin_at = $request->input('checkin_at');
         }
 
         // Get the quantity to check-in from the request
         $qty_to_checkin = $request->input('qty_to_checkin');
 
           // Ensure the quantity to check-in does not exceed the quantity checked out
         if ($qty_to_checkin > $accessory_user->qty_checkedout) {
             return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.checkin.error'));
         }
 
         // Update the quantity checked out
         $new_qty = $accessory_user->qty_checkedout - $qty_to_checkin;
 
         // If the new quantity is 0, delete the record, otherwise update the quantity
         if ($new_qty == 0) {
             $delete = DB::table('accessories_users')->where('id', '=', $accessory_user->id)->delete();
         } else {
             $delete = DB::table('accessories_users')->where('id', '=', $accessory_user->id)->update(['qty_checkedout' => $new_qty]);
         }
         
         // Was the accessory updated?
         if ($delete) {
             $return_to = e($accessory_user->assigned_to);
 
             event(new CheckoutableCheckedIn($accessory, User::find($return_to), Auth::user(), $request->input('note'), $checkin_at));
 
             return redirect()->route('accessories.show', $accessory->id)->with('success', trans('admin/accessories/message.checkin.success'));
         }
         // Redirect to the accessory management page with error
         return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.checkin.error'));
     }

    public function store2(Request $request, $accessoryLocationId = null, $backto = null)
    {
        // Check if the accessory exists
        if (is_null($accessory_location = DB::table('accessories_locations')->find($accessoryLocationId))) {
            // Redirect to the accessory management page with error
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.does_not_exist'));
        }

        $accessory = Accessory::find($accessory_location->accessory_id);

        $this->authorize('checkin', $accessory);

        $checkin_at = date('Y-m-d');
        if ($request->filled('checkin_at')) {
            $checkin_at = $request->input('checkin_at');
        }

        // Get the quantity to check-in from the request
        $qty_to_checkin = $request->input('qty_to_checkin');

        // Ensure the quantity to check-in does not exceed the quantity checked out
        if ($qty_to_checkin > $accessory_location->qty_checkedout) {
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.checkin.error'));
        }

        // Update the quantity checked out
        $new_qty = $accessory_location->qty_checkedout - $qty_to_checkin;

        // If the new quantity is 0, delete the record, otherwise update the quantity
        if ($new_qty == 0) {
            $delete = DB::table('accessories_locations')->where('id', '=', $accessory_location->id)->delete();
        } else {
            $delete = DB::table('accessories_locations')->where('id', '=', $accessory_location->id)->update(['qty_checkedout' => $new_qty]);
        }

        // Was the accessory updated?
        if ($delete) {
            $return_to = e($accessory_location->assigned_to_location);

            event(new CheckoutableCheckedIn($accessory, Location::find($return_to), Auth::user(), $request->input('note'), $checkin_at));

            return redirect()->route('accessories.show', $accessory->id)->with('success', trans('admin/accessories/message.checkin.success'));
        }
        // Redirect to the accessory management page with error
        return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.checkin.error'));
    }

    //store3 is backup for function store2

    public function store3(Request $request, $accessoryLocationId = null, $backto = null)
    {
        // Check if the accessory exists
        if (is_null($accessory_location = DB::table('accessories_locations')->find($accessoryLocationId))) {
            // Redirect to the accessory management page with error
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.does_not_exist'));
        }

        $accessory = Accessory::find($accessory_location->accessory_id);

        $this->authorize('checkin', $accessory);

        $checkin_at = date('Y-m-d');
        if ($request->filled('checkin_at')) {
            $checkin_at = $request->input('checkin_at');
        }

        // Was the accessory updated?
        if (DB::table('accessories_locations')->where('id', '=', $accessory_location->id)->delete()) {
            $return_to = e($accessory_location->assigned_to_location);

            event(new CheckoutableCheckedIn($accessory, Location::find($return_to), Auth::user(), $request->input('note'), $checkin_at));

            return redirect()->route('accessories.show', $accessory->id)->with('success', trans('admin/accessories/message.checkin.success'));
        }
        // Redirect to the accessory management page with error
        return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.checkin.error'));
    }
    //store4 is backup for function store
    public function store4(Request $request, $accessoryUserId = null, $backto = null)
    {
        // Check if the accessory exists
        if (is_null($accessory_user = DB::table('accessories_users')->find($accessoryUserId))) {
            // Redirect to the accessory management page with error
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.does_not_exist'));
        }

        $accessory = Accessory::find($accessory_user->accessory_id);

        $this->authorize('checkin', $accessory);

        $checkin_at = date('Y-m-d');
        if ($request->filled('checkin_at')) {
            $checkin_at = $request->input('checkin_at');
        }

        // Was the accessory updated?
        if (DB::table('accessories_users')->where('id', '=', $accessory_user->id)->delete()) {
            $return_to = e($accessory_user->assigned_to);

            event(new CheckoutableCheckedIn($accessory, User::find($return_to), Auth::user(), $request->input('note'), $checkin_at));

            return redirect()->route('accessories.show', $accessory->id)->with('success', trans('admin/accessories/message.checkin.success'));
        }
        // Redirect to the accessory management page with error
        return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.checkin.error'));
    }


}
