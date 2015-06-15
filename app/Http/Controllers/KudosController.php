<?php namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Services\DrupalAPI;
use Northstar\Models\User;


class KudosController extends Controller
{

    public function __construct(DrupalAPI $drupal)
    {
        $this->drupal = $drupal;
    }

    public function store(Request $request)
    {
        $user = User::current();

        $drupal_id = $user->drupal_id;

        dd($request->reportback_item_id);

        $kudos = $this->drupal->storeKudos($drupal_id, $request);

        return $this->respond('Kudos Saved!');
    }

    // public function destroy(Request $request)
    // {
    //     $user = User::where('_id', $id)->first();

    //     if ($user instanceof User) {
    //         $user->delete();

    //         return $this->respond('No Content.');
    //     } else {
    //         throw new NotFoundHttpException('The resource does not exist.');
    //     }
    // }

}

