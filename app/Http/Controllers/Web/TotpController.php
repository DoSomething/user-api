<?php

namespace Northstar\Http\Controllers\Web;

use OTPHP\TOTP;
use OTPHP\Factory;
use Endroid\QrCode\QrCode;
use Northstar\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TotpController extends Controller
{
    /**
     * Make a new TotpController, inject dependencies,
     * and set middleware for this controller's methods.
     */
    public function __construct()
    {
        $this->middleware('throttle', ['only' => ['verify']]);
        $this->middleware('auth:web', ['only' => ['create', 'store']]);
    }

    /**
     * Display the TOTP prompt.
     *
     * @return \Illuminate\Http\Response
     */
    public function prompt()
    {
        return view('totp.prompt');
    }

    /**
     * Verify a given TOTP code & user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        $id = session('totp.user');

        $this->validate($request, [
            'code' => 'required|numeric',
        ]);

        // If we aren't fulfilling a login prompt, then no code will be valid:
        if (! $id) {
            return back()->with('status', 'That code isn\'t valid. Try again!');
        }

        // Verify provided TOTP code for the user:
        $user = User::find($id);
        $totp = Factory::loadFromProvisioningUri($user->totp);
        if (! $totp->verify($request->code)) {
            return back()->with('status', 'That code isn\'t valid. Try again!');
        }

        // Authenticate the user & redirect to intended location:
        Auth::login($user, true);
        session()->forget('totp.user');

        return redirect()->intended('/');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();

        // Don't overwrite an existing code.
        if ($user->totp) {
            return redirect('/')->with('status', 'You\'ve already configured a two-factor device.');
        }

        // Create a TOTP provisioning URI:
        $totp = TOTP::create();
        $totp->setLabel(Auth::id());

        $uri = $totp->getProvisioningUri();
        $qr = new QrCode($uri);

        return view('totp.create', ['user' => $user, 'uri' => $uri, 'qr' => $qr]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $this->validate($request, [
            'uri' => 'required',
            'code' => 'required|integer',
        ]);

        // Don't overwrite an existing code.
        if ($user->totp) {
            return redirect('/')->with('status', 'You\'ve already configured a two-factor device.');
        }

        // Verify the provided code & provisoning URI:
        $otp = Factory::loadFromProvisioningUri($request->uri);
        if (! $otp->verify($request->code)) {
            return back()->with('status', 'That code isn\'t valid. Try again!');
        }

        // Store the TOTP provisioning URI on the user now that
        // we know they've set it up on their authenticator.
        $user->totp = $request->uri;
        $user->save();

        return redirect('/')->with('status', 'You now have two-factor authentication enabled!');
    }
}
