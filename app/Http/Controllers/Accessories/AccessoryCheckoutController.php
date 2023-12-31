<?php

namespace App\Http\Controllers\Accessories;

use App\Events\CheckoutableCheckedOut;
use App\Http\Controllers\Controller;
use App\Models\Accessory;
use App\Models\Location;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class AccessoryCheckoutController extends Controller
{
    /**
     * Return the form to checkout an Accessory to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $accessoryId
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create($accessoryId)
    {
        // Check if the accessory exists
        if (is_null($accessory = Accessory::withCount('users as users_count')->find($accessoryId))) {
            // Redirect to the accessory management page with error
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.not_found'));
        }

        // Make sure there is at least one available to checkout
        if ($accessory->numRemaining() <= 0){
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.checkout.unavailable'));
        }
        
        if ($accessory->category) {
            $this->authorize('checkout', $accessory);

            // Get the dropdown of users and then pass it to the checkout view
            return view('accessories/checkout', compact('accessory'));
        }

        return redirect()->back()->with('error', 'The category type for this accessory is not valid. Edit the accessory and select a valid accessory category.');
    }

    /**
     * Save the Accessory checkout information.
     *
     * If Slack is enabled and/or asset acceptance is enabled, it will also
     * trigger a Slack message and send an email.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param Request $request
     * @param  int $accessoryId
     * @return Redirect
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, $accessoryId)
    {
        // Check if the accessory exists
        if (is_null($accessory = Accessory::withCount('users as users_count')->find($accessoryId))) {
            // Redirect to the accessory management page with error
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.user_not_found'));
        }

        $this->authorize('checkout', $accessory);
        $quantity = $request->input('qty');
        if (!isset($quantity) || !ctype_digit((string)$quantity) || $quantity <= 0) {
            $quantity = 1;
        }

        $settings = \App\Models\Setting::getSettings();


        $checkoutType = $request->input('checkout_to_type');

        if ($checkoutType === 'user') {

            if (!$user = User::find($request->input('assigned_to'))) {
                return redirect()->route('accessories.checkout.show', $accessory->id)->with('error', trans('admin/accessories/message.checkout.user_does_not_exist'));
            }
        
            // Make sure there is at least one available to checkout
            if ($accessory->numRemaining() <= 0 || $quantity > $accessory->numRemaining()){
                return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.checkout.unavailable'));
            }


            // Update the accessory data
            $accessory->assigned_to = e($request->input('assigned_to'));

            if ($settings->full_multiple_companies_support){
                if ($accessory->company_id != $user->company_id){
                    return redirect()->route('accessories.checkout.show', $accessory->id)->with('error', trans('admin/accessories/message.checkout.user_missmatch_accessory'));
                }
            }    

            $accessory->users()->attach($accessory->id, [
                'accessory_id' => $accessory->id,
                'created_at' => Carbon::now(),
                'user_id' => Auth::id(),
                'assigned_to' => $request->get('assigned_to'),
                'qty_checkedout' => e($request->input('qty')),
                'note' => $request->input('note'),
            ]);

            DB::table('accessories_users')->where('assigned_to', '=', $accessory->assigned_to)->where('accessory_id', '=', $accessory->id)->first();
            event(new CheckoutableCheckedOut($accessory, $user, Auth::user(), $request->input('note')));


        }elseif ($checkoutType === 'location') {

            if (!$location = Location::find($request->input('assigned_location'))) {
                return redirect()->route('accessories.checkout.show', $accessory->id)->with('error', trans('admin/accessories/message.checkout.location_does_not_exist'));
            }
            

            if ($accessory->numRemaining() <= 0 || $quantity > $accessory->numRemaining()){
                return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.checkout.unavailable'));
            }


            $accessory->assigned_to_location = e($request->input('assigned_location'));

            if ($settings->full_multiple_companies_support){
                if ($accessory->company_id != $location->company_id){
                    return redirect()->route('accessories.checkout.show', $accessory->id)->with('error', trans('admin/accessories/message.checkout.location_missmatch_accessory'));
                }
            }    

            $accessory->locations()->attach($accessory->id, [
                'accessory_id' => $accessory->id,
                'created_at' => Carbon::now(),
                'user_id' => Auth::id(),
                'assigned_to_location' => $request->get('assigned_location'),
                'qty_checkedout' => e($request->input('qty')),
                'notes' => $request->input('note'),
            ]);

            DB::table('accessories_locations')->where('assigned_to_location', '=', $accessory->assigned_to_location)->where('accessory_id', '=', $accessory->id)->first();
            
            event(new CheckoutableCheckedOut($accessory, $location, Auth::user(), $request->input('note')));

        }
        


        // Redirect to the new accessory page
        return redirect()->route('accessories.index')->with('success', trans('admin/accessories/message.checkout.success'));
    }




}
